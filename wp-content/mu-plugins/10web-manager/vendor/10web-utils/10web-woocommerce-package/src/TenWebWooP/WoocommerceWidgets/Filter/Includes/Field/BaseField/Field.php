<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Field\BaseField;

class Field {

    public $id = null;

    public $slug = null;

    public $type = null;

    public $name = null;

    public $state = null;

    public $template = null;

    public $inputName = null;

    public $step = null;

    public $minPrice = null;

    public $maxPrice = null;

    public $variation = null;

    public $options = null;

    public $value = null;

    public $parent = null;

    public $fieldView = null;

    public $position;

    public function getId() {
        return $this->id;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function getName() {
        return $this->name;
    }

    public function getTemplate() {
        return $this->template;
    }

    public function getState() {
        return $this->state;
    }
}
