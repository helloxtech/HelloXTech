<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Field\BaseField;

abstract class BaseField implements BaseFieldInterface {

    public $field;

    private $filter_id = null;

    public function __construct($field_data) {
        $this->init($field_data);
    }

    protected function init($field_data) {
        $this->field = new Field();
        $this->filter_id = $field_data['filter_id'];
        $this->setFieldId($field_data['id']);
        $this->setFieldSlug($field_data['slug']);
        $this->setFieldType($field_data['fieldType']);
        $this->setFieldName($field_data['fieldName']);
        $this->setFieldState($field_data['fieldState']);
        $this->setFieldTemplate($field_data['fieldType']);
        $this->setFiledInputName($field_data['id']);
        $this->setFieldSettings($field_data, $field_data['filtered_data']);
        $this->field->position = 1;

        if (isset($field_data['position'])) {
            $this->setFieldPosition($field_data['position']);
        }
    }

    public function setFieldPosition($position) {
        $this->field->position = (int) $position;
    }

    public function setFieldId($id) {
        $this->field->id = $id;
    }

    public function setFieldSlug($slug) {
        $this->field->slug = $slug;
    }

    public function setFieldType($type) {
        $this->field->type = $type;
    }

    public function setFieldName($name) {
        $this->field->name = $name;
    }

    public function setFieldState($state) {
        $this->field->state = $state;
    }

    public function setFieldSettings($field_data, $filtered_data = array()) {
        $variation = $field_data['fieldVariation'];
        $field_variation_class = '\TenWebWooP\WoocommerceWidgets\Filter\Includes\Variations\\' . $variation;

        if (class_exists($field_variation_class)) {
            $variation_object = new $field_variation_class($field_data, $this->field, $filtered_data);
            $this->field = $variation_object->get();
        }
    }

    public function setFieldTemplate($template) {
        $this->field->template = $template;
    }

    public function setFiledInputName($id) {
        $this->field->inputName = 'twwf[' . $id . '][]';
    }

    public function get() {
        return $this->field;
    }
}
