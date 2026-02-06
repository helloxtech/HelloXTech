<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\Component;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\HookManager;

class PostType extends Component {

    public function attachHooks(HookManager $hook_manager) {
        add_action('elementor/widgets/register', array($this, 'registerFilterWidget'));
        add_action('init', array($this, 'init'));
    }

    public function init() {
        register_post_type(
            TWW_FILTER_POST_TYPE,
            array(
                'public' => false,
                'has_archive' => false,
                'publicaly_queryable' => false,
                'show_in_menu' => 'woocommerce',
                'show_in_admin_bar' => false,
                'show_ui' => true,
                'hierarchical' => false,
                'rewrite' => array('slug' => 'custom-posts'),
                'supports' => array(
                    'author',
                ),
                'labels' => array(
                    'name' => 'Filters',
                    'singular_name' => 'Filter',
                    'name_admin_bar' => 'Filter',
                    'add_new' => 'Add New Filter',
                    'add_new_item' => 'Add New Filter',
                    'new_item' => 'New Filter',
                    'edit_item' => 'Edit Filter',
                    'view_item' => 'View Filter',
                    'all_items' => 'Filters',
                    'search_items' => 'Search Filter',
                    'not_found' => 'No Filters found.',
                    'not_found_in_trash' => 'No Filters found in Trash.'
                ),
            )
        );
        register_post_type(
            TWW_FILTER_ITEM_POS_TYPE,
            array(
                'public' => false,
                'hierarchical' => false,
                'supports' => array(),
                'rewrite' => array('slug' => 'custom-posts'),
                'labels' => array(
                    'name' => __('Filter Item', 'woocommerce-product-filters'),
                ),
            )
        );
    }

    public function registerFilterWidget($widgets_manager) {
        $widgets_manager->register(new ElementorWidget());
    }
}
