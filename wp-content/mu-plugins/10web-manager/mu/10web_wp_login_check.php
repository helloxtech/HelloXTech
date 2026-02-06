<?php

if (isset($_SERVER["HTTP_USER_AGENT"]) && (strpos($_SERVER["REQUEST_URI"], 'wp-login.php') !== false
    or strpos($_SERVER["HTTP_USER_AGENT"], 'bot') !== false
    or strpos($_SERVER["HTTP_USER_AGENT"], 'crawler') !== false)) {

    define('DISABLE_WP_CRON', 'true');
}

if (
    strpos($_SERVER["REQUEST_URI"], 'wp-login.php') !== false
    and $_SERVER["REQUEST_METHOD"] == "POST"
    and
    (
        !isset($_SERVER["HTTP_REFERER"]) or
        (isset($_SERVER["HTTP_REFERER"]) and strpos($_SERVER["HTTP_REFERER"], $_SERVER["SERVER_NAME"]) === false)
    )
) {

    http_response_code(403);
    die('Forbidden');
}