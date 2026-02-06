<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Product_Tags_Controller;

class WoocommerceTags extends WC_REST_Product_Tags_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }
}
