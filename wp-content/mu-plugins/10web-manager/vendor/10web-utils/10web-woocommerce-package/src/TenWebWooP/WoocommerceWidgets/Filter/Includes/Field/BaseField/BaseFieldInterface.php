<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Field\BaseField;

interface BaseFieldInterface {

    public function setFieldId($id);

    public function setFieldName($name);

    public function setFieldType($type);

    public function setFieldState($state);

    public function setFieldTemplate($template);
}
