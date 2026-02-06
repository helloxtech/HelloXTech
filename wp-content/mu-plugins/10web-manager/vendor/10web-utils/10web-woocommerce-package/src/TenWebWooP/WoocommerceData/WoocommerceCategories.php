<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Product_Categories_Controller;

class WoocommerceCategories extends WC_REST_Product_Categories_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }
}
