<?php

namespace TenWebWooP\PaymentMethods\Stripe;

use TenWebWooP\Config;
use TenWebWooP\PaymentMethods\TenWebPaymentsBlock;

class TenWebPaymentsBlockStripe extends TenWebPaymentsBlock {

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var array
     */
    protected $account;

    /**
     * @var string
     */
    protected $public_key;

    public function initialize() {
        parent::initialize();

        $this->name = 'tenweb_payments_stripe';
        $this->settings = get_option('woocommerce_tenweb_payments_stripe_settings', array());
        $this->mode = isset($this->settings['test_mode']) && $this->settings['test_mode'] === 'no' ? 'live' : 'test';
        $this->account = Config::get_stripe_account($this->mode);
        $this->public_key = Config::get_stripe_keys($this->mode);
        wp_enqueue_style('twwp_payment_method_style', Config::get_url('PaymentMethods/Stripe', 'assets/style.css'), array(), Config::VERSION);
        wp_enqueue_script('twwp_stripe', 'https://js.stripe.com/v3/', array( 'jquery' ), null, false);
        wp_register_script('twwp_script', Config::get_url('PaymentMethods/Stripe', 'assets/script.js'), array('jquery', 'js-cookie'), Config::VERSION);
        wp_register_script('twwp_block_editor_script', Config::get_url('PaymentMethods/Stripe', 'assets/build/block-compiled.js'), array('react', 'react-dom', 'wc-blocks-registry', 'wp-dom-ready', 'wp-element', 'wp-i18n', 'wp-polyfill', 'twwp_stripe', 'twwp_script'), Config::VERSION, true);

        // Prepare data to be localized for use in JavaScript
        $localize_data = array(
            'stripe_public_key' => $this->public_key, // Stripe public key for client-side transactions
            'stripe_account_id' => $this->account['id'], // Stripe account ID for identification
            'cookiepath' => COOKIEPATH, // Path for storing cookies
            'cookiehash' => COOKIEHASH, // Hash for cookie name to prevent conflicts
            'ajaxurl' => admin_url('admin-ajax.php'), // URL for admin-ajax.php to handle AJAX requests
            'ajaxnonce' => wp_create_nonce('twwp_ajax_nonce'), // Nonce for securing AJAX requests
            'edit_page' => 'no',
        );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['elementor-preview']) || (isset($_GET['action']) && $_GET['action'] === 'edit' && is_admin())) {
            $localize_data['edit_page'] = 'yes';
        }

        // Retrieve Elementor global styles
        $styles_arr = TenWebPaymentsStripeHelper::get_elementor_global_styles();

        $localize_data = array_merge($localize_data, $styles_arr);

        // Localize script for use in front-end JavaScript
        wp_localize_script(
            'twwp_script', // Handle of the script being localized
            'twwp_config', // Name of the JavaScript object that will contain the localized data
            $localize_data  // Data array to be made available in the script
        );

        if (!empty($localize_data['payment_font_family'])) {
            $custom_css = "
                .tenweb-woocommerce-checkout .woocommerce-checkout *{
                        font-family: {$localize_data['payment_font_family']} !important;
                }";
            wp_add_inline_style('twwp_payment_method_style', $custom_css);
        }
    }

    public function get_payment_method_script_handles() {
        return array('twwp_block_editor_script');
    }
}
