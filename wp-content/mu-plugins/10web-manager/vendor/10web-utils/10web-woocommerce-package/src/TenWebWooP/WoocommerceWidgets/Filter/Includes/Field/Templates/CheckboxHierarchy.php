<?php
$field = $context['field'];
$settings = $context['settings'];
$collapsed_class = '';
$twwf_open_close_field_class = '';

if ($settings['field_state'] === 'collapsed') {
    $collapsed_class = 'twwf_hide_field';
    $twwf_open_close_field_class = 'twwf_close';
}

if (isset($field->options)) {
    $options = $field->options;
    $options = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::organize_terms_hierarchy($options);
}
$field_uid = uniqid('twwf_checkbox');

if ( ! function_exists('twwf_print_hierarchy')) {
    function twwf_print_hierarchy($field, $options, $depth = 0) {
        foreach ($options as $option) {
            if (isset($option->fieldState) && $option->fieldState === 'off') {
                continue;
            }
            $checked = '';
            $u_id = $option->name . '_' . $option->item_id . '_' . $field->id;

            if (isset($option->checked) && $option->checked) {
                $checked = 'checked';
            }
            $option_data_attr = '';
            $filed_option_class = '';
            $filed_label_option_class = '';

            if ($field->variation === 'Category' && isset($option->parent)) {
                $option_data_attr = 'data-cat_id="' . esc_attr($option->item_id) . '" data-parent_cat_id="' . esc_attr($option->parent) . '" ';
                $filed_option_class = 'twwf_root_cat';

                if ($option->parent > 0) {
                    $filed_option_class = 'twwf_child_cat';
                }

                if (isset($field->value) && $field->value === 'all' && $option->parent > 0) {
                    $filed_label_option_class = 'twwf_child_cat_label';
                }
            }

            echo '<label class="twwf_checkbox_item container twwf_field_option_container ' . esc_attr($filed_label_option_class) . '" for="' . esc_attr($u_id) . '">
                        <input ' . esc_attr($option_data_attr) . ' data-variation="' . esc_attr($field->variation) . '" data-field_slug="' . esc_attr($field->slug) . '" data-option_slug="' . esc_attr($option->slug) . '" data-title="' . esc_attr($option->name) . '" ' . esc_attr($checked) . ' name="' . esc_attr($field->inputName) . '" id="' . esc_attr($u_id) . '" type="checkbox" value="' . esc_attr($option->item_id) . '" class="tww_filter_element ' . esc_attr($filed_option_class) . '">
                        <span class="checkmark"></span>
                        <span class="checkbox_field_option_title">' . esc_html($option->name) . '</span>
                    </label>';

            if (!empty($option->children)) {
                twwf_print_hierarchy($field, $option->children, $depth + 1);
            }
        }
    }
}

?>

<?php if (!empty($options)) { ?>
    <div class="tww_filter_field_block" data-field_variation="<?php echo esc_attr($field->variation); ?>">
        <div class="twwf_field_header">
            <span class="tww_filter_field_title"><?php _e($field->name, 'tenweb-builder'); ?></span>
            <span data-field_id="<?php echo esc_attr($field_uid); ?>" class="twwf_open_close_field <?php echo esc_attr($twwf_open_close_field_class); ?>"></span>
        </div>
        <div id="<?php echo esc_attr($field_uid); ?>" class="twwf_checkbox_list twwf_filter_field <?php echo esc_attr($collapsed_class); ?>">
            <?php twwf_print_hierarchy($field, $options, 0); ?>
        </div>
    </div>
<?php }?>