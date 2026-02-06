<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\Component;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\HookManager;

class Assets extends Component {

    public function attachHooks(HookManager $hook_manager) {
        add_filter('two_modify_exclude_js_from_delay', array($this, 'noDelayScripts'));
        $hook_manager->addAction('wp_enqueue_scripts', 'registerAssets');
    }

    public function registerAssets() {
        //data-noptimize
        wp_register_style(
            'tww_select2-style',
            TWW_PRODUCT_FILTER_URL . 'Includes/assets/libraries/css/select2.min.css?data-noptimize',
            array(),
            TWW_PRODUCT_FILTER_VERSION
        );
        wp_register_style(
            'tww_filter-style',
            TWW_PRODUCT_FILTER_URL . 'Includes/assets/css/filter.css?data-noptimize',
            array(),
            TWW_PRODUCT_FILTER_VERSION
        );
        wp_register_style(
            'tww_filter-icons',
            TWW_PRODUCT_FILTER_URL . 'Includes/assets/css/icons.css?data-noptimize',
            array(),
            TWW_PRODUCT_FILTER_VERSION
        );
        wp_register_style(
            'jquery_ui-style',
            TWW_PRODUCT_FILTER_URL . 'Includes/assets/libraries/jquery-ui/jquery-ui.css?data-noptimize',
            array(),
            TWW_PRODUCT_FILTER_VERSION
        );
        wp_register_script(
            'jquery_ui-script',
            TWW_PRODUCT_FILTER_URL . 'Includes/assets/libraries/jquery-ui/jquery-ui.js',
            array('jquery'),
            TWW_PRODUCT_FILTER_VERSION,
            false
        );
        wp_register_script(
            'tww_select2-script',
            TWW_PRODUCT_FILTER_URL . 'Includes/assets/libraries/js/select2.min.js',
            array('jquery'),
            TWW_PRODUCT_FILTER_VERSION,
            false
        );
        wp_register_script(
            'tww_filter-script',
            TWW_PRODUCT_FILTER_URL . 'Includes/assets/js/filter.js',
            array('jquery', 'jquery_ui-script', 'tww_select2-script'),
            TWW_PRODUCT_FILTER_VERSION,
            false
        );
        wp_register_script(
            'tww_filter-ajax',
            TWW_PRODUCT_FILTER_URL . 'Includes/assets/js/ajax.js',
            array('jquery'),
            TWW_PRODUCT_FILTER_VERSION,
            false
        );
    }

    public function noDelayScripts($excluded_scripts) {
        return array_unique(array_merge(is_array($excluded_scripts) ? $excluded_scripts : array(), array('filter.js', 'ajax.js', 'select2.min.js', 'jquery-ui.js', 'jquery.min.js', 'jquery.js')));
    }
}
