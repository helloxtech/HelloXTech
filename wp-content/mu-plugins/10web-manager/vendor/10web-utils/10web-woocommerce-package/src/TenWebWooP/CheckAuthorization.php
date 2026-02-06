<?php

namespace TenWebWooP;

use WP_Error;
use WP_REST_Request;

trait CheckAuthorization {

    public function check_authorization(WP_REST_Request $request) {
        $jwt = new TenWebJwt();
        $jwt_authorization = $jwt->jwtAuthorization();
        $return_data = array(
            'success' => false,
            'jwt_token' => array()
        );

        if (!$jwt_authorization) {
            if (!\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
                $data_for_response = array(
                    'code' => 'unauthorized',
                    'message' => 'unauthorized',
                    'data' => array(
                        'status' => 401
                    )
                );

                return new WP_Error('rest_forbidden', $data_for_response, 401);
            }
            $authorize = \Tenweb_Authorization\Login::get_instance()->authorize($request);

            if (is_array($authorize)) {
                return new WP_Error('rest_forbidden', $authorize, 401);
            }
            $get_jwt_token = $jwt->getJwtToken();
            $return_data['jwt_token'] = $get_jwt_token;
            //phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
            header('X-WOOP-jwt-token: ' . json_encode($get_jwt_token));
        }
        $return_data['success'] = true;

        return $return_data;
    }
}
