<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Tax_Classes_Controller;

class WoocommerceTaxClasses extends WC_REST_Tax_Classes_Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }
}
