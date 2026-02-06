<?php
/**
 * Created by PhpStorm.
 * User: mher
 * Date: 9/15/17
 * Time: 1:24 PM
 */

namespace Tenweb_Manager {

    use Tenweb_Authorization\Helper as AuthHelper;
    use Tenweb_Authorization\Login;
    class Api
    {
        protected static $instance = null;

        private $api_url = TENWEB_API_URL;
        private $domain_id;
        private $workspace_id;
        private $network_domain_id;
        private $login_instance;
        private $last_response = null;
        private $helper_instance;

        public function __construct()
        {
            $this->login_instance = Login::get_instance();
            $this->helper_instance = AuthHelper::get_instance();
            $this->domain_id = get_option('tenweb_domain_id');
            $this->network_domain_id = get_site_option('tenweb_domain_id');
            $this->workspace_id = \TenwebServices::get_workspace_id();
        }



        /**
         * @param string $url       Site URL to retrieve.
         * @param array  $args      Optional.
         * @param string $error_key Optional.
         *
         * @return null|array Response body or null on failure.
         */
        public function request($url, $args = array(), $error_key = null)
        {
            return $this->helper_instance->request($url, $args, $error_key);
        }

        /*
         * @param $type string ['all','plugin','theme','addon']
         * @return array on success or false on fail
         * */
        public function get_products($type = 'all')
        {
            return AuthHelper::get_instance()->get_products($type);
        }

        /**
         * @param array $data
         *
         * @return boolean
         */
        public function send_site_state($data)
        {
            return AuthHelper::send_site_state($data);
        }

        /**
         * @return array|null on success or null on failure
         */
        public function get_user_info()
        {

            $url = TENWEB_API_URL . '/domains/' . $this->domain_id . '/user/me/';
            $args = array(
                'method' => 'GET',
            );

            $user_info = $this->request($url, $args, 'get_user_info');
            update_site_option('tenweb_req_result', $user_info);
            if ($user_info == null || isset($user_info['error'])) {
                return null;
            }


            return $user_info;
        }


        /**
         * @param integer $product_id
         *
         * @return array on success or null on failure
         */
        public function get_amazon_tokens($product_id)
        {
            return AuthHelper::get_instance()->get_amazon_tokens($product_id);
        }

        /**
         * @param integer $domain_id
         *
         * @return array|null on success or null on failure
         */
        public function get_amazon_tokens_for_migration($domain_id)
        {
            $url = TENWEB_API_URL . '/domains/' . $domain_id . '/get-temporary-credentials';
            $args = array(
                'method' => 'GET',
            );

            $response = $this->request($url, $args, 'get_amazon_tokens_for_migration');
            if ($response == null || isset($response['error'])) {
                return null;
            }

            return $response;
        }

        public function availability_request()
        {
            $url = TENWEB_API_URL . '/domains/' . $this->get_domain_id() . '/availability';
            $args = array(
                'method' => 'GET',
            );

            $response = $this->request($url, $args, 'availability_request');

            if (isset($response['status']) && $response['status'] === 'ok') {
                return true;
            }

            return false;
        }

        private function get_product_data_from_api($url)
        {
            AuthHelper::get_instance()->get_product_data_from_api($url);
        }

        /**
         * @param string  $token one time login token
         * @param boolean $check_for_network
         *
         * @param bool    $is_login
         *
         * @return boolean true|false
         */
        public function check_single_token($token, $check_for_network = false, $is_login = false, $email = null)
        {
            return $this->helper_instance->check_single_token($token, $check_for_network, $is_login, $email);
        }

        /**
         *  returns true if normally formatted token obtained (not necessarily valid one), false otherwise
         *
         */

//        private function refresh_token()
//        {
//            return AuthHelper::refresh_token();
//        }

        /**
         * @return bool
         */
        public function add_workspace_id()
        {
            $url = TENWEB_API_URL . '/domains/' . $this->domain_id . '/add-manager-workspace-id';
            $args = array(
                'method' => 'POST',
            );

            $response = $this->request($url, $args, 'add_workspace_id');
            update_site_option('tenweb_req_result', $response);
            if ($response == null || isset($response['error'])) {
                return false;
            }

            if (!empty($response['data']['workspace_id'])) {
                update_site_option('tenweb_workspace_id', $response['data']['workspace_id']);
            }

            return true;
        }

        private function check_url($url)
        {
            return $this->helper_instance->check_url($url);
        }

        private function get_access_token()
        {
            return $this->login_instance->get_access_token();
        }

        public function set_domain_id($domain_id)
        {
            $this->domain_id = $domain_id;
        }

        public function get_domain_id()
        {
            return $this->domain_id;
        }

        public function get_network_domain_id()
        {
            return $this->network_domain_id;
        }

        public function set_workspace_id($workspace_id)
        {
            $this->workspace_id = $workspace_id;
        }

        public function get_workspace_id()
        {
            return $this->workspace_id;
        }

        public function get_last_response()
        {
            return $this->helper_instance->last_response;
        }

        public static function get_instance()
        {
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * @param array $data
         *
         * @return boolean
         */
        public function send_pagespeed($data)
        {
            $url = $this->api_url . '/domains/' . $this->domain_id . '/pagespeed-request';
            $args = array(
                'method' => 'POST',
                'body'   => array('event_label' => $data)
            );

            $response = $this->request($url, $args, 'send_pagespeed');

            if ($response == null || isset($response['error'])) {
                return false;
            }

            return true;
        }

    }

}
