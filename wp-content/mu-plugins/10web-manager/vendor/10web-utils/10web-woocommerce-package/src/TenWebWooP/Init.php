<?php

namespace TenWebWooP;

use TenWebWooP\WoocommerceWidgets\Filter\WoocommerceProductFilters;

class Init {

    private static $instance = null;

    private function __construct() {
        // This is used also by 10web CoPilot as a way to authenticate the user for Wordpress REST API actions
        $this->initAuth();
        /*Check if WooCommerce is activated*/
        if (class_exists('woocommerce')) {
            $this->init();

            if (defined('TWBB_VERSION')) {
                $this->registerWidgets();
            }
        }
    }

    /**
     * @return Init|null
     */
    public static function getInstance() {
        if (self::$instance === null) {
            return new self();
        }

        return self::$instance;
    }

    public static function initBasicActions() {
        if (class_exists('woocommerce')) {
            Settings::trackIfOptionUpdated('woocommerce_default_country');
        }
    }

    private function init() {
        new Api();

        // Safe redirect to woocommerce email preview page with nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['twwp_preview_woocommerce_mail']) && 'true' === $_GET['twwp_preview_woocommerce_mail']) {
            wp_safe_redirect(str_replace('&amp;', '&', wp_nonce_url(admin_url('?preview_woocommerce_mail=true'), 'preview-mail')));
            exit;
        }

        // Retrieve the 'twwp_orders_cookies_data' option from the database
        $twwp_orders_cookies_data = get_option('twwp_orders_cookies_data');

        // Check if the retrieved data is an array
        if (is_array($twwp_orders_cookies_data)) {
            // Loop through each cookie data entry in the array
            foreach ($twwp_orders_cookies_data as $cookie_data) {
                // Check if the necessary 'payment_intent_cookie_id' is set and exists in the $_COOKIE superglobal
                if (isset($cookie_data['payment_intent_cookie_id']) && isset($_COOKIE[$cookie_data['payment_intent_cookie_id']])) {
                    // Set or update the cookie with an extended expiration time
                    wc_setcookie($cookie_data['payment_intent_cookie_id'], '', time() + 100);
                }
            }
        }

        // Delete the 'twwp_orders_cookies_data' option from the database after processing
        delete_option('twwp_orders_cookies_data');
        $user_agreement = get_option('tenweb_user_agreements');
        $subscription = $user_agreement && is_array($user_agreement) && isset($user_agreement['subscription_id']) ? $user_agreement['subscription_id'] : '';
        $is_agency = \Tenweb_Manager\Helper::is_agency_customer();
        if (defined('TWM_ENABLE_PAYMENT') && TWM_ENABLE_PAYMENT && !$is_agency) {
            $clientInfo = get_site_option(TENWEB_PREFIX . '_user_info');
            //show payment only if user has agreement for woocommerce

            /**
             * Convert any objects within $clientInfo to arrays using a recursive function
             * We use this because $clientInfo may contain a mix of objects (stdClass) and arrays.
             * Since we want to access all data consistently as arrays (even if it's initially an object),
             * we ensure that any object is recursively converted to an associative array.
             * This simplifies accessing the 'agreement_info', 'plan', 'services', and 'woocommerce' keys.
             * */
            $clientInfo = Utils::convert_objects_to_arrays($clientInfo);

            if (isset($clientInfo['agreement_info']['plan']['services']['woocommerce'])) {
                PaymentMethods\Stripe\TenWebPaymentsStripe::get_instance()->register_payment_method();
            }
            //phpcs:ignore Squiz.PHP.CommentedOutCode.Found
            //            PaymentMethods\Payengine\TenWebPaymentsPayengine::get_instance()->register_payment_method();
        }

        // Hide Woocommerce Marketplace Suggestions and Setup Wizard
        add_filter('woocommerce_allow_marketplace_suggestions', '__return_false');

        if (!get_option('woocommerce_onboarding_profile')) {
            add_option('woocommerce_onboarding_profile', array('skipped' => true));
        }

        if (!get_option('woocommerce_pre_install_woocommerce_payments_promotion_settings')) {
            add_option('woocommerce_pre_install_woocommerce_payments_promotion_settings', array('is_dismissed' => 'yes'));
        }

        if (!get_option('woocommerce_show_marketplace_suggestions')) {
            add_option('woocommerce_show_marketplace_suggestions', 'no');
        }
        // Hide Payment Method tasks in WooCommerce Onboarding
        add_filter('woocommerce_admin_features', function ($features) {
            $features = array_diff($features, array('payment-gateway-suggestions'));

            return $features;
        });
        // Hide Payment Method suggestions in WooCommerce Settings Payments tab
        if (!get_option('woocommerce_setting_payments_recommendations_hidden')) {
            add_option('woocommerce_setting_payments_recommendations_hidden', 'yes');
        }
        // This will remove WooPayments from suggestions and rename the list title
        add_filter('woocommerce_admin_payment_gateway_suggestion_specs', function ($payment_gateways) {
            $payment_gateways = array(
                array(
                    'id' => 'tenweb_payments',
                ),
            );

            return $payment_gateways;
        });
    }

    private function registerWidgets() {
        if (!defined('TWM_DISABLE_WOOCOMMERCE_PRODUCT_FILTER') || !TWM_DISABLE_WOOCOMMERCE_PRODUCT_FILTER) {
            WoocommerceProductFilters::getInstance();
        }
    }

    private function initAuth() {
        $woop_header = sanitize_text_field(isset($_SERVER[TenWebLoginRequest::TENWEB_WOOP_REST_AUTH_HEADER]) ? $_SERVER[TenWebLoginRequest::TENWEB_WOOP_REST_AUTH_HEADER] : null);

        if ($woop_header === '1') {
            $Authorization = new Authorization();
            $Authorization->init();
        }
    }
}
