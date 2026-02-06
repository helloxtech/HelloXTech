<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Variations;

class Category extends BaseVariation {

    public function __construct($field_data, $field, $filtered_data = array()) {
        parent::__construct($field_data, $field, $filtered_data);
        $this->setValue($field_data['fieldValue']);
        $this->setOptions($filtered_data);
    }

    private function setOptions($filtered_data) {
        $category_id = null;

        if (is_product_category()) {
            $category = get_queried_object(); // Get the current queried category object
            $category_id = $category->term_id; // Get the category ID
            $categories = $this->get_categories($category_id);
        } else {
            if ($this->field->value === 'subcategories') {
                $categories = $this->get_categories(null, 'subcategories');
            } elseif ($this->field->value === 'categories') {
                $categories = $this->get_categories(null, 'categories');
            } else {
                $categories = $this->get_categories();
            }
        }

        if (!empty($categories)) {
            foreach ($categories as $key => $term) {
                if ($term->count === 0) {
                    unset($categories[$key]);
                    continue;
                }
                $term->item_id = $term->term_id;

                if (!empty($filtered_data)) {
                    foreach ($filtered_data as $filtered) {
                        if ($term->term_id === (int) $filtered) {
                            $term->checked = true;
                        }
                    }
                }
            }
        }
        $this->field->options = $categories;

        if (isset($category_id)) {
            $this->field->parent = get_term($category_id);
        }
    }

    private function get_categories($parent_id = null, $type = 'all') {
        $taxonomy = 'product_cat';
        $orderby = 'name';
        $show_count = 0;
        $pad_counts = 0;
        $hierarchical = 1;
        $title = '';
        $empty = 0;
        $args = array(
            'taxonomy' => $taxonomy,
            'orderby' => $orderby,
            'show_count' => $show_count,
            'pad_counts' => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li' => $title,
            'hide_empty' => $empty,
        );

        if ($type === 'categories') {
            $args['parent'] = 0;
        }

        if (!empty($parent_id)) {
            $args['parent'] = $parent_id;
            $categories = get_categories($args);
        } else {
            $categories = get_categories($args);

            if ($type === 'subcategories') {
                $child_categories = array_filter($categories, function ($category) {
                    return $category->parent > 0;
                });

                return $child_categories;
            }

            return $categories;
        }

        $all_categories = array();

        foreach ($categories as $category) {
            $all_categories[] = $category;

            // Get subcategories recursively
            $subcategories = $this->get_categories($category->term_id);

            foreach ($subcategories as $subcategory) {
                $all_categories[] = $subcategory; // Append each subcategory individually
            }
        }

        return $all_categories;
    }
}
