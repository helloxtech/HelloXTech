<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Variations;

class Attribute extends BaseVariation {

    private $field_data;

    public function __construct($field_data, $field, $filtered_data = array()) {
        parent::__construct($field_data, $field, $filtered_data);
        $this->field_data = $field_data;
        $this->setOptions($field_data['fieldValue'], $filtered_data);
        $this->setValue($field_data['fieldValue']);
    }

    private function setOptions($value, $filtered_data) {
        $terms = get_terms('pa_' . $value);

        if (!is_wp_error($terms)) {
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    $term->item_id = $term->term_id;

                    if (isset($this->field_data['tww_color_attr_input_' . $term->term_id])) {
                        $term->color = $this->field_data['tww_color_attr_input_' . $term->term_id];
                    }

                    if (!empty($filtered_data)) {
                        foreach ($filtered_data as $filtered) {
                            if ($term->term_id === (int) $filtered) {
                                $term->checked = true;
                            }
                        }
                    }
                }
            }
            $this->field->options = $terms;
        }
    }
}
