<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Orders_Controller;

class WoocommerceOrders extends WC_REST_Orders_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }

    public function get_item($request) {
        return parent::get_item($request);
    }
}
