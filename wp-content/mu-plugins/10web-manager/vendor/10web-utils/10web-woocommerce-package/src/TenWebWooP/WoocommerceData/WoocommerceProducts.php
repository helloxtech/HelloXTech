<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Products_Controller;

class WoocommerceProducts extends WC_REST_Products_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }
}
