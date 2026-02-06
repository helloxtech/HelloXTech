<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Query\QueryArgs;

class TaxQuery implements QueryArgsInterface {

    private $query_args;

    private $field;

    public function __construct($query_args, $field) {
        $this->query_args = $query_args;
        $this->field = $field;
        $this->setArgs();
    }

    public function setArgs() {
        if (isset($this->field->options)) {
            $options = $this->field->options;
            $args = array();
            $taxonomy = '';

            if ($this->field->variation === 'Category' && $this->field->value === 'all') {
                foreach ($options as $option) {
                    $taxonomy = $option->taxonomy;

                    if ($option->checked && $option->parent === 0) {
                        $args[] = $option->slug;
                    }
                }

                foreach ($options as $option) {
                    if ($option->checked && $option->parent > 0) {
                        $parent_root_cat = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::get_root_parent_category($option->item_id, $option->parent, $options);

                        if (in_array($parent_root_cat->slug, $args, true)) {
                            unset($args[array_search($parent_root_cat->slug, $args, true)]);
                        }
                        $args[] = $option->slug;
                    }
                }
            } else {
                foreach ($options as $option) {
                    if ($option->checked) {
                        $taxonomy = $option->taxonomy;
                        $args[] = $option->slug;
                    }
                }
            }

            if (!empty($taxonomy) && !empty($args)) {
                $this->query_args['tax_query'][] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $args,
                    'operator' => 'IN',
                );
            }
        }
    }

    public function get() {
        return $this->query_args;
    }
}
