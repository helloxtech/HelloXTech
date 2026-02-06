<?php

namespace TenWebWooP;

class Authorization {

    private $user_id = null;

    public function init() {
        if ($this->check_woo_authorization()) {
            $headers = Utils::getAllHeaders();
            $this->get_admin_user();
            add_filter('determine_current_user', array($this, 'woop_authentication'), 0); //to allow using woocommerce rest api

            if (isset($headers[TenWebLoginRequest::TENWEB_AUTH_FROM_SERVICE])) { // to set current user only for CoPilot requests
                wp_set_current_user($this->user_id);
            }
        }
    }

    public function woop_authentication() {
        return $this->user_id;
    }

    private function get_admin_user() {
        $users = get_users(array(
            'role' => 'administrator',
        ));

        if (is_array($users) && isset($users['0'])) {
            $this->user_id = $users['0']->ID;
        }
    }

    public function check_woo_authorization() {
        $woop_header = sanitize_text_field(isset($_SERVER[TenWebLoginRequest::TENWEB_WOOP_REST_AUTH_HEADER]) ? $_SERVER[TenWebLoginRequest::TENWEB_WOOP_REST_AUTH_HEADER] : null);
        $req = new TenWebLoginRequest(sanitize_text_field(isset($_SERVER[TenWebLoginRequest::TENWEB_AUTH_HTTP_HEADER_NAME]) ? $_SERVER[TenWebLoginRequest::TENWEB_AUTH_HTTP_HEADER_NAME] : null));
        $jwt = new TenWebJwt();
        $jwt_authorization = $jwt->jwtAuthorization();

        if ($jwt_authorization) {
            add_filter('woocommerce_rest_check_permissions', '__return_true', 99);

            return true;
        }

        if ($woop_header === '1') {
            if (!\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
                return false;
            }
            $authorize = \Tenweb_Authorization\Login::get_instance()->authorize($req);

            if (is_array($authorize)) {
                return false;
            }
            //phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
            header('X-WOOP-jwt-token: ' . json_encode($jwt->getJwtToken()));
            add_filter('woocommerce_rest_check_permissions', '__return_true', 99);

            return true;
        }

        return false;
    }
}
