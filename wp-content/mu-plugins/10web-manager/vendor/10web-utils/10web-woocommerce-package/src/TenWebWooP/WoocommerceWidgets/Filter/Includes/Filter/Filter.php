<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Filter;

class Filter {

    public $id = null;

    public $name = null;

    public $slug = null;

    public $fields = null;

    public function getId() {
        return $this->id;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function getName() {
        return $this->name;
    }

    public function getFields() {
        return $this->fields;
    }
}
