<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Variations;

class Tag extends BaseVariation {

    public function __construct($field_data, $field, $filtered_data = array()) {
        parent::__construct($field_data, $field, $filtered_data);
        $this->setOptions($filtered_data);
    }

    private function setOptions($filtered_data) {
        $taxonomy = 'product_tag';
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
            'hide_empty' => $empty
        );
        $tags = get_tags($args);

        if (!empty($tags)) {
            foreach ($tags as $term) {
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
        $this->field->options = $tags;
    }
}
