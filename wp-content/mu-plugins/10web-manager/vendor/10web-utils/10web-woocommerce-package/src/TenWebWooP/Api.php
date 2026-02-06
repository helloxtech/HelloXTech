<?php

namespace TenWebWooP;

use TenWebWooP\PaymentMethods\OrderActions;
use TenWebWooP\WoocommerceData\WoocommerceDataRepository;
use WP_REST_Request;
use WP_REST_Server;

class Api {

    const SETTING_OPTIONS_FOR_PRODUCT_CREATION = array(
        'woocommerce_weight_unit'
    );

    use CheckAuthorization;
    public $media_handler;

    public $payments_handler;

    private $request_from_proxy = true;

    private $data_repository = null;

    private $jwt_token = array();

    public function __construct() {
        /*When WooPayments Multi-currency is enabled, prices are converted to the local currency based on the user's location. This code disables the currency converter.*/
        if (isset($_GET['rest_route']) && (str_contains(sanitize_text_field($_GET['rest_route']), '/tenweb_woop/v1/products') || str_contains(sanitize_text_field($_GET['rest_route']), '/wc/v3/products'))) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            add_filter('option__wcpay_feature_customer_multi_currency', '__return_false');
        }

        $this->data_repository = new WoocommerceDataRepository();
        $this->media_handler = new Media('attachment');
        $this->payments_handler = new PaymentMethods\Api();
        add_action('rest_api_init', array($this, 'tenweb_woocommerce_rest'));
    }

    public function tenweb_woocommerce_rest() {
        register_rest_route(
            'tenweb_woop/v1',
            'products',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'products'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->products->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'products/(?P<id>[\d]+)',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_product'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->products->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'products',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'set_product'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->products->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'edit_product',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'edit_product'),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'update_product/(?P<id>[\d]+)',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'update_product'),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'orders',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'orders'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->orders->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'orders/(?P<id>[\d]+)',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'order'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->orders->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'customers',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'customers'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->customers->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'customers/(?P<id>[\d]+)',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'customer'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->customers->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'reports/customers',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'customers_reports'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->customersReports->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'analytics',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'analytics'),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'jwt_token',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_jwt_token'),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'shop_info',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_shop_info'),
                    'permission_callback' => array($this, 'check_rest_auth'),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'set_shop_info'),
                    'permission_callback' => array($this, 'check_rest_auth'),
                )
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'product_with_variations',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'product_with_variations'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->products->get_endpoint_args_for_item_schema(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'product_with_variations/(?P<id>[\d]+)',
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'product_with_variations'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->products->get_endpoint_args_for_item_schema(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'set_up_info',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'set_up_info'),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );

        register_rest_route(
            'tenweb_woop/v1',
            'settings',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_settings'),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'settings',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'set_settings'),
                'permission_callback' => array($this, 'check_rest_auth'),
                'args' => $this->data_repository->settingsOptions->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
            )
        );

        register_rest_route(
            'tenweb_woop/v1',
            'set_default_country',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'set_default_country'),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );

        register_rest_route(
            'tenweb_woop/v1',
            'payment_test_mode',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'set_payment_test_mode'),
                'args' => array(
                    'mode' => array(
                        'type' => 'string',
                        'required' => true,
                        'enum' => array('no', 'yes')
                    ),
                ),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'get_emails',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_emails'),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );

        register_rest_route(
            'tenweb_woop/v1',
            'payment_methods_positions',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'set_payment_methods_positions'),
                'args' => array(
                    'gateways' => array(
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => array($this, 'validate_unique_positions'),
                    ),
                ),
                'permission_callback' => array($this, 'check_rest_auth'),
            )
        );

        $this->media_handler->register_routes();
        $this->payments_handler->register_routes();
    }

    public function set_payment_methods_positions(WP_REST_Request $request) {
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        $gateways_data = $request->get_param('gateways');
        $gateways_data = json_decode($gateways_data, true);
        $order = get_option('woocommerce_gateway_order', array());

        $new_order = $order;

        foreach ($gateways_data as $gateway_data) {
            if (isset($gateway_data['id'], $gateway_data['position'])) {
                $gateway_id = $gateway_data['id'];

                if (isset($payment_gateways[$gateway_id])) {
                    $gateway = $payment_gateways[$gateway_id];

                    if (isset($gateway_data['enabled'])) {
                        $settings = $gateway->settings;
                        $settings['enabled'] = wc_bool_to_string($gateway_data['enabled']);
                        $gateway->enabled = $settings['enabled'];
                        $gateway->settings = $settings;
                        update_option($gateway->get_option_key(), apply_filters('woocommerce_gateway_' . $gateway->id . '_settings_values', $settings, $gateway));
                    }
                }

                $new_order[ $gateway_id ] = (int) $gateway_data['position'];
            }
        }

        update_option('woocommerce_gateway_order', $new_order);

        return $this->modify_response(array('success' => true));
    }

    public function get_emails() {
        $wc_emails = $this->data_repository->getEmails();

        if (!empty($wc_emails)) {
            return $this->modify_response($wc_emails);
        }

        return $this->modify_response(array());
    }

    /**
     * @return array|mixed
     */
    public function get_settings(WP_REST_Request $request) {
        $return_data = array();
        $settings_data = $this->data_repository->getSettings();
        $return_data['settings'] = $settings_data['settings'];
        $return_data['country_currency_codes'] = $settings_data['country_currency_codes'];
        $units = $this->data_repository->getUnits();

        return $this->modify_response(array_merge($return_data, $units));
    }

    /**
     * @return string[]
     */
    public function set_settings(WP_REST_Request $request) {
        $data = $this->data_repository->setSettings($request);

        return $this->modify_response($data);
    }

    /**
     * This function for get all customers from reports.
     * This endpoint is used for populating all the customers including the ones without registration
     * Limit result set to resources with a specific role.
     * Options: all, administrator, editor, author, contributor, subscriber, customer, shop_manager and guest. Default is customer
     *
     * @param WP_REST_Request $request full details about the request
     *
     * @return array {
     *
     * @var int   $customersTotals all customers count
     * @var array $customers       Registered customers list.
     *            }
     */
    public function customers_reports(WP_REST_Request $request) {
        $return_data = array();
        $customersReportTotals = $this->data_repository->customersReportTotals();
        $customersReport = $this->data_repository->customersReport($request, true);
        $return_data['customersTotals'] = $customersReportTotals;
        $return_data['customers'] = $customersReport;

        return $this->modify_response($return_data);
    }

    /**
     *  Merge all overview data in one request
     */
    public function analytics(WP_REST_Request $request) {
        $return_data = array('current' => array(), 'previous' => array());
        $args = $request->get_params();
        $start_date_current = isset($args['after_current']) ? sanitize_text_field($args['after_current']) : '';
        $end_date_current = isset($args['before_current']) ? sanitize_text_field($args['before_current']) : '';
        $start_date_previous = isset($args['after_previous']) ? sanitize_text_field($args['after_previous']) : '';
        $end_date_previous = isset($args['before_previous']) ? sanitize_text_field($args['before_previous']) : '';

        $request = new WP_REST_Request('GET', '/wc-analytics/reports/performance-indicators');
        $request->set_query_params(
            array(
                'after' => $start_date_current,
                'before' => $end_date_current,
                'stats' => 'revenue/total_sales,revenue/net_revenue,orders/orders_count,products/items_sold,taxes/total_tax,variations/items_sold',
            )
        );
        $response = rest_do_request($request);

        if (!is_wp_error($response) && 200 === $response->get_status()) {
            $return_data['current']['performance-indicators'] = $response->get_data();
        }

        $request->set_query_params(
            array(
                'after' => $start_date_previous,
                'before' => $end_date_previous,
                'stats' => 'revenue/total_sales,revenue/net_revenue,orders/orders_count,products/items_sold,taxes/total_tax,variations/items_sold',
            )
        );
        $response = rest_do_request($request);

        if (!is_wp_error($response) && 200 === $response->get_status()) {
            $return_data['previous']['performance-indicators'] = $response->get_data();
        }

        $request = new WP_REST_Request('GET', '/wc-analytics/reports/orders/stats');
        $request->set_query_params(
            array(
                'order' => 'asc',
                'interval' => 'day',
                'per_page' => '100',
                'fields[0]' => 'orders_count',
                'fields[1]' => 'avg_order_value',
                'after' => $start_date_current,
                'before' => $end_date_current,
            )
        );
        $response = rest_do_request($request);

        if (!is_wp_error($response) && 200 === $response->get_status()) {
            $return_data['current']['orders/stats'] = $response->get_data();
        }

        $request->set_query_params(
            array(
                'order' => 'asc',
                'interval' => 'day',
                'per_page' => '100',
                'fields[0]' => 'orders_count',
                'fields[1]' => 'avg_order_value',
                'after' => $start_date_previous,
                'before' => $end_date_previous,
            )
        );
        $response = rest_do_request($request);

        if (!is_wp_error($response) && 200 === $response->get_status()) {
            $return_data['previous']['orders/stats'] = $response->get_data();
        }

        $request = new WP_REST_Request('GET', '/wc-analytics/reports/revenue/stats');
        $request->set_query_params(
            array(
                'order' => 'asc',
                'interval' => 'day',
                'per_page' => '100',
                'fields[0]' => 'orders_count',
                'fields[1]' => 'avg_order_value',
                'after' => $start_date_current,
                'before' => $end_date_current,
            )
        );
        $response = rest_do_request($request);

        if (!is_wp_error($response) && 200 === $response->get_status()) {
            $return_data['current']['revenue/stats'] = $response->get_data();
        }

        $request->set_query_params(
            array(
                'order' => 'asc',
                'interval' => 'day',
                'per_page' => '100',
                'fields[0]' => 'orders_count',
                'fields[1]' => 'avg_order_value',
                'after' => $start_date_previous,
                'before' => $end_date_previous,
            )
        );
        $response = rest_do_request($request);

        if (!is_wp_error($response) && 200 === $response->get_status()) {
            $return_data['previous']['revenue/stats'] = $response->get_data();
        }

        $this->request_from_proxy = true; // to avoid modifying response in this case

        return $this->modify_response($return_data);
    }

    /**
     * @return false|mixed
     */
    public function check_rest_auth(WP_REST_Request $request) {
        $headers = Utils::getAllHeaders();

        if (!isset($headers['X-TENWEB-SERVICE-REQUEST'])) {
            $this->request_from_proxy = false;
        }
        $check_authorization = $this->check_authorization($request);

        if (is_array($check_authorization)) {
            if (!empty($check_authorization['jwt_token'])) {
                $this->jwt_token = $check_authorization['jwt_token'];
            }

            return $check_authorization['success'];
        }

        return false;
    }

    /**
     * This function for get jwt token.
     *
     * @return array jwt token
     */
    public function get_jwt_token() {
        return $this->jwt_token;
    }

    /**
     * @return array
     */
    public function product_with_variations(WP_REST_Request $request) {
        $return_data = array();

        if (isset($request['id'])) {
            $product = $this->data_repository->products->update_item($request);
        } else {
            $product = $this->data_repository->products->create_item($request);
        }

        $return_data['product'] = $product;
        $args = $request->get_url_params();
        $args['product_id'] = $product->data['id'];
        $request->set_url_params($args);
        $variations = $this->data_repository->productsVariations->batch_items($request);
        $return_data['variations'] = $variations;

        $request->set_param('per_page', 25);
        $request->set_param('order', 'desc');
        $products = $this->data_repository->products($request);
        $return_data['products'] = $products->data;

        return $return_data;
    }

    /**
     * @return array|mixed
     */
    public function edit_product(WP_REST_Request $request) {
        $request_custom_params = array(
            'per_page' => 10000
        );
        $return_data = array();
        $categories = $this->data_repository->categories($request, true, $request_custom_params);
        $tags = $this->data_repository->tags($request, true, $request_custom_params);
        $shipping_class_list = $this->data_repository->shippingClasses($request, true);
        $tax_classes_list = $this->data_repository->taxClasses($request, true);

        $products_request = $request;
        $products_request->set_param('per_page', 25);
        $products_request->set_param('order', 'desc');
        $products = $this->data_repository->products($products_request);
        $products_attributes = $this->data_repository->productsAttributes($products_request, true);
        $products_attributes_data = $this->data_repository->fillProductAttributeTerms($products_attributes->data);
        $settings_data = $this->data_repository->settingsOptions('products', $request, true);
        $return_data['settings'] = array(
            'products' => array_column(array_filter(
                $settings_data->data,
                function ($setting) {
                    return in_array($setting['id'], self::SETTING_OPTIONS_FOR_PRODUCT_CREATION, true);
                }
            ), null, 'id')
        );
        $return_data['categories'] = $categories->data;
        $return_data['tags'] = $tags->data;
        $return_data['shipping_class_list'] = $shipping_class_list->data;
        $return_data['tax_classes'] = $tax_classes_list->data;
        $return_data['products'] = $products->data;
        $return_data['products_attributes'] = $products_attributes_data;
        $return_data['single_templates'] = $this->data_repository->get_single_templates();
        $return_data['all_products_template_id'] = $this->data_repository->get_default_product_template();

        $units = $this->data_repository->getUnits();

        return $this->modify_response(array_merge($return_data, $units));
    }

    /**
     * Updates a WooCommerce product and sets product conditions based on the request.
     *
     * @param WP_REST_Request $request the REST request object containing product data
     *
     * @return WP_REST_Response|void returns a REST response with the result or outputs JSON and terminates
     */
    public function update_product(WP_REST_Request $request) {
        if (!isset($request['id'])) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Product ID is missing in the request.'
            ), 400);
        }
        $product_id = (int) $request->get_param('id');
        $template_id = (int) $request->get_param('template_id');

        if (class_exists('\Tenweb_Builder\Condition')) {
            if ($template_id > 0) {
                $this->data_repository->set_products_conditions($product_id, $template_id);
            } else {
                $this->data_repository->delete_product_condition($product_id);
            }
        }

        // Prepare WooCommerce REST API request to update the product
        $woo_rest_route = '/wc/v3/products/' . $product_id;
        $woo_request = new WP_REST_Request('PUT', $woo_rest_route);
        $body_params = $request->get_params();
        $woo_request->set_body_params($body_params);
        $response = rest_do_request($woo_request);

        if ($response->is_error()) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Failed to update WooCommerce product.'
            ), 500);
        }

        $server = rest_get_server();
        $response_data = $server->response_to_data($response, false);

        return new \WP_REST_Response($response_data, 200);
    }

    /**
     * Retrieves a WooCommerce product and adds additional template data.
     *
     * @param WP_REST_Request $request the REST request object containing product ID
     *
     * @return WP_REST_Response|void returns a REST response with the product data or an error message
     */
    public function get_product(WP_REST_Request $request) {
        if (!isset($request['id'])) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Product ID is missing in the request.'
            ), 400);
        }

        $product_id = (int) $request['id'];

        // Prepare WooCommerce REST API request to get the product
        $woo_rest_route = '/wc/v3/products/' . $product_id;
        $woo_request = new WP_REST_Request('GET', $woo_rest_route);

        $response = rest_do_request($woo_request);

        if ($response->is_error()) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Failed to retrieve product from WooCommerce.'
            ), 500);
        }

        $server = rest_get_server();
        $response_data = $server->response_to_data($response, false);

        if (is_array($response_data)) {
            $response_data['single_templates'] = $this->data_repository->get_single_templates();
            $response_data['selected_template_id'] = $this->data_repository->get_product_condition($product_id);
        }
        $response_data = array('dataKey' => $response_data);

        return new \WP_REST_Response($response_data, 200); // Success
    }

    /**
     * Creates a new WooCommerce product
     *
     * @param WP_REST_Request $request the REST request object containing product data
     *
     * @return WP_REST_Response returns a REST response with the result of product creation and additional data
     */
    public function set_product(WP_REST_Request $request) {
        // Prepare WooCommerce REST API request to create a new product
        $woo_rest_route = '/wc/v3/products';
        $woo_request = new WP_REST_Request('POST', $woo_rest_route);
        $body_params = $request->get_params();
        $woo_request->set_body_params($body_params);
        $response = rest_do_request($woo_request);

        if ($response->is_error()) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Failed to create WooCommerce product.'
            ), 500);
        }

        if (isset($response->data['id'])) {
            $product_id = (int) $response->data['id'];
            $template_id = (int) $request->get_param('template_id');

            if ($template_id > 0 && $product_id > 0) {
                $this->data_repository->set_products_conditions($product_id, $template_id);
            }
        } else {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Product creation response missing product ID.'
            ), 500);
        }
        $server = rest_get_server();
        $response_data = $server->response_to_data($response, false);
        $response_data = array('dataKey' => $response_data);

        return new \WP_REST_Response($response_data, 200); // Success
    }

    /**
     * @return false|mixed|null
     */
    public function get_shop_info() {
        return Settings::get('shop_info');
    }

    /**
     * @return string[]
     */
    public function set_shop_info(WP_REST_Request $request) {
        $shop_info = $request->get_param('shop_info');
        Settings::update('shop_info', $shop_info);

        return array('Success');
    }

    /**
     * This function for get products data.
     *
     * @param WP_REST_Request $request full details about the request
     *
     * @return array {
     *
     * @var int   $productsTotals all products count
     * @var array $products       products list per page
     * @var array $categories     all products categories
     * @var array $tags           All products categories.
     *            }
     */
    public function products(WP_REST_Request $request) {
        $status = $request->get_param('status');
        $stock_status = $request->get_param('stock_status');
        $return_data = array();
        $productsTotals = $this->data_repository->productsTotals($status, $stock_status);

        $products = $this->data_repository->products($request);
        $request_custom_params = array(
            'per_page' => 10000
        );
        $categories = $this->data_repository->categories($request, true, $request_custom_params);
        $tags = $this->data_repository->tags($request, true, $request_custom_params);
        $return_data['productsTotals'] = $productsTotals;
        $return_data['products'] = $products->data;
        $return_data['categories'] = $categories->data;
        $return_data['tags'] = $tags->data;
        $return_data['single_templates'] = $this->data_repository->get_single_templates();

        return $this->modify_response($return_data);
    }

    /**
     * This function for get orders data.
     *
     * @param WP_REST_Request $request full details about the request
     *
     * @return array {
     *
     * @var int   $ordersTotals all orders count
     * @var array $orders       Products list per page.
     *            }
     */
    public function orders(WP_REST_Request $request) {
        $return_data = array();
        $ordersTotals = $this->data_repository->ordersTotals();
        $orders = $this->data_repository->orders($request);
        $return_data['ordersTotals'] = $ordersTotals;
        $return_data['orders'] = $orders;
        $return_data['test_mode'] = Utils::getTestMode(true);

        return $this->modify_response($return_data);
    }

    /**
     * This function for get single order.
     *
     * @param WP_REST_Request $request full details about the request
     *
     * @return array Single order data
     */
    public function order(WP_REST_Request $request) {
        $return_data = $this->data_repository->order($request);

        return $this->modify_response($return_data);
    }

    /**
     * This function for get customers if customer registered user.
     * Limit result set to resources with a specific role.
     * Options: all, administrator, editor, author, contributor, subscriber, customer and shop_manager. Default is customer
     *
     * @param WP_REST_Request $request full details about the request
     *
     * @depecated please use Api::customers_reports() function
     *
     * @return array {
     *
     * @var int   $customersTotals all orders count
     * @var array $customers       Registered customers list.
     *            }
     */
    public function customers(WP_REST_Request $request) {
        $return_data = array();
        $customersTotals = $this->data_repository->customersTotals();
        $return_data['customersTotals'] = $customersTotals;
        $return_data['customers'] = $this->data_repository->customers($request);

        return $this->modify_response($return_data);
    }

    /**
     * This function for get customer by customer_id.
     *
     * @param WP_REST_Request $request full details about the request
     *
     * @return array Customer data
     */
    public function customer(WP_REST_Request $request) {
        $customer_data = $this->data_repository->getCustomerData($request['id']);

        return $this->modify_response($customer_data);
    }

    /**
     * @return void
     */
    public function set_up_info(WP_REST_Request $request) {
        $set_up = new SetUp();
        $data = array(
            'default_country' => get_option('woocommerce_default_country', ''),
            'country_edited' => $set_up->getIsCountryEdited(),
            'product_added' => $set_up->getIsProductAdded(),
            'website_edited' => $set_up->getIsWebsiteEdited(),
            'payment_added' => $set_up->getIsPaymentAdded(),
            'countries_list' => Utils::getCountriesStatesList(),
            'payment_mode' => Utils::getTestMode(true),
        );

        return $this->modify_response($data);
    }

    /**
     * @return string[]
     */
    public function set_default_country(WP_REST_Request $request) {
        $default_country = $request->get_param('default_country');
        SetUp::updateDefaultCountry($default_country);

        return $this->modify_response();
    }

    public function set_payment_test_mode(WP_REST_Request $request) {
        $mode = $request->get_param('mode');
        Utils::updateTenwebPayTestMode($mode, 'stripe');
        $orderActions = new OrderActions();
        $orderActions->change_test_order_statuses();

        return $this->modify_response();
    }

    /**
     * @param $params
     * @param $request
     * @param $key
     *
     * @return bool
     */
    public function validate_unique_positions($params, $request, $key) {
        $params = json_decode($params, true);
        $positions_arr = array();

        foreach ($params as $param) {
            $positions_arr[] = $param['position'];
        }
        $counts = count(array_unique($positions_arr));

        if ($counts !== count($params)) {
            return false;
        }

        return true;
    }

    /**
     * @param $response
     *
     * @return array|mixed
     */
    private function modify_response($response = array()) {
        if ($this->request_from_proxy) {
            return $response;
        }

        return array(
            'msg' => 'success',
            'status' => 200,
            'data' => $response
        );
    }
}
