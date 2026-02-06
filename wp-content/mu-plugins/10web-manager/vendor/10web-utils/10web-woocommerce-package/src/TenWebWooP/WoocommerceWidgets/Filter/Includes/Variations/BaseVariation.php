<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Variations;

abstract class BaseVariation {

    protected $field;

    public function __construct($field_data, $field, $filtered_data = array()) {
        $this->field = $field;
        $this->setVariation($field_data['fieldVariation']);
        $this->setValue('');
    }

    protected function setVariation($variation) {
        $this->field->variation = $variation;
    }

    protected function setValue($value) {
        $this->field->value = $value;
    }

    public function get() {
        return $this->field;
    }
}
