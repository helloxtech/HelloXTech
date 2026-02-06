<?php

namespace TenWebWooP\PaymentMethods;

use Automattic\WooCommerce\Admin\API\Reports\Cache;
use TenWebWooP\Utils;
use WC_Admin_Settings;

class OrderActions {

    private $mode;

    private $test_mode_flag = 'test';

    private $test_order_status = 'wc-tenwebpay-archive';

    private $order_status_meta_key = 'twwp_order_status';

    private $order_environment_key = 'twwp_environment';

    public function __construct() {
        $this->register_tenwebpay_archive_status();
        $this->set_test_mode();
        $this->add_test_order_flug();
    }

    public function register_tenwebpay_archive_status() {
        register_post_status($this->test_order_status, array(
            'label' => '10WebPay Archived',
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('10WebPay Archived <span class="count">(%s)</span>', '10WebPay Archived <span class="count">(%s)</span>')
        ));
    }

    private function set_test_mode() {
        $settings = get_option('woocommerce_tenweb_payments_settings');
        $this->mode = (isset($settings['test_mode']) && $settings['test_mode'] === 'no') ? 'live' : 'test';
    }

    private function add_test_order_flug() {
        if ($this->mode === $this->test_mode_flag) {
            $excluded_statuses = WC_Admin_Settings::get_option('woocommerce_excluded_report_order_statuses', array());
            $key = array_search('tenwebpay-archive', $excluded_statuses, true);

            if ($key !== false) {
                unset($excluded_statuses[$key]);
            }
            update_option('woocommerce_excluded_report_order_statuses', $excluded_statuses);
            add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'display_wc_order_list_test_column_content'), 10, 2);
            add_action('manage_shop_order_posts_custom_column', array($this, 'display_wc_order_list_test_column_content_legacy'), 10, 2);
            add_filter('manage_edit-shop_order_columns', array($this, 'add_wc_order_list_test_column_legacy'));
            add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'add_wc_order_list_test_column' ));
            add_action('woocommerce_admin_order_data_after_order_details', array($this, 'add_wc_notice'));
            add_filter('wc_order_statuses', array($this, 'add_tenwebpay_archive_status'));
        } else {
            $excluded_statuses = WC_Admin_Settings::get_option('woocommerce_excluded_report_order_statuses', array());

            if ((array_search('tenwebpay-archive', $excluded_statuses, true)) === false) {
                $excluded_statuses[] = 'tenwebpay-archive';
                update_option('woocommerce_excluded_report_order_statuses', $excluded_statuses);
            }
        }
        add_filter('woocommerce_update_options_checkout', array($this, 'change_test_order_statuses'));
    }

    public function add_tenwebpay_archive_status($order_statuses) {
        $order_statuses[$this->test_order_status] = '10WebPay Archived';

        return $order_statuses;
    }

    /**
     * @return void
     *              add notice for test orders , order edit page
     */
    public function add_wc_notice($order) {
        if ($order->get_meta($this->order_environment_key) === $this->test_mode_flag) {
            echo '<div class="twwp_notice notice is-dismissible notice-info"><p>Test order</p></div>';
            echo '<style>#woocommerce-order-data .twwp_notice{display: none}</style>';
        }
    }

    /**
     * @return array
     *               added custom column for test orders
     */
    public function add_wc_order_list_test_column($columns) {
        $reordered_columns = array();

        // Inserting columns to a specific location
        foreach ($columns as $key => $column) {
            $reordered_columns[$key] = $column;

            if ($key === 'order_status') {
                // Inserting after "Status" column
                $reordered_columns['test_order'] = 'Test order';
            }
        }

        return $reordered_columns;
    }

    /**
     * @return array
     *               added custom column for test orders
     */
    public function add_wc_order_list_test_column_legacy($columns) {
        $reordered_columns = array();
        // Inserting columns to a specific location
        foreach ($columns as $key => $column) {
            $reordered_columns[$key] = $column;

            if ($key === 'order_status') {
                // Inserting after "Status" column
                $reordered_columns['test_order'] = 'Test order';
            }
        }

        return $reordered_columns;
    }

    /**
     * @return void
     *              added test flag for orders
     */
    public function display_wc_order_list_test_column_content($column, $order) {
        switch ($column) {
            case 'test_order':
                // Get tenweb order metadata
                $twwp_environment = $order->get_meta($this->order_environment_key);

                if ($twwp_environment === $this->test_mode_flag) {
                    echo '<span>' . esc_html($twwp_environment) . '</span>';
                }
                break;
        }
    }

    /**
     * @return void
     *              added test flag for orders
     */
    public function display_wc_order_list_test_column_content_legacy($column, $order_id) {
        switch ($column) {
            case 'test_order':
                // Get tenweb order metadata
                $twwp_environment = get_post_meta($order_id, $this->order_environment_key, true);

                if ($twwp_environment === $this->test_mode_flag) {
                    echo '<span>' . esc_html($twwp_environment) . '</span>';
                }
                break;
        }
    }

    /**
     * @return void
     *              changes test order statuses
     *              we used wpdb so there wasn't too much query to db
     */
    public function change_test_order_statuses() //phpcs:ignore WordPressVIPMinimum.Hooks.AlwaysReturnInFilter.MissingReturnStatement
    {
        $this->set_test_mode();
        global $wpdb;

        if ($this->mode === $this->test_mode_flag) {
            try {
                if (Utils::isHposActive()) {
                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}wc_orders_meta AS pm1
                                        JOIN {$wpdb->prefix}wc_orders AS p ON pm1.order_id = p.ID
                                        JOIN {$wpdb->prefix}wc_orders_meta AS pm2 ON p.ID = pm2.order_id
                                        SET 
                                            p.status = pm1.meta_value
                                        WHERE 
                                             pm1.meta_key = '%s'
                                             AND pm2.meta_key = '%s'
                                             AND pm2.meta_value = '%s'", $this->order_status_meta_key, $this->order_environment_key, $this->test_mode_flag));

                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}postmeta AS pm1
                                        JOIN {$wpdb->prefix}posts AS p ON pm1.post_id = p.ID
                                        JOIN {$wpdb->prefix}postmeta AS pm2 ON p.ID = pm2.post_id
                                        SET 
                                            p.post_status = pm1.meta_value
                                        WHERE 
                                             pm1.meta_key = '%s'
                                             AND pm2.meta_key = '%s'
                                             AND pm2.meta_value = '%s'", $this->order_status_meta_key, $this->order_environment_key, $this->test_mode_flag));

                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}wc_orders_meta AS pm1
                                                    JOIN {$wpdb->prefix}wc_order_stats AS os ON pm1.order_id = os.order_id
                                                    SET os.status = pm1.meta_value
                                                    WHERE 
                                                    pm1.meta_key = '%s'", $this->order_status_meta_key));
                } else {
                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}postmeta AS pm1
                                        JOIN {$wpdb->prefix}posts AS p ON pm1.post_id = p.ID
                                        JOIN {$wpdb->prefix}postmeta AS pm2 ON p.ID = pm2.post_id
                                        SET 
                                            p.post_status = pm1.meta_value
                                        WHERE 
                                             pm1.meta_key = '%s'
                                             AND pm2.meta_key = '%s'
                                             AND pm2.meta_value = '%s'", $this->order_status_meta_key, $this->order_environment_key, $this->test_mode_flag));

                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}wc_orders_meta AS pm1
                                        JOIN {$wpdb->prefix}wc_orders AS p ON pm1.order_id = p.ID
                                        JOIN {$wpdb->prefix}wc_orders_meta AS pm2 ON p.ID = pm2.order_id
                                        SET 
                                            p.status = pm1.meta_value
                                        WHERE 
                                             pm1.meta_key = '%s'
                                             AND pm2.meta_key = '%s'
                                             AND pm2.meta_value = '%s'", $this->order_status_meta_key, $this->order_environment_key, $this->test_mode_flag));

                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}postmeta AS pm1
                                                    JOIN {$wpdb->prefix}wc_order_stats AS os ON pm1.order_id = os.order_id
                                                    SET os.status = pm1.meta_value
                                                    WHERE 
                                                    pm1.meta_key = '%s'", $this->order_status_meta_key));
                }
            } catch (Exception $err) {
                return false;
            }
        } else {
            try {
                if (Utils::isHposActive()) {
                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}wc_orders_meta AS pm1
                                    JOIN {$wpdb->prefix}wc_orders AS p ON pm1.order_id = p.ID
                                    JOIN {$wpdb->prefix}wc_orders_meta AS pm2 ON p.ID = pm2.order_id
                                    SET
                                        p.status = '%s'
                                    WHERE
                                        pm1.meta_key = '%s'
                                        AND pm2.meta_key = '%s'
                                        AND pm2.meta_value = '%s'", $this->test_order_status, $this->order_status_meta_key, $this->order_environment_key, $this->test_mode_flag));

                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}postmeta AS pm1
                                    JOIN {$wpdb->prefix}posts AS p ON pm1.post_id = p.ID
                                    JOIN {$wpdb->prefix}postmeta AS pm2 ON p.ID = pm2.post_id
                                    SET
                                        p.post_status = '%s'
                                    WHERE
                                        pm1.meta_key = '%s'
                                        AND pm2.meta_key = '%s'
                                        AND pm2.meta_value = '%s'", $this->test_order_status, $this->order_status_meta_key, $this->order_environment_key, $this->test_mode_flag));

                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}wc_orders_meta AS pm1
                                                    JOIN {$wpdb->prefix}wc_order_stats AS os ON pm1.order_id = os.order_id
                                                    SET os.status = '%s'
                                                    WHERE 
                                                    pm1.meta_key = '%s'", $this->test_order_status, $this->order_status_meta_key));
                } else {
                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}postmeta AS pm1
                                    JOIN {$wpdb->prefix}posts AS p ON pm1.post_id = p.ID
                                    JOIN {$wpdb->prefix}postmeta AS pm2 ON p.ID = pm2.post_id
                                    SET
                                        p.post_status = '%s'
                                    WHERE
                                        pm1.meta_key = '%s'
                                        AND pm2.meta_key = '%s'
                                        AND pm2.meta_value = '%s'", $this->test_order_status, $this->order_status_meta_key, $this->order_environment_key, $this->test_mode_flag));

                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}wc_orders_meta AS pm1
                                    JOIN {$wpdb->prefix}wc_orders AS p ON pm1.order_id = p.ID
                                    JOIN {$wpdb->prefix}wc_orders_meta AS pm2 ON p.ID = pm2.order_id
                                    SET
                                        p.status = '%s'
                                    WHERE
                                        pm1.meta_key = '%s'
                                        AND pm2.meta_key = '%s'
                                        AND pm2.meta_value = '%s'", $this->test_order_status, $this->order_status_meta_key, $this->order_environment_key, $this->test_mode_flag));

                    //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}postmeta AS pm1
                                                    JOIN {$wpdb->prefix}wc_order_stats AS os ON pm1.order_id = os.order_id
                                                    SET os.status = '%s'
                                                    WHERE 
                                                    pm1.meta_key = '%s'", $this->test_order_status, $this->order_status_meta_key));
                }
            } catch (Exception $err) {
                return false;
            }
        }
        Cache::invalidate();
    }
}
