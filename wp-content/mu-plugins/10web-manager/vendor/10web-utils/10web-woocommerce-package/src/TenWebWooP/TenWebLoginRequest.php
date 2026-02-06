<?php

namespace TenWebWooP;

class TenWebLoginRequest {

    private $headers = array();

    const TENWEB_AUTH_HEADER_NAME = 'tenweb-authorization';

    const TENWEB_WOOP_REST_AUTH_HEADER = 'HTTP_TENWEB_WOOP_REST';

    const TENWEB_AUTH_HTTP_HEADER_NAME = 'HTTP_TENWEB_AUTHORIZATION';

    const TENWEB_AUTH_FROM_SERVICE = 'X-TENWEB-FROM-SERVICE';

    public function __construct($headerValue) {
        $this->headers[self::TENWEB_AUTH_HEADER_NAME] = $headerValue;
    }

    public function get_header($key) {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }
}
