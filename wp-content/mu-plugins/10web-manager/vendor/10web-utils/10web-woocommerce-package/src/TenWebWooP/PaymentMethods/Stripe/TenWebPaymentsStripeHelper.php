<?php

namespace TenWebWooP\PaymentMethods\Stripe;

use WC_Order;
use WC_Order_Refund;

class TenWebPaymentsStripeHelper {

    /**
     * List of currencies supported by Stripe that has no decimals
     * https://stripe.com/docs/currencies#zero-decimal from https://stripe.com/docs/currencies#presentment-currencies
     * ugx is an exception and not in this list for being a special cases in Stripe https://stripe.com/docs/currencies#special-cases
     *
     * @return array $currencies
     */
    public static function no_decimal_currencies() {
        return array(
            'bif', // Burundian Franc
            'clp', // Chilean Peso
            'djf', // Djiboutian Franc
            'gnf', // Guinean Franc
            'jpy', // Japanese Yen
            'kmf', // Comorian Franc
            'krw', // South Korean Won
            'mga', // Malagasy Ariary
            'pyg', // Paraguayan Guaraní
            'rwf', // Rwandan Franc
            'vnd', // Vietnamese Đồng
            'vuv', // Vanuatu Vatu
            'xaf', // Central African Cfa Franc
            'xof', // West African Cfa Franc
            'xpf', // Cfp Franc
        );
    }

    /**
     * List of currencies supported by Stripe that has three decimals
     * https://docs.stripe.com/currencies?presentment-currency=AE#three-decimal
     *
     * @return array $currencies
     */
    private static function three_decimal_currencies() {
        return array(
            'bhd', // Bahraini Dinar
            'jod', // Jordanian Dinar
            'kwd', // Kuwaiti Dinar
            'omr', // Omani Rial
            'tnd', // Tunisian Dinar
        );
    }

    /**
     * Get stripe amount
     *
     * @param float  $total    total amount
     * @param string $currency currency code
     *
     * @return int
     */
    public static function get_stripe_amount($total, $currency = '') {
        if (! $currency) {
            $currency = get_woocommerce_currency();
        }

        $currency = strtolower($currency);

        if (in_array($currency, self::no_decimal_currencies(), true)) {
            return absint($total);
        } elseif (in_array($currency, self::three_decimal_currencies(), true)) {
            $price_decimals = wc_get_price_decimals();
            $amount = absint(wc_format_decimal(((float) $total * 1000), $price_decimals)); // For tree decimal currencies.

            return $amount - ($amount % 10); // Round the last digit down. See https://docs.stripe.com/currencies?presentment-currency=AE#three-decimal
        } else {
            return absint(wc_format_decimal(((float) $total * 100), wc_get_price_decimals())); // In cents.
        }
    }

    /**
     * Interprets amount from Stripe API.
     *
     * @param int    $amount   the amount returned by Stripe API
     * @param string $currency the currency we get from Stripe API for the amount
     *
     * @return float the interpreted amount
     */
    public static function interpret_stripe_amount($amount, $currency = 'usd') {
        $currency = strtolower($currency);
        $conversion_rate = 100;

        if (in_array(strtolower($currency), self::no_decimal_currencies(), true)) {
            $conversion_rate = 1;
        }

        return (float) $amount / $conversion_rate;
    }

    /**
     * Get order by transaction id
     *
     * @param string $transaction_id
     *
     * @return bool|WC_Order|WC_Order_Refund|null
     */
    public static function get_order_by_transaction_id($transaction_id) {
        $args = array(
            'limit' => -1,
            'transaction_id' => $transaction_id,
            'return' => 'ids',
        );

        $orders_ids = wc_get_orders($args);

        if (!empty($orders_ids)) {
            $order = wc_get_order($orders_ids[0]);

            return !empty($order) ? $order : null; // no order found
        } else {
            return null; // no order found
        }
    }

    /**
     * Sets the status of an order.
     *
     * This function updates the status of the given order by prefixing the status
     * with 'wc-' if it doesn't already have that prefix. It then updates the
     * order's metadata with the new status and updates the order status itself.
     *
     * @param object $order  the order object to update
     * @param string $status the new status to set for the order
     */
    public static function set_order_status($order, $status) {
        $status = (0 !== strpos($status, 'wc-')) ? 'wc-' . $status : $status;
        $order->update_meta_data('twwp_order_status', $status);
        $order->update_status($status);
    }

    /**
     * Retrieves Elementor global styles for text color and font family.
     *
     * @return array $return_data array containing the payment text color and font family
     */
    public static function get_elementor_global_styles() {
        $return_data = array();

        if (class_exists('\Elementor\Plugin')) {
            // Access the global settings manager
            $global_settings = \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend()->get_settings();

            if (isset($global_settings['system_colors']) && is_array($global_settings['system_colors'])) {
                foreach ($global_settings['system_colors'] as $color) {
                    if (isset($color['_id']) && $color['_id'] === 'text') {
                        $return_data['payment_text_color'] = $color['color'];
                    }
                }
            }

            if (isset($global_settings['system_typography']) && is_array($global_settings['system_typography'])) {
                foreach ($global_settings['system_typography'] as $typography) {
                    if (isset($typography['_id']) && $typography['_id'] === 'text') {
                        $return_data['payment_font_family'] = $typography['typography_font_family'];
                    }
                }
            }
        }

        return $return_data;
    }
}
