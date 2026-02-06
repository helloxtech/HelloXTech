<?php

namespace TenWebWooP\PaymentMethods;

use Tenweb_Authorization\Login;
use TenwebServices;
use TenWebWooP\Config;

class Service {

    public static function request($endpoint, $provider = 'payengine', $args = array(), $method = 'GET', $idempotency_key = false) {
        $workspace_id = TenwebServices::get_workspace_id();
        $domain_id = TenwebServices::get_domain_id();
        $service_url = Config::get_service_url($workspace_id, $domain_id, $endpoint, $provider);
        $login_instance = Login::get_instance();

        if (empty($login_instance->get_access_token()) || empty($workspace_id) || empty($domain_id)) {
            $logger = wc_get_logger();

            if ($logger) {
                //phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
                $logger->error('TenWebWooP: Service request failed because we got missing data for it - ' . json_encode(array('service_url' => $service_url, 'workspace_id' => $workspace_id, 'domain_id' => $domain_id, 'is_token_empty' => empty($login_instance->get_access_token()))), array('source' => 'tenweb-payment'));
            }

            return false;
        }
        $arguments = array('sslverify' => false,
            'headers' => array(
                'Accept' => 'application/x.10webwoocommerce.v1+json',
                'Authorization' => 'Bearer ' . $login_instance->get_access_token(),
            ),
            'body' => $args,
            //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
            'timeout' => 15, // Timeout in seconds
        );
        // TODO: We should check this automatically, when there are any endpoints not supporting it.
        if ($idempotency_key) {
            $arguments['headers']['request-key'] = $idempotency_key;
        }
        switch ($method) {
            case 'GET':
                //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
                $response = wp_remote_get($service_url, $arguments);
                break;

            case 'POST':
                $response = wp_remote_post($service_url, $arguments);
                break;

            case 'REQUEST':
                $response = wp_remote_request($service_url, $arguments);
                break;
        }

        if (!is_wp_error($response) || wp_remote_retrieve_response_code($response) === 200) {
            return json_decode(wp_remote_retrieve_body($response));
        }

        $logger = wc_get_logger();

        if ($logger) {
            //phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
            $logger->error('TenWebWooP: Service request failed - ' . json_encode(array('service_url' => $service_url, 'response' => $response->get_error_message())), array('source' => 'tenweb-payment'));
        }

        return false;
    }
}
