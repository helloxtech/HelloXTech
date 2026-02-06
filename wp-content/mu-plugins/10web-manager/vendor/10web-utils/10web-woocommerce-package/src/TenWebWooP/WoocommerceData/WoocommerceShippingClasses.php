<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Product_Shipping_Classes_Controller;

class WoocommerceShippingClasses extends WC_REST_Product_Shipping_Classes_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }
}
