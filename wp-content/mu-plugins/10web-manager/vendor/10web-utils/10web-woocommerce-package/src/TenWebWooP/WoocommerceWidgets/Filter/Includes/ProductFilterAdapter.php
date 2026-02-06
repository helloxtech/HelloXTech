<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes;

class ProductFilterAdapter {

    /**
     * Retrieve filtered fields based on request data and field information.
     *
     * @param array $requestData
     * @param array $fieldResults
     * @param int   $filterId
     *
     * @return array
     */
    public function getFilteredFields($requestData, $fieldResults, $filterId) {
        $filteredFields = array();

        foreach ($fieldResults as $field) {
            $fieldId = (int) $field->post_id;
            $fieldMetaData = $this->deserializeFieldMetadata($field->meta_value);

            if (!$fieldMetaData || !isset($fieldMetaData['fieldType'])) {
                continue; // Skip invalid field metadata
            }
            $fieldSlug = Helper::get_post_slug_by_id($fieldId);

            if (isset($requestData['_' . $fieldSlug])) {
                $fieldValue = $requestData['_' . $fieldSlug];

                if ($fieldMetaData['fieldType'] === 'PriceSlider') {
                    $filteredFields[$fieldId] = $this->validatePriceRange($fieldValue);
                } elseif ($fieldMetaData['fieldVariation'] === 'Attribute') {
                    $filteredFields[$fieldId] = $this->getAttributeFilterValues($fieldValue, $fieldMetaData['fieldValue']);
                } elseif ($fieldMetaData['fieldVariation'] === 'Tag') {
                    $filteredFields[$fieldId] = $this->getTagFilterValues($fieldValue);
                } elseif ($fieldMetaData['fieldVariation'] === 'StockStatus') {
                    $filteredFields[$fieldId] = $this->validateStockStatuses($fieldValue);
                } elseif ($fieldMetaData['fieldVariation'] === 'Sale') {
                    $filteredFields[$fieldId] = $this->validateSales($fieldValue);
                } else {
                    $filteredFields[$fieldId] = $this->getCategoryFilterValues($fieldValue);
                }
            }
        }

        return $filteredFields;
    }

    /**
     * Deserialize and validate field metadata.
     *
     * @param string $metaValue
     *
     * @return array|null
     */
    protected function deserializeFieldMetadata($metaValue) {
        if (!is_string($metaValue)) {
            return null;
        }
        $unserialized = unserialize($metaValue); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize

        return is_array($unserialized) ? $unserialized : null;
    }

    /**
     * Validate and retrieve PriceSlider range values.
     *
     * @param string $value
     *
     * @return array|null
     */
    protected function validatePriceRange($value) {
        $rangeValues = explode('-', $value);

        if (count($rangeValues) === 2 && is_numeric($rangeValues[0]) && is_numeric($rangeValues[1])) {
            return array(
                'min' => $rangeValues[0],
                'max' => $rangeValues[1]
            );
        }

        return null;
    }

    /**
     * Retrieve attribute filter values based on slugs.
     *
     * @param string $slugData
     * @param string $taxonomy
     *
     * @return array
     */
    protected function getAttributeFilterValues($slugData, $taxonomy) {
        $attributeValues = array();
        $taxonomyName = 'pa_' . $taxonomy;
        $slugs = explode(',', $slugData);

        foreach ($slugs as $slug) {
            $attribute = get_term_by('slug', $slug, $taxonomyName);

            if ($attribute && isset($attribute->term_id)) {
                $attributeValues[] = $attribute->term_id;
            }
        }

        return $attributeValues;
    }

    /**
     * Retrieve tag filter values based on slugs.
     *
     * @param string $slugData
     *
     * @return array
     */
    protected function getTagFilterValues($slugData) {
        $tagValues = array();
        $slugs = explode(',', $slugData);

        foreach ($slugs as $slug) {
            $tag = get_term_by('slug', $slug, 'product_tag');

            if ($tag && isset($tag->term_id)) {
                $tagValues[] = $tag->term_id;
            }
        }

        return $tagValues;
    }

    /**
     * Validate and retrieve stock status values.
     *
     * @param string $value
     *
     * @return array|null
     */
    protected function validateStockStatuses($value) {
        $statuses = explode(',', $value);

        foreach ($statuses as $status) {
            if (!is_string($status)) {
                return null;
            }
        }

        return $statuses;
    }

    protected function validateSales($value) {
        return array(sanitize_text_field($value));
    }

    /**
     * Retrieve category filter values based on slugs.
     *
     * @param string $slugData
     *
     * @return array
     */
    protected function getCategoryFilterValues($slugData) {
        $categoryValues = array();
        $slugs = explode(',', $slugData);

        foreach ($slugs as $slug) {
            $category = get_term_by('slug', $slug, 'product_cat');

            if ($category && isset($category->term_id)) {
                $categoryValues[] = $category->term_id;
            }
        }

        return $categoryValues;
    }
}
