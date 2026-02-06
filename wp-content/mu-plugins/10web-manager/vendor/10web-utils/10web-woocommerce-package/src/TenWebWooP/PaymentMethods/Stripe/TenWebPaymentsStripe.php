<?php

namespace TenWebWooP\PaymentMethods\Stripe;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use TenWebWooP\Config;
use TenWebWooP\PaymentMethods\Service;
use TenWebWooP\PaymentMethods\TenWebPayments;
use TenWebWooP\PaymentMethods\UUID;

class TenWebPaymentsStripe extends TenWebPayments {

    protected static $instance = null;

    protected $account = null;

    /**
     * @var string
     */
    protected $public_key = null;

    public static function add_gateway_block_support() {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function (PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new TenWebPaymentsBlockStripe());
                }
            );
        }
    }

    public function __construct() {
        $this->id = 'tenweb_payments_stripe';
        $this->icon = '';
        $this->has_fields = true;
        $this->title = 'Credit card / Debit card';
        $this->method_title = '10Web Payments Stripe';
        $this->supports = array(
            'products',
            'refunds',
        );
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));
        add_filter('woocommerce_generate_twwp_setup_html', array($this, 'get_setup_html'));

        $this->dashboard_url = Config::get_dashboard_url('/ecommerce/payment-methods?setup_payment_method=1');
        $this->mode = $this->get_option('test_mode') === 'no' ? 'live' : 'test';
        $this->account = Config::get_stripe_account($this->mode);
        $this->public_key = Config::get_stripe_keys($this->mode);
        $this->init_form_fields();
        $this->init_settings();

        $this->method_description = $this->get_method_description();
        // Force disabling payment method if the account is not 'active'
        $this->enabled = $this->is_account_active() ? $this->enabled : 'no';

        add_filter('pre_update_option_woocommerce_tenweb_payments_settings', array( $this, 'maybe_set_payment_method_date' ), 10, 3);
//        Config::maybe_set_hubspot_property(); todo EC-231 fix fatal error here, maybe we need to add this logic here with stripe

        add_action('wp_ajax_twwp_stripe_create_intent', array($this, 'handle_payment_intent'));
        add_action('wp_ajax_nopriv_twwp_stripe_create_intent', array($this, 'handle_payment_intent'));
    }

    public function enqueue_admin_scripts() {
        if(strtolower(TENWEB_COMPANY_NAME) != '10web'){
            wp_enqueue_style('twwp_payment_method_white_label_style', Config::get_url('PaymentMethods/Stripe', 'assets/white_label.css'), array(), Config::VERSION);
        }
    }

    protected function is_account_active() {
        return $this->account
           && isset($this->account['can_accept_payments']) && $this->account['can_accept_payments'];
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'twwp'),
                'type' => 'checkbox',
                'description' => ($this->is_account_active() ? '' : __('This checkbox will be ignored while account is not active.', 'twwp')),
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
                'description' => __('Please connect your live account account to start accepting payments.', 'twwp'),
            );
        }
    }

    public function get_method_description() {
        $description = 'Take payments using ' . $this->method_title . '.<br />';

        if ($this->account && $this->account['id']) {
            $description .= 'Current account is: ' . $this->account['id'] . ' (Payment Method Configuration Id: ' . $this->account['stripe_payment_method_configuration_id'] . ', Is Active: ' . wc_bool_to_string($this->account['is_active']) . ', Status: ' . $this->account['status'] . ', Can accept payments: ' . wc_bool_to_string($this->account['can_accept_payments']) . ', Can accept payouts: ' . wc_bool_to_string($this->account['can_accept_payouts']) . '.).';
        } else {
            $description .= 'No account connected.';
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
		  <?php
          // Check the following page for the details why these parameters are checked here
          // https://stripe.com/docs/connect/collect-then-transfer-guide?payment-ui=embedded-checkout
          if ($this->is_account_active()) {
              ?>
            Your account is connected.
			  <?php
          } ?>

      </td>
		</tr>

		<?php
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        wp_enqueue_script('twwp_stripe', 'https://js.stripe.com/v3/', array( 'jquery' ), null, false);
        wp_enqueue_style('twwp_payment_method_style', Config::get_url('PaymentMethods/Stripe', 'assets/style.css'), array(), Config::VERSION);
        wp_enqueue_script('twwp_script', Config::get_url('PaymentMethods/Stripe', 'assets/script.js'), array('jquery', 'js-cookie'), Config::VERSION);

        // Prepare data to be localized for use in JavaScript
        $localize_data = array(
            'stripe_public_key' => $this->public_key, // Stripe public key for client-side transactions
            'stripe_account_id' => $this->account['id'], // Stripe account ID for identification
            'stripe_payment_method_configuration_id' => $this->account['stripe_payment_method_configuration_id'], // Payment Method Configuration to use in Stripe Checkout
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

    public function payment_fields() {
        if ('test' === $this->mode) {
            ?>
            <div class="twwp-card-element-notice"><?php wc_print_notice(__('<strong>Test mode:</strong> use the test VISA card 4242424242424242 with any expiry date and CVC. Never provide your real card data when test mode is enabled.', 'twwp'), 'notice'); ?></div>
            <?php
        } ?>
      <div id="twwp-card-element">
        <!-- A Stripe Element will be inserted here. -->
      </div>
		<?php
    }

    /**
     * Handle the creation and updating of a Stripe payment intent.
     *
     * This method processes AJAX requests to either create or update a Stripe payment intent.
     * It verifies the nonce for security, determines the requested action, and makes the
     * appropriate API call to Stripe via a service. If successful, it sets a cookie with the
     * payment intent details and returns a JSON response with the necessary information.
     *
     * Expected POST parameters:
     * - 'nonce': A security token used to verify the request.
     * - 'intentAction': The action to be performed ('create' or 'update').
     *
     * On success, this method returns a JSON response with the payment intent ID, client secret,
     * and cart hash. On failure, it returns a JSON error response.
     *
     * @return void
     */
    public function handle_payment_intent() {
        // Check if nonce and intentAction are set
        if (empty($_POST['nonce']) || empty($_POST['intentAction'])) {
            wp_send_json_error(new WP_Error('missing_parameters', 'Nonce and intentAction parameters are required'));

            return;
        }

        // Verify nonce for security
        $nonce = sanitize_text_field($_POST['nonce']);

        if (!wp_verify_nonce($nonce, 'twwp_ajax_nonce')) {
            wp_send_json_error(new WP_Error('invalid_nonce', 'Nonce verification failed'));

            return;
        }

        $intentAction = sanitize_text_field($_POST['intentAction']);
        $cart_hash = WC()->cart->get_cart_hash();
        $amount = TenWebPaymentsStripeHelper::get_stripe_amount(WC()->cart->get_total(false), get_woocommerce_currency());
        $data = array(
            'amount' => $amount,
            'currency' => strtolower(get_woocommerce_currency()),
            'payment_method_types' => array('card'), // TODO: Add other payment methods here
            'automatic_payment_methods' => array('enabled' => false),
        );

        // Determine the endpoint and data based on the intentAction
        switch ($intentAction) {
            case 'create':
                $endpoint = 'create_payment_intent';
                break;

            case 'update':
                if (empty($_POST['intentId'])) {
                    wp_send_json_error(new WP_Error('missing_parameters', 'Intent ID is required for update action'));

                    return;
                }
                $endpoint = 'update_payment_intent';
                $data['intent_id'] = sanitize_text_field($_POST['intentId']);

                if (isset($_POST['order_id'])) {
                    $data['order_id'] = sanitize_text_field($_POST['order_id']);
                }
                break;

            default:
                wp_send_json_error(new WP_Error('invalid_action', 'Invalid action provided'));

                return;
        }

        // Request the payment intent data from the service
        $paymentIntentData = Service::request("{$this->mode}/accounts/{$this->account['id']}/{$endpoint}", 'stripe', $data, 'POST');
        // Check if the payment intent data is valid
        if ($paymentIntentData && !empty($paymentIntentData->data)) {
            $payment_intent = array(
                'intentId' => $paymentIntentData->data->id,
                'clientSecret' => $paymentIntentData->data->client_secret,
                'cartHash' => $cart_hash
            );

            // Set a cookie with the payment intent information
            wc_setcookie('twwp_payment_intent_' . COOKIEHASH, wp_json_encode($payment_intent), time() + 3600);

            // Send a success response with the payment intent information
            wp_send_json_success($payment_intent);

            return;
        }

        // If the request fails, send an error response
        wp_send_json_error('Failed to retrieve payment intent data from the service');
    }

    /**
     * Process payment for a WooCommerce order.
     *
     * This method handles the validation and processing of a payment intent, updates order metadata,
     * and manages the payment status. It also logs key information and handles various payment statuses.
     *
     * @param int $order_id the WooCommerce order ID to process the payment for
     *
     * @return array result array containing the outcome and redirect URL
     *
     * @throws RouteException if payment fails due to missing or incorrect payment information
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        if ($order === false) {
            throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Order not found', 'twwp'), 402);
        }
        // Check if the form submission contains required payment validation fields
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_POST['twwp_checkout_validation']) && isset($_POST['intent_id']) && isset($_POST['intent_cookie_id'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $intent_cookie_id = sanitize_text_field($_POST['intent_cookie_id']);

            // Sanitize and set the transaction ID and cookie metadata for the order
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $order->set_transaction_id(sanitize_text_field($_POST['intent_id']));
            $order->update_meta_data('intent_cookie_id', $intent_cookie_id);

            // Check if the relevant cookie is set, then update it with order information
            if (isset($_COOKIE[$intent_cookie_id])) {
                // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
                $intent_cookie = json_decode(stripslashes(sanitize_text_field($_COOKIE[$intent_cookie_id])), false);
                $intent_cookie->order_id = $order_id;
                wc_setcookie('twwp_payment_intent_' . COOKIEHASH, wp_json_encode($intent_cookie), time() + 3600);
            }

            // Send a success response and terminate script execution
            echo wp_json_encode(array(
                'result' => 'validation_success',
                'order_id' => $order_id
            ));
            die();  // Properly terminates script execution to avoid further processing
        }

        global $woocommerce;
        $logger = wc_get_logger();  // Initialize WooCommerce logger
        $logger_prefix = 'Order ID: ' . $order_id . '. ';

        // Retrieve or validate the payment intent ID
        $intentId = $order->get_transaction_id();

        if (empty($intentId)) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            if (isset($_POST['intentId'])) {
                // phpcs:ignore WordPress.Security.NonceVerification.Missing
                $intentId = sanitize_text_field($_POST['intentId']);
            } else {
                // Fallback: Retrieve the payment intent from the cookie
                if (isset($_COOKIE['twwp_payment_intent_' . COOKIEHASH])) {
                    // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
                    $payment_intent = json_decode(stripslashes(sanitize_text_field($_COOKIE['twwp_payment_intent_' . COOKIEHASH])), false);
                    $intentId = isset($payment_intent->intentId) ? $payment_intent->intentId : null;
                }
            }
        }
        wc_setcookie('twwp_payment_intent_' . COOKIEHASH, '', time() + 100);
        // Proceed if the order total is greater than zero
        if ($order->get_total() > 0) {
            if (!empty($intentId)) {
                // Clear the payment intent cookie
                $logger->info($logger_prefix . 'Retrieving payment (id=' . $intentId . ').', array('source' => 'tenweb-payment'));

                // Request the payment details from Stripe
                $payment = Service::request($this->mode . '/accounts/' . $this->account['id'] . '/retrieve_payment_intent/' . $intentId . '/', 'stripe');
                $logger->info($logger_prefix . wc_print_r($payment, true), array('source' => 'tenweb-payment'));

                if ($payment && isset($payment->data)) {
                    // Set the transaction ID and update the order metadata
                    $order->set_transaction_id($payment->data->id);
                    $order->update_meta_data('twwp_transaction', $payment->data);
                    $order->update_meta_data('twwp_environment', $this->mode);
                    $order->save();
                    $logger->info($logger_prefix . 'Transaction ID ' . $payment->data->id . ' is set.', array('source' => 'tenweb-payment'));
                    // Handle payment statuses
                    $order = wc_get_order($order_id);

                    if ('succeeded' === $payment->data->status) {
                        $current_status = $order->get_status();
                        if ($current_status !== 'completed' && $current_status !== 'processing') {
                            $logger->info($logger_prefix . 'Payment succeeded.', array('source' => 'tenweb-payment'));
                            $this->set_order_status($order, 'completed', $payment->data->id);
                            $this->add_payment_success_notes($order, $payment->data);
                        }
                        $woocommerce->cart->empty_cart();

                        return array(
                            'result' => 'success',
                            'redirect' => $this->get_return_url($order),
                        );
                    } elseif ($order->get_status() !== 'pending' && 'processing' === $payment->data->status) {
                        $logger->error($logger_prefix . 'Payment is processing. Transaction ID: ' . $payment->data->id . '.', array('source' => 'tenweb-payment'));

                        if ($order->get_status() !== 'pending') {
                            $this->set_order_status($order, 'pending');
                            $order->add_order_note('Payment is processing. Transaction ID: ' . $payment->data->id . '.');
                        }

                        return array(
                            'result' => 'success',
                            'redirect' => $this->get_return_url($order),
                        );
                    } elseif ($order->get_status() !== 'failed' && 'requires_payment_method' === $payment->data->status) {
                        $logger->error($logger_prefix . 'Payment failed. Transaction ID: ' . $payment->data->id . '.', array('source' => 'tenweb-payment'));
                        $this->set_order_status($order, 'failed');
                        $order->add_order_note('Payment failed. Transaction ID: ' . $payment->data->id . '.');
                        throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Payment failed: Payment method required.', 'twwp'), 402);
                    }
                    // Handle unknown or incorrect responses
                    $logger->error($logger_prefix . 'Payment failed: Incorrect response.', array('source' => 'tenweb-payment'));
                    throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Payment failed: Incorrect response.', 'twwp'), 402);
                } else {
                    // Handle no response from payment gateway
                    $logger->error($logger_prefix . 'Payment failed: No response.', array('source' => 'tenweb-payment'));
                    $order->add_order_note('Payment failed: No response.');
                    throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Payment failed: No response.', 'twwp'), 402);
                }
            } else {
                // Handle missing payment intent
                $logger->error($logger_prefix . 'Payment failed: No Payment Intent.', array('source' => 'tenweb-payment'));
                $order->add_order_note('Payment failed: No Payment Intent.');
                throw new RouteException('woocommerce_rest_checkout_process_payment_error', __('Payment failed: No Payment Intent.', 'twwp'), 402);
            }
        } else {
            // If order total is zero, mark payment as complete and clear the cart
            $order->payment_complete();
            WC()->cart->empty_cart();

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        // TODO EC-203: Add handling for pending refunds
        $order = new \WC_Order($order_id);
        $logger = wc_get_logger();
        $account_id = $this->account['id'];
        $transaction = $order->get_meta('twwp_transaction');
        $refunds = $order->meta_exists('twpp_refunds') ? $order->get_meta('twwp_refunds') : array();
        $environment = $order->get_meta('twwp_environment');
        $unique_id = UUID::v4();
        $logger->info(
            'Started processing refund for order ' . $order_id . ' with amount ' . $amount . ' and reason ' . $reason .
            '. Transaction ID: ' . $transaction->id . '. Environment: ' . $environment,
            array('source' => 'tenweb-payment')
        );
        $args = array(
            'id' => $transaction->id,
            'amount' => TenWebPaymentsStripeHelper::get_stripe_amount($amount, get_woocommerce_currency()),
            'reason' => empty(trim($reason)) ? 'No reason provided' : $reason,
            'unique_id' => $unique_id,
        );
        $refund = Service::request($environment . '/accounts/' . $account_id . '/refund', 'stripe', $args, 'POST');

        if ($refund && !isset($refund->data->err) && isset($refund->data->status) && in_array($refund->data->status, array('succeeded', 'pending', 'requires_action'), true)) {
            $order->add_meta_data('twwp_refunds', array_merge($refunds, array( $unique_id => $refund->data )));

            if ('succeeded' === $refund->data->status) {
                $order->add_order_note('Refund successful for Transaction ID: ' . $transaction->id . '. Refund amount: ' . $amount . '. Refund reason: ' . $reason . '.');

                return true;
            }

            if ('pending' === $refund->data->status) {
                $order->add_order_note('Refund is pending for Transaction ID: ' . $transaction->id . '. Refund amount: ' . $amount . '. Refund reason: ' . $reason . '.');

                return true;
            }

            if ('requires_action' === $refund->data->status) {
                $order->add_order_note('Refund requires action for Transaction ID: ' . $transaction->id . ', please go to the Stripe Dashboard. Refund amount: ' . $amount . '. Refund reason: ' . $reason . '.');

                return true;
            }
        }

        $order->add_order_note('Refund failed for Transaction ID: ' . $transaction->id . '.');

        return false;
    }

    public function get_transaction_url($order) {
        return Config::get_dashboard_url('/ecommerce/transactions/');
    }

    protected function add_payment_success_notes($order, $payment) {
        $transaction_url = $this->get_transaction_url($order);

        $success_note = sprintf(
            __('A payment of %s was <strong>successfully charged</strong> using %s (<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>).', 'twwp'),
            wc_price($order->get_total(), array( 'currency' => $order->get_currency() )),
            $this->method_title,
            $transaction_url,
            $payment->id
        );
        $order->add_order_note($success_note);

        $merchant_id = $this->account['id'];
        //TODO: fix this
      //phpcs:ignore Squiz.PHP.CommentedOutCode.Found
      /*if ($payment && isset($transaction->data->details->host_report->fee_amount) && isset($transaction->data->details->host_report->net_amount)) {
        $fee_note = sprintf(
          __('<strong>Fee details:</strong><br />Base fee: %s<br />Net deposit: %s', 'twwp'),
          wc_price((float) $transaction->data->details->host_report->fee_amount, array( 'currency' => $order->get_currency() )),
          wc_price((float) $transaction->data->details->host_report->net_amount, array( 'currency' => $order->get_currency() ))
        );
        $order->add_order_note($fee_note);
      }*/
    }
}
