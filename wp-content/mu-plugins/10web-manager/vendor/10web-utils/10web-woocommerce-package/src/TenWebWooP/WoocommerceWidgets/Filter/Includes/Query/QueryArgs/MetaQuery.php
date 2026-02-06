<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Query\QueryArgs;

class MetaQuery implements QueryArgsInterface {

    private $query_args;

    private $field;

    public function __construct($query_args, $field) {
        $this->query_args = $query_args;
        $this->field = $field;
        $this->setArgs();
    }

    public function setArgs() {
        if (isset($this->field->options) && is_array($this->field->options)) {
            $query_data = array(
                'relation' => 'OR',
            );

            foreach ($this->field->options as $option) {
                if (isset($option->checked) && $option->checked) {
                    $query_data[] = array(
                        'key' => $option->key,
                        'value' => $option->value,
                        'compare' => '=',
                    );
                }
            }

            if (!empty($query_data)) {
                $this->query_args['meta_query'][] = $query_data;
            }
        }
    }

    public function get() {
        return $this->query_args;
    }
}
