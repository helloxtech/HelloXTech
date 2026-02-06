<?php

namespace TenWebWooP\WoocommerceData;

use Automattic\WooCommerce\Admin\API\Reports\Customers\Controller;

class WoocommerceCustomersReports extends Controller {

    public function get_items($request) {
        return parent::get_items($request);
    }
}
