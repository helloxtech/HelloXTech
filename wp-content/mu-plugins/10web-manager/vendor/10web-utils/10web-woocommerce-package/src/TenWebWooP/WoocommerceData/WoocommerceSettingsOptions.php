<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Setting_Options_V2_Controller;

class WoocommerceSettingsOptions extends WC_REST_Setting_Options_V2_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }

    public function get_group_settings($group_id) {
        return parent::get_group_settings($group_id);
    }

    public function batch_items($request) {
        return parent::batch_items($request);
    }

    public function get_endpoint_args_for_item_schema($method = WP_REST_Server::CREATABLE) {
        return parent::get_endpoint_args_for_item_schema($method);
    }
}
