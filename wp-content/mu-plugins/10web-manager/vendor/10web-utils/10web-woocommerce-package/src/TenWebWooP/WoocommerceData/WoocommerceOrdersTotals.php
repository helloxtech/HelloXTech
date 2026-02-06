<?php

namespace TenWebWooP\WoocommerceData;

use WC_REST_Report_Orders_Totals_Controller;

class WoocommerceOrdersTotals extends WC_REST_Report_Orders_Totals_Controller {

    public function get_reports() {
        return parent::get_reports();
    }
}
