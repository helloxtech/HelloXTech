<?php

namespace TenWebWooP\PaymentMethods;

use TenWebWooP\CheckAuthorization;
use TenWebWooP\Config;
use TenWebWooP\PaymentMethods\Stripe\TenWebPaymentsStripe;
use TenWebWooP\PaymentMethods\Stripe\TenWebPaymentsStripeHelper;
use TenWebWooP\Utils;
use WC_Order;
use WP_REST_Request;
use WP_REST_Server;

class Api {

    use CheckAuthorization;

    public function register_routes() {
        register_rest_route(
            'tenweb_woop/v1',
            'payment_methods/payengine/merchant',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'update_merchant' ),
                'permission_callback' => array($this, 'check_authorization'),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'payment_methods/stripe/accounts',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'update_stripe_accounts' ),
                'permission_callback' => array($this, 'check_authorization'),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'payment_methods/stripe/payment_intent',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'update_payment_intent' ),
                'permission_callback' => array($this, 'check_authorization'),
            )
        );

        register_rest_route(
            'tenweb_woop/v1',
            'payment_methods/stripe/process_refund',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'process_refunds' ),
                'permission_callback' => array($this, 'check_authorization'),
            )
        );
    }

    public function process_refunds(WP_REST_Request $request) {
        $logger = wc_get_logger();
        $logger_prefix = 'TenWeb Payments[Processing refunds from Stripe]: ';
        $refunds = json_decode($request->get_body());
        $refunds = $refunds->data;
        $errors = array();

        if (count($refunds) > 0) {
            $logger->info($logger_prefix . 'Count of refunds to process: ' . count($refunds) . '.', array('source' => 'tenweb-payment'));

            $order = TenWebPaymentsStripeHelper::get_order_by_transaction_id($refunds[0]->payment_intent);
            $logger->info($logger_prefix . wc_print_r($order, true), array('source' => 'tenweb-payment'));

            if ($order) {
                $wooRefundsMeta = $order->meta_exists('twpp_stripe_refunds') ? $order->get_meta('twpp_stripe_refunds') : array();
                $logger->info($logger_prefix . 'twpp_stripe_refunds before the processing ' . wc_print_r($wooRefundsMeta, true), array('source' => 'tenweb-payment'));

                foreach ($refunds as $refund) {
                    if (isset($refund->payment_intent) && isset($refund->amount)) {
                        if (empty($wooRefundsMeta[$refund->id])) {
                            $logger->info($logger_prefix . 'Refund with id=' . $refund->id . ' is not processed in the order, creating it now', array('source' => 'tenweb-payment'));
                            $refund_reason = 'Refund requested from Stripe Dashboard';
                            $refundAmount = TenWebPaymentsStripeHelper::interpret_stripe_amount($refund->amount, $refund->currency);
                            $wooRefund = wc_create_refund(array(
                                'amount' => $refundAmount,
                                'reason' => $refund_reason,
                                'order_id' => $order->get_id(),
                                'refund_payment' => false,
                            ));

                            if (is_wp_error($wooRefund)) {
                                $errors[] = $wooRefund->get_error_message();
                            } else {
                                $order->add_order_note('Refund successful for Transaction ID: ' . $refund->payment_intent . '. Refund amount: ' . $refundAmount . ' ' . $refund->currency . '. Refund reason: ' . $refund_reason . '.');
                                $wooRefundsMeta[$refund->id] = $wooRefund->get_id();
                            }
                        } else {
                            $logger->info($logger_prefix . 'Refund with id=' . $refund->id . ' is already processed in the order', array('source' => 'tenweb-payment'));
                        }
                    } else {
                        $errors[] = 'Refund data is missing or invalid.';
                    }
                }
                $logger->info($logger_prefix . 'twpp_stripe_refunds after the processing ' . wc_print_r($wooRefundsMeta, true), array('source' => 'tenweb-payment'));
                $order->update_meta_data('twpp_stripe_refunds', $wooRefundsMeta);

                if (count($errors)) {
                    $logger->error($logger_prefix . 'Found this errors after processing the refunds from Stripe ' . wc_print_r($errors, true), array('source' => 'tenweb-payment'));
                    $order->add_meta_data('twwp_stripe_refund_errors', $errors);
                }
                $order->save();
            } else {
                return array(
                    'msg' => 'No order found with this transaction ID.',
                    'status' => 404,
                );
            }
        }

        return array(
            'msg' => 'success',
            'status' => 200,
            'errors' => $errors,
            'data' => array(
                'processed' => count($refunds) - count($errors),
                'wooRefundsCount' => count($wooRefundsMeta),
            )
        );
    }

    /**
     * Updates the payment intent based on the request data.
     *
     * This method processes the payment intent received from the request, checks for order and transaction details,
     * updates order metadata, and sets the order status to 'completed' if the payment succeeded.
     *
     * @param WP_REST_Request $request the incoming REST request containing the payment intent details
     *
     * @return array the response array containing a message and status code
     */
    public function update_payment_intent(WP_REST_Request $request) {
        // Decode the request body to retrieve payment intent data
        $payment_intent = json_decode($request->get_body());

        // Check if the payment intent contains the necessary status field
        if (isset($payment_intent->data->status)) {
            $payment_intent_id = $payment_intent->data->id;

            // Ensure the payment intent has metadata and orderId
            if (isset($payment_intent->data->metadata, $payment_intent->data->metadata->orderId)) {
                $order_id = $payment_intent->data->metadata->orderId;

                // Load the WooCommerce order using the order ID
                $order = new WC_Order($order_id);

                // Check if the transaction ID matches the payment intent ID
                if ($order->get_transaction_id() === $payment_intent_id) {
                    $twwp_orders_cookies_data = get_option('twwp_orders_cookies_data', array());

                    // Handle the payment status
                    switch ($payment_intent->data->status) {
                        case 'succeeded':
                            // Add new cookie data for successful payments
                            $twwp_orders_cookies_data[] = array(
                                'order_id' => $order_id,
                                'payment_intent_cookie_id' => 'twwp_payment_intent_' . COOKIEHASH,
                            );
                            update_option('twwp_orders_cookies_data', $twwp_orders_cookies_data);

                            // Update the order metadata with the transaction details
                            $order->update_meta_data('twwp_transaction', $payment_intent->data);

                            break;
                    }

                    // Process the payment for the order
                    TenWebPaymentsStripe::get_instance()->process_payment($order_id);
                } else {
                    // Return an error if no order matches the transaction ID
                    return array(
                        'msg' => 'No order found with this transaction ID.',
                        'status' => 404,
                    );
                }
            } else {
                // Return an error if the orderId is missing
                return array(
                    'msg' => 'No order found with this transaction ID.',
                    'status' => 404,
                );
            }
        } else {
            // Return an error if the payment intent data is missing or invalid
            return array(
                'msg' => 'Payment intent data is missing or invalid.',
                'status' => 400,
            );
        }

        // Return success response
        return array(
            'msg' => 'success',
            'status' => 200,
        );
    }

    public function update_merchant(WP_REST_Request $request) {
        $data = json_decode($request->get_body());
        // TODO: validate this before save
        update_option(Config::PREFIX . '_payengine_data', $data);

        $merchant_test = Config::get_payengine_data('test');
        $merchant_live = Config::get_payengine_data('live');

        // Enable appropriate mode if only one merchant is added.
        $mode = false;

        if ($merchant_test['merchant_id'] && !$merchant_live['merchant_id']) {
            $mode = 'yes';
        } elseif (!$merchant_test['merchant_id'] && $merchant_live['merchant_id']) {
            $mode = 'no';
        }

        if ($mode) {
            Utils::updateTenwebPayTestMode($mode);
        }

        return array(
            'msg' => 'success',
            'status' => 200,
        );
    }

    /**
     * Updates Stripe account settings and configures test/live mode.
     *
     * @param WP_REST_Request $request incoming request with Stripe account data
     *
     * @return array success message and status code
     */
    public function update_stripe_accounts(WP_REST_Request $request) {
        $data = json_decode($request->get_body());
        // TODO: validate this before save
        update_option(Config::PREFIX . '_stripe_account', $data);

        $account_test = Config::get_stripe_account('test');
        $account_live = Config::get_stripe_account('live');

        // Enable appropriate mode if only one merchant is added.
        $mode = false;

        if ($account_test['id'] && !$account_live['id']) {
            $mode = 'yes';
        } elseif (!$account_test['id'] && $account_live['id']) {
            $mode = 'no';
        }
        // Set test-mode to 'yes' if the test account can accept payments and the live account cannot.
        if (isset($account_test['can_accept_payments'], $account_live['can_accept_payments']) && $account_test['can_accept_payments'] && !$account_live['can_accept_payments']) {
            $mode = 'yes';
        }

        if ($mode) {
            Utils::updateTenwebPayTestMode($mode, 'stripe');
        }

        return array(
            'msg' => 'success',
            'status' => 200,
        );
    }
}
