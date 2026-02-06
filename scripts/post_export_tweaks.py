#!/usr/bin/env python3
import shutil
from datetime import datetime, timezone
from pathlib import Path
from bs4 import BeautifulSoup

ROOT = Path('/Users/hellox/Library/CloudStorage/OneDrive-HelloX/GitHub/HelloXTech/static-site')
EMAIL = 'Info@HelloX.ca'
FORM_ACTION = f"https://formsubmit.co/{EMAIL.lower()}"
OVERRIDES_CSS_PATH = ROOT / 'overrides.css'

FORM_HTML = f"""
<div class=\"elementor-element elementor-element-hxcontactform elementor-widget elementor-widget-text-editor\" data-element_type=\"widget\" data-id=\"hxcontactform\" data-widget_type=\"text-editor.default\">
  <div class=\"elementor-widget-container\">
    <style>
      .hx-contact-form {{ margin-top: 20px; }}
      .hx-contact-form label {{ display: block; font-weight: 600; margin-bottom: 6px; }}
      .hx-contact-form input,
      .hx-contact-form select,
      .hx-contact-form textarea {{
        width: 100%;
        border: 1px solid #d7dbe2;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 16px;
        font-family: inherit;
        background: #fff;
      }}
      .hx-contact-form textarea {{ min-height: 140px; resize: vertical; }}
      .hx-form-grid {{ display: grid; gap: 12px; }}
      .hx-form-grid .full {{ grid-column: 1 / -1; }}
      @media (min-width: 768px) {{
        .hx-form-grid {{ grid-template-columns: 1fr 1fr; }}
      }}
      .hx-contact-form .elementor-button {{ margin-top: 12px; }}
      .hx-form-note {{ margin-top: 10px; font-size: 14px; color: #667085; }}
    </style>
    <form class=\"hx-contact-form\" action=\"{FORM_ACTION}\" method=\"POST\">
      <input type=\"hidden\" name=\"_subject\" value=\"New lead from HelloX.ca\">
      <input type=\"hidden\" name=\"_captcha\" value=\"false\">
      <input type=\"hidden\" name=\"_template\" value=\"table\">
      <input type=\"hidden\" name=\"_next\" value=\"https://hellox.ca/contact-us/\">
      <div class=\"hx-form-grid\">
        <div>
          <label for=\"hx-name\">Full Name</label>
          <input id=\"hx-name\" name=\"name\" type=\"text\" autocomplete=\"name\" required>
        </div>
        <div>
          <label for=\"hx-email\">Work Email</label>
          <input id=\"hx-email\" name=\"email\" type=\"email\" autocomplete=\"email\" required>
        </div>
        <div>
          <label for=\"hx-company\">Company</label>
          <input id=\"hx-company\" name=\"company\" type=\"text\" autocomplete=\"organization\" required>
        </div>
        <div>
          <label for=\"hx-phone\">Phone (optional)</label>
          <input id=\"hx-phone\" name=\"phone\" type=\"tel\" autocomplete=\"tel\">
        </div>
        <div class=\"full\">
          <label for=\"hx-interest\">How can we help?</label>
          <select id=\"hx-interest\" name=\"interest\" required>
            <option value=\"\" disabled selected>Select a service</option>
            <option value=\"Dynamics 365\">Dynamics 365</option>
            <option value=\"Power Platform\">Power Platform</option>
            <option value=\"CRM & Sales\">CRM & Sales</option>
            <option value=\"Implementation & Integration\">Implementation & Integration</option>
            <option value=\"Support & Training\">Support & Training</option>
            <option value=\"Other\">Other</option>
          </select>
        </div>
        <div class=\"full\">
          <label for=\"hx-message\">Project Details</label>
          <textarea id=\"hx-message\" name=\"message\" required></textarea>
        </div>
      </div>
      <button type=\"submit\" class=\"elementor-button elementor-size-sm\">
        <span class=\"elementor-button-content-wrapper\"><span class=\"elementor-button-text\">Send Message</span></span>
      </button>
      <p class=\"hx-form-note\">We will respond within 1-2 business days.</p>
    </form>
  </div>
</div>
"""


def remove_signup_links(soup: BeautifulSoup) -> None:
    for a in soup.find_all('a', href=True):
        href = a['href']
        if href.startswith('/sign-up'):
            li = a.find_parent('li')
            if li:
                li.decompose()
            else:
                a.decompose()


def adjust_header_ctas(soup: BeautifulSoup) -> None:
    header = soup.find(id='header')
    if not header:
        return

    # Remove the header "Learn More" CTA that duplicates About Us.
    for a in header.find_all('a', href=True):
        href = a['href']
        text = a.get_text(strip=True)
        if href.startswith('/about-us') and text == 'Learn More':
            widget = a.find_parent(class_='elementor-widget-button')
            if widget:
                widget.decompose()
            else:
                a.decompose()

    # Ensure Contact Us CTA shows on mobile/tablet by removing hidden classes.
    for a in header.find_all('a', href=True):
        href = a['href']
        if href.startswith('/contact-us'):
            node = a
            while node and node != header:
                if hasattr(node, 'get'):
                    classes = node.get('class') or []
                    if 'elementor-hidden-mobile' in classes or 'elementor-hidden-tablet' in classes:
                        classes = [c for c in classes if c not in ('elementor-hidden-mobile', 'elementor-hidden-tablet')]
                        if classes:
                            node['class'] = classes
                        else:
                            node.attrs.pop('class', None)
                node = node.parent


def insert_contact_form(soup: BeautifulSoup) -> None:
    if soup.find(class_='hx-contact-form'):
        return
    target = soup.find('div', {'data-id': 'thf727d1'})
    if not target:
        return
    form_soup = BeautifulSoup(FORM_HTML, 'lxml')
    fragment = form_soup.body if form_soup.body else form_soup
    children = list(fragment.contents)
    for node in reversed(children):
        if getattr(node, 'name', None) is not None or str(node).strip():
            target.insert_after(node)


def update_contact_email(soup: BeautifulSoup) -> None:
    for a in soup.find_all('a', href=True):
        if a.get('data-cfemail') or a['href'].startswith('/cdn-cgi/l/email-protection'):
            a['href'] = f'mailto:{EMAIL}'
            a.string = EMAIL
            a.attrs.pop('data-cfemail', None)
            if 'class' in a.attrs:
                    a.attrs['class'] = [c for c in a.attrs['class'] if c != '__cf_email__']


def ensure_overrides_css() -> None:
    css = """/* Shared mobile fixes */\n@media (max-width: 767px) {\n  /* Prevent hero background from cropping the HelloX image on mobile */\n  #hero {\n    background-size: contain !important;\n    background-position: center top !important;\n    background-repeat: no-repeat !important;\n  }\n\n  /* Reduce large gaps between Resource cards */\n  .elementor-page-123 .elementor-element-dkn22tik .elementor-posts-container {\n    row-gap: 16px !important;\n    grid-row-gap: 16px !important;\n  }\n  .elementor-page-123 .elementor-element-dkn22tik .elementor-post {\n    margin-bottom: 0 !important;\n  }\n  .elementor-page-123 .elementor-element-dkn22tik .elementor-post__thumbnail {\n    margin-bottom: 12px !important;\n  }\n}\n"""
    OVERRIDES_CSS_PATH.write_text(css, encoding='utf-8')


def inject_overrides_link(soup: BeautifulSoup) -> None:
    head = soup.find('head')
    if not head:
        return
    for link in head.find_all('link', href=True):
        if link['href'] == '/overrides.css':
            return
    new_link = soup.new_tag('link', rel='stylesheet', href='/overrides.css')
    head.append(new_link)


def main() -> None:
    signup_dir = ROOT / 'sign-up'
    if signup_dir.exists():
        shutil.rmtree(signup_dir)

    ensure_overrides_css()

    for html_path in ROOT.rglob('*.html'):
        soup = BeautifulSoup(html_path.read_text(encoding='utf-8'), 'lxml')
        remove_signup_links(soup)
        adjust_header_ctas(soup)
        inject_overrides_link(soup)

        if html_path.parent.name == 'contact-us' and html_path.name == 'index.html':
            update_contact_email(soup)
            insert_contact_form(soup)

        html_path.write_text(str(soup), encoding='utf-8')

    # Build a clean sitemap from the static files we actually serve.
    exclude_dirs = {'feed'}
    exclude_files = {'404.html', 'sitemap.xml'}
    urls = []
    for path in ROOT.rglob('*.html'):
        rel = path.relative_to(ROOT)
        if path.name in exclude_files or path.name.startswith('wp-sitemap'):
            continue
        if any(part in exclude_dirs for part in rel.parts):
            continue
        if path.name == 'index.html':
            parent = rel.parent.as_posix()
            if parent == '.':
                url = 'https://hellox.ca/'
            else:
                url = f'https://hellox.ca/{parent}/'
        else:
            url = f'https://hellox.ca/{rel.as_posix()}'
        mtime = datetime.fromtimestamp(path.stat().st_mtime, tz=timezone.utc).date().isoformat()
        urls.append((url, mtime))

    urls = sorted(set(urls))
    items = [f'  <url><loc>{url}</loc><lastmod>{lastmod}</lastmod></url>' for url, lastmod in urls]
    sitemap = (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
        + '\n'.join(items)
        + '\n</urlset>\n'
    )
    (ROOT / 'sitemap.xml').write_text(sitemap, encoding='utf-8')

    # Remove old WordPress sitemap files if present.
    for wp in ROOT.glob('wp-sitemap-*.xml'):
        wp.unlink()

    # Update robots.txt
    robots = """User-agent: *
Allow: /
Sitemap: https://hellox.ca/sitemap.xml
"""
    (ROOT / 'robots.txt').write_text(robots, encoding='utf-8')


if __name__ == '__main__':
    main()
    print('Post-export tweaks applied.')
