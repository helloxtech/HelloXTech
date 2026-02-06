<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Product_Variations_Controller;

class WoocommerceProductsVariations extends WC_REST_Product_Variations_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }
}
