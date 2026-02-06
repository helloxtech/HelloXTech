<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Variations;

class StockStatus extends BaseVariation {

    private $field_options = array(
        'inStock',
        'outOfStock',
        'onBackorder',
    );

    public function __construct($field_data, $field, $filtered_data = array()) {
        parent::__construct($field_data, $field, $filtered_data);
        $this->setOptions($field_data, $filtered_data);
    }

    private function setOptions($field_data, $filtered_data) {
        $options = array();

        foreach ($this->field_options as $option) {
            if (isset($field_data[$option], $field_data[$option . 'Check'])) {
                $item = array(
                    'name' => $field_data[$option],
                    'item_id' => $option,
                    'slug' => $option,
                    'key' => '_stock_status',
                    'value' => strtolower($option),
                );

                if ($field_data[$option . 'Check'] === 'on') {
                    $item['fieldState'] = 'on';
                } else {
                    $item['fieldState'] = 'off';
                }
                $item = (object) $item;

                if (!empty($filtered_data)) {
                    foreach ($filtered_data as $filtered) {
                        if ($option === $filtered) {
                            $item->checked = true;
                        }
                    }
                }
                $options[] = $item;
            }
        }
        $this->field->options = $options;
    }
}
