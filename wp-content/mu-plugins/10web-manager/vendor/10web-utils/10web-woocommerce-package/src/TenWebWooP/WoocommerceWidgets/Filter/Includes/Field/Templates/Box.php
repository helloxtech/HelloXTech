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
}
$field_uid = uniqid('twwf_box');
?>

<?php if (!empty($options)) { ?>
    <div class="tww_filter_field_block">
        <div class="twwf_field_header">
            <span class="tww_filter_field_title"><?php _e($field->name, 'tenweb-builder'); ?></span>
            <span data-field_id="<?php echo esc_attr($field_uid); ?>" class="twwf_open_close_field <?php echo esc_attr($twwf_open_close_field_class); ?>"></span>
        </div>
        <div id="<?php echo esc_attr($field_uid); ?>" class="tww_box_item_container twwf_filter_field <?php echo esc_attr($collapsed_class); ?>">
        <?php foreach ($options as $option) { ?>
            <?php
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

            if ($field->variation === 'Category' && isset($option->parent)) {
                $option_data_attr = 'data-cat_id="' . esc_attr($option->item_id) . '" data-parent_cat_id="' . esc_attr($option->parent) . '" ';
                $filed_option_class = 'twwf_root_cat';

                if ($option->parent > 0) {
                    $filed_option_class = 'twwf_child_cat';
                }
            }
            ?>

            <div class="tww_box_item twwf_field_option_container">
                <input <?php echo $option_data_attr; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?> data-variation="<?php echo esc_attr($field->variation); ?>" data-field_slug="<?php echo esc_attr($field->slug); ?>" data-option_slug="<?php echo esc_attr($option->slug); ?>" data-title="<?php echo esc_attr($option->name); ?>" <?php echo esc_attr($checked); ?> value="<?php echo esc_attr($option->item_id); ?>" name="<?php echo esc_attr($field->inputName); ?>" type="checkbox" class="tww_box_checkbox tww_filter_element <?php echo esc_attr($filed_option_class); ?>" id="<?php echo esc_attr($u_id); ?>" />
                <label class="tww_box_checkbox_label" for="<?php echo esc_attr($u_id); ?>"><?php echo esc_html($option->name); ?></label>
            </div>
        <?php }?>
        </div>



    </div>
<?php }?>
