<?php

namespace TenWebWooP;

use TenWebWooP\PaymentMethods\Service;

class Config {

    const VERSION = '1.1.2';

    const PREFIX = 'twwp';

    const SERVICE_URL = array('payengine' => TENWEB_WOOCOM_API_URL . 'payments/', 'stripe' => TENWEB_WOOCOM_API_URL . 'payments-stripe/');

    const PAYENGINE_URL = array(
        'live' => 'https://console.payengine.co',
        'test' => 'https://console.payengine.dev',
    );

    public static function get_dir() {
        return __DIR__;
    }

    public static function get_url($component, $path) {
        return TENWEB_URL . '/vendor/10web-utils/10web-woocommerce-package/src/TenWebWooP/' . $component . '/' . $path;
    }

    public static function get_service_url($workspace_id, $domain_id, $endpoint = '', $provider = 'payengine') {
        return self::SERVICE_URL[$provider] . 'workspaces/' . $workspace_id . '/domains/' . $domain_id . '/' . $endpoint;
    }

    public static function get_dashboard_url($endpoint = '') {
        $domain_id = intval(get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id', 0));
        $dashboard_url = TENWEB_DASHBOARD . '/websites/' . $domain_id . $endpoint;

        return $dashboard_url;
    }

    public static function get_payengine_data($mode, $force_update = false) {
        $merchants = get_option(self::PREFIX . '_payengine_data');

        if (!$merchants || $force_update) {
            $merchants = Service::request('merchants/my?force=1');

            if ($merchants === false || $merchants === null) {
                $merchants = (object) array();
            }
            $merchants->twwp_last_updated_date = time();
            update_option(self::PREFIX . '_payengine_data', $merchants);
        }
        $merchant = array('merchant_id' => '', 'merchant_status' => '', 'merchant_hash' => '', 'public_key' => '', 'script_url' => '');

        if (isset($merchants->data) && is_array($merchants->data)) {
            foreach ($merchants->data as $m) {
                if (isset($m->env) && $mode === $m->env) {
                    $merchant['merchant_id'] = isset($m->merchant_id) ? $m->merchant_id : '';
                    $merchant['merchant_status'] = isset($m->merchant_status) ? $m->merchant_status : '';
                    $merchant['public_key'] = isset($m->data->public_key) ? $m->data->public_key : '';
                    $merchant['updated_at'] = isset($m->data->updated_at) ? $m->data->updated_at : '';

                    if (empty($merchant['public_key']) && !empty($m->public_key)) { //fallback to our in db saved public key
                        $merchant['public_key'] = $m->public_key;
                    }
                    $merchant['script_url'] = self::PAYENGINE_URL[$mode] . '/js/1.0.0/securefields.min.js?key=' . $merchant['public_key'];
                }
            }
        }

        // To handle the case when an old or corrupted state is saved. Forcing an update one time.
        // Allow force only once an hour.
        if ((!$merchant['merchant_id'] || 'active' !== $merchant['merchant_status'])
            && (!isset($merchants->twwp_last_updated_date) || time() - $merchants->twwp_last_updated_date > HOUR_IN_SECONDS)
            && !$force_update) {
            return self::get_payengine_data($mode, true);
        }

        return $merchant;
    }

    public static function maybe_set_hubspot_property() {
        $merchant_live = Config::get_payengine_data('live');
        $settings = get_option('woocommerce_tenweb_payments_settings');

        if ('active' === $merchant_live['merchant_status'] && 'no' === $settings['enabled']) {
            $merchant_live_updated_at = strtotime($merchant_live['updated_at']);
            $settings_disabled_at = strtotime($settings['disabled_at']);
            $month_ago = strtotime('-1 month');

            if ($merchant_live_updated_at < $month_ago && (!isset($settings['disabled_at']) || $settings_disabled_at < $month_ago)) {
                Service::request('merchants/' . $merchant_live['merchant_id'] . '/live/payment-method-inactive', 'payengine', array(), 'POST');
            }
        }
    }

    /**
     * @param $mode
     * @param $force_update
     *
     * @return array{id: string, status: string, can_accept_payments: bool, can_accept_payouts: bool, is_active: bool, stripe_payment_method_configuration_id: string}
     */
    public static function get_stripe_account($mode, $force_update = false) {
        $accounts = get_option(self::PREFIX . '_stripe_account');

        if (!$accounts || $force_update) {
            $accounts = Service::request('accounts/', 'stripe');

            if ($accounts === false || $accounts === null) {
                $accounts = (object) array();
            }
            $accounts->twwp_last_updated_date = time();
            update_option(self::PREFIX . '_stripe_account', $accounts);
        }
        $account = array(
            'id' => '',
            'status' => false,
            'can_accept_payments' => false,
            'can_accept_payouts' => false,
            'is_active' => false,
            'stripe_payment_method_configuration_id' => ''
        );

        if (isset($accounts->data) && is_array($accounts->data)) {
            foreach ($accounts->data as $a) {
                if (isset($a->env) && $mode === $a->env) {
                    $account['id'] = isset($a->stripe_account_id) ? $a->stripe_account_id : '';
                    $account['status'] = isset($a->stripe_account_status) ? $a->stripe_account_status : '';
                    $account['can_accept_payments'] = isset($a->can_accept_payments) ? $a->can_accept_payments : true;
                    $account['can_accept_payouts'] = isset($a->can_accept_payouts) ? $a->can_accept_payouts : true;
                    $account['is_active'] = isset($a->is_active) ? $a->is_active : true;
                    $account['stripe_payment_method_configuration_id'] = isset($a->stripe_payment_method_configuration_id) ? $a->stripe_payment_method_configuration_id : '';
                }
            }
        }

        // To handle the case when an old or corrupted state is saved. Forcing an update one time.
        // Allow force only once an hour.
        if ((!$account['id'] || false === $account['can_accept_payments'])
            && (!isset($accounts->twwp_last_updated_date) || time() - $accounts->twwp_last_updated_date > HOUR_IN_SECONDS)
            && !$force_update) {
            return self::get_stripe_account($mode, true);
        }

        return $account;
    }

    public static function have_any_stripe_account() {
        $accounts = get_option(self::PREFIX . '_stripe_account');

        if (!$accounts) {
            $accounts = Service::request('accounts/', 'stripe');

            if ($accounts === false || $accounts === null) {
                $accounts = (object) array();
            }
            $accounts->twwp_last_updated_date = time();
            update_option(self::PREFIX . '_stripe_account', $accounts);
        }

        if (isset($accounts->data) && is_array($accounts->data) && count($accounts->data) > 0) {
            return true;
        }

        return false;
    }

    public static function get_stripe_keys($mode) {
        $keys = get_option(self::PREFIX . '_stripe_keys');

        if (!$keys || !isset($keys->{$mode})) {
            $keys = Service::request('get_public_key', 'stripe');

            if ($keys !== false && isset($keys->data)) {
                $keys = $keys->data;
                update_option(self::PREFIX . '_stripe_keys', $keys);
            }
        }

        return isset($keys->{$mode}) ? $keys->{$mode} : '';
    }
}
