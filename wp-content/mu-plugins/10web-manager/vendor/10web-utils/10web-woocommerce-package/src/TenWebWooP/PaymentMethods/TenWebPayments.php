<?php

namespace TenWebWooP\PaymentMethods;

use TenWebWooP\Config;
use WC_Payment_Gateway;

abstract class TenWebPayments extends WC_Payment_Gateway {

    const TAX_CODE_SEPARATOR = '::';

    public string $plugin_file = TENWEB_FILE;
    protected $mode = null;

    /**
     * @var string
     */
    protected $dashboard_url = null;

    /**
     * @var string[]
     */
    protected $merchant;

    /**
     * @return self
     */
    public static function get_instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function register_payment_method() {
        add_filter('woocommerce_payment_gateways', array(self::get_instance(), 'add_gateway_class'));
        add_action('woocommerce_blocks_loaded', array(self::get_instance(), 'add_gateway_block_support'));

        add_action('admin_enqueue_scripts', array( self::get_instance(), 'enqueue_admin_scripts' ));
        add_action('wp_enqueue_scripts', array( self::get_instance(), 'enqueue_scripts' ));
        $orderActions = new OrderActions();
    }

    public static function add_gateway_class($methods) {
        $methods[] = get_class(self::get_instance());

        return $methods;
    }

    public function __construct() {
        $this->id = 'tenweb_payments';
        $this->icon = '';
        $this->has_fields = true;
        $this->title = 'Credit card / Debit card';
        $this->method_title = '10Web Payments';
        $this->supports = array(
            'products',
            'refunds',
        );
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));
        add_filter('woocommerce_generate_twwp_setup_html', array($this, 'get_setup_html'));

        $this->dashboard_url = Config::get_dashboard_url('/ecommerce/payment-methods?setup_payment_method=1');
        $this->mode = $this->get_option('test_mode') === 'no' ? 'live' : 'test';
        $this->merchant = Config::get_payengine_data($this->mode);
        $this->init_form_fields();
        $this->init_settings();

        $this->method_description = $this->get_method_description();
        // Force disabling payment method if merchant is not 'active'
        $this->enabled = self::is_account_active() ? $this->enabled : 'no';

        add_filter('pre_update_option_woocommerce_tenweb_payments_settings', array( $this, 'maybe_set_payment_method_date' ), 10, 3);
        Config::maybe_set_hubspot_property();
    }

    public function process_admin_options() {
        parent::process_admin_options();
    }

    protected function is_account_active() {
        return 'active' === $this->merchant['merchant_status'];
    }

    public function maybe_set_payment_method_date($value, $old_value, $option) {
        if ('no' === $value['enabled'] && isset($old_value['enabled']) && 'yes' === $old_value['enabled']) {
            $value['disabled_at'] = time();
        }

        if (isset($old_value['enabled']) && 'no' === $old_value['enabled'] && 'yes' === $value['enabled']) {
            unset($value['disabled_at']);
        }

        return $value;
    }

    protected function set_order_status($order, $status, $transaction_id = '') {
        // If status is 'completed', use WooCommerce's payment_complete()
        // to automatically determine correct status based on product type
        if ($status === 'completed') {
            // Get transaction ID from order if not provided
            if (empty($transaction_id)) {
                $transaction_id = $order->get_transaction_id();
            }
            
            // Use WooCommerce's payment_complete() which automatically determines
            // the correct status (completed for virtual/downloadable, processing for physical)
            $order->payment_complete($transaction_id);
            
            // Save the actual status that WooCommerce set to metadata
            $actual_status = $order->get_status();
            $status_with_prefix = (0 !== strpos($actual_status, 'wc-')) ? 'wc-' . $actual_status : $actual_status;
            $order->update_meta_data('twwp_order_status', $status_with_prefix);
            $order->save();
        } else {
            // For other statuses (pending, failed, etc.), use direct status setting
            $status = (0 !== strpos($status, 'wc-')) ? 'wc-' . $status : $status;
            $order->update_meta_data('twwp_order_status', $status);
            $order->update_status($status);
        }
    }
}
