<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Report_Customers_Totals_Controller;

class WoocommerceCustomersTotals extends WC_REST_Report_Customers_Totals_Controller {

    public function get_reports() {
        return parent::get_reports();
    }
}
