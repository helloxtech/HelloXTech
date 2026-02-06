<?php


namespace Tenweb_Manager;
use Tenweb_Authorization\Login;

class TenwebCache
{
    protected static $instance = null;

    private $login_instance;

    private function __construct()
    {
        $this->login_instance = Login::get_instance();
        if(isset($_GET["action"]) && $_GET["action"] === 'tenweb_purge_cf_cache') {
            if(!empty($_GET["permalink"]) && wp_verify_nonce("tenweb_purge_cf_cache") != null) {
                $permalink = sanitize_url( $_GET["permalink"] );
                $this->purgeTenwebCloudflareCache(false, array($permalink), false);
            }
        }
    }

    public function register_hooks()
    {
        //Manager::enqueue_cache_scripts();
        add_action('wp_enqueue_scripts', array($this, 'enqueue_cache_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_cache_scripts'));
        add_action('post_updated', array($this, 'on_post_updated_purge'), 10, 3);
        add_action('transition_post_status', array($this, 'on_transition_post_purge'), 10, 3);
        add_action('wp_insert_comment', array($this, 'on_wp_insert_comment_purge'), 10, 2);
        add_action('transition_comment_status', array($this, 'on_transition_comment_purge'), 10, 3);
        add_action('edit_comment', array($this, 'on_edit_comment_purge'), 10, 2);
        add_action('wp_ajax_' . TENWEB_PREFIX . '_cache_purge_all', array($this, 'purge_all_caches'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_cache_purge_cloudflare', array($this, 'flushCloudflareCache'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_cache_purge_optimizer', array($this, 'flushSpeedOptimizerCache'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_cache_clear_all', array($this, 'flushAllCache'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_get_cache_exclude', array($this, 'get_cache_exclude'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_set_cache_exclude', array($this, 'set_cache_exclude'));
        add_action(TENWEB_PREFIX . '_purge_all_caches', array($this, 'purge_all_caches_hook'), 10, 1);
        add_action('wp_ajax_' . TENWEB_PREFIX . '_clear_object_cache', array($this, 'clear_object_cache'));

        /* delete cloudflare cache */
        if (defined('TENWEB_CF_STATUS') && !in_array(TENWEB_CF_STATUS, array('0', 'disabled'))) {
            add_action('wp_ajax_' . TENWEB_PREFIX . '_cf_cache_purge', array($this, 'purgeTenwebCloudflareCache'));
            //add_action('activated_plugin', array($this, 'purgeTenwebCloudflareCache'), 10, 1);
            //add_action('upgrader_process_complete', array($this, 'purgeTenwebCloudflareCache'), 10, 2);

            add_filter('page_row_actions', array($this, 'post_row_actions'), 10, 2);
            add_filter('post_row_actions', array($this, 'post_row_actions'), 10, 2);
        }
        /* ------- */

        // Clear 10Web cache when Elementor cache is cleared or CSS is regenerated
        // Only register hooks if Elementor is active
        if (did_action('elementor/loaded')) {
            add_action('elementor/core/files/clear_cache', array($this, 'on_elementor_clear_cache'));
            // Accept 0-2 arguments to handle different Elementor versions
            add_filter('elementor/css-file/post/parse', array($this, 'on_elementor_css_regenerate'), 10, 0);
            add_action('elementor/editor/after_save', array($this, 'on_elementor_editor_save'), 10, 2);
            add_action('update_post_meta', array($this, 'on_elementor_post_meta_update'), 10, 4);
        }

        $user = wp_get_current_user();
        if(in_array('administrator', $user->roles)) {
            add_action('admin_bar_menu', array($this, 'tenweb_cache_menu'), 100);
        }
    }

    public function on_post_updated_purge($post_id, $post_after, $post_before)
    {

        if (($post_after->post_status == 'publish' || $post_before->post_status == 'publish') && get_option(TENWEB_PREFIX."_import_in_progress") != 1) {
            $this->purge_cache($post_id);
        }

    }

    public function on_transition_post_purge($new_status, $old_status, $post)
    {
        if ($new_status == 'publish' || $old_status == 'future') {
            $this->purge_cache($post->ID);
        }

        // if status changed from publish (e.g. to trash) or to publish (e.g. from draft to publish) clear page cache
        if($post->post_type === 'post' && $new_status !== $old_status && ($new_status === "publish" || $old_status === "publish")) {
            $this->purgeTenwebCloudflareCache(false, array(get_home_url()), true, true);
        }

    }

    public function on_wp_insert_comment_purge($id, $comment)
    {
        if ((int)$comment->comment_approved == 1) {
            $this->purge_cache($comment->comment_post_ID);
        }

    }

    public function on_transition_comment_purge($new_status, $old_status, $comment)
    {
        if ($new_status == 'approved' || $old_status == 'approved') {
            $this->purge_cache($comment->comment_post_ID);
        }
    }

    public function on_edit_comment_purge($id, $comment)
    {
        if ((int)$comment->comment_approved == 1) {
            $this->purge_cache($comment->comment_post_ID);
        }
    }

    public function on_elementor_clear_cache()
    {
        // Clear all 10Web caches when Elementor cache is cleared
        $this->flushAllCache(true);
    }

    public function on_elementor_css_regenerate($css=null, $post_css_file=null)
    {
        // Clear cache for specific post when Elementor regenerates its CSS
        // This prevents 404 errors on CSS files during regeneration
        if ($post_css_file && method_exists($post_css_file, 'get_post_id')) {
            $post_id = $post_css_file->get_post_id();
            if ($post_id) {
                $this->purge_cache($post_id);
            }
        }
        // Must return the CSS since this is a filter
        return $css;
    }

    public function on_elementor_editor_save($post_id = null, $editor_data = null)
    {
        // Clear cache when Elementor editor saves content
        // This ensures fresh CSS files are referenced after save
        if ($post_id) {
            $this->purge_cache($post_id);
        }
    }

    public function on_elementor_post_meta_update($meta_id = null, $post_id = null, $meta_key = null, $meta_value = null)
    {
        // Clear cache when Elementor updates post meta (especially _elementor_css meta)
        // This catches cases where CSS is regenerated without triggering other hooks
        if ($meta_key === '_elementor_css' && $post_id) {
            $this->purge_cache($post_id);
        }
    }


    public function purge_cache($post_id)
    {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        $public_post_types = get_post_types(array('public' => true));
        $post_type = get_post_type($post_id);
        if (!in_array($post_type, $public_post_types)) {
            return false;
        }
        $url = get_permalink($post_id);
        if ($url) {
            $this->purge_cache_via_url($url);
        }

        return true;
    }

    public function purge_cache_via_url($url)
    {
        if (empty($url)) {
            return false;
        }
        $hash = $this->get_cache_file_hash($url);
        if ($hash) {
            return $this->flush_cache(
                $hash,
                rtrim(site_url(), '/') === rtrim($url, '/') && !get_option(TENWEB_PREFIX . '_import_in_progress')
            ); //only flush two cache on homepage update when no template is importing
        }

        return false;
    }

    private function get_cache_file_hash($url)
    {
        if (empty($url)) {
            return false;
        }
        $url = esc_url($url);
        $url = parse_url($url);
        $hash = md5($url['scheme'] . 'GET' . $url['host'] . $url['path']);

        return $hash;
    }


    private function get_tenweb_cache_purge_endpoint()
    {
        $path = explode("/", ABSPATH);
        $subdomain = '';

        if ($path[2] != 'wplive' && $path[2] != 'wpstaging') {
            $subdomain = $path[2];
        }

        $env = defined('TENWEB_ENV') ? TENWEB_ENV : 'live';

        return $subdomain . "/" . $env;
    }

    private function flush_cache($hash, $flushTWOCache = true)
    {

        //only clear cache if plugin is MU and TENWEB_CACHE is enabled
        if (Helper::check_if_manager_mu() && defined('TENWEB_CACHE') && !in_array(TENWEB_CACHE, array('0', 'disabled'))) {
            $resp = wp_remote_get('http://127.0.0.1/purge' . $this->get_tenweb_cache_purge_endpoint() . '/' . $hash);

            if (is_wp_error($resp) || 200 !== wp_remote_retrieve_response_code($resp)) {
                return false;
            }
        }
        //flush PageSpeed module cache if Optimizer Plugin is disabled
        if (!class_exists(\TenWebOptimizer\OptimizerCache::class)) {
            $this->flush_pagespeed_cache();
        } else {
            if ($flushTWOCache) {
                Helper::clear_optimizer_cache(true);
            }
        }


        return true;
    }

    public function purge_all_caches_hook($flushTWOCache = true)
    {
        if ($this->flush_cache('all', $flushTWOCache)) {
            //only warm up if plugin is MU and TENWEB_CACHE is enabled
            if (Helper::check_if_manager_mu() && defined('TENWEB_CACHE') && !in_array(TENWEB_CACHE, array('0', 'disabled'))) {
                $site_url = site_url();
                wp_remote_get($site_url, array('sslverify' => false, 'blocking' => false, 'timeout' => 0.1));
            }
        }
    }

    public function purge_all_caches($all = false)
    {

        $return_resp = array();
        $return_resp['status'] = "ok";
        $return_resp['message'] = "Cache Successfully purged";

        if ($this->flush_cache('all') === false) {
            $return_resp['status'] = "error";
            $return_resp['message'] = "Something went wrong";
        }

        //only warmup if plugin is MU and TENWEB_CACHE is enabled
        if (Helper::check_if_manager_mu() && defined('TENWEB_CACHE') && !in_array(TENWEB_CACHE, array('0', 'disabled'))) {
            $site_url = site_url();
            wp_remote_get($site_url, array('sslverify' => false,'blocking' => false, 'timeout' => 0.1));
        }

        if (!$all) {
            echo json_encode($return_resp);
            exit;
        }
        return json_encode($return_resp);
    }

    public function get_cache_exclude()
    {
        $return_resp = array();
        $workspace_id = \TenwebServices::get_workspace_id();
        $domain_id = \TenwebServices::get_domain_id();
        if (!empty($domain_id)) {
            $url = TENWEB_API_URL . '/domains/' . $domain_id . '/cache/exclude';
            $result = \TenwebServices::do_request($url, array('method' => 'GET'), 'get_cache_exclude');
            if (!is_wp_error($result) || wp_remote_retrieve_response_code($result) === 200) {
                if (isset($result['body'])) {
                    $response_data = json_decode($result['body'], true);
                    if (isset($response_data['data']) && is_array($response_data['data'])) {

                        $return_resp = $response_data['data'];

                    }
                }
            }
        }
        echo json_encode($return_resp);
        exit;
    }

    public function set_cache_exclude()
    {
        $return_resp = array();
        $workspace_id = \TenwebServices::get_workspace_id();
        $domain_id = \TenwebServices::get_domain_id();
        if (!empty($domain_id)) {
            $url = TENWEB_API_URL .  '/domains/' . $domain_id . '/cache/exclude';
            $result = \TenwebServices::do_request($url, array('method' => 'POST', 'body' => array('pages' => $_POST['data'])), 'set_cache_exclude');
            if (!is_wp_error($result) || wp_remote_retrieve_response_code($result) === 200) {
                if (isset($result['body'])) {
                    $response_data = json_decode($result['body'], true);
                    if (isset($response_data['status'])) {

                        $return_resp = $response_data;
                    }
                }
            }
        }

        echo json_encode($return_resp);
        exit;
    }

    public function tenweb_cache_menu() {
        global $wp_admin_bar;
        $hasAnyTWCache = false;
        $cacheTWOptions = [
            (defined('TENWEB_CACHE') && !in_array(TENWEB_CACHE, array('0', 'disabled'))),
            class_exists('\CF\WordPress\Hooks'),
            class_exists(\TenWebOptimizer\OptimizerCache::class)
        ];
        if(in_array('true', $cacheTWOptions)) {
            $hasAnyTWCache = true;
        }
        add_action('admin_notices', array($this, 'flushAllCacheNotice'));
        $menu_id = 'tenweb_cache';
        if ($hasAnyTWCache) {
            $wp_admin_bar->add_menu(array('id' => $menu_id, 'title' => __('Manage '.Helper::get_company_name().' Cache'), 'href' => '#'));
        }


        if (defined('TENWEB_CACHE') && !in_array(TENWEB_CACHE, array('0', 'disabled'))) {
            $wp_admin_bar->add_menu(array(
                    'parent' => $menu_id,
                    'title' => __('Clear ' . Helper::get_company_name() . ' cache'),
                    'id' => 'tenweb_manager',
                    'href' => '#',
                    'meta' => array('title' => __('Clear Cache', "tenweb_manager"), 'onclick' => 'tenwebCachePurgeDropdown()'))
            );
        }
        if (class_exists('\CF\WordPress\Hooks')) {
            $wp_admin_bar->add_menu(array(
                    'parent' => $menu_id,
                    'title' => __('Clear CloudFlare Cache'),
                    'id' => 'tenweb_clear_cloudflare',
                    'href' => '#',
                    'meta' => array('title' => __('Clear CloudFlare Cache', "tenweb_manager"), 'onclick' => 'tenwebCloudflareCachePurge()'))
            );
        }
        if (class_exists(\TenWebOptimizer\OptimizerCache::class)) {
            $wp_admin_bar->add_menu(array(
                'parent' => $menu_id,
                'title' => __('Clear SB cache'),
                'id' => 'tenweb_clear_so_cache',
                'href' => '#',
                'meta' => array('title' => __('Clear SO Cache', "tenweb_manager"), 'onclick' => 'tenwebClearSOCache()'))
            );
        }
        if (defined('TENWEB_CF_STATUS') && !in_array(TENWEB_CF_STATUS, array('0', 'disabled'))) {
            $wp_admin_bar->add_menu(array(
                    'parent' => $menu_id,
                    'title' => __('Clear CF cache'),
                    'id' => 'tenweb_purge_cf_cache',
                    'href' => '#',
                    'meta' => array('title' => __('Clear CF Cache', "tenweb_manager"), 'onclick' => 'tenwebCFCachePurgeDropdown()'))
            );
        }


        if(count(array_filter($cacheTWOptions,function($check) {return $check===true;})) > 1) { // check if min 2 cache options turned on
            $wp_admin_bar->add_menu(array(
                    'parent' => $menu_id,
                    'title' => __('Clear all cache'),
                    'id' => 'tenweb_clear_all_cache',
                    'href' => '#',
                    'meta' => array('title' => __('Clear All Cache', "tenweb_manager"), 'onclick' => 'tenwebClearAllCache()'))
            );
        }

        if (defined('TENWEB_OBJECT_CACHE') && !in_array(TENWEB_OBJECT_CACHE, array('0', 'disabled'))) {
            $wp_admin_bar->add_menu([
                'parent' => $menu_id,
                'title' => __('Clear Object Cache'),
                'id' => 'tenweb_clear_object_cache',
                'href' => '#',
                'meta' => ['title' => __('Clear Object Cache', "tenweb_manager"), 'onclick' => 'tenwebClearObjectCache()']
            ]);
        }

    }

    public function enqueue_cache_scripts()
    {
        if (is_user_logged_in()) {
            wp_register_script(TENWEB_PREFIX . '_scripts_cache', TENWEB_URL . '/assets/js/cache.js', array(), TENWEB_VERSION);
            wp_enqueue_script(TENWEB_PREFIX . '_scripts_cache');
            wp_localize_script(TENWEB_PREFIX . '_scripts_cache', TENWEB_PREFIX, array(
                'ajaxurl' => admin_url('admin-ajax.php'),
            ));
        }

    }

    public static function get_instance()
    {
        if (null == self::$instance) {

            self::$instance = new self;
        }

        return self::$instance;
    }

    private function flush_pagespeed_cache()
    {
        if (defined('TW_NGX_PAGESPEED') && TW_NGX_PAGESPEED === 'enabled') {
            $url = rtrim(get_home_url(), '/') . '/*';
            wp_remote_request($url, array('method' => 'PURGE', 'sslverify' => false, 'blocking' => false, 'timeout' => 0.1));
        }
    }


    public  function flushCloudflareCache($all = false)
    {
        if (class_exists('\CF\WordPress\Hooks')) {
            $return_resp = array();
            $return_resp['status'] = "ok";
            $cloudflareHooks = new \CF\WordPress\Hooks();
            $cloudflareHooks->purgeCacheEverything();
            $return_resp['message'] = "Purged Cloudflare cache";
            if (!$all) {
                echo json_encode($return_resp);
                exit();
            }
            return json_encode($return_resp);
        }
    }

    public  function flushSpeedOptimizerCache($all = false){
        $return_resp = array();
        $return_resp['status'] = "ok";
        //flush PageSpeed module cache if Optimizer Plugin is disabled
        if (!class_exists(\TenWebOptimizer\OptimizerCache::class)) {
            self::flush_pagespeed_cache();
            $return_resp['message'] = "Purged pagespeed cache";
        } else {
                Helper::clear_optimizer_cache(true);
            $return_resp['message'] = "Purged SpeedOptimizer Cache";
        }
        if (!$all) {
            echo json_encode($return_resp);
            exit();
        }
        return json_encode($return_resp);
    }

    public function clear_object_cache() {
        // Clear WordPress object cache
        wp_cache_flush();

        return true;
    }

    public function flushAllCache($all = false)
    {
        $cloudflare_purge =  $this->flushCloudflareCache(true);
        $so_purge =  $this->flushSpeedOptimizerCache(true);
        $tenweb_purge =  $this->purge_all_caches(true);
        $tenweb_cf_purge =  $this->purgeTenwebCloudflareCache(true);

        // Clear object cache
        $this->clear_object_cache();

        $return_resp = array();
        $return_resp['status'] = "ok";

        if (isset(json_decode($cloudflare_purge)->message)) {
            $return_resp['cloudflare'] = json_decode($cloudflare_purge)->message;
        } else {
            $return_resp['cloudflare'] = '';
        }

        if (isset(json_decode($so_purge)->message)) {
            $return_resp['so'] = json_decode($so_purge)->message;
        } else {
            $return_resp['so'] = '';
        }

        if (isset(json_decode($tenweb_purge)->message)) {
            $return_resp['tenweb'] = json_decode($tenweb_purge)->message;
        } else {
            $return_resp['tenweb'] = '';
        }

        if (isset(json_decode($tenweb_cf_purge)->message)) {
            $return_resp['tenweb_cf'] = json_decode($tenweb_cf_purge)->message;
        } else {
            $return_resp['tenweb_cf'] = '';
        }

        if (!$all) {
            echo json_encode($return_resp);
            exit();
        }
        return json_encode($return_resp);
    }

    public  function flushAllCacheNotice()
    {
        echo '<div id="tenweb_cache_dropdown_message" class="notice updated hidden is-dismissible">
                <p></p>
                <button id="my-dismiss-admin-message" class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button>
             </div>';
    }

    public function purgeTenwebCloudflareCache($all=false, $prefixes=array(), $display_msg=true, $post_status_changed=false) {
        if (!defined('TENWEB_CF_STATUS') || in_array(TENWEB_CF_STATUS, array('0', 'disabled'))) {
            return;
        }
        $return_resp = array();
        $return_resp['status'] = "ok";
        $domain_id = \TenwebServices::get_domain_id();
        if (!empty($domain_id)) {
            $url = TENWEB_API_URL . '/domains/' . $domain_id . '/cloudflare/cache/purge';
            $args = array('sslverify' => false,'blocking' => false, 'timeout' => 0.1,
                'headers' => array("Accept" => "application/x.10webmanager.v1+json",
                "Authorization" => "Bearer " . $this->login_instance->get_access_token())
            );
            if (!empty($prefixes)){
                $args['prefixes'] = $prefixes;
            }
            $result = wp_remote_post($url, $args);
            if (!is_wp_error($result) || wp_remote_retrieve_response_code($result) === 200) {
                $return_resp['message'] = "Purged cloudflare cache";
                if ($post_status_changed) {
                     return true;
                }
            } else {
                Helper::set_error_log('purge_cloudflare_error', $result->get_error_message());
                if ($post_status_changed) {
                     return false;
                }
            }

        }
        if (!$display_msg) {
            $redirect_to = (!empty($_SERVER['HTTP_REFERER'])) ? sanitize_text_field( $_SERVER['HTTP_REFERER'] ) : "/wp-admin/edit.php";
            wp_safe_redirect($redirect_to);
            die;
        }
        if (!$all) {
            echo json_encode($return_resp);
            exit();
        }
        return json_encode($return_resp);
    }
    public function post_row_actions($actions, $post){
        $url = wp_nonce_url(admin_url('admin-post.php?action=tenweb_purge_cf_cache&permalink=' . get_permalink($post)), "tenweb_purge_cf_cache");
        $actions['tenweb_purge_cf_cache'] = sprintf('<a href="%s">%s</a>', $url, __('Clear CF cache', 'tenweb-manager'));

        return $actions;
    }
}
