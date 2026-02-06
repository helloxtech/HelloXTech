<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Admin;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\Component;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\HookManager;

class Admin extends Component {

    public function attachHooks(HookManager $hook_manager) {
        $hook_manager->addAction('elementor/editor/after_enqueue_scripts', 'registerWidgetScripts');
        $hook_manager->addAction('elementor/editor/after_enqueue_styles', 'registerWidgetStyles');
    }

    public function registerWidgetScripts() {
        wp_enqueue_script(
            'tww_elementor_widget-script',
            TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/js/elementor_widget.js',
            array(
                'jquery',
            ),
            TWW_PRODUCT_FILTER_VERSION,
            true
        );
    }

    public function registerWidgetStyles() {
        wp_enqueue_style(
            'tww_elementor_widget-style',
            TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/css/elementor_widget.css',
            array(),
            TWW_PRODUCT_FILTER_VERSION,
            'all'
        );
    }
}
