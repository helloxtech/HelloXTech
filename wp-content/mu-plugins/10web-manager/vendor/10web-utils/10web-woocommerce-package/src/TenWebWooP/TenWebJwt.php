<?php

namespace TenWebWooP;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TenWebJwt {

    private $authentication_jwt_algorithm = 'HS256';

    /**
     * Create JWT token.
     *
     * @return array
     */
    public function getJwtToken() {
        do {
            $client_secret = openssl_random_pseudo_bytes(255, $crypto);
        } while (!$client_secret || !$crypto);
        $client_secret = bin2hex($client_secret);

        if (empty(get_option('tenweb_api_authentication_jwt_client_secret'))) {
            update_option('tenweb_api_authentication_jwt_client_secret', $client_secret);
        }
        $domain_id = get_option('tenweb_domain_id');
        $client_secret = sanitize_text_field(get_option('tenweb_api_authentication_jwt_client_secret'));
        $iat = time();
        $exp = time() + 157680000;
        $payload = array(
            'domain_id' => $domain_id,
            'iat' => $iat,
            'exp' => $exp,
        );
        $jwt = JWT::encode($payload, $client_secret, $this->authentication_jwt_algorithm);

        return array(
            'token_type' => 'Bearer',
            'iat' => $iat,
            'expires_in' => $exp,
            'jwt_token' => $jwt,
        );
    }

    /**
     * Check if request is valid.
     *
     * @return bool
     */
    public function jwtAuthorization() {
        $headers = Utils::getAllHeaders();

        if (isset($headers['AUTHORIZATION'])) {
            $authorization_header = explode(' ', $headers['AUTHORIZATION']);

            if (isset($authorization_header[0]) && (strcasecmp($authorization_header[0], 'Bearer') === 0) && isset($authorization_header[1]) && '' !== $authorization_header[1]) {
                $domain_id = get_option('tenweb_domain_id');
                $jwt = $authorization_header[1];
                $client_secret = sanitize_text_field(get_option('tenweb_api_authentication_jwt_client_secret'));

                try {
                    $decoded = JWT::decode($jwt, new Key($client_secret, $this->authentication_jwt_algorithm));

                    if (is_object($decoded) && isset($decoded->domain_id) && $decoded->domain_id === $domain_id) {
                        return true;
                    }
                } catch (Exception $err) {
                    return false;
                }
            }
        }

        return false;
    }
}
