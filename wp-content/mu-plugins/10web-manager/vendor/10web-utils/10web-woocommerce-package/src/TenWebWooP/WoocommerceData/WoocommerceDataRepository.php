<?php

namespace TenWebWooP\WoocommerceData;

use stdClass;
use WC_Emails;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class WoocommerceDataRepository {

    public $products = null;

    public $orders = null;

    public $refunds = null;

    public $categories = null;

    public $tags = null;

    public $customers = null;

    public $ordersTotals = null;

    public $customersTotals = null;

    public $shippingClasses = null;

    public $productsAttributes = null;

    public $productsVariations = null;

    public $settingsOptions = null;

    public $customersReports = null;

    private $json_currency_codes = '{"BD": "BDT", "BE": "EUR", "BF": "XOF", "BG": "BGN", "BA": "BAM", "BB": "BBD", "WF": "XPF", "BL": "EUR", "BM": "BMD", "BN": "BND", "BO": "BOB", "BH": "BHD", "BI": "BIF", "BJ": "XOF", "BT": "BTN", "JM": "JMD", "BV": "NOK", "BW": "BWP", "WS": "WST", "BQ": "USD", "BR": "BRL", "BS": "BSD", "JE": "GBP", "BY": "BYR", "BZ": "BZD", "RU": "RUB", "RW": "RWF", "RS": "RSD", "TL": "USD", "RE": "EUR", "TM": "TMT", "TJ": "TJS", "RO": "RON", "TK": "NZD", "GW": "XOF", "GU": "USD", "GT": "GTQ", "GS": "GBP", "GR": "EUR", "GQ": "XAF", "GP": "EUR", "JP": "JPY", "GY": "GYD", "GG": "GBP", "GF": "EUR", "GE": "GEL", "GD": "XCD", "GB": "GBP", "GA": "XAF", "SV": "USD", "GN": "GNF", "GM": "GMD", "GL": "DKK", "GI": "GIP", "GH": "GHS", "OM": "OMR", "TN": "TND", "JO": "JOD", "HR": "HRK", "HT": "HTG", "HU": "HUF", "HK": "HKD", "HN": "HNL", "HM": "AUD", "VE": "VEF", "PR": "USD", "PS": "ILS", "PW": "USD", "PT": "EUR", "SJ": "NOK", "PY": "PYG", "IQ": "IQD", "PA": "PAB", "PF": "XPF", "PG": "PGK", "PE": "PEN", "PK": "PKR", "PH": "PHP", "PN": "NZD", "PL": "PLN", "PM": "EUR", "ZM": "ZMK", "EH": "MAD", "EE": "EUR", "EG": "EGP", "ZA": "ZAR", "EC": "USD", "IT": "EUR", "VN": "VND", "SB": "SBD", "ET": "ETB", "SO": "SOS", "ZW": "ZWL", "SA": "SAR", "ES": "EUR", "ER": "ERN", "ME": "EUR", "MD": "MDL", "MG": "MGA", "MF": "EUR", "MA": "MAD", "MC": "EUR", "UZ": "UZS", "MM": "MMK", "ML": "XOF", "MO": "MOP", "MN": "MNT", "MH": "USD", "MK": "MKD", "MU": "MUR", "MT": "EUR", "MW": "MWK", "MV": "MVR", "MQ": "EUR", "MP": "USD", "MS": "XCD", "MR": "MRO", "IM": "GBP", "UG": "UGX", "TZ": "TZS", "MY": "MYR", "MX": "MXN", "IL": "ILS", "FR": "EUR", "IO": "USD", "SH": "SHP", "FI": "EUR", "FJ": "FJD", "FK": "FKP", "FM": "USD", "FO": "DKK", "NI": "NIO", "NL": "EUR", "NO": "NOK", "NA": "NAD", "VU": "VUV", "NC": "XPF", "NE": "XOF", "NF": "AUD", "NG": "NGN", "NZ": "NZD", "NP": "NPR", "NR": "AUD", "NU": "NZD", "CK": "NZD", "XK": "EUR", "CI": "XOF", "CH": "CHF", "CO": "COP", "CN": "CNY", "CM": "XAF", "CL": "CLP", "CC": "AUD", "CA": "CAD", "CG": "XAF", "CF": "XAF", "CD": "CDF", "CZ": "CZK", "CY": "EUR", "CX": "AUD", "CR": "CRC", "CW": "ANG", "CV": "CVE", "CU": "CUP", "SZ": "SZL", "SY": "SYP", "SX": "ANG", "KG": "KGS", "KE": "KES", "SS": "SSP", "SR": "SRD", "KI": "AUD", "KH": "KHR", "KN": "XCD", "KM": "KMF", "ST": "STD", "SK": "EUR", "KR": "KRW", "SI": "EUR", "KP": "KPW", "KW": "KWD", "SN": "XOF", "SM": "EUR", "SL": "SLL", "SC": "SCR", "KZ": "KZT", "KY": "KYD", "SG": "SGD", "SE": "SEK", "SD": "SDG", "DO": "DOP", "DM": "XCD", "DJ": "DJF", "DK": "DKK", "VG": "USD", "DE": "EUR", "YE": "YER", "DZ": "DZD", "US": "USD", "UY": "UYU", "YT": "EUR", "UM": "USD", "LB": "LBP", "LC": "XCD", "LA": "LAK", "TV": "AUD", "TW": "TWD", "TT": "TTD", "TR": "TRY", "LK": "LKR", "LI": "CHF", "LV": "EUR", "TO": "TOP", "LT": "LTL", "LU": "EUR", "LR": "LRD", "LS": "LSL", "TH": "THB", "TF": "EUR", "TG": "XOF", "TD": "XAF", "TC": "USD", "LY": "LYD", "VA": "EUR", "VC": "XCD", "AE": "AED", "AD": "EUR", "AG": "XCD", "AF": "AFN", "AI": "XCD", "VI": "USD", "IS": "ISK", "IR": "IRR", "AM": "AMD", "AL": "ALL", "AO": "AOA", "AQ": "", "AS": "USD", "AR": "ARS", "AU": "AUD", "AT": "EUR", "AW": "AWG", "IN": "INR", "AX": "EUR", "AZ": "AZN", "IE": "EUR", "ID": "IDR", "UA": "UAH", "QA": "QAR", "MZ": "MZN"}';

    /**
     * @var array[]
     *              Options that can be changed from the REST API are listed here, please use the correct WC group names as they are grouped by it to be saved
     */
    private $settingsNames = array(
        'general' => array(
            'woocommerce_default_country',
            'woocommerce_currency'
        ),
        'products' => array(
            'woocommerce_weight_unit',
            'woocommerce_dimension_unit'
        )
    );

    /**
     * @var WoocommerceTaxClasses
     */
    public $taxClasses;

    public function __construct() {
        $this->products = new WoocommerceProducts();
        $this->categories = new WoocommerceCategories();
        $this->tags = new WoocommerceTags();
        $this->orders = new WoocommerceOrders();
        $this->refunds = new WoocommerceRefunds();
        $this->customers = new WoocommerceCustomers();
        $this->ordersTotals = new WoocommerceOrdersTotals();
        $this->customersTotals = new WoocommerceCustomersTotals();
        $this->shippingClasses = new WoocommerceShippingClasses();
        $this->productsAttributes = new WoocommerceProductsAttributes();
        $this->productsVariations = new WoocommerceProductsVariations();
        $this->settingsOptions = new WoocommerceSettingsOptions();
        $this->customersReports = new WoocommerceCustomersReports();
        $this->taxClasses = new WoocommerceTaxClasses();
    }

    /**
     * @param $groupId
     * @param $request
     * @param $modify_request restore request params
     *
     * @return array|WP_Error|WP_REST_Response
     */
    public function settingsOptions($groupId, $request, $modify_request = false) {
        if ($modify_request) {
            $collection_params = $this->settingsOptions->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }
        $request['group_id'] = $groupId;

        return $this->settingsOptions->get_items($request);
    }

    /**
     * @return array
     */
    public function getSettings() {
        $settings = array();
        $country_currency_codes = array();

        foreach ($this->settingsNames as $group_id => $options) {
            $settings_arr = $this->settingsOptions->get_group_settings($group_id);

            if (is_array($settings_arr)) {
                foreach ($settings_arr as $option) {
                    if (empty($country_currency_codes) && $option['id'] === 'woocommerce_default_country') {
                        $country_currency_codes = $this->getCountryCurrencyCodes($option['options']);
                    }

                    if (in_array($option['id'], $options, true)) {
                        $settings[] = $option;
                    }
                }
            }
        }

        return array(
            'settings' => $settings,
            'country_currency_codes' => $country_currency_codes,
        );
    }

    /**
     * @param $request
     *
     * @return array[]
     */
    public function setSettings($request) {
        $return_data = array(
            'update' => array()
        );
        $items = array_filter($request->get_params());
        $settings = array();

        if (isset($items['update']) && is_array($items['update'])) {
            foreach ($this->settingsNames as $group_id => $options) {
                foreach ($items['update'] as $update_el) {
                    if (in_array($update_el['id'], $options, true)) {
                        $settings[$group_id][] = $update_el;
                    }
                }
            }
        } else {
            return new WP_REST_Response(array('success' => false), 404);
        }

        $args = $request->get_url_params();

        foreach ($settings as $group_id => $update) {
            $args['group_id'] = $group_id;
            $request->set_param('update', $update);
            $request->set_url_params($args);
            $data = $this->settingsOptions->batch_items($request);

            if (isset($data['update'])) {
                $return_data['update'] = array_merge($return_data['update'], $data['update']);
            }
        }

        return $return_data;
    }

    /**
     * @param $country_list
     *
     * @return mixed
     */
    public function getCountryCurrencyCodes($country_list) {
        $currency_codes = json_decode($this->json_currency_codes, true);

        foreach ($country_list as $key => $val) {
            $country_code = explode(':', $key);

            if (isset($country_code[0], $currency_codes[$country_code[0]])) {
                $country_list[$key] = $currency_codes[$country_code[0]];
            }
        }

        return $country_list;
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return WP_Error|WP_REST_Response
     */
    public function shippingClasses($request, $modify_request = false) {
        if ($modify_request) {
            $collection_params = $this->shippingClasses->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }

        return $this->shippingClasses->get_items($request);
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return WP_Error|WP_REST_Response
     */
    public function taxClasses($request, $modify_request = false) {
        if ($modify_request) {
            $collection_params = $this->taxClasses->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }

        return $this->taxClasses->get_items($request);
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return array
     */
    public function productsAttributes($request, $modify_request = false) {
        if ($modify_request) {
            $collection_params = $this->productsAttributes->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }

        return $this->productsAttributes->get_items($request);
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return mixed
     */
    public function products($request, $modify_request = false) {
        if ($modify_request) {
            $collection_params = $this->products->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }
        $products_object = $this->products->get_items($request);

        return $this->add_product_variations($products_object);
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     * @param $custom_params
     *
     * @return WP_Error|WP_REST_Response
     */
    public function categories($request, $modify_request = false, $custom_params = array()) {
        if ($modify_request) {
            $collection_params = $this->categories->get_collection_params();
            $request = $this->modify_request($request, $collection_params, $custom_params);
        }

        return $this->categories->get_items($request);
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     * @param $custom_params
     *
     * @return WP_Error|WP_REST_Response
     */
    public function tags($request, $modify_request = false, $custom_params = array()) {
        if ($modify_request) {
            $collection_params = $this->tags->get_collection_params();
            $request = $this->modify_request($request, $collection_params, $custom_params);
        }

        return $this->tags->get_items($request);
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return array|mixed
     */
    public function orders($request, $modify_request = false) {
        if ($modify_request) {
            $collection_params = $this->orders->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }
        $order_stats = $this->get_order_stats();
        $customer_lookup = $this->get_customer_lookup();
        $orders_data = $this->orders->get_items($request);

        if (isset($orders_data->data)) {
            $orders_data = $orders_data->data;

            foreach ($orders_data as $key => $order) {
                if (isset($order['id'])) {
                    foreach ($order_stats as $stat) {
                        if (isset($stat->order_id, $stat->customer_id) && (int) $stat->order_id === (int) $order['id']) {
                            foreach ($customer_lookup as $lookup) {
                                if (isset($lookup->customer_id) && (int) $stat->customer_id === (int) $lookup->customer_id) {
                                    $orders_data[$key]['customer_data'] = $lookup;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $orders_data = array();
        }

        return $orders_data;
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return array[]
     */
    public function order($request, $modify_request = false) {
        $return_data = array(
            'orders' => array(),
            'customer' => array(),
            'refunds' => array(),
        );

        if ($modify_request) {
            $collection_params = $this->orders->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }
        $order_obj = $this->orders->get_item($request);

        if (isset($order_obj->data)) {
            $order_data = $order_obj->data;
            $return_data['orders'] = $order_data;
            $order_stats = $this->get_order_stats($order_data['id']);

            if (isset($order_stats->customer_id)) {
                $customer = $this->get_customer_lookup($order_stats->customer_id);
                $return_data['customer'] = $customer;
            }

            if (!empty($order_data['refunds'])) {
                $refundCollectionParams = $this->refunds->get_collection_params();
                $refundRequest = $this->modify_request($request, $refundCollectionParams, array(
                    'order_id' => $order_data['id'],
                ));

                $refundData = $this->refunds->get_items($refundRequest);

                if (!empty($refundData->data)) {
                    $return_data['refunds'] = $refundData->data;
                }
            }
        }

        return $return_data;
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return array|mixed
     */
    public function customers($request, $modify_request = false) {
        if ($modify_request) {
            $collection_params = $this->customers->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }
        $customers_obj = $this->customers->get_items($request);

        if (isset($customers_obj->data)) {
            $customers_data = $customers_obj->data;
            $customers_orders_data = array();
            $orders = wc_get_orders(array());

            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $order_base_data = $order->get_base_data();

                    if (!empty($order_base_data) && isset($order_base_data['customer_id'])) {
                        $customer_id = (int) $order_base_data['customer_id'];

                        if (isset($customers_orders_data[$customer_id])) {
                            $customers_orders_data[$customer_id]['totalPrice'] += (float) $order_base_data['total'];
                            ++$customers_orders_data[$customer_id]['count'];
                        } else {
                            $customers_orders_data[$customer_id]['totalPrice'] = (float) $order_base_data['total'];
                            $customers_orders_data[$customer_id]['count'] = 1;
                        }
                    }
                }
            }

            foreach ($customers_data as $key => $customer) {
                if (isset($customers_orders_data[$customer['id']])) {
                    $customers_data[$key]['ordersData'] = $customers_orders_data[$customer['id']];
                }
            }

            return $customers_data;
        }

        return array();
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return WP_Error|WP_REST_Response
     */
    public function customer($request, $modify_request = false) {
        if ($modify_request) {
            $collection_params = $this->customers->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }

        return $this->customers->get_item($request);
    }

    /**
     * @param $customer_id
     *
     * @return array
     */
    public function getCustomerData($customer_id) {
        $return_data = array(
            'customer' => $this->get_customer_lookup($customer_id),
            'ordersData' => array()
        );
        $order_stats = $this->get_order_stats();
        $order_ids = array();

        foreach ($order_stats as $stat) {
            if (isset($stat->customer_id, $stat->order_id) && (int) $stat->customer_id === (int) $customer_id) {
                $order_ids[] = (int) $stat->order_id;
            }
        }

        if (!empty($order_ids)) {
            $args = array(
                'limit' => -1,
                'post__in' => $order_ids,
                'return' => 'data',
            );

            $orders = wc_get_orders($args);
            $return_data['ordersData'] = $this->generate_orders_data($orders);
        }

        return $return_data;
    }

    /**
     * @return WC_Email[]
     */
    public function getEmails() {
        $wc_emails = WC_Emails::instance();

        return $wc_emails->get_emails();
    }

    /**
     * @param $id
     *
     * @return array|object|stdClass|stdClass[]|null
     */
    public function get_customer_lookup($id = null) {
        global $wpdb;

        if (isset($id)) {
            $customers = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wc_customer_lookup WHERE `customer_id` = %d', $id)); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        } else {
            $customers = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wc_customer_lookup'); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        }

        return $customers;
    }

    /**
     * Get the total count of WooCommerce products based on status and stock status.
     *
     * @param string      $status       The post status filter. Options: 'publish', 'draft', 'outofstock', 'any'. Default: 'any'.
     * @param string|null $stock_status Optional stock status filter. Options: 'instock', 'outofstock', or null for no filter. Default: null.
     *
     * @return int the total count of products matching the criteria
     */
    public function productsTotals($status = 'any', $stock_status = null) {
        global $wpdb;

        // Define post status filter
        switch ($status) {
            case 'publish':
                $status_filter = array('publish');
                break;

            case 'draft':
                $status_filter = array('draft');
                break;
            default:
                $status_filter = array('publish', 'draft');
                break;
        }
        $status_filter_string = implode(',', array_fill(0, count($status_filter), '%s'));

        // Base query
        $query = "
        SELECT COUNT(*)
        FROM $wpdb->posts AS p
    ";

        // Add join and condition for stock_status if provided
        if (isset($stock_status)) {
            $query .= "
            JOIN $wpdb->postmeta AS pm
            ON p.ID = pm.post_id
            WHERE pm.meta_key = '_stock_status'
              AND pm.meta_value = %s
        ";
            $query .= ' AND p.post_status IN (' . $status_filter_string . ") AND p.post_type = 'product'";
            $query_args = array_merge(array($stock_status), $status_filter);
        } else {
            $query .= 'WHERE p.post_status IN (' . $status_filter_string . ") AND p.post_type = 'product'";
            $query_args = $status_filter;
        }

        // Prepare and execute query
        $products_count = $wpdb->get_var($wpdb->prepare($query, $query_args)); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

        return (int) $products_count;
    }

    /**
     * @return array
     */
    public function ordersTotals() {
        return $this->ordersTotals->get_reports();
    }

    /**
     * @return array|array[]
     */
    public function customersTotals() {
        return $this->customersTotals->get_reports();
    }

    /**
     * @return int
     */
    public function customersReportTotals() {
        global $wpdb;
        $customers_count = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wc_customer_lookup'); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

        return (int) $customers_count;
    }

    /**
     * @param $request
     * @param $modify_request restore request params
     *
     * @return array
     */
    public function customersReport($request, $modify_request) {
        if ($modify_request) {
            $collection_params = $this->customersReports->get_collection_params();
            $request = $this->modify_request($request, $collection_params);
        }
        $customersReport = $this->customersReports->get_items($request);
        $customer_ids = array();

        if (!empty($customersReport)) {
            foreach ($customersReport->data as $key => $customer) {
                $customer_ids[] = $customer['id'];
                $customersReport->data[$key]['orders_count'] = 0;
            }

            if (!empty($customer_ids)) {
                $product_ids_arr = $this->get_customer_orders_count($customer_ids);

                if (is_array($product_ids_arr)) {
                    foreach ($product_ids_arr as $product_id_arr) {
                        foreach ($customersReport->data as $key => $customer) {
                            if ((int) $customer['id'] === (int) $product_id_arr['customer_id']) {
                                $customersReport->data[$key]['orders_count'] = (int) $customersReport->data[$key]['orders_count'] + 1;
                            }
                        }
                    }
                }
            }

            return $customersReport->data;
        }

        return array();
    }

    /**
     * @param $orders
     *
     * @return array
     */
    private function generate_orders_data($orders) {
        $orders_list = array();
        $total_price = 0;
        $price_list_by_status = array();
        $return_data = array();

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $order_base_data = $order->get_base_data();
                $price = 0;

                if (isset($order_base_data['total'])) {
                    $price = (float) $order_base_data['total'];
                    $total_price += $price;
                }

                if (isset($order_base_data['status'])) {
                    if (isset($price_list_by_status[$order_base_data['status']])) {
                        $price_list_by_status[$order_base_data['status']] += $price;
                    } else {
                        $price_list_by_status[$order_base_data['status']] = $price;
                    }
                }
                $orders_list[] = $order_base_data;
            }
        }
        $return_data['orders'] = $orders_list;
        $return_data['totalPrice'] = $total_price;
        $return_data['priceList'] = $price_list_by_status;

        return $return_data;
    }

    /**
     * @param $customer_ids
     *
     * @return array|object|stdClass[]|null
     */
    private function get_customer_orders_count($customer_ids) {
        global $wpdb;
        $ids_string = implode(',', array_fill(0, count($customer_ids), '%s'));

        return $wpdb->get_results($wpdb->prepare('SELECT `order_id`,`customer_id`  FROM `' . $wpdb->prefix . 'wc_order_stats` WHERE `customer_id` IN (' . $ids_string . ')', $customer_ids), ARRAY_A); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * @param $order_id
     *
     * @return array|object|stdClass|stdClass[]|null
     */
    private function get_order_stats($order_id = null) {
        global $wpdb;

        if (isset($order_id)) {
            $order_stats = $wpdb->get_row($wpdb->prepare('SELECT `order_id`, `customer_id` FROM ' . $wpdb->prefix . 'wc_order_stats WHERE `order_id` = %d', $order_id)); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        } else {
            $order_stats = $wpdb->get_results('SELECT `order_id`, `customer_id` FROM ' . $wpdb->prefix . 'wc_order_stats'); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        }

        return $order_stats;
    }

    /**
     * This function to add default parameters to the request
     *
     * @param $request
     * @param $collection_params
     * @param $custom_params
     *
     * @return mixed
     */
    private function modify_request($request, $collection_params, $custom_params = array()) {
        $new_request = $request;

        if (!empty($custom_params)) {
            $new_request = clone $request;
        }

        if (!empty($custom_params)) {
            foreach ($custom_params as $key => $val) {
                $new_request->set_param($key, $val);
            }
        }
        $new_request->set_attributes(array(
            'args' => $collection_params
        ));
        $new_request->set_default_params(
            $this->default_args($collection_params)
        );

        return $new_request;
    }

    /**
     * @param $collection_params
     *
     * @return array
     */
    private function default_args($collection_params) {
        $return_data = array();

        foreach ($collection_params as $key => $val) {
            if (isset($val['default'])) {
                $return_data[$key] = $val['default'];
            }
        }

        return $return_data;
    }

    /**
     * @param $products_object
     *
     * @return mixed
     */
    private function add_product_variations($products_object) {
        $variation_product_ids = array();

        if (isset($products_object->data)) {
            foreach ($products_object->data as $key => $product_data) {
                if (!empty($product_data['variations'])) {
                    $variation_product_ids[] = $product_data['id'];
                }
            }
        }

        if (is_array($variation_product_ids) && !empty($variation_product_ids)) {
            $prices_arr = array();
            global $wpdb;
            $ids_string = implode(',', array_fill(0, count($variation_product_ids), '%s'));
            $variation_prices_arr = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . $wpdb->prefix . 'postmeta` WHERE `post_id` IN (' . $ids_string . ') AND `meta_key` = "_price"', $variation_product_ids), ARRAY_A); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

            if (is_array($variation_prices_arr)) {
                foreach ($variation_prices_arr as $price_arr) {
                    $prices_arr[$price_arr['post_id']][] = (float) $price_arr['meta_value'];
                }

                foreach ($prices_arr as $pid => $price_range) {
                    //phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
                    $found_key = array_search($pid, array_column($products_object->data, 'id'), false);

                    if (isset($products_object->data[$found_key])) {
                        $products_object->data[$found_key]['price_range'] = array(
                            'min' => min($price_range),
                            'max' => max($price_range)
                        );
                    }
                }
            }
        }

        return $products_object;
    }

    public function getUnits() {
        return array(
            'currency_symbol' => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '',
            'weight_unit' => get_option('woocommerce_weight_unit'),
            'dimension_unit' => get_option('woocommerce_dimension_unit')
        );
    }

    /**
     * Retrieve a list of published Elementor single templates of type 'twbb_single'.
     *
     * @return array List of templates, where each template is an associative array with 'id' and 'title'.
     *               If no templates are found, returns an empty array.
     */
    public function get_single_templates() {
        $args = array(
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array( //phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key' => '_elementor_template_type',
                    'value' => array('twbb_single_product'),
                    'compare' => 'IN',
                ),
            )
        );
        //phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
        $templates = get_posts($args);
        $template_list = array();

        if ($templates) {
            foreach ($templates as $template) {
                $template_list[] = array(
                    'id' => $template->ID,
                    'title' => $template->post_title,
                );
            }
        } else {
            return array();
        }

        return $template_list;
    }

    /**
     * Retrieve the global product template ID if it exists.
     *
     * Attempts to fetch the product template ID and validates it.
     * Catches any exceptions that may occur during the process.
     *
     * @return int|null returns the template ID if valid; otherwise, null
     */
    public function get_default_product_template() {
        try {
            if (!class_exists('\Tenweb_Builder\Condition')) {
                return null;
            }

            $template_id = \Tenweb_Builder\Condition::get_instance()->get_product_template(0);

            // Validate that the template ID is a positive integer
            if (is_int($template_id) && $template_id > 0) {
                return $template_id;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Set conditions for a product and associate them with a template.
     *
     * @param int $product_id  the ID of the product
     * @param int $template_id the ID of the template
     *
     * @return array an array with 'status' (success or error) and 'message'
     */
    public function set_products_conditions($product_id, $template_id) {
        if (!class_exists('\Tenweb_Builder\Condition')) {
            return array(
                'status' => 'error',
                'message' => 'The Condition class from Tenweb_Builder does not exist.'
            );
        }

        if (!is_int($product_id) || $product_id <= 0) {
            return array(
                'status' => 'error',
                'message' => "Invalid product ID: $product_id"
            );
        }

        if (!is_int($template_id) || $template_id <= 0) {
            return array(
                'status' => 'error',
                'message' => "Invalid template ID: $template_id"
            );
        }
        $conditions_list = get_option('twbb_singular_conditions', array());

        if (!isset($conditions_list[$template_id])) {
            $conditions_list[$template_id] = array();
        }

        $conditions = array(
            'condition_type' => 'include',
            'page_type' => 'singular',
            'post_type' => 'product',
            'filter_type' => 'specific_posts',
            'specific_pages' => array($product_id)
        );

        if ($this->is_condition_unique($conditions, $conditions_list, $template_id)) {
            $conditions_list[$template_id][] = $conditions;
        }

        try {
            \Tenweb_Builder\Condition::get_instance()->save_conditions($conditions_list[$template_id], $template_id);

            return array(
                'status' => 'success',
                'message' => "Conditions successfully saved for product ID $product_id and template ID $template_id."
            );
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => 'Failed to save conditions: ' . $e->getMessage()
            );
        }
    }

    /**
     * Retrieves the template ID for a given product if it exists.
     *
     * @param int $product_id the ID of the product
     *
     * @return int|null the template ID if found, or null if not
     *
     * @throws Exception if an unexpected error occurs during the process
     */
    public function get_product_condition($product_id) {
        try {
            if (!class_exists('\Tenweb_Builder\Condition')) {
                return null;
            }

            if (!is_int($product_id) || $product_id <= 0) {
                return null;
            }
            $template_id = \Tenweb_Builder\Condition::get_instance()->get_product_template($product_id);

            return $template_id > 0 ? $template_id : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Deletes the product condition associated with a specific product ID.
     *
     * @param int $product_id the ID of the product whose condition is to be deleted
     *
     * @return array an array containing the result of the deletion operation
     */
    public function delete_product_condition($product_id) {
        if (!class_exists('\Tenweb_Builder\Condition')) {
            return array(
                'status' => 'error',
                'message' => 'The Condition class from Tenweb_Builder does not exist.'
            );
        }

        if (!is_int($product_id) || $product_id <= 0) {
            return array(
                'status' => 'error',
                'message' => 'Invalid product ID provided.'
            );
        }
        $conditions_list = get_option('twbb_singular_conditions', array());

        if (empty($conditions_list)) {
            return array(
                'status' => 'error',
                'message' => 'No conditions found to delete.'
            );
        }
        $condition_deleted = false;

        foreach ($conditions_list as $key => &$subArray) {
            foreach ($subArray as $index => $conditions) {
                if (in_array($product_id, $conditions['specific_pages'], true)) {
                    unset($subArray[$index]);
                    $condition_deleted = true;
                }
            }

            if (empty($subArray)) {
                unset($conditions_list[$key]);
            }
        }

        if ($condition_deleted) {
            update_option('twbb_singular_conditions', $conditions_list);

            return array(
                'status' => 'success',
                'message' => "Conditions for product ID $product_id deleted successfully."
            );
        } else {
            return array(
                'status' => 'error',
                'message' => "No conditions found for product ID $product_id."
            );
        }
    }

    /**
     * Checks if the given condition is unique within a template's condition list.
     *
     * @param array $conditions      the condition to check for uniqueness
     * @param array $conditions_list the list of all conditions, grouped by template ID
     * @param int   $template_id     the template ID under which the condition is being checked
     *
     * @return bool returns true if the condition is unique, otherwise false
     */
    private function is_condition_unique($conditions, $conditions_list, $template_id) {
        $conditions_without_order = $conditions;
        unset($conditions_without_order['order']);

        if (!isset($conditions_list[$template_id])) {
            return true;
        }

        foreach ($conditions_list[$template_id] as $existing_condition) {
            $existing_condition_without_order = $existing_condition;
            unset($existing_condition_without_order['order']);

            if ($existing_condition_without_order === $conditions_without_order) {
                return false;
            }
        }

        return true;
    }

    public function fillProductAttributeTerms($products_attributes) {
        foreach ($products_attributes as &$products_attribute) {
            // Prepare WooCommerce REST API request to update the product
            $products_attribute['terms'] = array();

            if (!empty($products_attribute['id'])) {
                $woo_rest_route = '/wc/v3/products/attributes/' . $products_attribute['id'] . '/terms';
                $woo_request = new WP_REST_Request('GET', $woo_rest_route);
                $response = rest_do_request($woo_request);

                if (! $response->is_error()) {
                    $server = rest_get_server();
                    $products_attribute['terms'] = $server->response_to_data($response, false);
                }
            }
        }

        return $products_attributes;
    }
}
