<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Field;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Field\BaseField\BaseField;

class PriceSliderField extends BaseField {

    private $minPrice = null;

    private $maxPrice = null;

    private $step = 1;

    protected function init($field_data) {
        parent::init($field_data);
    }

    public function setFieldSettings($field_data, $filtered_data = array()) {
        if (!empty($field_data['minPrice'])) {
            $this->minPrice = (int) $field_data['minPrice'];
        } elseif (empty($this->minPrice)) {
            $this->setMinMaxPrices();
        }

        if (!empty($field_data['step'])) {
            $this->step = (int) $field_data['step'];
        }

        if (!empty($field_data['maxPrice'])) {
            $this->maxPrice = (int) $field_data['maxPrice'];
        } elseif (empty($this->maxPrice)) {
            $this->setMinMaxPrices();
        }
        // Adjust the max value to step
        $this->maxPrice += $this->step - ($this->maxPrice - $this->minPrice) % $this->step;

        $this->field->step = $this->step;
        $this->field->minPrice = $this->minPrice;
        $this->field->maxPrice = $this->maxPrice;
        $this->field->variation = 'price';

        $this->field->options = array(
            'step' => (int) $this->step,
            'maxPrice' => (int) $this->maxPrice,
            'minPrice' => (int) $this->minPrice,
        );

        if (isset($field_data['filtered_data']) && is_array($field_data['filtered_data'])) {
            if (!empty($field_data['filtered_data']['min'])) {
                $this->field->options['minPrice'] = (int) $field_data['filtered_data']['min'];
            }

            if (!empty($field_data['filtered_data']['max'])) {
                $this->field->options['maxPrice'] = (int) $field_data['filtered_data']['max'];
            }
        }
    }

    public function setFiledInputName($id) {
        $this->field->inputName = 'twwf[' . $id . ']';
    }

    public function setMinMaxPrices() {
        if (empty($this->maxPrice) || empty($this->minPrice)) {
            global $wpdb;
            //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $res = $wpdb->get_row("SELECT max(cast(meta_value as unsigned)) as 'max' ,min(cast(meta_value as unsigned)) as 'min' 
                                         FROM $wpdb->postmeta
                                         INNER JOIN $wpdb->posts 
                                         ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) 
                                         AND ( $wpdb->postmeta.meta_key = '_price' ) 
                                         AND $wpdb->posts.post_type = 'product' 
                                         AND (($wpdb->posts.post_status = 'publish')) 
                                         WHERE `meta_key` LIKE '_price'");

            if (!empty($res)) {
                if (isset($res->max)) {
                    $this->maxPrice = (int) $res->max;
                }

                if (isset($res->max)) {
                    $this->minPrice = (int) $res->min;
                }
            }
        }
    }
}
