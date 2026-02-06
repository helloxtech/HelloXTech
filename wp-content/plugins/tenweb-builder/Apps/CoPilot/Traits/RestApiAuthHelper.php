<?php

namespace Tenweb_Builder\Apps\CoPilot\Traits;

use TenWebWooP\CheckAuthorization;
use WP_REST_Request;

trait RestApiAuthHelper
{
    use CheckAuthorization;

    private array $jwt_token = array();

    public function check_rest_auth(WP_REST_Request $request) {
        $check_authorization = $this->check_authorization($request);

        if (is_array($check_authorization)) {
            if (!empty($check_authorization['jwt_token'])) {
                $this->jwt_token = $check_authorization['jwt_token'];
            }

            return $check_authorization['success'];
        }

        return false;
    }
}