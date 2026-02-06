<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Query\QueryArgs;

class PriceQuery implements QueryArgsInterface {

    private $query_args;

    private $field;

    public function __construct($query_args, $field) {
        $this->query_args = $query_args;
        $this->field = $field;
        $this->setArgs();
    }

    public function setArgs() {
        if (isset($this->field->options) && is_array($this->field->options)) {
            if (!empty($this->field->options['minPrice'])) {
                $this->query_args['meta_query'][] = array(
                    'key' => '_price',
                    'value' => $this->field->options['minPrice'], // From price value
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }

            if (!empty($this->field->options['maxPrice'])) {
                $this->query_args['meta_query'][] = array(
                    'key' => '_price',
                    'value' => $this->field->options['maxPrice'], // To price value
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
        }
    }

    public function get() {
        return $this->query_args;
    }
}
