<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Query\QueryArgs;

class SaleQuery implements QueryArgsInterface {

    private $query_args;

    private $field;

    public function __construct($query_args, $field) {
        $this->query_args = $query_args;
        $this->field = $field;
        $this->setArgs();
    }

    public function setArgs() {
        foreach ($this->field->options as $option) {
            if (isset($option->checked) && $option->checked) {
                $product_ids_on_sale = wc_get_product_ids_on_sale();

                if (is_array($product_ids_on_sale) && !empty($product_ids_on_sale)) {
                    $this->query_args['post__in'] = $product_ids_on_sale;
                }
            }
        }
    }

    public function get() {
        return $this->query_args;
    }
}
