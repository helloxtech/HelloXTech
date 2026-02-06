<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Product_Attributes_Controller;

class WoocommerceProductsAttributes extends WC_REST_Product_Attributes_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }
}
