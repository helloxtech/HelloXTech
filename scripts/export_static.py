#!/usr/bin/env python3
import base64
import html as html_lib
import json
import os
import re
import sys
import time
from collections import deque
from pathlib import Path
from typing import Optional
from urllib.parse import urljoin, urlparse, urldefrag, unquote

import requests
from bs4 import BeautifulSoup

DEFAULT_BASE_URL = "https://hellox.ca/"
PRIMARY_DOMAIN = "hellox.ca"
USER_AGENT = (
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
    "AppleWebKit/537.36 (KHTML, like Gecko) "
    "Chrome/122.0.0.0 Safari/537.36"
)

ASSET_EXTENSIONS = {
    ".css", ".js", ".png", ".jpg", ".jpeg", ".webp", ".gif", ".svg", ".ico",
    ".woff", ".woff2", ".ttf", ".eot", ".otf", ".mp4", ".webm", ".mp3",
    ".json", ".xml", ".pdf", ".txt", ".map", ".webmanifest"
}

SKIP_URL_PREFIXES = ("mailto:", "tel:", "javascript:", "data:")
URL_PREFIXES = ("/", "./", "../", "http://", "https://", "//")

URL_IN_CSS_RE = re.compile(r"url\(([^)]+)\)", re.IGNORECASE)
CSS_IMPORT_RE = re.compile(r"@import\s+(?:url\()?['\"]?([^'\")]+)", re.IGNORECASE)

URL_ATTRS = {
    "src",
    "href",
    "poster",
    "action",
    "data-src",
    "data-lazy-src",
    "data-ll-src",
    "data-ultimate-src",
    "data-bg",
    "data-bg-src",
    "data-background",
    "data-background-image",
    "data-url",
    "data-link",
    "data-href",
    "data-video",
    "data-video-src",
    "data-two_delay_src",
    "xlink:href",
}

SRCSET_ATTRS = {
    "srcset",
    "data-srcset",
    "data-lazy-srcset",
    "data-ll-srcset",
    "imagesrcset",
}

LAZY_SRC_ATTRS = {
    "data-src",
    "data-lazy-src",
    "data-ll-src",
    "data-ultimate-src",
}

LAZY_SRCSET_ATTRS = {
    "data-srcset",
    "data-lazy-srcset",
    "data-ll-srcset",
}

LAZY_SIZES_ATTRS = {
    "data-sizes",
}


def log(msg: str) -> None:
    print(msg, flush=True)


def normalize_url(url: str, base_url: str) -> str:
    if not url:
        return ""
    if url.startswith(SKIP_URL_PREFIXES):
        return url
    url = clean_url_artifacts(url)
    absolute = urljoin(base_url, url)
    absolute, _frag = urldefrag(absolute)
    return absolute


def is_internal(url: str) -> bool:
    try:
        parsed = urlparse(url)
    except Exception:
        return False
    host = (parsed.hostname or "").lower()
    return host.endswith(PRIMARY_DOMAIN)


def looks_like_url(value: str) -> bool:
    return value.startswith(URL_PREFIXES)


def clean_url_artifacts(value: str) -> str:
    cleaned = value.replace("#038;", "&").replace("&#038;", "&")
    cleaned = cleaned.replace("&display=swap&display=swap", "&display=swap")
    return cleaned


def is_asset(url: str) -> bool:
    path = urlparse(url).path.lower()
    for ext in ASSET_EXTENSIONS:
        if path.endswith(ext):
            return True
    return False


def url_to_path(url: str, out_dir: Path, content_type: Optional[str]) -> Path:
    parsed = urlparse(url)
    path = parsed.path
    if not path or path.endswith("/"):
        path = f"{path}index.html"
    else:
        ext = Path(path).suffix.lower()
        if not ext and (content_type or "").startswith("text/html"):
            path = f"{path}/index.html"
    if path.startswith("/"):
        path = path[1:]
    return out_dir / path


def to_root_relative(url: str) -> str:
    parsed = urlparse(url)
    path = parsed.path or "/"
    query = f"?{parsed.query}" if parsed.query else ""
    return f"{path}{query}"


def rewrite_srcset(value: str, base_url: str) -> str:
    parts = []
    for item in value.split(","):
        item = item.strip()
        if not item:
            continue
        if " " in item:
            url_part, descriptor = item.split(" ", 1)
        else:
            url_part, descriptor = item, ""
        abs_url = normalize_url(url_part, base_url)
        if abs_url and is_internal(abs_url):
            url_part = to_root_relative(abs_url)
        parts.append(f"{url_part} {descriptor}".strip())
    return ", ".join(parts)


def rewrite_srcset_and_enqueue(value: str, base_url: str, enqueue) -> str:
    parts = []
    for item in value.split(","):
        item = item.strip()
        if not item:
            continue
        if " " in item:
            url_part, descriptor = item.split(" ", 1)
        else:
            url_part, descriptor = item, ""
        abs_url = normalize_url(url_part, base_url)
        if abs_url and is_internal(abs_url):
            enqueue(abs_url)
            url_part = to_root_relative(abs_url)
        parts.append(f"{url_part} {descriptor}".strip())
    return ", ".join(parts)


def extract_json_assignment(text: str, var_name: str) -> Optional[str]:
    idx = text.find(var_name)
    if idx == -1:
        return None
    start = text.find("{", idx)
    if start == -1:
        return None
    depth = 0
    in_str = False
    escape = False
    for i in range(start, len(text)):
        ch = text[i]
        if in_str:
            if escape:
                escape = False
            elif ch == "\\":
                escape = True
            elif ch == "\"":
                in_str = False
        else:
            if ch == "\"":
                in_str = True
            elif ch == "{":
                depth += 1
            elif ch == "}":
                depth -= 1
                if depth == 0:
                    return text[start:i + 1]
    return None


def decode_inline_code(code: str) -> str:
    if not code:
        return ""
    try:
        decoded = base64.b64decode(code)
    except Exception:
        return code
    try:
        return unquote(decoded.decode("utf-8", errors="replace"))
    except Exception:
        return decoded.decode("utf-8", errors="replace")


def extract_urls_from_css(css_text: str, base_url: str) -> set[str]:
    found = set()
    for match in URL_IN_CSS_RE.finditer(css_text):
        raw = match.group(1).strip().strip("\"'")
        if not raw or raw.startswith(SKIP_URL_PREFIXES) or not looks_like_url(raw):
            continue
        found.add(normalize_url(raw, base_url))
    for match in CSS_IMPORT_RE.finditer(css_text):
        raw = match.group(1).strip().strip("\"'")
        if not raw or raw.startswith(SKIP_URL_PREFIXES) or not looks_like_url(raw):
            continue
        found.add(normalize_url(raw, base_url))
    return found


def rewrite_css_urls(css_text: str, base_url: str) -> str:
    def repl(match: re.Match) -> str:
        raw = match.group(1).strip().strip("\"'")
        if not raw or raw.startswith(SKIP_URL_PREFIXES) or not looks_like_url(raw):
            return match.group(0)
        abs_url = normalize_url(raw, base_url)
        if abs_url and is_internal(abs_url):
            return f"url('{to_root_relative(abs_url)}')"
        return match.group(0)

    return URL_IN_CSS_RE.sub(repl, css_text)


def rewrite_html(html: str, page_url: str, enqueue) -> str:
    soup = BeautifulSoup(html, "lxml")

    def handle_attr(tag, attr: str):
        val = tag.get(attr)
        if not val:
            return
        if isinstance(val, list):
            return
        if not looks_like_url(val):
            return
        if val.startswith(SKIP_URL_PREFIXES) or val.startswith("#"):
            return
        abs_url = normalize_url(val, page_url)
        if not abs_url:
            return
        if is_internal(abs_url):
            enqueue(abs_url)
            tag[attr] = to_root_relative(abs_url)

    def promote_lazy_attr(tag, lazy_attr: str, target_attr: str):
        val = tag.get(lazy_attr)
        if not val:
            return
        handle_attr(tag, lazy_attr)
        current = tag.get(target_attr, "")
        if not current or current.startswith("data:"):
            tag[target_attr] = tag.get(lazy_attr)
        tag.attrs.pop(lazy_attr, None)

    for tag in soup.find_all(True):
        for attr in URL_ATTRS:
            if tag.get(attr):
                handle_attr(tag, attr)

        for attr in SRCSET_ATTRS:
            if tag.get(attr):
                tag[attr] = rewrite_srcset_and_enqueue(tag[attr], page_url, enqueue)

        for lazy_attr in LAZY_SRC_ATTRS:
            if tag.get(lazy_attr):
                promote_lazy_attr(tag, lazy_attr, "src")

        for lazy_attr in LAZY_SRCSET_ATTRS:
            if tag.get(lazy_attr):
                tag[lazy_attr] = rewrite_srcset_and_enqueue(tag[lazy_attr], page_url, enqueue)
                if not tag.get("srcset"):
                    tag["srcset"] = tag[lazy_attr]
                tag.attrs.pop(lazy_attr, None)

        for lazy_attr in LAZY_SIZES_ATTRS:
            if tag.get(lazy_attr) and not tag.get("sizes"):
                tag["sizes"] = tag.get(lazy_attr)
                tag.attrs.pop(lazy_attr, None)

        if tag.name == "meta" and tag.get("content"):
            handle_attr(tag, "content")

        style_attr = tag.get("style")
        if style_attr and isinstance(style_attr, str) and "url(" in style_attr:
            tag["style"] = rewrite_css_urls(style_attr, page_url)

    for style_tag in soup.find_all("style"):
        if style_tag.string and "url(" in style_tag.string:
            style_tag.string.replace_with(rewrite_css_urls(style_tag.string, page_url))

    replaced_js = False
    replaced_css = False

    for script in list(soup.find_all("script")):
        text = script.string or script.get_text() or ""
        if "two_worker_data_js" in text:
            json_str = extract_json_assignment(text, "two_worker_data_js")
            if json_str:
                try:
                    data = json.loads(html_lib.unescape(json_str))
                except json.JSONDecodeError:
                    data = None
                if data:
                    new_tags = []
                    for item in data.get("js", []):
                        if item.get("inline"):
                            code = decode_inline_code(item.get("code", ""))
                            new_script = soup.new_tag("script")
                            new_script.string = code
                        else:
                            url = clean_url_artifacts(item.get("url", ""))
                            if not url:
                                continue
                            abs_url = normalize_url(url, page_url)
                            if abs_url and is_internal(abs_url):
                                enqueue(abs_url)
                                url = to_root_relative(abs_url)
                            new_script = soup.new_tag("script", src=url)
                        if item.get("id"):
                            new_script["id"] = item["id"]
                        new_tags.append(new_script)
                    for new_tag in reversed(new_tags):
                        script.insert_after(new_tag)
                    script.decompose()
                    replaced_js = True
        if "two_worker_data_css" in text:
            json_str = extract_json_assignment(text, "two_worker_data_css")
            if json_str:
                try:
                    data = json.loads(html_lib.unescape(json_str))
                except json.JSONDecodeError:
                    data = None
                if data:
                    new_links = []
                    for item in data.get("css", []):
                        href = clean_url_artifacts(item.get("url", ""))
                        if not href:
                            continue
                        abs_url = normalize_url(href, page_url)
                        if abs_url and is_internal(abs_url):
                            enqueue(abs_url)
                            href = to_root_relative(abs_url)
                        link = soup.new_tag("link", rel="stylesheet", href=href)
                        media = item.get("media")
                        if media and media != "all":
                            link["media"] = media
                        new_links.append(link)
                    for link in reversed(new_links):
                        script.insert_after(link)
                    script.decompose()
                    replaced_css = True

    if replaced_js:
        for tag in list(soup.find_all("script")):
            if tag.get("data-two_delay_src") or tag.get("data-two_delay_id"):
                tag.decompose()

    if replaced_css:
        for tag in list(soup.find_all("noscript")):
            if tag.find("link"):
                tag.decompose()

    for script in list(soup.find_all("script")):
        text = script.string or script.get_text() or ""
        if "two_worker_data_" in text:
            script.decompose()

    for script in list(soup.find_all("script")):
        text = script.string or script.get_text() or ""
        if "wp-emoji-release.min.js" in text or "wpEmojiSettings" in text:
            script.decompose()

    return str(soup)


def discover_sitemap_urls(base_url: str, session: requests.Session) -> list[str]:
    sitemap_candidates = [
        urljoin(base_url, "sitemap.xml"),
        urljoin(base_url, "wp-sitemap.xml"),
    ]
    urls: list[str] = []
    for candidate in sitemap_candidates:
        try:
            resp = session.get(candidate, timeout=20)
        except requests.RequestException:
            continue
        if resp.status_code != 200:
            continue
        for loc in re.findall(r"<loc>([^<]+)</loc>", resp.text):
            loc = loc.strip()
            if loc:
                urls.append(loc)
    return urls


def main() -> int:
    base_url = os.environ.get("BASE_URL", DEFAULT_BASE_URL)
    out_dir = os.environ.get("OUT_DIR")
    if out_dir:
        out_dir = Path(out_dir).expanduser()
    else:
        out_dir = Path(__file__).resolve().parents[1] / "static-site"

    session = requests.Session()
    session.headers.update({"User-Agent": USER_AGENT})

    out_dir.mkdir(parents=True, exist_ok=True)

    queue = deque()
    seen = set()

    base_url = normalize_url(base_url, base_url)
    queue.append(base_url)

    sitemap_urls = discover_sitemap_urls(base_url, session)
    for url in sitemap_urls:
        queue.append(normalize_url(url, base_url))

    log(f"Exporting from {base_url}")
    log(f"Output dir: {out_dir}")

    downloaded = 0
    while queue:
        url = queue.popleft()
        if not url or url.startswith(SKIP_URL_PREFIXES):
            continue
        if not is_internal(url):
            continue
        canonical = url.split("?", 1)[0]
        if canonical in seen:
            continue
        seen.add(canonical)

        try:
            resp = session.get(url, timeout=30)
        except requests.RequestException as exc:
            log(f"[warn] failed {url}: {exc}")
            continue

        if resp.status_code >= 400:
            log(f"[warn] {resp.status_code} {url}")
            continue

        content_type = resp.headers.get("content-type", "").split(";")[0].strip().lower()
        data = resp.content

        save_path = url_to_path(url, out_dir, content_type)
        save_path.parent.mkdir(parents=True, exist_ok=True)

        if content_type == "text/html" or save_path.suffix.lower() in {".html", ".htm"}:
            html = resp.text

            def enqueue(new_url: str):
                if not new_url:
                    return
                if not is_internal(new_url):
                    return
                if new_url.split("?", 1)[0] in seen:
                    return
                queue.append(new_url)

            html = rewrite_html(html, url, enqueue)
            save_path.write_text(html, encoding="utf-8")

            # Also enqueue asset URLs found in inline styles and linked CSS
            for css_url in extract_urls_from_css(html, url):
                if is_internal(css_url):
                    queue.append(css_url)
        elif content_type == "text/css" or save_path.suffix.lower() == ".css":
            text = resp.text
            for asset_url in extract_urls_from_css(text, url):
                if is_internal(asset_url):
                    queue.append(asset_url)
            text = rewrite_css_urls(text, url)
            save_path.write_text(text, encoding="utf-8")
        else:
            save_path.write_bytes(data)

        downloaded += 1
        if downloaded % 20 == 0:
            log(f"Downloaded {downloaded} files...")
        time.sleep(0.1)

    log(f"Done. Downloaded {downloaded} files.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
