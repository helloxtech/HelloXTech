<?php

namespace TenWebWooP\PaymentMethods;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

abstract class TenWebPaymentsBlock extends AbstractPaymentMethodType {

    public function initialize() {
        add_action('woocommerce_rest_checkout_process_payment_with_context', array( $this, 'process_payment' ), 11, 2);
    }

    public function is_active() {
        return ! empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
    }

    // I don't really understand why this function is required to be declared as it is passed to the old one.
    // But it does not work without this line.
    public function process_payment($context, &$result) {
    }

    public function get_payment_method_script_handles_for_admin() {
        return $this->get_payment_method_script_handles();
    }

    public function get_payment_method_data() {
        // Access to this in frontend like wc.wcSettings.getSetting( 'tenweb_payments_data' )
        return array('test_mode' => $this->get_setting('test_mode'));
    }
}
