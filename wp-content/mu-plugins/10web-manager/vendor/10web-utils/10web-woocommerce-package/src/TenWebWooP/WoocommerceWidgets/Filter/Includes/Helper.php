<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes;

class Helper {

    public static function getShortNames() {
        $shortNames = array(
            'Checkbox' => 'chk',
            'RadioList' => 'rd',
            'Dropdown' => 'drp',
            'PriceSlider' => 'prc',
            'Box' => 'bx',
            'ColorList' => 'clr',
            'Pillbox' => 'plb'
        );

        return $shortNames;
    }

    public static function getFieldsList() {
        $fieldsList = array(
            'Checkbox' => array(
                'id' => 'Checkbox',
                'title' => 'Checkbox',
                'image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields/checkbox.svg',
                'preview_image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields_preview/checkbox.png',
                'checked' => '',
                'view' => 'checkbox.php'
            ),
            'RadioList' => array(
                'id' => 'RadioList',
                'title' => 'Radio list',
                'image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields/radio_list.svg',
                'preview_image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields_preview/radio_list.png',
                'checked' => '',
                'view' => 'radio_list.php'
            ),
            'Dropdown' => array(
                'id' => 'Dropdown',
                'title' => 'Dropdown',
                'image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields/dropdown.svg',
                'preview_image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields_preview/dropdown.png',
                'checked' => '',
                'view' => 'dropdown.php'
            ),
            'PriceSlider' => array(
                'id' => 'PriceSlider',
                'title' => 'Price slider',
                'image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields/price_slider.svg',
                'preview_image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields_preview/price_slider.png',
                'checked' => '',
                'view' => 'price_slider.php'
            ),
            'Box' => array(
                'id' => 'Box',
                'title' => 'Box',
                'image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields/box.svg',
                'preview_image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields_preview/box.png',
                'checked' => '',
                'view' => 'box.php'
            ),
            'ColorList' => array(
                'id' => 'ColorList',
                'title' => 'Color list',
                'image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields/color_list.svg',
                'preview_image' => array(
                    'color_without_text' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields_preview/color1.png',
                    'color_with_text' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields_preview/color2.png'
                ),
                'checked' => '',
                'view' => 'color_list.php'
            ),
            'Pillbox' => array(
                'id' => 'Pillbox',
                'title' => 'Pillbox',
                'image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields/pillbox.svg',
                'preview_image' => TWW_PRODUCT_FILTER_URL . 'Includes/Admin/assets/images/fields_preview/pillbox.png',
                'checked' => '',
                'view' => 'pillbox.php'
            ),
        );

        return $fieldsList;
    }

    public static function getSources() {
        $sources_list = array(
            array(
                'id' => 'Attribute',
                'title' => 'Variations'
            ),
            array(
                'id' => 'Tag',
                'title' => 'Collections'
            ),
            array(
                'id' => 'Category',
                'title' => 'Categories'
            ),
            array(
                'id' => 'StockStatus',
                'title' => 'Stock Status'
            ),
            array(
                'id' => 'Sale',
                'title' => 'Sale'
            ),
        );

        return $sources_list;
    }

    public static function getFilters() {
        $filters = get_posts(array( //phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
            'numberposts' => -1,
            'post_type' => TWW_FILTER_POST_TYPE,
            'post_status' => 'publish'
        ));

        return $filters;
    }

    public static function getTerms() {
        $attributes_list = wc_get_attribute_taxonomies();
        $wc_terms = array();

        foreach ($attributes_list as $attribute) {
            $key = 'pa_' . $attribute->attribute_name;
            $terms = get_terms($key);
            $wc_terms[$key] = $terms;
        }

        return $wc_terms;
    }

    /**
     * Recursive sanitation for an array
     *
     * @param $array
     *
     * @return mixed
     */
    public static function recursiveSanitizeTextField($array) {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = self::recursiveSanitizeTextField($value);
            } else {
                $value = sanitize_text_field($value);
            }
        }

        return $array;
    }

    public static function getAnalyticsData() {
        global $wpdb;
        $filters_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE `post_status` = 'publish' AND `post_type` = '%s'", TWW_FILTER_POST_TYPE)); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $analytics_data = 'cnt:' . $filters_count . ',';
        $fields_data = $wpdb->get_results("SELECT `meta_value` FROM wp_postmeta WHERE meta_key = 'tww_field_data'");  //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $short_names = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getShortNames();
        $fields_count = array();

        if (is_array($fields_data)) {
            foreach ($fields_data as $field) {
                if (isset($field->meta_value)) {
                    $field_data = unserialize($field->meta_value); // phpcs:ignore

                    if (!empty($field_data['fieldType'])) {
                        $fieldType = $field_data['fieldType'];

                        if (isset($fields_count[$fieldType])) {
                            ++$fields_count[$fieldType];
                        } else {
                            $fields_count[$fieldType] = 1;
                        }
                    }
                }
            }

            foreach ($fields_count as $name => $count) {
                if (isset($short_names[$name])) {
                    $analytics_data .= $short_names[$name] . ':' . $count . ',';
                }
            }
            $analytics_data = rtrim($analytics_data, ', ');
        }

        return $analytics_data;
    }

    /**
     * Get the URL of the parent category or the shop URL.
     *
     * This method retrieves the URL of the parent category for a given category ID.
     * If the category does not exist or has no parent, the shop URL is returned.
     *
     * @param int $category_id the ID of the category to check
     *
     * @return string the URL of the parent category or the shop URL
     */
    public static function get_category_or_shop_url($category_id) {
        // Get the category object
        $category = get_term($category_id, 'product_cat');

        // Check if the category exists
        if (!$category || is_wp_error($category)) {
            $shop_page_id = wc_get_page_id('shop'); // Get the shop page ID

            if ($shop_page_id && get_post_status($shop_page_id) === 'publish') {
                $shop_url = get_permalink($shop_page_id);
            } else {
                $shop_url = home_url('/');
            }

            return $shop_url;
        }

        // Check if the category has a parent
        if ($category->parent) {
            // Get the parent category
            $parent_category = get_term($category->parent, 'product_cat');

            // Check if the parent category exists
            if ($parent_category && !is_wp_error($parent_category)) {
                return get_term_link($parent_category); // Return parent category URL
            }
        }

        // Fallback to shop URL if no parent category
        return wc_get_page_permalink('shop');
    }

    /**
     * Retrieves the slug of a post by its ID.
     *
     * This function fetches a post object based on the given post ID and returns its slug.
     * If the post slug is empty, it generates a slug from the post title.
     * If the post doesn't exist, it returns an empty string.
     *
     * @param int $post_id the ID of the post
     *
     * @return string the post slug or an empty string if the post doesn't exist
     */
    public static function get_post_slug_by_id($post_id) {
        $post = get_post($post_id);

        if ($post) {
            // If the slug exists, return it
            if (!empty($post->post_name)) {
                return $post->post_name;
            }

            // Generate a slug from the post title if the slug is empty
            return sanitize_title($post->post_title);
        }

        // Return an empty string if the post doesn't exist
        return '';
    }

    /**
     * Organizes a flat array of terms into a hierarchical structure based on parent-child relationships.
     *
     * @param array $options array of term objects with `term_id` and `parent` properties
     *
     * @return array hierarchical array of terms with nested children
     */
    public static function organize_terms_hierarchy($options) {
        $terms = $options;
        $terms_by_id = array();
        $hierarchy = array();

        // Index terms by their term_id for easy lookup
        foreach ($terms as $term) {
            $terms_by_id[$term->term_id] = $term;
            $terms_by_id[$term->term_id]->children = array();
        }

        // Build the hierarchy
        foreach ($terms as $term) {
            if ($term->parent && isset($terms_by_id[$term->parent])) {
                $terms_by_id[$term->parent]->children[] = $term;
            } else {
                $hierarchy[] = $term;
            }
        }

        return $hierarchy;
    }

    /**
     * Retrieves the root parent category for a given category ID and parent ID from a list of categories.
     *
     * @param int   $category_id   the ID of the category to search for
     * @param int   $cat_parent_id the parent ID of the category
     * @param array $cat_list      the list of category objects
     *
     * @return object|null the root parent category object or the category with the given ID, or null if not found
     */
    public static function get_root_parent_category($category_id, $cat_parent_id, $cat_list) {
        foreach ($cat_list as $cat) {
            if ($cat->term_id === $cat_parent_id) {
                if ($cat->parent === 0) {
                    return $cat; // Found root parent category.
                }
                // Recursively search for the root parent.
                return self::get_root_parent_category($category_id, $cat->parent, $cat_list);
            }
        }

        // Return the category with term_id === $category_id if no match is found.
        foreach ($cat_list as $cat) {
            if ($cat->term_id === $category_id) {
                return $cat;
            }
        }

        return null;
    }
}
