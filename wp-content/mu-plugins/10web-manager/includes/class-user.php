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

    class User
    {

        protected static $instance = null;

        private function __construct()
        {
            add_action('pre_user_query', array($this, 'hide_tenweb_user'));
        }

        private function update_user($pwd, $user_email = null)
        {

            $user_email = isset($user_email) ? $user_email : '';
            /* When performing an update operation using wp_insert_user, user_pass should be the hashed password and not the plain text password. */
            if (email_exists($user_email)) {
                return;
            }

            $userdata = array(
                'user_login' => $user_email,
                'user_email' => $user_email,
                'user_url'   => '',
                'user_pass'  => $pwd,  // When creating an user, `user_pass` is expected.
                'role'       => 'administrator'
            );

            $user_data_by_username = get_user_by('login', $user_email);
            if ($user_data_by_username && $user_data_by_username->user_email == '') {
                $user_data_by_username->user_email = $user_email;

                $userdata = array(
                    'ID' => $user_data_by_username->ID,
                    'user_email' => $user_email,
                    'user_login' => $user_data_by_username->user_login,
                    'user_pass' => $user_data_by_username->user_pass
                );
            }


            require_once(ABSPATH . 'wp-admin/includes/user.php');
            $user_id = wp_insert_user($userdata);
            update_site_option('tewneb_user_Error',$user_id);

            if (is_wp_error($user_id)) {
                $login = Login::get_instance();
                //do not logout if website hosted on 10web
                if(!Helper::check_if_manager_mu()){
                    $login->logout();
                }
                add_action('network_admin_notices', array($this, 'notice'));

            } else if (is_multisite()) {
                grant_super_admin($user_id);
            }
        }


        public function force_logout()
        {

        }


        public function check_password($pwd)
        {

           return AuthHelper::get_instance()->check_password($pwd);

        }

        public function notice()
        {
            echo '<div class="notice notice-error">' . __("Cannot create ".Helper::get_company_name()." user. Check database permissions.", TENWEB_LANG) . '</div>';
        }

        public function hide_tenweb_user($user_query)
        {
            $user = get_user_by('login', TENWEB_USERNAME);
            $id = !empty($user->ID) ? $user->ID : null;
            global $wpdb;
            $username_tenweb = TENWEB_USERNAME;
            // just str_replace() the SQL query
            $user_query->query_where = str_replace('WHERE 1=1', "WHERE 1=1 AND {$wpdb->users}.user_login != '{$username_tenweb}'", $user_query->query_where); // do not forget to change user ID here as well

        }

        public function login()
        {
            $user_email = isset($_GET['email']) ? rawurldecode(urlencode($_GET['email'])) : '';

            $user_data = get_user_by('email', $user_email) ? get_user_by('email', $user_email) : get_user_by('login', $user_email);

            if ($user_data !== false) {

                if (Api::get_instance()->check_single_token($_GET['twb_wp_login_token'],false,true)) {
                    $this->login_user($user_data);
                }

            }
            else {
                /* create user by email */
                if (Api::get_instance()->check_single_token($_GET['twb_wp_login_token'], false, true, $_GET['email'])) {
                    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!()=";
                    $pwd = substr( str_shuffle( $chars ), 0, 8 );
                    $this->update_user($pwd, $user_email);

                    $user_data = get_user_by('email', $user_email);
                    if ($user_data !== false) {
                        $this->login_user($user_data);
                    }
                }
            }

        }
        public function login_user($user_data) {
            wp_set_auth_cookie($user_data->ID);
            Manager::redirect_to_requested_page();
            header("Refresh:0");
            exit();
        }

        /**
         * @return boolean true|false
         */
        public static function login_tenweb_user()
        {
            return self::login_wp_user(TENWEB_USERNAME);
        }

        public static function login_prev_user($user_obj)
        {

            $self = self::get_instance();
            if (!empty($user_obj) && isset($user_obj->data->user_login)) {
                self::login_wp_user($user_obj->data->user_login);
            }

        }

        private static function login_wp_user($user_name)
        {
            $user_data = get_user_by('login', $user_name);

            if ($user_data !== false) {
                wp_set_current_user($user_data->ID, $user_data->user_login);
                wp_set_auth_cookie($user_data->ID);
                do_action('wp_login', $user_data->user_login, $user_data);

                return true;
            }

            return false;
        }

        public static function get_instance()
        {
            if (null == self::$instance) {

                self::$instance = new self();
            }

            return self::$instance;
        }


    }
}
