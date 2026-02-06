<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Variations;

class Sale extends BaseVariation {

    private $field_options = array(
        'productsInSale',
    );

    public function __construct($field_data, $field, $filtered_data = array()) {
        parent::__construct($field_data, $field, $filtered_data);
        $this->setOptions($field_data, $filtered_data);
    }

    private function setOptions($field_data, $filtered_data) {
        $options = array();
        $item = array(
            'name' => __('Sale', 'tenweb-builder'),
            'item_id' => 'productsInSale',
            'slug' => 'productsInSale',
            'key' => 'Sale',
            'value' => strtolower('productsInSale'),
        );

        foreach ($filtered_data as $filtered) {
            if ($item['slug'] === $filtered) {
                $item['checked'] = true;
            }
        }
        $item = (object) $item;

        $options[] = $item;
        $this->field->options = $options;
    }
}
