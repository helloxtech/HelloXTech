<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Filter;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Field\BaseField\BaseField;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\ProductFilterAdapter;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\TemplateLoader;

class FilterBuilder implements FilterBuilderInterface {

    private $filter;

    private $default_post_status = 'auto-draft';

    public function __construct($id) {
        $this->init($id);
    }

    private function init($id) {
        $this->filter = new Filter();
        $this->setFilterId($id);
        $this->setFilterName($id);
        $this->setFilterFields($id);
    }

    public function setFilterId($id) {
        $this->filter->id = $id;
    }

    public function setFilterName($id) {
        $post_status = get_post_status($id);

        if ($this->default_post_status === $post_status) {
            $this->filter->name = '';
        } else {
            $this->filter->name = get_the_title($id);
        }
    }

    public function setFilterFields($id) {
        $field_ids = get_post_meta($id, 'tww_fields', true);

        if (is_array($field_ids) && !empty($field_ids)) {
            global $wpdb;
            $field_ids_imploded = implode(',', array_fill(0, count($field_ids), '%s'));
            $fields_res = $wpdb->get_results($wpdb->prepare("SELECT `meta_value`,`post_id` FROM $wpdb->postmeta WHERE `post_id` IN (" . $field_ids_imploded . ") AND `meta_key` = 'tww_field_data'", $field_ids)); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
            $filter_fields = array();

            if (isset($_GET['twwf'], $_GET['twwf_id']) && is_array($_GET['twwf']) && (int) $_GET['twwf_id'] === (int) $id) { // phpcs:ignore
                $filter_fields = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::recursiveSanitizeTextField($_GET['twwf']); //phpcs:ignore
            } elseif (isset($_GET['apply_filter'])) { // phpcs:ignore
                $request_data = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::recursiveSanitizeTextField($_GET); //phpcs:ignore
                $filterAdapter = new ProductFilterAdapter();
                $filter_fields = $filterAdapter->getFilteredFields($request_data, $fields_res, $id);
            }

            foreach ($fields_res as $res) {
                //todo when we have multiple Price slider in one filter, query dies
                if (!empty($res->meta_value) && !empty($res->post_id)) {
                    $field_data = unserialize($res->meta_value); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
                    $field_id = $res->post_id;
                    $field_data['id'] = $field_id;
                    $field_data['filter_id'] = $id;
                    $field_data['filtered_data'] = array();
                    $field_data['slug'] = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::get_post_slug_by_id($field_id);

                    if (isset($filter_fields[$field_id])) {
                        $field_data['filtered_data'] = $filter_fields[$field_id];
                    }

                    if (!isset($field_data['fieldType'])) {
                        continue;
                    }
                    $field_type = $field_data['fieldType'];
                    $field_object_class = '\TenWebWooP\WoocommerceWidgets\Filter\Includes\Field\\' . $field_type . 'Field';

                    if (class_exists($field_object_class)) {
                        $field_object = new $field_object_class($field_data);
                        $field = $field_object->get();

                        if ($field_object instanceof BaseField) {
                            $this->filter->fields[$field->position] = $field;
                        }
                    }
                }
            }

            if (is_array($this->filter->fields)) {
                ksort($this->filter->fields);
            }
        }
    }

    public function getFilter() {
        return $this->filter;
    }

    public function renderFilter($settings = array()) {
        $template_loader = new TemplateLoader();

        if (isset($this->filter)) {
            $template_loader->render_template('filter.php', array(
                'filter' => $this->filter,
                'template_loader' => $template_loader,
                'template_dir' => dirname(__DIR__) . '/Field/Templates',
                'settings' => $settings
            ), dirname(__DIR__) . '/Filter/Templates');
        }
    }
}
