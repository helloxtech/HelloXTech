<?php

namespace TenWebWooP\WoocommerceWidgets\Filter;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Widget;

class WoocommerceProductFilters {

    private static $instance = null;

    private function __construct() {
        require_once __DIR__ . '/config.php';
        $GLOBALS['tww_widget'] = new Widget();
        $GLOBALS['tww_widget']->init();
    }

    /**
     * @return init|null
     */
    public static function getInstance() {
        if (self::$instance === null) {
            return new self();
        }

        return self::$instance;
    }
}
