<?php

namespace Tenweb_Builder\Modules\WebsiteNavigation;

use Tenweb_Builder\Condition;
class GetWPData
{
    public static function getNavMenuId($page_id) {
        $elementor_data_json = get_post_meta($page_id, '_elementor_data', true);
        $elementor_data = json_decode($elementor_data_json, true);
        update_option('twbb_wn_nav_ids',[]);
        //'twbb_wn_nav_ids' option is updateing in navMenuWalker function
        $callback = [
            'twbb-nav-menu' => [self::class, 'navMenuWalker']
        ];
        \Tenweb_Builder\Modules\Helper::elementorTreeWalker($elementor_data, $callback);
        $nav_menus = get_option('twbb_wn_nav_ids');
        return $nav_menus;
    }

    public static function getAnyMenuID() {
        //get the header menu id
        $header_menu = !empty(get_nav_menu_locations()['header_menu']) ? get_nav_menu_locations()['header_menu'] : null;
        $menu_obj = get_term($header_menu, 'nav_menu');
        if (is_wp_error($menu_obj)) {
            //get any menu
            if( !empty(wp_get_nav_menus()) && !is_wp_error(wp_get_nav_menus()) && is_array(wp_get_nav_menus()) ) {
                $menu_obj = wp_get_nav_menus()[0];
            } else {
                return null;
            }
        }
        if( !empty($menu_obj) && !is_wp_error($menu_obj) ) {
            $menu_id = $menu_obj->name;
            update_option('twbb_wn_nav_ids', [[$menu_id => null]]);
        }

        $nav_menus = get_option('twbb_wn_nav_ids');

        return $nav_menus;
    }

    public static function navMenuWalker($element) {
        $all_navs = get_option('twbb_wn_nav_ids',[]);
        $menu_info = [];
        $menu_info[$element['settings']['menu']] = $element['id'];
        $all_navs[] = $menu_info;
        update_option('twbb_wn_nav_ids', $all_navs);
    }

    public static function getNavMenuItems() {
        $header_template_id = Condition::get_instance()->get_header_template();
        $current_page_id = get_the_ID();
        $nav_menu_widget_info = self::getNavMenuId($header_template_id);
        $page_where_is_menu = $header_template_id;
        if( empty($nav_menu_widget_info)) {
            $nav_menu_widget_info = self::getNavMenuId($current_page_id);
            $page_where_is_menu = $current_page_id;
        }
        if ( empty($nav_menu_widget_info) ) {
            $nav_menu_widget_info = self::getAnyMenuID();
        }
        if ( empty($nav_menu_widget_info) ) {
	        return [
		        'nav_menu_id' => false,
		        'nav_menu_items' => [],
		        'nav_menu_item_ids' => [],
		        'page_where_is_menu' => false,
		        'nav_widget_id' => false,
	        ];
        }
        $nav_menu_id = array_keys($nav_menu_widget_info[0])[0];
        $nav_widget_id = $nav_menu_widget_info[0][$nav_menu_id];
        // Get the menu items for the menu ID
        $menu_items = wp_get_nav_menu_items($nav_menu_id);
		$menu_object = wp_get_nav_menu_object($nav_menu_id);

        /* Case when menu and items not available */
        if ( empty( $menu_object ) && empty( $menu_items ) ) {
            return [
                'nav_menu_id'        => false,
                'nav_menu_items'     => [],
                'nav_menu_item_ids'  => [],
                'page_where_is_menu' => false,
                'nav_widget_id'      => false,
            ];
        }

        /* Case when menu available but items not available empty menu */
        if ( ! empty( $menu_object ) && empty( $menu_items ) ) {
            return [
                'nav_menu_id'        => $menu_object->term_id,
                'nav_menu_items'     => [],
                'nav_menu_item_ids'  => [],
                'page_where_is_menu' => $page_where_is_menu,
                'nav_widget_id'      => $nav_widget_id,
            ];
        }

        // Return the menu items
        return [
            'nav_menu_id' => $menu_object->term_id,
            'nav_menu_items' => $menu_items,
            'nav_menu_item_ids' => array_map(function($item) {
                return (int) $item->object_id;
            }, $menu_items),
            'page_where_is_menu' => $page_where_is_menu,
            'current_page_id' => $current_page_id,
            'nav_widget_id' => $nav_widget_id,
        ];
    }

    public static function allPagesInfo() {
        $args = [
            'sort_order'   => 'DESC',
            'sort_column'  => 'post_date',
            'post_type'    => 'page',
            'post_status'  => ['publish','draft']
        ];
        $pages = get_pages($args);
        $pages_info = array();
        foreach ($pages as $page) {
            if( $page->post_type === 'product' && !TENWEB_WHITE_LABEL ) {
                $domain_id = get_option(TENWEB_PREFIX . '_domain_id');
                $content_edit_link = TENWEB_DASHBOARD . '/websites/'. $domain_id . '/ecommerce/products/edit-product/' . $page->ID;
            } else {
                $content_edit_link = get_edit_post_link($page->ID);
                if (\Elementor\Plugin::instance()->documents->get($page->ID)->is_built_with_elementor()) {
                    $content_edit_link = admin_url('post.php?post=' . $page->ID . '&action=elementor');
                }
            }
            $pages_info[] = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'slug' => $page->post_name,
                'url' => get_permalink($page->ID),
                'status' => $page->post_status,
                'post_type' => $page->post_type,
                'content_edit_link' => $content_edit_link,
            );
        }
        return $pages_info;
    }

    public static function filteredPagesList() {
        $pages = self::allPagesInfo();
        $nav_menu_item_ids = self::getNavMenuItems()['nav_menu_item_ids'];
        $pages = array_filter($pages, function($page) use ($nav_menu_item_ids) {
            return !in_array((int) $page['id'], $nav_menu_item_ids, true);
        });
        return $pages;
    }

    public static function objectsCountInMenu($nav_menu_items) {
        $objects_count_in_menu = [];
        foreach ($nav_menu_items as $item) {
            if( !isset($objects_count_in_menu[$item->object]) ) {
                $objects_count_in_menu[$item->object] = 1;
            } else {
                $objects_count_in_menu[$item->object]++;
            }
        }
        return $objects_count_in_menu;
    }

    public static function getNeededTypes($nav_menu_items) {
        $product_count = !empty(wp_count_posts('product')->publish) ? wp_count_posts('product')->publish : 0;
        $needed_types = [
            ['type' => 'post', 'post_type' => 'page', 'title' => 'Pages', 'count' => wp_count_posts('page')->publish],
            ['type' => 'post', 'post_type' => 'post', 'title' => 'Posts','count' => wp_count_posts('post')->publish],
            ['type' => 'taxonomy', 'post_type' => 'category', 'title' => 'Post categories','count' => wp_count_terms([
                'taxonomy' => 'category',
                'hide_empty' => true,
            ])],
            ['type' => 'taxonomy', 'post_type' => 'post_tag', 'title' => 'Post tags','count' => wp_count_terms([
                'taxonomy' => 'post_tag',
                'hide_empty' => true,
            ])],
            ['type' => 'post', 'post_type' => 'product', 'title' => 'Products','count' => $product_count],
            ['type' => 'taxonomy', 'post_type' => 'product_cat', 'title' => 'Products categories','count' => wp_count_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
            ])],
            ['type' => 'taxonomy', 'post_type' => 'product_tag', 	'title' => 	'Products collections','count'=>wp_count_terms([
                'taxonomy' => 'product_tag',
                'hide_empty' => true,
            ])],
            ['type' => 'taxonomy', 'post_type' => 'product_brand', 'title' => 'Product brands','count'=>wp_count_terms([
                'taxonomy' => 'product_brand',
                'hide_empty' => true,
            ])],
        ];
        $objectsCountInMenu = self::objectsCountInMenu($nav_menu_items);
        foreach ( $needed_types as &$the_type ) {
            //check if there are available items of $post_type['post_type']
            $the_type['available'] = false;
            if( empty($objectsCountInMenu[$the_type['post_type']])) {
                $objectsCountInMenu[$the_type['post_type']] = 0;
            }
            if ( $the_type['count'] !== NULL &&
                !is_wp_error($the_type['count']) &&
                ((int)$the_type['count'] - (int)$objectsCountInMenu[$the_type['post_type']]) > 0 ) {
                $the_type['available'] = true;
            }
        }
        $needed_types[] = ['type' => 'custom', 'post_type' => 'custom', 'title' => 'Custom link', 'available' => true, 'count' => 1];
        return $needed_types;
    }

}
