<?php
/**
 * Created by PhpStorm.
 * User: mher
 * Date: 9/13/17
 * Time: 4:22 PM
 */

namespace Tenweb_Manager;
use TenWebWOOP\Init;
use Tenweb_Authorization\InstalledPlugin;
use twoptimizeUtils;

// require_once(TENWEB_INCLUDES_DIR . "/class-login-old.php");
require_once(TENWEB_INCLUDES_DIR . '/class-tenweb-services.php');
require_once(TENWEB_INCLUDES_DIR . "/class-multisite.php");
require_once(TENWEB_INCLUDES_DIR . "/class-wpadminpagespeedfixes.php");

use Tenweb_Authorization\Login;

use Tenweb_Authorization\Helper as AuthHelper;

class Manager
{

    public $access_token = null;
    protected static $instance = null;
    private $menu_ids = array();
    private $user_logged_in = false;
    private $tenweb_screen = false;
    private $class_login;
    private $user_info = array();
    private $user_agreements = array();

    private $cache;
    private $whiteLabel;
	private  $trial_flow = false;
	private  $is_reseller_trial = false;
	private  $wvc = false;

    private static $plugins = array();
    private static $themes = array();
    private static $addons = array();


    public  $company_name = null;

    private static $products_raw_data = array();

    private $other_pages = array(
        "plugins",
        "plugins-network",
        "plugin-install",
        "themes",
        "themes-network",
        "options-general",
        "plugin-editor",
        "theme-editor",
        "site-info-network",
        "site-settings-network",
        "settings-network"
    );

    public function __construct()
    {
        $this->company_name = Helper::get_company_name();
        if (get_site_transient(TENWEB_PREFIX . "_disable_logout")) {
            $this->deactivation_popup();
        }

        $this->init();
        $this->login_wp_user();
        self::redirect_to_requested_page();
        if (!empty($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], '10web_register_url')) {
            add_action('admin_init', array($this, 'register_from_dashboard'));
        } elseif (!empty($_GET['nonce']) && !empty($_GET['email']) && !empty($_GET['token'])) {
            update_site_option(TENWEB_PREFIX . '_connect_error', 'Something went wrong');
        }  elseif (!empty($_GET['new_connection_flow']) && !empty($_GET['connection_error']) && empty($_GET['old_connection_flow']) ) {
            $registration_link = $this->get_registration_link(['old_connection_flow' => 1]);
            wp_redirect($registration_link);
        }

        \Tenweb_Manager\Navigation\Navigation::get_instance();
        $this->register_woop();
        add_action('init', array($this, 'register_hooks'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_dashboard_login', array($this, 'login_user'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_get_trial_limits', array($this, 'get_trial_limits'));
        add_action('tenweb_send_site_state', array($this, 'tenweb_send_site_state'));

    }


    public function get_trial_limits() {
	    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field($_POST['nonce']) : '';
	    if ( !wp_verify_nonce( $nonce, 'tenweb_tf_nonce' ) ) {
		    wp_send_json_error("invalid_nonce");
	    }
	    $workspace_id = \TenwebServices::get_workspace_id();
	    $auth_header = get_site_option(TENWEB_PREFIX . '_access_token');
	    $url = TENWEB_AI_ASSISTANT . 'actions/workspaces/' . $workspace_id . '/limits?actionType=ai_trial';
	    $headers =  [
		    'Authorization' => 'Bearer ' . $auth_header,
		    'Accept'        => 'application/x.10webaiassistantapi.v1+json'
	    ];

        // Vibe Coding
        if ( $this->wvc === true ) {
	        $wvc_url = defined('TENWEB_WVC_URL') ? TENWEB_WVC_URL : 'https://wvc.10web.io';
            $url = $wvc_url . '/api/workspaces/' . $workspace_id . '/limitations/';
            $headers = [
	            'Authorization' => 'Bearer ' . $auth_header,
	            'Accept'        => 'application/x.10webwvc.v1+json'
            ];
        }

	    $res = wp_safe_remote_get($url, [
		    'headers' => $headers,
	    ]);

	    if (wp_remote_retrieve_response_code($res) !== 200) {
		    wp_send_json_error("error");
            die;
	    }

	    $api_response = json_decode($res['body'], true)['data'];

	    if ( $this->wvc === true && isset($api_response['edit_limitations']) && isset($api_response['trial_data'])) {
		    $limitation = [
			    'already_used' => $api_response['edit_limitations']['already_used'],
			    'plan_limit' => $api_response['edit_limitations']['limitation'],
			    'hosting_trial_expire_date' =>  $api_response['trial_data']['hosting_trial_expire_date']
            ];
        }
        else {
	        $limitation = [
		        "already_used" => $api_response['alreadyUsed'],
		        "plan_limit" =>  $api_response['planLimit'],
		        "hosting_trial_expire_date" => $api_response['hostingTrialExpireDate'],
	        ];
        }

	    wp_send_json_success([
		    'status' => 'success',
		    'data' => $limitation
	    ]);
	    die;
    }

    /**
     * Handles the AJAX request to remind about the Copilot introduction.
     * @return void Outputs a JSON response with a success flag.
     */
    public function set_copilot_introduction_remind() {
        // Initialize default response
        $response = array('success' => false);

        // Validate nonce for security
        $nonce = isset($_POST['twbbm_nonce']) ? $_POST['twbbm_nonce'] : '';
        $is_valid = wp_verify_nonce($nonce, 'twbbm_introduction');

        // Proceed only if nonce is valid and remind flag is set to "1"
        if ($is_valid && isset($_POST['twbbm_introduction_remind']) && $_POST['twbbm_introduction_remind'] === '1') {
            // Get current status (as integer)
            $status = (int) get_option('twbbm_copilot_introducing_status', 0);

            // Set transient for 2 days
            set_transient('twbbm_copilot_introducing_remind', '1', 2 * DAY_IN_SECONDS);

            // Update status counter
            update_option('twbbm_copilot_introducing_status', $status + 1);

            $response['success'] = true;
        }
        echo json_encode($response);
        exit;
    }
    /**
     * Determine whether the Copilot introduction should be shown.
     *
     * Returns true only if:
     * - The 10Web Builder plugin is active.
     * - The plugin version is below 1.32.25.
     * - The company name is "10web".
     * - The Elementor experiment "co_pilot" is not active.
     * - The user has not dismissed the intro more than 3 times,
     *   and the reminder transient is not explicitly set to "1".
     *  - The user is an administrator.
     *
     * @return bool
     */
    private function should_show_copilot_introduction() {
        // Only show for administrators
        if ( ! current_user_can('administrator') ) {
            return false;
        }

        // Check if 10Web Builder plugin is active
        if ( ! is_plugin_active('tenweb-builder/tenweb-builder.php') ) {
            return false;
        }

        // Skip if version is >= 1.32.25
        if ( !defined('TWBB_VERSION') || version_compare(constant('TWBB_VERSION'), '1.32.25', '>=') ) {
            return false;
        }

        // Only for company "10web"
        if ( strtolower($this->company_name) !== '10web' ) {
            return false;
        }

        // Check Elementor experiment state
        $experiment_state = get_option('elementor_experiment-co_pilot');
        if ( $experiment_state === 'active' ) {
            return false;
        }

        // Check copilot intro status and reminder
        $intro_status = (int) get_option('twbbm_copilot_introducing_status', 0);
        $remind_flag  = get_transient('twbbm_copilot_introducing_remind');

        // Show intro if reminder is not set to "1" and status is less than 3
        if ( $remind_flag !== '1' && $intro_status < 3 ) {
            return true;
        }

        return false;
    }


    /**
     * Updates plugin information for specified plugins (from WP.org or 10Web)
     * by initializing product data and calling their update methods.
     */
    public function update_plugins_for_copilot()
    {

        $return_data = array(
                'success' => false
        );
        if (isset($_POST['twbbm_nonce']) && wp_verify_nonce($_POST['twbbm_nonce'], 'twbbm_introduction')) {
            update_option('twbbm_copilot_introducing_status', 3);
            $return_data['success'] = true;
            $plugins_data = array(
                array(
                    'slug'    => 'elementor',
                    'wp_slug' => 'elementor/elementor.php',
                    'origin'  => 'wp.org',
                ),
                array(
                    'slug'    => 'tenweb-builder',
                    'wp_slug' => 'tenweb-builder/tenweb-builder.php',
                    'origin'  => '10web',
                ),
            );
            foreach ($plugins_data as $plugin_data) {
                $product = null;
                $return_data[$plugin_data['slug']] = false;
                if ($plugin_data['origin'] === '10web') {
                    $product = $this->get_product_by('slug', $plugin_data['slug'], 'plugin', 'all');
                } elseif ($plugin_data['origin'] === 'wp.org') {
                    $this->set_products(false);
                    $states = Helper::get_products_state();
                    $state = null;
                    foreach ($states['plugins'] as $plugin_state) {
                        if ($plugin_state->slug === $plugin_data['slug']) {
                            $state = $plugin_state;
                            break;
                        }
                    }
                    $product = new InstalledPlugin(
                        $state,
                        0,
                        $plugin_data['slug'],
                        '',
                        '',
                        $plugin_data['wp_slug']
                    );
                }
                if (isset($product)) {
                    $product->set_origin($plugin_data['origin']);
                    if($product->update()){
                        $return_data[$plugin_data['slug']] = true;
                    }
                }
            }
        }
        echo json_encode($return_data);
        die;
    }

    public function twbbm_introducing_copilot_notice() {
        $screen = get_current_screen();
        if ( $screen && in_array( $screen->base, array( 'dashboard', 'plugins' ), true ) ) {
            wp_enqueue_style('twbb_introducing_copilot_notice-style', TENWEB_URL . '/assets/css/twbbm_introducing_copilot_notice.css', array(), TENWEB_VERSION);
            wp_enqueue_script('twbb_introducing_copilot_notice-script', TENWEB_URL . '/assets/js/twbb_introducing_copilot_notice.js', array('jquery'), TENWEB_VERSION);
            wp_localize_script('twbb_introducing_copilot_notice-script', 'twbbm', array(
                'ajaxurl'   => admin_url('admin-ajax.php'),
                'ajaxnonce' => wp_create_nonce('twbbm_introduction'),
            ));
            require_once TENWEB_DIR . '/views/twbbm_introducing_copilot_notice.php';
        }
    }
    public function set_twbb_templates()
    {
        require_once TENWEB_DIR . '/views/twbbm_copilot_templates.php';
    }
    public function enqueue_twbb_templates_assets()
    {
        wp_enqueue_style('twbb-copilot-templates-style', TENWEB_URL . '/assets/css/twbb-copilot-templates.css', array(), TENWEB_VERSION);
        wp_enqueue_script('twbb-copilot-templates-script', TENWEB_URL . '/assets/js/twbb-copilot-templates.js', array('jquery'), TENWEB_VERSION);
        wp_localize_script('twbb-copilot-templates-script', 'twbbm', array(
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'ajaxnonce'       => wp_create_nonce('twbbm_introduction'),
        ));
    }


    public function admin_head()
    {
        if ($this->user_logged_in) {
            echo '<input type="hidden" id="tenweb_user_is_logged_in" value="1"/>';
        }
        echo '<input type="hidden" id="tenweb_plugin_is_active" value="1"/>';
    }

    private function init()
    {

        /*update version*/
        if (TENWEB_VERSION != get_site_option(TENWEB_PREFIX . '_version')) {
            $error_msgs = tenweb_activate("0");
            foreach ($error_msgs as $error_msg) {
                Helper::add_notices($error_msg);
            }
        }

        $this->load_classes();
        $this->class_login = Login::get_instance();
        $this->user_logged_in = $this->class_login->check_logged_in();

        //check if 10web hosted website
        if (Helper::check_if_manager_mu()) {

            $this->whiteLabel = TenwebWhiteLabel::get_instance();
            $this->whiteLabel->register_hooks();

            $this->cache = TenwebCache::get_instance();
            $this->cache->register_hooks();


            // check if manager plugin in plugins list, deactivate it
            include_once ABSPATH . "wp-admin/includes/plugin.php";
            if (is_plugin_active(TENWEB_SLUG)) {
                $network_wide = is_multisite() ? true : false;
                deactivate_plugins(TENWEB_SLUG, false, $network_wide);
            }
        }
        if ($this->user_logged_in) {

            User::get_instance();
        }
        $this->check_for_update_workspace_id();

    }

    private function login_wp_user()
    {
        if (!empty($_GET['twb_wp_login_token']) && !is_user_logged_in()) {
            User::get_instance()->login();
        }
    }

    private function check_for_update_workspace_id()
    {
        $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');
        $workspace_id = \TenwebServices::get_workspace_id();
        $check = get_site_transient(TENWEB_PREFIX . '_add_workspace_id_checked');
        if ($domain_id && !$workspace_id && !$check) {
            Api::get_instance()->add_workspace_id();
            // save one week
            set_site_transient(TENWEB_PREFIX . '_add_workspace_id_checked', 1, 604800);
        }
    }
    public function register_woop(){
        if( !defined('TWM_DISABLE_WOOCOMMERCE_PACKAGE') || !TWM_DISABLE_WOOCOMMERCE_PACKAGE ){
            \TenWebWooP\Init::getInstance();
        }
    }

    public function register_hooks()
    {
        \TenWebWooP\Init::initBasicActions();
        if (!function_exists('get_plugins') && file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ($this->should_show_copilot_introduction()) {
            add_action('elementor/editor/footer', array($this, 'set_twbb_templates'));
            add_action('elementor/editor/after_enqueue_styles', array( $this, 'enqueue_twbb_templates_assets' ));
            add_action( 'admin_notices', array( $this, 'twbbm_introducing_copilot_notice') );
            add_action( 'wp_ajax_twbbm_update_plugins', array($this, 'update_plugins_for_copilot'));
            add_action( 'wp_ajax_twbbm_set_copilot_introduction_remind', array($this, 'set_copilot_introduction_remind'));
        }
        if ($this->user_logged_in && is_admin()) {
            $this->set_products();
        }

        //check if 10web not hosted website
        if (!Helper::check_if_manager_mu()) {
            if (get_transient("tenweb_initial_score")) {
                add_action('admin_notices', array($this, 'admin_notice_after_migrate'));
            }
            add_action('wp_ajax_tenweb_send_pagespeed', array($this, 'tenweb_send_pagespeed'));
            add_action('wp_ajax_tenweb_notice_after_migrate_dismissed', array($this, 'tenweb_notice_after_migrate_dismissed'));
        }
        add_action('rest_api_init', array($this, 'init_rest_api'));

        if (is_multisite() === true) {
            add_action('network_admin_menu', array($this, 'add_menu'), 24);
        } else {
            add_action('admin_menu', array($this, 'add_menu'), 24);
        }


        add_action('current_screen', array($this, 'is_tenweb_screen'));//for including login for styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('elementor/editor/before_enqueue_scripts', array($this, 'enqueue_scripts'));

        if (is_user_logged_in()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }


        add_action('admin_init', array($this, 'check_debug_mode'));
        //add_filter('http_request_args', array($this, 'add_request_timeout'), 2, 2);
        if (get_site_option(TENWEB_PREFIX . '_connect_error') !== false) {
            add_action('admin_notices', array($this, 'connection_notices'));
        }

        if ($this->user_logged_in && is_admin()) {

            if (get_site_option(TENWEB_PREFIX . '_activated') !== false) {

                //$this->send_availability_request();
            }

            if (is_multisite() === true) {
                add_action('network_admin_notices', array($this, 'admin_notices'));
            } else {
                add_action('admin_notices', array($this, 'admin_notices'));
            }

            add_action('wp_ajax_check_site_state', array($this, 'check_site_state'));
            add_action('wp_ajax_populate_agreement_info', array($this, 'populate_agreement_info'));


            add_action('deleted_plugin', array($this, 'check_site_state_after_action'));//todo
            add_action('tenweb_check_site_state_after_action', array($this, 'tenweb_check_site_state_after_action'));
            $user_info = Helper::get_tenweb_user_info(false, true);
            $this->user_info = $user_info['client_info'];
            $this->user_agreements = $user_info['agreement_info'];
            $this->add_paid_products_update_msg();
            
            $this->check_and_fetch_agency_dashboard_url();

            //init optimizer
            add_filter('plugin_action_links_tenweb_manager', array($this, 'add_action_link'), 10, 2);
            if (!isset($_GET['elementor-preview']) && !is_admin()) {
                add_action('admin_bar_menu', array($this, 'my_admin_bar_menu'), 99999);
            }

            if (is_multisite()) {
                Multisite::get_instance();
            }
            
            if (Helper::check_if_manager_mu() && is_array($user_info['agreement_info'])) {
	            $platform_id = defined('TENWEB_PLATFORM_FREE_SUBSCRIPTION_ID') ? TENWEB_PLATFORM_FREE_SUBSCRIPTION_ID : 318;
	            $wvc_id = defined('TENWEB_WVC_PRODUCT_ID') ? TENWEB_WVC_PRODUCT_ID : 221;
	            $trial_flow = ( isset( $user_info['agreement_info']['subscription_id'] ) && $user_info['agreement_info']['subscription_id'] === $platform_id );

                $this->trial_flow = $trial_flow;
                $this->wvc        = ( $trial_flow && isset( $user_info['agreement_info']['product_id'] ) && (int) $user_info['agreement_info']['product_id'] === $wvc_id );
                $this->is_reseller_trial   = isset($user_info['agreement_info']['subscription_category']) &&
                    $user_info['agreement_info']['subscription_category'] === 'starter' &&
                    isset($user_info['agreement_info']['plan']['services']['agency_suite']);
            }
	        if ( ( $this->trial_flow || $this->wvc ) &&  !$this->is_reseller_trial ) {
		        add_action('admin_bar_menu', array($this, 'trial_flow_elements'), 999999);
	        }
        }

        if (is_admin()) {
            WpAdminPagespeedFixes::enable();
        }

        add_filter('site_transient_update_plugins', array($this, 'remove_plugins_from_updates'), 1);
        add_filter('site_transient_update_themes', array($this, 'remove_themes_from_updates'), 1);
    }

    public function trial_flow_elements($wp_admin_bar)
    {
        $args = array(
            'id'    => 'tenweb-trial-flow',
            'title' => '<span class="tenweb-trial-flow__title">10Web <br>free trial</span>',
            'meta'  => array(
	            'class' => 'hidden',
            )
        );
	    $wp_admin_bar->add_node($args);
    }

    public function add_action_link($links, $file)
    {
        if (TWO_BASENAME === $file) {
            $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=two_settings_page')) . '">' . __('Settings') . '</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    public function check_site_state()
    {
        $screen_id = !empty($_POST['screen_id']) ? $_POST['screen_id'] : null;
        $current_blog_id = !empty($_POST['current_blog_id']) ? $_POST['current_blog_id'] : 1;
        Helper::check_site_state(false, $screen_id, $current_blog_id);
    }

    public function populate_agreement_info()
    {
        $user_info = Helper::get_tenweb_user_info();
        $this->user_info = $user_info['client_info'];
        $this->user_agreements = $user_info['agreement_info'];
    }

    public function check_site_state_after_action()
    {
        wp_cache_delete('plugins', 'plugins');

        Helper::check_site_state();
    }

    public function tenweb_check_site_state_after_action()
    {
        wp_cache_delete('plugins', 'plugins');
        wp_clean_themes_cache(false);

        Helper::check_site_state();
    }

    public function admin_enqueue_scripts($hook_suffix)
    {
        $screen = get_current_screen();
        $products_diff = Helper::get_products_diff();
        $site_info_diff = Helper::get_site_info_diff($screen);
        wp_enqueue_script(TENWEB_PREFIX . '_populate_agreement_info_js', TENWEB_URL . '/assets/js/populate_agreement_info.js', array('jquery'), TENWEB_VERSION);
        wp_localize_script(TENWEB_PREFIX . '_populate_agreement_info_js', TENWEB_PREFIX . "_populate_agreement_info", array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));
        if (($products_diff || $site_info_diff) && (($this->tenweb_screen || in_array($screen->id, $this->other_pages)) || (isset($_GET['page']) && ($_GET['page'] == 'WDD_plugins' || $_GET['page'] == 'WDD_themes' || $_GET['page'] == 'WDD_addons')))
            || (isset($_GET['page']) && $_GET['page'] == "two_settings_page" && isset($_GET['two_after_activation']) && $_GET['two_after_activation'])) {
            wp_enqueue_script(TENWEB_PREFIX . '_send_state_js', TENWEB_URL . '/assets/js/send_state.js', array('jquery'), TENWEB_VERSION);
            $current_blog_id = 1;
            if (is_multisite()) {
                if ($screen->id == 'site-info-network' && isset($_GET['id'])) {
                    $current_blog_id = $_GET['id'];
                } else {
                    $current_blog_id = get_current_blog_id();
                }
            }
            wp_localize_script(TENWEB_PREFIX . '_send_state_js', TENWEB_PREFIX . "_state", array(
                'ajaxurl'         => admin_url('admin-ajax.php'),
                'screen_id'       => $screen->id,
                'current_blog_id' => $current_blog_id,
            ));
        }

        wp_enqueue_script(TENWEB_PREFIX . '_send_pagespeed_js', TENWEB_URL . '/assets/js/send_pagespeed.js', array('jquery'), TENWEB_VERSION);
        wp_localize_script(TENWEB_PREFIX . '_send_pagespeed_js', TENWEB_PREFIX . "_pagespeed", array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));

        wp_register_style(TENWEB_PREFIX . '_styles_whitelabel', TENWEB_URL . '/assets/css/whitelabel.css', array(), TENWEB_VERSION);
        wp_enqueue_style(TENWEB_PREFIX . '_styles_whitelabel');

        if ( $this->trial_flow || $this->wvc) {
	        $agreement_date = (isset($this->user_agreements['agreement_date'])) ? $this->user_agreements['agreement_date'] : '';
	        $clients_id = (isset($this->user_agreements['clients_id'])) ? $this->user_agreements['clients_id'] : 0;
            wp_enqueue_script( TENWEB_PREFIX . '_trial_flow', TENWEB_URL . '/assets/js/tenweb_trial_flow.js', array( 'jquery' ), TENWEB_VERSION );
	        wp_localize_script( TENWEB_PREFIX . '_trial_flow', TENWEB_PREFIX . "_trial_flow", array(
		        'ajaxurl'    => admin_url( 'admin-ajax.php' ),
		        'ajaxnonce'  => wp_create_nonce( TENWEB_PREFIX . '_tf_nonce' ),
		        'dashboard'  => TENWEB_DASHBOARD,
		        'plugin_url' => TENWEB_URL,
		        'agreement_date' => $agreement_date,
		        'clients_id' => $clients_id,
		        'send_ga_event' => defined('TENWEB_SEND_GA_EVENT') ? TENWEB_SEND_GA_EVENT : 'https://core.10web.io/api/send-ga-event'
	        ) );
	        wp_enqueue_style( TENWEB_PREFIX . '_trial_flow', TENWEB_URL . '/assets/css/tenweb_trial_flow.css', array(), TENWEB_VERSION );
        }


        /*enqueue scripts and styles only on our page*/
        if ($this->tenweb_screen == false) {
            return;
        }
        //$rest_route = get_rest_url() . TENWEB_REST_NAMESPACE . '/action';
        $rest_route = add_query_arg(array(
            'rest_route' => '/' . TENWEB_REST_NAMESPACE . '/action'
        ), get_home_url() . "/");

        $workspace_id = \TenwebServices::get_workspace_id();
        $route = TENWEB_API_URL . '/manager/upate-manager';
        $auth_header = get_site_option(TENWEB_PREFIX . '_access_token');
        $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');

        wp_enqueue_script('player_api', 'https://www.youtube.com/player_api', array(), TENWEB_VERSION);
        wp_register_script(TENWEB_PREFIX . '_scripts_main', TENWEB_URL . '/assets/js/main.js', array(), TENWEB_VERSION);
        wp_register_script(TENWEB_PREFIX . '_scripts_caret', TENWEB_URL . '/assets/js/jquery.caret.min.js', array(), TENWEB_VERSION);
        wp_register_script(TENWEB_PREFIX . '_scripts_tageditor', TENWEB_URL . '/assets/js/jquery.tag-editor.min.js', array(), TENWEB_VERSION);
        wp_enqueue_script(TENWEB_PREFIX . '_scripts_main');
        wp_enqueue_script(TENWEB_PREFIX . '_scripts_caret');
        wp_enqueue_script(TENWEB_PREFIX . '_scripts_tageditor');
        wp_localize_script(TENWEB_PREFIX . '_scripts_main', TENWEB_PREFIX, array(
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'ajaxnonce'       => wp_create_nonce('wp_rest'),
            'plugin_url'      => TENWEB_URL,
            'action_endpoint' => $route,
            'domain_id'       => $domain_id,
            'auth_header'     => $auth_header,
        ));

        if (Helper::check_if_manager_mu()) {
            wp_register_style(TENWEB_PREFIX . '_styles_main', TENWEB_URL . '/assets/css/main.css', array(), TENWEB_VERSION);
            wp_enqueue_style(TENWEB_PREFIX . '_styles_main');
        }
        wp_register_style(TENWEB_PREFIX . '_styles_login', TENWEB_URL . '/assets/css/login.css', array(), TENWEB_VERSION);
        wp_enqueue_style(TENWEB_PREFIX . '_styles_login');
        wp_enqueue_style(TENWEB_PREFIX . '_styles_backgrounds', TENWEB_URL . '/assets/css/backgrounds.css', array(), TENWEB_VERSION);

        wp_enqueue_style(TENWEB_PREFIX . '_styles_tageditor', TENWEB_URL . '/assets/css/jquery.tag-editor.css', array(), TENWEB_VERSION);
        //enqueue scripts and styles
    }

    //enqueue scripts on both admin and front
    public function enqueue_scripts()
    {
        if (file_exists(WPMU_PLUGIN_DIR . '/' . TENWEB_LO_SCRITP_PATH)) {
            wp_register_script(TENWEB_PREFIX . '_scripts_lo', WPMU_PLUGIN_URL . '/' . TENWEB_LO_SCRITP_PATH, array(), TENWEB_VERSION);
            wp_enqueue_script(TENWEB_PREFIX . '_scripts_lo');
        }
    }


    public function add_menu()
    {

        $company_name = Helper::get_company_name();
        $debug_mode = get_site_option(TENWEB_PREFIX . '_debug_mode');
        if (Helper::check_if_manager_mu() && defined('TENWEB_CACHE') && !in_array(TENWEB_CACHE, array('0', 'disabled'))) {
            $this->menu_ids[] = add_menu_page(
                __($company_name . ' Cache', TENWEB_LANG),
                __($company_name . ' Cache', TENWEB_LANG),
                'manage_options',
                TENWEB_PREFIX . '_cache_menu',
                array($this, 'display_cache'),
                Helper::get_cache_icon(),
                4
            );
        }


        if ((Helper::check_if_manager_mu() && $debug_mode == '1') || !Helper::check_if_manager_mu()) {

            $this->menu_ids[] = add_menu_page(
                __($company_name . ' Manager', TENWEB_LANG),
                __($company_name . ' Manager', TENWEB_LANG),
                'manage_options',
                TENWEB_PREFIX . '_menu',
                array($this, 'display_products'),
                TENWEB_URL_IMG . '/manager-logo.png',
                4
            );
        }

        if ($debug_mode == '1') {

            $this->menu_ids[] = add_submenu_page(
                TENWEB_PREFIX . '_menu',
                __($company_name . ' Products', TENWEB_LANG),
                __($company_name . ' Products', TENWEB_LANG),
                'manage_options',
                TENWEB_PREFIX . '_menu',
                array($this, 'display_products')
            );


            $this->menu_ids[] = add_submenu_page(
                TENWEB_PREFIX . '_menu',
                __($company_name . ' Config', TENWEB_LANG),
                __($company_name . ' Config', TENWEB_LANG),
                'manage_options',
                TENWEB_PREFIX . '_config',
                array($this, 'config_menu')
            );

            $this->menu_ids[] = add_submenu_page(
                TENWEB_PREFIX . '_menu',
                __($company_name . ' Logs', TENWEB_LANG),
                __($company_name . ' Logs', TENWEB_LANG),
                'manage_options',
                TENWEB_PREFIX . '_error_logs',
                array($this, 'error_logs_menu')
            );

            $this->menu_ids[] = add_submenu_page(
                TENWEB_PREFIX . '_menu',
                __($company_name . ' Migration Logs', TENWEB_LANG),
                __($company_name . ' Migration Logs', TENWEB_LANG),
                'manage_options',
                TENWEB_PREFIX . '_migration_logs',
                array($this, 'migration_logs_menu')
            );

            $this->menu_ids[] = add_submenu_page(
                TENWEB_PREFIX . '_menu',
                __($company_name . ' Php info', TENWEB_LANG),
                __($company_name . ' Php info', TENWEB_LANG),
                'manage_options',
                TENWEB_PREFIX . '_php_info',
                array($this, 'php_info_menu')
            );
        }

        if (is_multisite()) {
            foreach ($this->menu_ids as $key => $menu_id) {
                $this->menu_ids[$key] = $menu_id . '-network';
            }
        }

    }

    public function settings_page()
    {
        include_once TENWEB_VIEWS_DIR . '/optimizer-settings.php';
    }

    public function display_products()
    {

        $user_name = (isset($this->user_info['name'])) ? $this->user_info['name'] : null;
        /*Self update notice*/
        if ($this->class_login->check_logged_in()) {
            $manager = self::get_product_by('slug', '10web-manager', 'plugin', 'installed');

            if (!is_null($manager) && $manager->has_update()) {
                self::manager_update($manager->id);
            }
        }

        if ($this->class_login->check_logged_in()) {
            include_once TENWEB_VIEWS_DIR . '/products-menu.php';
        } else {
            $registration_link = $this->get_registration_link();
            if (isset($_GET["login"]) && $_GET["login"] == "1") {
                include_once TENWEB_VIEWS_DIR . '/login-menu.php';
            } else {
                $plugin_from = get_site_option("tenweb_manager_installed");
                if ($plugin_from === false) {
                    include_once TENWEB_VIEWS_DIR . '/tenweb-menu.php';
                } else {
                    $plugin_from = json_decode($plugin_from, true);
                    if (is_array($plugin_from)) {
                        $pugin_slug = key($plugin_from);
                        include_once TENWEB_VIEWS_DIR . '/plugins-from.php';
                    } else {
                        include_once TENWEB_VIEWS_DIR . '/tenweb-menu.php';
                    }
                }
            }

        }

    }

    public function display_cache()
    {
        $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');
        $tenweb_hosting_tools_page = TENWEB_DASHBOARD . '/websites/' . $domain_id . '/hosting/tools';
        include_once TENWEB_VIEWS_DIR . '/cache-menu.php';

    }

    public function error_logs_menu()
    {
        $logs = array_reverse(Helper::get_error_logs());
        $time_zone = date_default_timezone_get();

        include_once TENWEB_VIEWS_DIR . "/error-logs-menu.php";
    }

    public function migration_logs_menu()
    {
        $logs = array_reverse(Helper::get_migration_logs());
        $time_zone = date_default_timezone_get();
        $is_migration = true;

        include_once TENWEB_VIEWS_DIR . "/error-logs-menu.php";
    }

    /*show phpinfo for debug*/
    public function php_info_menu()
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        echo "
        <div id='TENWEB_phpinfo'>
            $phpinfo
        </div>
        ";
    }

    public function config_menu()
    {
        include_once TENWEB_VIEWS_DIR . "/configs.php";
    }

    public function set_products($reset = false)
    {
        $helper_instance = AuthHelper::get_instance();
        $helper_instance->set_products($reset);
    }
    public function remove_plugins_from_updates($plugins)
    {
        if (isset($GLOBALS['tenweb_update_process']) && $GLOBALS['tenweb_update_process'] == true) {
            return $plugins;
        }

        if (empty($plugins) || !is_object($plugins) || empty($plugins->response)) {
            return $plugins;
        }

        if (empty(self::$plugins['installed_products'])) {
            return $plugins;
        }

        foreach (self::$plugins['installed_products'] as $installed_product) {

            $state = $installed_product->get_state();
            if ($state->is_paid == false) {
                continue;
            }

            $wp_slug = $installed_product->get_wp_slug();
            if (isset($plugins->response[$wp_slug])) {
                unset($plugins->response[$wp_slug]);
            }

        }

        return $plugins;
    }

    public function remove_themes_from_updates($themes)
    {

        if (isset($GLOBALS['tenweb_update_process']) && $GLOBALS['tenweb_update_process'] == true) {
            return $themes;
        }

        if (empty($themes) || !is_object($themes) || empty($themes->response)) {
            return $themes;
        }

        if (empty(self::$themes['installed_products'])) {
            return $themes;
        }

        foreach (self::$themes['installed_products'] as $installed_product) {

            $state = $installed_product->get_state();
            if ($state->is_paid == false) {
                continue;
            }

            $wp_slug = $installed_product->slug;
            if (isset($themes->response[$wp_slug])) {
                unset($themes->response[$wp_slug]);
            }
        }

        return $themes;
    }


    public function get_products_raw_data()
    {
        return AuthHelper::$products_raw_data;
    }

    public function is_tenweb_screen()
    {
        $screen = get_current_screen();
        $this->tenweb_screen = (!($screen == null || !in_array($screen->id, $this->menu_ids)));
    }

    public function init_rest_api()
    {
        require_once TENWEB_INCLUDES_DIR . '/class-rest-api.php';
        RestApi::get_instance();
    }

    public function clear_logs()
    {
        if (check_ajax_referer('wp_rest', 'tenweb_nonce', false)) {
            delete_site_transient(TENWEB_PREFIX . '_error_logs');
        }
    }

    public function clear_migration_logs()
    {
        if (check_ajax_referer('wp_rest', 'tenweb_nonce', false)) {
            Helper::flush_migration_log();
        }
    }

    public function clear_cache()
    {
        if (check_ajax_referer('wp_rest', 'tenweb_nonce', false)) {
            Helper::clear_cache();
        }
    }

    public function save_configs()
    {
        if (check_ajax_referer('wp_rest', 'tenweb_nonce', false)) {
            Helper::save_configs($_POST);
        }
    }

    public function delete_banned_ip()
    {
        if (check_ajax_referer('wp_rest', 'tenweb_nonce', false)) {
            if (isset($_POST['ips'])) {
                Helper::delete_banned_ip($_POST['ips']);
            }
        }
    }

    public function check_curl()
    {
        if (check_ajax_referer('wp_rest', 'tenweb_nonce', false)) {

            $urls = array(
                'https://www.google.com/',
                'https://core.10web.io/',
                'https://manager.10web.io/',
                'https://optimizer.10web.io/',
                'https://backup.10web.io/',
                'https://seo.10web.io/'
            );

            $data = array();
            foreach ($urls as $url) {

                $response = wp_remote_get($url);

                if (is_wp_error($response)) {
                    $data[] = array('success' => false, 'url' => $url, 'error_msg' => $response->get_error_message());
                } else {
                    $data[] = array('success' => true, 'url' => $url, 'error_msg' => "");
                }

            }

            die(json_encode($data));
        }
    }

    public function login_user()
    {

        if (!check_ajax_referer('tenweb_login_nonce', 'tenweb_nonce', false)) {
            $error = array(
                'error'   => 'nonce_error',
                'message' => 'Wrong nonce.'
            );
            die(json_encode($error));
        }

        $data = $this->class_login->login();

        if (!$this->class_login->check_logged_in()) {
            die(json_encode($this->class_login->get_errors()));
        }
    }

    private function load_classes()
    {
        include_once TENWEB_INCLUDES_DIR . '/class-api.php';
       // include_once TENWEB_INCLUDES_DIR . '/product-state.php';
        include_once TENWEB_INCLUDES_DIR . '/class-user.php';
        //   include_once TENWEB_INCLUDES_DIR . '/class-login-old.php';
       // include_once TENWEB_INCLUDES_DIR . '/class-product.php';
        include_once TENWEB_INCLUDES_DIR . '/interface-product-actions.php';
       // include_once TENWEB_INCLUDES_DIR . '/class-installed-plugin.php';
       // include_once TENWEB_INCLUDES_DIR . '/class-installed-theme.php';
        include_once TENWEB_INCLUDES_DIR . '/class-migration-run.php';
        require_once TENWEB_INCLUDES_DIR . '/class-cache.php';
        require_once TENWEB_INCLUDES_DIR . '/class-whitelabel.php';

        include_once TENWEB_INCLUDES_DIR . '/navigation/navigation.php';
        include_once TENWEB_DIR . '/vendor/autoload.php';

    }

//    private function send_availability_request()
//    {
//
//        delete_option(TENWEB_PREFIX . '_activated');
//
//        $api = Api::get_instance();
//        if ($api->availability_request() === true) {
//            delete_site_transient(TENWEB_PREFIX . '_send_states_transient');
//        }
//
//    }

    public function register_from_dashboard($parameters = null)
    {
        if (empty($parameters)) {
            $parameters = array();
            $parameters['email'] = !empty($_GET['email']) ? sanitize_email($_GET['email']) : null;
            $parameters['token'] = !empty($_GET['token']) ? sanitize_text_field($_GET['token']) : null;
            $parameters['new_connection_flow'] = !empty($_GET['new_connection_flow']) ? rest_sanitize_boolean($_GET['new_connection_flow']) : null;
            $parameters['sign_up_from_free_plugin'] = !empty($_GET['sign_up_from_free_plugin']) ? rest_sanitize_boolean($_GET['sign_up_from_free_plugin']) : null;
        }
        $email = !empty($parameters['email']) ? sanitize_email($parameters['email']) : null;
        $token = !empty($parameters['token']) ? sanitize_text_field($parameters['token']) : null;
        $new_connection_flow = !empty($parameters['new_connection_flow']);
        $sign_up_from_free_plugin = !empty($parameters['sign_up_from_free_plugin']);
        if (!empty($email) && !empty($token)) {
            $pwd = md5($token);
            $args = ['connected_from'=>'manager'];
            if ($this->class_login->login($email, $pwd, $token, $args) == true && $this->class_login->check_logged_in()) {
                if($new_connection_flow) {
                    die(json_encode(array("connected_domain_id" => get_site_option(TENWEB_PREFIX . '_domain_id'))));
                }
                Helper::get_tenweb_user_info(true);
                $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');
                $url = TENWEB_DASHBOARD . '/websites?website_connected=' . $domain_id;
                if ($sign_up_from_free_plugin) {
                    $url .= '&from_free_plugin=1';
                }
                die('<script>window.location.href="' . $url . '"</script>');
            } else {
                $errors = $this->class_login->get_errors();
                $err_msg = (!empty($errors)) ? $errors['message'] : 'Something went wrong.';
                update_site_option(TENWEB_PREFIX . '_connect_error', $err_msg);
                return false;
            }

        }
        if (is_multisite()) {
            die('<script>window.location.href="' . network_admin_url() . 'admin.php?page=tenweb_menu' . '"</script>');
        }
        die('<script>window.location.href="' . get_admin_url() . 'admin.php?page=tenweb_menu' . '"</script>');
    }

    /**
     * @param array $args [sign_up_from_free_plugin, client_email, name]
     *
     * @return mixed
     */
    public function get_registration_link($args = [])
    {
        $return_url = get_admin_url() . 'admin.php';
        if (is_multisite()) {
            $return_url = network_admin_url() . 'admin.php';
        }

        $return_url_args = array('page' => 'tenweb_menu');

        // $token = md5(get_admin_url().'10web_register_url');
       // set_site_transient(TENWEB_PREFIX . '_token', $token, 60 * 10);
        $token =  wp_create_nonce('10web_register_url');
        update_site_option(TENWEB_PREFIX . '_saved_nonce', $token );
        $register_url_args = array(
            'site_url'   => urlencode(get_site_url()),
            'utm_source' => '10webplugin',
            'utm_medium' => 'freeplugin',
            'new_connection_flow' => 1,
            'nonce' =>  $token,
        );

        if (!empty($args['old_connection_flow'])){
            unset($register_url_args['new_connection_flow']);
        }

        if (!empty($args)) {
            $register_url_args = $register_url_args + $args;
            $return_url_args = $return_url_args + $args;
        }

        $register_url_args['return_url'] = urlencode(add_query_arg($return_url_args, $return_url));

        $plugin_from = get_site_option("tenweb_manager_installed");
        if ($plugin_from !== false) {
            $plugin_from = json_decode($plugin_from, true);
            if (is_array($plugin_from) && reset($plugin_from) !== false) {
                $register_url_args['plugin_id'] = reset($plugin_from);
                if (isset($plugin_from["type"])) {
                    $register_url_args['utm_source'] = $plugin_from["type"];
                }
            }
        }

        //    $user = wp_get_current_user();
        //    if(isset($user->data->ID)) {
        //      $register_url_args['email'] = $user->data->user_email;
        //      $register_url_args['first_name'] = get_user_meta($user->data->ID, 'first_name', true);
        //      $register_url_args['last_name'] = get_user_meta($user->data->ID, 'last_name', true);
        //    }

        $url = add_query_arg($register_url_args, TENWEB_DASHBOARD . '/sign-up/');

        return $url;
    }

    public function check_debug_mode()
    {
        if (isset($_GET['tenweb_debug'])) {
            if ($_GET['tenweb_debug'] == '1') {
                update_site_option(TENWEB_PREFIX . '_debug_mode', '1');
            } else {
                delete_site_option(TENWEB_PREFIX . '_debug_mode');
            }
        }

        add_action('wp_ajax_' . TENWEB_PREFIX . '_clear_logs', array($this, 'clear_logs'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_clear_migration_logs', array($this, 'clear_migration_logs'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_clear_cache', array($this, 'clear_cache'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_check_curl', array($this, 'check_curl'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_save_configs', array($this, 'save_configs'));
        add_action('wp_ajax_' . TENWEB_PREFIX . '_delete_banned_ip', array($this, 'delete_banned_ip'));
    }

    public function multisite_notice()
    {
        ?>
        <div class="notice notice-error">
            <p>"Ten web manager does not support multisite yet."</p>
        </div>
        <?php
    }

    public function tenweb_send_site_state()
    {
        delete_site_transient(TENWEB_PREFIX . '_send_states_transient');
        Helper::check_site_state();
    }

    public function add_request_timeout($args, $url)
    {

        if (strpos($url, '10web.io') !== false) {
            $args['timeout'] = 15;
        }

        return $args;
    }

    public function admin_notices()
    {

        if (!$this->tenweb_screen) {
            return;
        }

        $site_info = Helper::get_site_info();
        if ($site_info['other_data']['file_system']['config'] == false) {
            $text = '<b>10Web Manager plugin does not have file system permissions.</b> Please <a target="_blank"
             href="https://help.10web.io/hc/en-us/articles/13959935035410">troubleshoot the file permissions issue</a> to proceed.';
            Helper::add_notices($text);
        }
        /*if (true || get_site_option(TENWEB_PREFIX . '_is_available') !== '1') {
            $text = '<b>10Web Manager plugin does not have file system permissions to install, update and delete products. You can download your purchased plugins <a href="' . TENWEB_DASHBOARD . '/websites?from_local=1" target="_blank">here</a>. In order to fix the permission issue, please contact our customer care <a href="https://help.10web.io/hc/en-us/requests/new" target="_blank">team</a>.</b>';
            Helper::add_notices($text);
        }*/

        $notices = Helper::get_notices();
        foreach ($notices as $notice) {
            echo $notice;
        }

    }

    public function connection_notices()
    {
        $connect_error = get_site_option(TENWEB_PREFIX . '_connect_error');
        $screen = get_current_screen();
        echo '<div class="notice is-dismissible error tenweb_manager_notice ' . ($screen->parent_base == "tenweb_menu" ? "tenweb_menu_notice" : "") . '"><p>' . $connect_error . '</p></div>';

        delete_option(TENWEB_PREFIX . '_connect_error');
    }

    private function add_paid_products_update_msg()
    {
        /*if (is_multisite()) {
            return;
        }*/

        if (!empty(self::$plugins['installed_products'])) {
            foreach (self::$plugins['installed_products'] as $installed_product) {
                $state = $installed_product->get_state();

                if ($state->is_paid && $installed_product->has_update()) {
                    add_action('after_plugin_row_' . $installed_product->get_wp_slug(), array($this, 'plugin_update_msg'), 10, 2);
                }
            }
        }

        if (!empty(self::$addons['installed_products'])) {
            foreach (self::$addons['installed_products'] as $installed_product) {
                $state = $installed_product->get_state();

                if ($state->is_paid && $installed_product->has_update()) {
                    add_action('after_plugin_row_' . $installed_product->get_wp_slug(), array($this, 'plugin_update_msg'), 10, 2);
                }
            }
        }


        //    if(!empty(self::$themes['installed_products'])) {
        //      foreach(self::$themes['installed_products'] as $installed_product) {
        //
        //        if($installed_product->is_paid && $installed_product->has_update()) {
        //          add_action('after_theme_row_' . $installed_product->slug, array($this, 'theme_update_msg'), 10, 2);
        //
        //        }
        //      }
        //    }
    }

    public function plugin_update_msg($file, $plugin_data)
    {
        $active_class = is_plugin_active($file) ? ' active' : '';
        $wp_list_table = _get_list_table('WP_MS_Themes_List_Table');

        $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');
        $dashboard_url = TENWEB_DASHBOARD . '/websites/' . $domain_id . '/plugins/installed/';

        $product = null;

        foreach (self::$plugins['installed_products'] as $installed_product) {
            if ($installed_product->get_wp_slug() === $file) {
                $product = $installed_product;
                break;
            }
        }

        if ($product === null) {
            foreach (self::$addons['installed_products'] as $installed_product) {
                if ($installed_product->get_wp_slug() === $file) {
                    $product = $installed_product;
                    break;
                }
            }
        }

        $dashboard_url .= "?show_changelog=" . $product->id;
        $version = $product->latest_versions['paid'];
        ?>
        <tr class="plugin-update-tr <?php echo $active_class; ?>"
            id="<?php echo esc_attr($product->slug . '-update'); ?>"
            data-slug="<?php echo esc_attr($product->slug); ?>"
            data-plugin="<?php echo esc_attr($file); ?>">
            <td colspan="<?php echo esc_attr((string)$wp_list_table->get_column_count()); ?>"
                class="plugin-update colspanchange">
                <div class="update-message notice inline notice-warning notice-alt">
                    <p>
                        There is a new version of <?php echo $plugin_data['Name']; ?> available.
                        <a href="<?php echo $dashboard_url; ?>" target="_blank">
                            View version <?php echo $version; ?> details
                        </a> or
                        <a href="<?php echo $dashboard_url; ?>" target="_blank">update now.</a>
                    </p>
                </div>
            </td>
        </tr>
        <?php

    }

    public static function get_plugins()
    {
        return self::$plugins;
    }

    public static function get_addons()
    {
        return self::$addons;
    }

    public static function get_themes()
    {
        return self::$themes;
    }

    /**
     * @param $search_by
     * @param $search_value
     * @param $type
     * @param $search_on
     * @return \Tenweb_Authorization\InstalledPlugin|mixed
     */
    public static function get_product_by($search_by, $search_value, $type = "all", $search_on = "all")
    {
        $plugins = get_site_option(TENWEB_PREFIX . '_plugins_list');
        $themes = get_site_option(TENWEB_PREFIX . '_themes_list');
        $addons = get_site_option(TENWEB_PREFIX . '_addons_list');
        $products_objects = Helper::get_products_objects($plugins, $themes, $addons);

        self::$plugins = $products_objects['plugins'];
        self::$themes = $products_objects['themes'];
        self::$addons = $products_objects['addons'];


        $value = null;

        if ($type == "all" || $type == "plugin") {

            if (($search_on == "all" || $search_on == "installed") && !empty(self::$plugins['installed_products'])) {
                $value = self::get_product_from_list(self::$plugins['installed_products'], $search_by, $search_value);
                if ($value != null) {
                    return $value;
                }
            }

            if (($search_on == "all" || $search_on == "no_installed") && !empty(self::$plugins['products'])) {
                $value = self::get_product_from_list(self::$plugins['products'], $search_by, $search_value);
                if ($value != null) {
                    return $value;
                }
            }

        }

        if ($type == "all" || $type == "theme") {

            if (($search_on == "all" || $search_on == "installed") && !empty(self::$themes['installed_products'])) {
                $value = self::get_product_from_list(self::$themes['installed_products'], $search_by, $search_value);
                if ($value != null) {
                    return $value;
                }
            }

            if (($search_on == "all" || $search_on == "no_installed") && !empty(self::$themes['products'])) {
                $value = self::get_product_from_list(self::$themes['products'], $search_by, $search_value);
                if ($value != null) {
                    return $value;
                }
            }

        }

        if ($type == "all" || $type == "addon") {

            if (($search_on == "all" || $search_on == "installed") && !empty(self::$addons['installed_products'])) {
                $value = self::get_product_from_list(self::$addons['installed_products'], $search_by, $search_value);
                if ($value != null) {
                    return $value;
                }
            }

            if (($search_on == "all" || $search_on == "no_installed") && !empty(self::$addons['products'])) {
                $value = self::get_product_from_list(self::$addons['products'], $search_by, $search_value);
                if ($value != null) {
                    return $value;
                }
            }

        }

        return null;
    }


    /**
     * @param array  $products_list list of WDDProduct objects
     * @param string $search_by     'id'|'slug'
     * @param        $search_value
     *
     * @return mixed|null
     * */
    public static function get_product_from_list($products_list, $search_by = "id", $search_value = '')
    {

        foreach ($products_list as $product) {
            if ($search_by == "id") {
                if ($product->id == $search_value) {
                    return $product;
                }
            } else if ($search_by == "slug") {
                if ($product->slug == $search_value) {
                    return $product;
                }
            }
        }

        return null;
    }

    public static function manager_update($id)
    {
        $html = '
<div id="twebman_overlay" class="twebman_update_modal">
    <div class="twebman_update"><p>' . __("New version of 10WEB Manager is available!", TENWEB_LANG) . '</p> <a
                id="self_update" data-id="' . $id . '">' . __("Please update now", TENWEB_LANG) . '<span
                    class="spinner"></span></a>';
        $html .= '
    </div>
</div>';
        echo $html;
    }

    public function deactivation_popup()
    {
        add_action('admin_footer', array($this, 'display_deactivation_popup'));
        if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'tenweb_manager_deactivate')) {
            add_action('admin_init', array($this, 'submit_and_deactivate'));
        }
    }

    public function display_deactivation_popup()
    {
        $screen = get_current_screen();

        if ($screen->base === 'plugins') {

            $deactivate_url =
                add_query_arg(
                    array(
                        'action'   => 'deactivate',
                        'plugin'   => TENWEB_SLUG,
                        '_wpnonce' => wp_create_nonce('deactivate-plugin_' . TENWEB_SLUG)
                    ),
                    admin_url('plugins.php')
                );

            $admin = wp_get_current_user();

            wp_enqueue_style('10web-manager-deactivation-popup', TENWEB_URL . '/assets/css/deactivation-popup.css', array(), TENWEB_VERSION);
            wp_enqueue_script('10web-manager-deactivation-popup', TENWEB_URL . '/assets/js/deactivation-popup.js', array(), TENWEB_VERSION);
            include_once 'views/deactivation-popup.php';
        }

    }

    public function submit_and_deactivate()
    {

        if (isset($_POST["tenweb_submit_and_deactivate"])) {

            if ($_POST["tenweb_submit_and_deactivate"] == 2 || $_POST["tenweb_submit_and_deactivate"] == 3) {

                $data = array();

                $admin_data = wp_get_current_user();

                $data["reason"] = isset($_POST["tenweb_manager_reasons"]) ? $_POST["tenweb_manager_reasons"] : "";
                $data["site_url"] = site_url();
                $data["product_id"] = TENWEB_MANAGER_ID;

                if ($data["reason"] === "reason_other") {

                    $data["additional_details"] = (!empty($_POST['tenweb_reason_other_textarea'])) ? $_POST['tenweb_reason_other_textarea'] : "";

                }

                $user_first_name = get_user_meta($admin_data->ID, "first_name", true);
                $user_last_name = get_user_meta($admin_data->ID, "last_name", true);

                $data["name"] = $user_first_name || $user_last_name ? $user_first_name . " " . $user_last_name : $admin_data->data->user_login;
                $data["email"] = !empty($_POST["tenweb_reason_other_email"]) ? $_POST["tenweb_reason_other_email"] : $admin_data->data->user_email;

                $response = wp_remote_post(TENWEB_DEACTIVATION_REASONS_URL, array(
                        'method'      => 'POST',
                        'timeout'     => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking'    => true,
                        'headers'     => array("Accept" => "application/x.10webcore.v1+json"),
                        'body'        => $data,
                        'cookies'     => array()
                    )
                );

                $response_body = (!is_wp_error($response) && isset($response["body"])) ? json_decode($response["body"], true) : null;
            }

            if ($_POST["tenweb_submit_and_deactivate"] == 2 || $_POST["tenweb_submit_and_deactivate"] == 1) {
                $deactivate_url =
                    add_query_arg(
                        array(
                            'action'   => 'deactivate',
                            'plugin'   => TENWEB_SLUG,
                            '_wpnonce' => wp_create_nonce('deactivate-plugin_' . TENWEB_SLUG)
                        ),
                        admin_url('plugins.php')
                    );
                echo '<script>window.location.href="' . $deactivate_url . '";</script>';
            }

        }
    }

    public static function get_instance()
    {
        if (null == self::$instance) {

            self::$instance = new self;
        }

        return self::$instance;
    }

    public static function redirect_to_requested_page()
    {
        if (!empty($_GET['redirect'])) {
            switch ($_GET['redirect']) {
                case 'edit_front_page':
                    $front_page_id = get_site_option('page_on_front', 0);
                    if ($front_page_id) {
                        wp_redirect(admin_url("/post.php?post=" . $front_page_id . "&action=elementor"), 301);
                        break;
                    }
                    wp_redirect(admin_url(), 301);
                    break;
                case 'view_front_page':
                    wp_redirect(get_site_url(), 301);
                    break;
                default:
                    break;
            }
        }
    }


    public function admin_notice_after_migrate()
    {

        wp_enqueue_style(TENWEB_PREFIX . '_notice', TENWEB_URL . '/assets/css/migrate-notice.css', array(), TENWEB_VERSION);
        $user_agreement = get_option('tenweb_user_agreements');
        $subscription = $user_agreement ? $user_agreement['subscription_id'] : '';

        if (get_transient("tenweb_initial_score") && get_transient("tenweb_hosted_site_region") && $subscription == 211) {
            $migrated_score = get_option("tenweb_migrated_score");
            $initial_score = get_transient("tenweb_initial_score");
            $migrated_mobile_score = $migrated_score['mobile_score'];
            $migrated_desktop_score = $migrated_score['desktop_score'];
            $migrated_score_mob_bg_color = 'red';
            $migrated_score_pc_bg_color = 'red';
            if ($migrated_mobile_score >= 90) {
                $migrated_score_mob_bg_color = 'green';
            }
            if ($migrated_desktop_score >= 90) {
                $migrated_score_pc_bg_color = 'green';
            }

            $migrated_url = $migrated_score['url'];
            $migrated_domain_id = 0;
            if (get_transient('tenweb_migrate_domain_id_notice')) {
                $migrated_domain_id = get_transient('tenweb_migrate_domain_id_notice');
            }
            $initial_score_pc_bg_color = 'red';
            $initial_score_mob_bg_color = 'red';
            if ($initial_score['mobile_score'] && $initial_score['desktop_score']) {
                $initial_mobile_score = $initial_score['mobile_score'];
                $initial_desktop_score = $initial_score['desktop_score'];
                if ($initial_mobile_score >= 90) {
                    $initial_score_mob_bg_color = 'green';
                }
                if ($initial_desktop_score >= 90) {
                    $initial_score_pc_bg_color = 'green';
                }
            } else {
                $initial_mobile_score = '-';
                $initial_desktop_score = '-';
                $initial_score_pc_bg_color = 'gray';
                $initial_score_mob_bg_color = 'gray';
            }

            global $tenweb_regions;

            $hosted_site_region = get_transient("tenweb_hosted_site_region");
            $datacenter = substr($hosted_site_region, 0, strrpos($hosted_site_region, '-'));
            $datacenter_name = null;
            if ($tenweb_regions[$datacenter]) {
                $datacenter_name = $tenweb_regions[$datacenter];
            }

            echo '
<div class="notice notice-success  tenweb-after-migrate">
             <div class="tenweb-after-migrate-notice">
             <div class="tenweb-info">
                <span class="tenweb-logo">
                  <img src="' . TENWEB_URL_IMG . '/10web-logo-slogan.svg' . '" /> 
                 </span>
            <h4>The Copy of Your Website on 10Web Hosting is Ready</h4>
            <p class="info-head">Location / Data Center</p>
            <p>' . $datacenter_name . '</p>
            <hr class="tenweb-info-divider"/>
            <img class="tenweb-google-partner" src="' . TENWEB_URL_IMG . '/google-partner.png' . '" /> 
             </div>
             
             <div class="tenweb-scores">
             <h1>You got an optimized PageSpeed score & 99.9% Uptime</h1>
             <div class="scores-info">
             <div class="old-score">
                 <h4>Your Live Website</h4>
                 <hr/>
                 <div class="score-item mobile tenweb_' . $initial_score_mob_bg_color . '">                 
                  <img src="' . TENWEB_URL_IMG . '/mobile_icon.svg' . '" />
                    <span class="score-data">' . $initial_mobile_score . '</span> <span class="score-type">Mobile Score</span>
                </div>
                 <hr/>
                <div class="score-item tenweb_' . $initial_score_pc_bg_color . '">
                 <img src="' . TENWEB_URL_IMG . '/desktop_icon.svg' . '" />
                 <span class="score-data">' . $initial_desktop_score . '</span> <span class="score-type">Desktop Score</span>
                </div>
             </div>
             <div class="new-score">
             <h4>Copy of Your Website <span>on 10Web</span></h4>
              <hr/>
             <div class="score-item mobile tenweb_' . $migrated_score_mob_bg_color . '">             
                <img src="' . TENWEB_URL_IMG . '/mobile_icon.svg' . '" />
                <span class="score-data">' . $migrated_mobile_score . '</span> <span class="score-type">Mobile Score</span>
                </div>
                 <hr/>
                <div class="score-item tenweb_' . $migrated_score_pc_bg_color . '">
                 <img src="' . TENWEB_URL_IMG . '/desktop_icon.svg' . '" />
                <span class="score-data">' . $migrated_desktop_score . '</span> <span class="score-type">Desktop Score</span>
                </div>
             </div>
</div>
<p>You can always check your score with <a href="https://developers.google.com/speed/pagespeed/insights/?url=' . $migrated_url . ' " target="_blank"> Google PageSpeed Insights</a> or <a href="https://gtmetrix.com/">GTMetrix</a></p>
<a class="tenweb-explore tenweb-pagespeed" href="' . TENWEB_DASHBOARD . '/websites/' . $migrated_domain_id . '/overview?fromBanner=1" data-label="2" target="_blank">EXPLORE & TEST</a>
<a class="tenweb-after-migrate-dismissed tenweb-pagespeed " href="#" data-label="3"><img src="' . TENWEB_URL_IMG . '/close-notice.svg' . '" /></a>
</div>
          </div>   
         </div>';
        }
    }

    public function tenweb_notice_after_migrate_dismissed()
    {
        if (isset($_POST['label']) && $_POST['label'] == 3) {
            $api = Api::get_instance();
            $result = $api->send_pagespeed($_POST['label']);
            delete_transient('tenweb_initial_score');
            delete_transient('tenweb_hosted_site_region');
            delete_transient('tenweb_migrate_domain_id_notice');
            delete_transient('tenweb_send_pagespeed_1');
            delete_transient('tenweb_send_pagespeed_2');
            delete_transient('tenweb_send_pagespeed_3');
        }
    }

    public function tenweb_send_pagespeed()
    {
        $api = Api::get_instance();
        if ($_POST['label']) {
            $label = $_POST['label'];
            $result = false;

            if (!get_transient('tenweb_send_pagespeed_' . $label)) {
                $result = $api->send_pagespeed($label);
            };

            if ($result == true && $label == 1) {
                set_transient('tenweb_send_pagespeed_1', 1, 2 * WEEK_IN_SECONDS);
            } else if ($result == true && $label == 2) {
                set_transient('tenweb_send_pagespeed_2', 1, 2 * WEEK_IN_SECONDS);
            } else if ($result == true && $label == 3) {
                set_transient('tenweb_send_pagespeed_3', 1, 2 * WEEK_IN_SECONDS);
            }
        }

    }

    /**
     * Check and fetch agency dashboard URL if user has agency_suite service
     */
    public function check_and_fetch_agency_dashboard_url()
    {
        $is_agency_customer = get_option('tenweb_agency_customer', null);
        
        if ($is_agency_customer === '0') {
            return;
        }
        
        $user_agreement = get_site_option('tenweb_user_info');
        
        $has_agency_suite = false;
        if (!empty($user_agreement['agreement_info']['plan']['services']['agency_suite'])) {
            $has_agency_suite = true;
        }
        
        $tenweb_agency_dashboard_url = get_option("tenweb_agency_dashboard_url", "");
        
        if ($has_agency_suite && empty($tenweb_agency_dashboard_url)) {
            $transient_key = TENWEB_PREFIX . '_agency_dashboard_url_request_blocked';
            if (get_transient($transient_key)) {
                return;
            }
            
            $this->fetch_agency_dashboard_url_from_core();
        }
    }

    /**
     * Fetch agency dashboard URL from core service
     */
    private function fetch_agency_dashboard_url_from_core()
    {
        $domain_id = get_site_option('tenweb_domain_id');
        $workspace_id = \TenwebServices::get_workspace_id();
        
        if (empty($domain_id) || empty($workspace_id)) {
            set_transient(TENWEB_PREFIX . '_agency_dashboard_url_request_blocked', '1', 30 * MINUTE_IN_SECONDS);
            return;
        }

        $core_url = TENWEB_CORE_URL . '/api/workspaces/' . $workspace_id . '/domains/' . $domain_id . '/information';
        
        $args = array(
            'method' => 'GET',
            'headers' => array(
                'Accept' => 'application/x.10webcore.v1+json',
            ),
        );
        
        $api = \Tenweb_Manager\Api::get_instance();
        $response = $api->request($core_url, $args, 'get_agency_dashboard_url');
        
        if ($response != null && !isset($response['error']) && isset($response['status']) && $response['status'] === 'ok') {
            if (isset($response['agency_customer'])) {
                $agency_customer = $response['agency_customer'] ? '1' : '0';
                update_option('tenweb_agency_customer', $agency_customer);
            }
            
            if (isset($response['company_whitelabel_data']) && is_array($response['company_whitelabel_data'])) {
                update_option('tenweb_company_whitelabel_data', $response['company_whitelabel_data']);
            }
            
            if (isset($response['agency_customer']) && !$response['agency_customer']) {
                delete_option('tenweb_agency_dashboard_url');
                set_transient(TENWEB_PREFIX . '_agency_dashboard_url_request_blocked', '1', 30 * MINUTE_IN_SECONDS);
                return;
            }
            
            if (isset($response['agency_customer']) && $response['agency_customer'] && !empty($response['dashboard_url'])) {
                update_option('tenweb_agency_dashboard_url', sanitize_text_field($response['dashboard_url']));
                delete_transient(TENWEB_PREFIX . '_agency_dashboard_url_request_blocked');
            } else {
                set_transient(TENWEB_PREFIX . '_agency_dashboard_url_request_blocked', '1', 30 * MINUTE_IN_SECONDS);
            }
        } else {
            set_transient(TENWEB_PREFIX . '_agency_dashboard_url_request_blocked', '1', 30 * MINUTE_IN_SECONDS);
        }
    }

}
