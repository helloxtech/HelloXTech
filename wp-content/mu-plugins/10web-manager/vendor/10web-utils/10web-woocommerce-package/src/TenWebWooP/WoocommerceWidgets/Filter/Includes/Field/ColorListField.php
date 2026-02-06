<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Field;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Field\BaseField\BaseField;

class ColorListField extends BaseField {

    protected function init($field_data) {
        parent::init($field_data);
        $this->setFiledView($field_data);
    }

    public function setFiledView($field_data) {
        $this->field->fieldView = 'color_with_text';

        if (isset($field_data['fieldView'])) {
            $this->field->fieldView = $field_data['fieldView'];
        }
    }
}
