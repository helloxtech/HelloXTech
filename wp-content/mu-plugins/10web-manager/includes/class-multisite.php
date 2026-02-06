<?php
namespace Tenweb_Manager {
    use Tenweb_Authorization\Login;

    class Multisite
    {
        protected static $instance = null;

        private function __construct()
        {
            // add new blog
            add_action('wpmu_new_blog', array($this, 'blog_activated'), 20);
             //add_action('wp_initialize_site', array($this, 'blog_activated'), 900);
            //add_action('wp_insert_site', array($this, 'blog_activated'), 10);

            // delete blog
            add_action('delete_blog', array($this, 'blog_deleted'), 15);
            //add_action('wp_uninitialize_site', array($this, 'blog_deleted'), 20);

            // unarchive blog
            add_action('unarchive_blog', array($this, 'blog_activated'), 10, 1);
            // activate blog
            add_action('activate_blog', array($this, 'blog_activated'), 10, 1);
            // unspam blog
            add_action('make_ham_blog', array($this, 'blog_activated'), 10, 1);
            // archive blog
            add_action('archive_blog', array($this, 'blog_deleted'), 10, 1);
            // spam blog
            add_action('make_spam_blog', array($this, 'blog_deleted'), 10, 1);
            //deactivate blog
            add_action('deactivate_blog', array($this, 'blog_deleted'), 10, 1);
        }


        public function blog_activated($blog_id)
        {
            if ($blog_id instanceof \WP_Site) {
                $blog_id = $blog_id->blog_id;
            }
            /*$creating_blog_key = TENWEB_PREFIX . '_blog_is_creating_' . $blog_id;
            $creating_blog = get_site_transient($creating_blog_key);
            if ($creating_blog) {
                return true;
            }
            set_site_transient($creating_blog_key, 1, 60 * 15);*/

            $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');
            $api = Api::get_instance();
            $api->set_domain_id($domain_id);
            $data = Helper::get_site_info($blog_id);

            if (defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL === true) {
                $url = parse_url($data['site_url'], PHP_URL_HOST);
                $url = explode('.', $url);
                $subdoamin = $url[0];
            } else {
                $url = rtrim(parse_url($data['site_url'], PHP_URL_PATH), '/');
                $url = explode('/', $url);
                $subdoamin = end($url);
            }
            $data['subdomain'] = $subdoamin;
            $data['email'] = get_bloginfo('admin_email');
            $data['language'] = get_bloginfo('language');
            $data['title'] = get_bloginfo('name');

            $confirm_token = md5(uniqid((string)mt_rand(), true));
            Login::get_instance()->setConfirmToken($confirm_token);
            $data["confirm_token"] = $confirm_token;
            $url = TENWEB_API_URL . '/domains/' . $domain_id . '/add-new-blog';
            if (!empty($data)) {
                $data["other_data"] = json_encode($data["other_data"]);
            }
            $args = array(
                'method' => 'POST',
                'body'   => $data
            );

            $response = $api->request($url, $args, 'add_new_blog');

            if (empty($response['body'])) {
                Helper::set_error_log('add_new_blog_error', "Add new blog response error");

                return false;
            }
            update_blog_option($blog_id, TENWEB_PREFIX . '_domain_id', $response["body"]["new_domain_id"]);
            update_blog_option($blog_id, TENWEB_PREFIX . '_is_available', $response["body"]["is_available"]);
            Helper::check_site_state();

            //delete_site_transient($creating_blog_key);

            return true;
        }


        public function blog_deleted($blog_id)
        {
            if ($blog_id instanceof \WP_Site) {
                $blog_id = $blog_id->blog_id;
            }
            /*$deleting_blog_key = TENWEB_PREFIX . '_blog_is_deleting_' . $blog_id;
            $deleting_blog = get_site_transient($deleting_blog_key);
            if ($deleting_blog) {
                return true;
            }
            set_site_transient($deleting_blog_key, 1, 60 * 15);*/

            $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');
            $child_id = get_blog_option($blog_id, TENWEB_PREFIX . '_domain_id');

            $api = Api::get_instance();
            $api->set_domain_id($domain_id);

            $url = TENWEB_API_URL . '/domains/' . $domain_id . '/delete-blog/' . $child_id;

            $args = array(
                'method' => 'DELETE',
                'body'   => array()
            );

            $response = $api->request($url, $args, 'delete_blog');
            if (!$response) {
                Helper::set_error_log('add_new_blog_error', "Add new blog response error");

                return false;
            }
            Helper::check_site_state();

            //delete_site_transient($deleting_blog_key);

            return true;
        }

        public static function get_instance()
        {
            if (null == self::$instance) {

                self::$instance = new self;
            }

            return self::$instance;
        }

    }
}