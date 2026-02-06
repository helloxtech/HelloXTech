<?php

namespace TenWebWooP\PaymentMethods\Payengine;

use TenWebWooP\Config;
use TenWebWooP\PaymentMethods\TenWebPaymentsBlock;

class TenWebPaymentsBlockPayengine extends TenWebPaymentsBlock {

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var string[]
     */
    protected $merchant;

    public function initialize() {
        parent::initialize();

        $this->name = 'tenweb_payments';
        $this->settings = get_option('woocommerce_tenweb_payments_settings', array());
        $this->mode = isset($this->settings['test_mode']) && $this->settings['test_mode'] === 'no' ? 'live' : 'test';
        $this->merchant = Config::get_payengine_data($this->mode);
    }

    public function get_payment_method_script_handles() {
        wp_enqueue_style('twwp_payment_method_style', Config::get_url('PaymentMethods/Payengine', 'assets/style.css'), array(), Config::VERSION);
        wp_register_script('twwp_payengine', $this->merchant['script_url'], array( 'jquery' ), null, false);
        wp_register_script('twwp_script', Config::get_url('PaymentMethods/Payengine', 'assets/script.js'), array('jquery'), Config::VERSION);
        wp_register_script('twwp_block_editor_script', Config::get_url('PaymentMethods/Payengine', 'assets/build/block-compiled.js'), array('react', 'react-dom', 'wc-blocks-registry', 'wp-dom-ready', 'wp-element', 'wp-i18n', 'wp-polyfill', 'twwp_payengine', 'twwp_script'), Config::VERSION, true);
        wp_localize_script(
            'twwp_script',
            'twwp_config',
            array(
                'merchant_id' => $this->merchant['merchant_id'],
            )
        );

        return array('twwp_block_editor_script');
    }
}
