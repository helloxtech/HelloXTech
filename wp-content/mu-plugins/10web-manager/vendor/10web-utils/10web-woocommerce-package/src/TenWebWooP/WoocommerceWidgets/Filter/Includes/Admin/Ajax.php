<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Admin;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\Component;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\HookManager;

class Ajax extends Component {

    public function attachHooks(HookManager $hook_manager) {
        $hook_manager->addAction('wp_ajax_tww_save_filter', 'saveFilter');
    }

    public function saveFilter() {
        $return_data = array(
            'success' => true,
            'error' => '',
            'filter' => array()
        );

        if (
            isset($_POST['nonce'], $_POST['data'], $_POST['data']['twwFilterData'])
            && wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'tww_ajax_nonce')
            && is_array($_POST['data'])
        ) {
            $data = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::recursiveSanitizeTextField($_POST['data']); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            if (isset($data['twwFilterData'])) {
                $filter_data = $data['twwFilterData'];

                if (empty($filter_data['filterName'])) {
                    $return_data['error'] = 'Filter name is empty';
                    $return_data['success'] = false;
                }
                $filterName = $filter_data['filterName'];
                $filterId = $filter_data['filterId'];

                if ($return_data['success']) {
                    global $wpdb;
                    $object = $wpdb->get_row($wpdb->prepare("SELECT * FROM `wp_posts` WHERE post_title = %s AND post_type = '%s'", $filterName, TWW_FILTER_POST_TYPE)); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                    if (isset($object->ID) && $object->ID !== $filterId) {
                        $return_data['error'] = 'This filter name already exists. Please input a different name.';
                        $return_data['success'] = false;
                    }
                }

                if ($return_data['success']) {
                    $post_data = array(
                        'post_title' => sanitize_text_field($filterName),
                        'post_type' => TWW_FILTER_POST_TYPE,
                        'post_status' => 'publish',
                    );

                    if (!empty($filterId)) {
                        $post_data['ID'] = $filterId;
                        $filter_id = wp_update_post($post_data);
                        $return_data['filter']['id'] = $filter_id;
                        $return_data['filter']['title'] = sanitize_text_field($filterName);
                        $return_data['type'] = 'update';
                    } else {
                        $filter_id = wp_insert_post($post_data);
                        $return_data['filter']['id'] = $filter_id;
                        $return_data['filter']['title'] = sanitize_text_field($filterName);
                        $return_data['type'] = 'insert';
                    }

                    $field_ids = array();

                    if (isset($filter_data['fieldsData']) && is_array($filter_data['fieldsData'])) {
                        $fields_data = $filter_data['fieldsData'];

                        foreach ($fields_data as $field) { //todo fix N+1 query update here
                            if (!isset($field['fieldName']) && !isset($field['fieldId'])) {
                                continue;
                            }

                            if (isset($field['delete']) && $field['delete'] === '1') {
                                wp_delete_post($field['fieldId'], true);
                                continue;
                            }
                            $post_data = array(
                                'post_type' => TWW_FILTER_ITEM_POS_TYPE,
                                'post_parent' => $filter_id,
                                'meta_input' => array( 'tww_field_data' => $field ) //todo do this needs sanitization?
                            );

                            if (isset($field['fieldName'])) {
                                $post_data['post_title'] = sanitize_text_field($field['fieldName']);
                            }

                            if (!empty($field['fieldId']) && isset($field['update'])) {
                                $post_data['ID'] = $field['fieldId'];
                                $field_id = $field['fieldId'];
                                update_post_meta($field['fieldId'], 'tww_field_data', $field);
                                unset($post_data['meta_input']);
                                wp_update_post($post_data);
                            } elseif (!empty($field['fieldId'])) {
                                $post_data['ID'] = $field['fieldId'];
                                $tww_field_data = get_post_meta($field['fieldId'], 'tww_field_data', true);
                                $tww_field_data['fieldState'] = $field['fieldState'];
                                $tww_field_data['position'] = $field['position'];
                                $field_id = $field['fieldId'];
                                update_post_meta($field['fieldId'], 'tww_field_data', $tww_field_data);
                                unset($post_data['meta_input']);
                                wp_update_post($post_data);
                            } else {
                                $field_id = wp_insert_post($post_data);
                            }
                            $field_ids[] = (int) $field_id;
                        }
                    }
                    update_post_meta($filter_id, 'tww_fields', $field_ids);
                }
            } else {
                $return_data['error'] = 'Something wrong';
                $return_data['success'] = false;
            }
            $analyticsData = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getAnalyticsData();
            $return_data['analyticsData'] = $analyticsData;
        }
        echo wp_json_encode($return_data, true);
        die;
    }
}
