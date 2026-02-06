<?php

namespace TenWebWooP\PaymentMethods\Payengine;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use TenWebWooP\Config;
use TenWebWooP\PaymentMethods\Service;
use TenWebWooP\PaymentMethods\TenWebPayments;
use TenWebWooP\Utils;

class TenWebPaymentsPayengine extends TenWebPayments {

    protected static $instance = null;

    protected $merchant = null;

    public static function add_gateway_block_support() {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function (PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new TenWebPaymentsBlockPayengine());
                }
            );
        }
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
        $this->enabled = $this->is_merchant_active() ? $this->enabled : 'no';

        add_filter('pre_update_option_woocommerce_tenweb_payments_settings', array( $this, 'maybe_set_payment_method_date' ), 10, 3);
        Config::maybe_set_hubspot_property();
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script('twwp_admin_script', Config::get_url('PaymentMethods', 'assets/admin.js'), array('jquery'), Config::VERSION);
        wp_localize_script(
            'twwp_admin_script',
            'twwp_admin_config',
            array(
                'merchant_id' => $this->merchant['merchant_id'],
                'merchant_status' => $this->merchant['merchant_status'],
                'payment_method_disabled' => !$this->is_merchant_active(),
                'dashboard_url' => $this->dashboard_url,
            )
        );
    }

    protected function is_merchant_active() {
        return 'active' === $this->merchant['merchant_status'];
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'twwp'),
                'type' => 'checkbox',
                'description' => ($this->is_merchant_active() ? '' : __('This checkbox will be ignored while merchant is not active.', 'twwp')),
                'label' => __($this->method_title, 'twwp'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'twwp'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'twwp'),
                'default' => __($this->method_title, 'twwp'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Customer Message', 'twwp'),
                'type' => 'textarea',
                'default' => ''
            ),
            'test_mode' => array(
                'title' => __('Test mode', 'twwp'),
                'label' => __('Enable test mode', 'twwp'),
                'type' => 'checkbox',
                'description' => __('Simulate transactions using test card numbers.', 'twwp'),
                'default' => 'yes',
                'desc_tip' => true,
            ),
        );

        if ('live' !== $this->mode) {
            $this->form_fields['setup'] = array(
                'title' => __('Activate Live Mode', 'twwp'),
                'type' => 'twwp_setup',
                'description' => __('Please connect your live merchant account to start accepting payments.', 'twwp'),
            );
        }
    }

    public function get_method_description() {
        $description = 'Take payments using ' . $this->method_title . '.<br />';

        if ($this->merchant['merchant_id']) {
            $description .= 'Current merchant is: ' . $this->merchant['merchant_id'] . ' (Status: ' . $this->merchant['merchant_status'] . ').';
        } else {
            $description .= 'No merchant connected.';
        }

        $description .= '<br />Payment method is in <b>' . $this->mode . '</b> mode.';

        return $description;
    }

    public function get_setup_html() {
        ob_start(); ?>
      <tr valign="top">
        <th scope="row" class="titledesc">

        </th>
        <td class="forminp">
          <a class="button-primary twwp-connect-button" target="_blank"
             href="<?php echo esc_url($this->dashboard_url); ?>"
             value="<?php esc_attr_e('Activate Live Mode', 'twwp'); ?>">
              <?php esc_html_e('Activate Live Mode', 'twwp'); ?>
          </a>
        </td>
      </tr>

	    <?php
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        wp_enqueue_style('twwp_payment_method_style', Config::get_url('PaymentMethods/Payengine', 'assets/style.css'), array(), Config::VERSION);
        wp_enqueue_script('twwp_payengine', $this->merchant['script_url'], array( 'jquery' ), null, false);
        wp_enqueue_script('twwp_script', Config::get_url('PaymentMethods/Payengine', 'assets/script.js'), array('jquery'), Config::VERSION);
        wp_localize_script(
            'twwp_script',
            'twwp_config',
            array(
                'merchant_id' => $this->merchant['merchant_id'],
            )
        );
    }

    public function payment_fields() {
        // Replacing the form with a div in Elementor preview as it is removed in preview mode.
        $tagname = \Elementor\Plugin::instance()->preview->is_preview_mode() || is_preview() ? 'div' : 'form'; ?>
      <<?php echo esc_attr($tagname); ?> id="twwp-card-form" onsubmit="return false">
        <?php
        if ('test' === $this->mode) {
            ?>
          <div><?php wc_print_notice(__('<strong>Test mode:</strong> use the test VISA card 4242424242424242 with any expiry date and CVC. Never provide your real card data when test mode is enabled.', 'twwp'), 'notice'); ?></div>
          <?php
        } ?>
        <div class="form-field" id="cc-name"></div>
        <div class="form-field" id="cc-number"></div>
        <div class="form-field" id="cc-expiration-date"></div>
        <div class="form-field" id="cc-cvc"></div>
      </<?php echo esc_attr($tagname); ?>>
      <?php
    }

    public function process_payment($order_id) {
        global $woocommerce;
        $order = new \WC_Order($order_id);
        $logger = wc_get_logger();
        $logger_prefix = 'Order ID: ' . $order_id . '. ';

        if ($order->get_total() > 0) {
            $merchant_id = $this->merchant['merchant_id'];
            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            $card_token = isset($_POST['twwp_payengine_card_token']) ? sanitize_text_field($_POST['twwp_payengine_card_token']) : '';
            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            $browser_info = isset($_POST['twwp_payengine_browser_info']) ? sanitize_text_field($_POST['twwp_payengine_browser_info']) : '';
            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            $three_ds_result = isset($_POST['twwp_payengine_3ds_result']) ? sanitize_text_field($_POST['twwp_payengine_3ds_result']) : false;

            // The case when 3DS is performed. We get a value of success/fail.
            if ($three_ds_result) {
                delete_post_meta($order_id, 'twwp_idempotency_key');
                $logger->info($logger_prefix . '3DS action performed. The result is ' . $three_ds_result, array('source' => 'tenweb-payment'));

                if ('success' === $three_ds_result) {
                    // In case if the frontend reported the success for the 3DS verification we need to check it in backend.
                    $transaction = Service::request('merchants/' . $merchant_id . '/' . $this->mode . '/payment/3dstransaction/' . $order->get_transaction_id());

                    $logger->info($logger_prefix . wc_print_r($transaction, true), array('source' => 'tenweb-payment'));

                    if ($transaction && isset($transaction->data->SaleResponse->status) && 'PASS' === $transaction->data->SaleResponse->status) {
                        // PE changed the transaction ID when it is succeeded.
                        // Update the transaction ID so the real one will be available for Dashboard.
                        $order->set_transaction_id($transaction->data->SaleResponse->transactionID);
                        $this->set_order_status($order, 'completed');
                        $this->add_payment_success_notes($order, $transaction->data->SaleResponse->transactionID);

                        $woocommerce->cart->empty_cart();

                        return array(
                            'result' => 'success',
                            'redirect' => $this->get_return_url($order)
                        );
                    }
                }
                $logger->error($logger_prefix . 'Payment failed: 3DS action failed. Transaction Id: ' . $order->get_transaction_id() . '.', array('source' => 'tenweb-payment'));
                $order->add_order_note('Payment failed: 3DS action failed. Transaction Id: ' . $order->get_transaction_id() . '.');
                throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Payment failed: 3DS action failed.', 'twwp'), 402);
            } else {
                // This is the standard flow if 3DS is not required or it is not being performed yet.
                $order_summary = $order->get_base_data();
                $taxCodes = implode(self::TAX_CODE_SEPARATOR, array_keys($order->get_tax_totals()));
                $args = array(
                    // Disabled 3DS for test mode as it does not always work as expected in test mode.
                    'attempt3DSecure' => 'live' === $this->mode ? 'true' : 'false',
                    'browserInfo' => $browser_info,
                    'data' => array(
                        'transactionAmount' => number_format($order->get_total(), 2, '.', ''),
                        'cardToken' => $card_token,
                        'currencyCode' => $order->get_currency(),
                        'order_number' => $order_id,
                        'internalTransactionID' => $order_id,
                        'sales_tax' => number_format($order_summary['total_tax'], 2, '.', ''),
                        'items' => array(),
                        'taxCode' => $taxCodes
                    ),
                );

                foreach ($order->get_items() as $item) {
                    $args['data']['items'][] = array(
                        'name' => $item->get_name(),
                        'quantity' => $item->get_quantity(),
                        'totalAmount' => number_format($item->get_total(), 2, '.', ''),
                    );
                }
                $logger->info($logger_prefix . 'Payment initialized.', array('source' => 'tenweb-payment'));
                // General flow, this will either require a 3DS verification or pass the payment
                $payment = Service::request('merchants/' . $merchant_id . '/' . $this->mode . '/payment/sale', 'payengine', $args, 'POST', Utils::getIdempotencyKey($order_id, $card_token));
                $logger->info($logger_prefix . wc_print_r($payment, true), array('source' => 'tenweb-payment'));

                if ($payment) {
                    if (isset($payment->data->ThreeDSActionRequired) && $payment->data->ThreeDSActionRequired) {
                        $logger->info($logger_prefix . 'Payment requires 3DS verification.', array('source' => 'tenweb-payment'));
                        $order->set_transaction_id($payment->data->TransactionID);
                        $order->update_meta_data('twwp_merchant_id', $payment->data->MerchantID);
                        $order->update_meta_data('twwp_transaction', $payment->data);
                        $order->update_meta_data('twwp_environment', $payment->data->payengineEnv);
                        $this->set_order_status($order, 'pending');
                        $order->add_order_note('Payment requires 3DS action. Merchant ID: ' . $payment->data->MerchantID . '. Transaction ID: ' . $payment->data->TransactionID . '.');

                        // When 3DS verification is required tell the frontend to trigger it.
                        return array(
                            'result' => 'success',
                            'message' => '3ds required',
                            'twwp_3ds_action_required' => true,
                            'twwp_3ds_data' => $payment->data->ThreeDSData,
                        );
                    } elseif (isset($payment->data->SaleResponse->status) && 'PASS' === $payment->data->SaleResponse->status) {
                        $logger->info($logger_prefix . 'Payment succeeded.', array('source' => 'tenweb-payment'));
                        $order->set_transaction_id($payment->data->TransactionID);
                        $order->update_meta_data('twwp_merchant_id', $payment->data->MerchantID);
                        $order->update_meta_data('twwp_transaction', $payment->data);
                        $order->update_meta_data('twwp_environment', $payment->data->payengineEnv);
                        $this->set_order_status($order, 'completed');
                        $this->add_payment_success_notes($order, $payment->data->TransactionID);

                        $woocommerce->cart->empty_cart();
                        delete_post_meta($order_id, 'twwp_idempotency_key');

                        return array(
                            'result' => 'success',
                            'redirect' => $this->get_return_url($order)
                        );
                    } elseif (isset($payment->data->SaleResponse->status) && 'FAIL' === $payment->data->SaleResponse->status) {
                        delete_post_meta($order_id, 'twwp_idempotency_key');
                        $order->update_meta_data('twwp_environment', $payment->data->payengineEnv);
                        $this->set_order_status($order, 'pending');
                        $logger->error($logger_prefix . 'Payment failed: ' . $payment->data->MerchantID . '. Transaction ID: ' . $payment->data->TransactionID . '. Error Code: ' . $payment->data->SaleResponse->responseCode . '. Error Message: ' . $payment->data->SaleResponse->responseMessage . '.', array('source' => 'tenweb-payment'));
                        $order->add_order_note('Payment failed: Merchant ID: ' . $payment->data->MerchantID . '. Transaction ID: ' . $payment->data->TransactionID . '. Error Code: ' . $payment->data->SaleResponse->responseCode . '. Error Message: ' . $payment->data->SaleResponse->responseMessage . '.');
                        throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Payment failed: ', 'twwp') . $payment->data->SaleResponse->responseMessage . ' (Error Code: ' . $payment->data->SaleResponse->responseCode . ').', 402);
                    }
                    delete_post_meta($order_id, 'twwp_idempotency_key');
                    $logger->error($logger_prefix . 'Payment failed: Incorrect response', array('source' => 'tenweb-payment'));
                    $order->add_order_note('Payment failed: Incorrect response.');
                    throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Payment failed: Incorrect response.', 'twwp'), 402);
                } else {
                    delete_post_meta($order_id, 'twwp_idempotency_key');
                    $logger->error($logger_prefix . 'Payment failed: No response.', array('source' => 'tenweb-payment'));
                    $order->add_order_note('Payment failed: No response.');
                    throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Payment failed: No response.', 'twwp'), 402);
                }
            }
        } else {
            $order->payment_complete();
            WC()->cart->empty_cart();

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        $order = new \WC_Order($order_id);
        $logger = wc_get_logger();
        $merchant_id = $this->merchant['merchant_id'];
        $transaction = $order->get_meta('twwp_transaction');
        $environment = $order->get_meta('twwp_environment');
        $logger->info(
            'Started processing refund for order ' . $order_id . ' with amount ' . $amount . ' and reason ' . $reason .
                '. Transaction ID: ' . $transaction->TransactionID . '. Environment: ' . $environment,
            array('source' => 'tenweb-payment')
        );
        $args = array(
            'id' => $transaction->TransactionID,
            'amount' => number_format((float) $amount, 2, '.', ''),
            'reason' => empty(trim($reason)) ? 'No reason provided' : $reason,
        );
        $refund = Service::request('merchants/' . $merchant_id . '/' . $environment . '/payment/refund', 'payengine', $args, 'POST', Utils::getIdempotencyKey($order_id));

        if ($refund && !isset($refund->err) && 'PASS' === $refund->data->ReturnResponse->status) {
            $order->add_order_note('Refund successful for Transaction ID: ' . $transaction->TransactionID . '. Refund amount: ' . $amount . '. Refund reason: ' . $reason . '.');
            delete_post_meta($order_id, 'twwp_idempotency_key');

            return true;
        }

        $order->add_order_note('Refund failed for Transaction ID: ' . $transaction->TransactionID . '.');

        return false;
    }

    public function get_transaction_url($order) {
        return Config::get_dashboard_url('/ecommerce/transactions/');
    }

    protected function add_payment_success_notes($order, $transaction_id) {
        $transaction_url = $this->get_transaction_url($order);

        $success_note = sprintf(
            __('A payment of %s was <strong>successfully charged</strong> using %s (<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>).', 'twwp'),
            wc_price($order->get_total(), array( 'currency' => $order->get_currency() )),
            $this->method_title,
            $transaction_url,
            $transaction_id
        );
        $order->add_order_note($success_note);

        $merchant_id = $this->merchant['merchant_id'];
        $transaction = Service::request('merchants/' . $merchant_id . '/' . $this->mode . '/payment/transaction/' . $transaction_id);

        if ($transaction && isset($transaction->data->details->host_report->fee_amount) && isset($transaction->data->details->host_report->net_amount)) {
            $fee_note = sprintf(
                __('<strong>Fee details:</strong><br />Base fee: %s<br />Net deposit: %s', 'twwp'),
                wc_price((float) $transaction->data->details->host_report->fee_amount, array( 'currency' => $order->get_currency() )),
                wc_price((float) $transaction->data->details->host_report->net_amount, array( 'currency' => $order->get_currency() ))
            );
            $order->add_order_note($fee_note);
        }
    }
}
