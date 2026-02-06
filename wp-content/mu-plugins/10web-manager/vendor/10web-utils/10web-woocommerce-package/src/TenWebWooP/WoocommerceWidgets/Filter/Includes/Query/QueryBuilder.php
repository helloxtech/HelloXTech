<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Query;

class QueryBuilder {

    private $queryTypes = array(
        'Attribute' => 'TaxQuery',
        'Category' => 'TaxQuery',
        'Tag' => 'TaxQuery',
        'StockStatus' => 'MetaQuery',
        'price' => 'PriceQuery',
        'Sale' => 'SaleQuery'
    );

    private $filter;

    private $queryArgs;

    public function __construct($filter, $query_args) {
        $this->filter = $filter;
        $this->queryArgs = $query_args;
        $this->setQuery();
    }

    private function setQuery() {
        if (isset($this->filter->fields) && is_array($this->filter->fields)) {
            $fields = $this->filter->fields;

            foreach ($fields as $field) {
                if (isset($this->queryTypes[$field->variation]) && $field->state === 'on') {
                    $field_args_class = '\TenWebWooP\WoocommerceWidgets\Filter\Includes\Query\QueryArgs\\' . $this->queryTypes[$field->variation];

                    if (class_exists($field_args_class)) {
                        $field_args_object = new $field_args_class($this->queryArgs, $field);
                        $this->queryArgs = $field_args_object->get();
                    }
                }
            }
            $this->queryArgs['tax_query']['relation'] = 'AND';
            $this->queryArgs['meta_query']['relation'] = 'AND';
        }
    }

    public function get() {
        $this->queryArgs['meta_query']['stock_status_clause'] = [
            'key' => '_stock_status',
            'compare' => 'EXISTS',
        ];
        return $this->queryArgs;
    }
}
