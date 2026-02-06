<?php
$field = $context['field'];
$settings = $context['settings'];
$collapsed_class = '';
$twwf_open_close_field_class = '';

if ($settings['field_state'] === 'collapsed') {
    $collapsed_class = 'twwf_hide_field';
    $twwf_open_close_field_class = 'twwf_close';
}
$view_type = 'list';

if (isset($settings['filter_color_view_type'])) {
    $view_type = $settings['filter_color_view_type'];
}

if (isset($field->options)) {
    $options = $field->options;
}
$color_style = '';

$content_class = 'twwf_horizontal';

if ($view_type === 'list') {
    $content_class = 'twwf_vertical';
}
$field_uid = uniqid('twwf_color_list');
?>

<?php if (!empty($options)) { ?>
    <div class="tww_filter_field_block">
        <div class="twwf_field_header">
            <span class="tww_filter_field_title"><?php _e($field->name, 'tenweb-builder'); ?></span>
            <span data-field_id="<?php echo esc_attr($field_uid); ?>" class="twwf_open_close_field <?php echo esc_attr($twwf_open_close_field_class); ?>"></span>
        </div>
        <div id="<?php echo esc_attr($field_uid); ?>" class="twwf_color_list <?php echo  esc_attr($content_class); ?> twwf_filter_field <?php echo esc_attr($collapsed_class); ?>">
            <?php foreach ($options as $option) { ?>
                <?php
                $name = '<span class="tww_color_name">' . esc_html($option->name) . '</span>';
                $checked = '';
                $u_id = uniqid($option->slug);
                $label_class = 'twwf_color_checkbox_label_' . esc_attr($option->item_id) . '_' . $field->id . ' tww_color_input_flag';
                $label_class_css = ' .twwf_color_checkbox_label_' . esc_attr($option->item_id) . '_' . $field->id . ' .tww_color_input_flag';
                $label_border_class_css = ' .tww_filter_form .twwf_color_checkbox:checked +  .twwf_color_checkbox_label_' . esc_attr($option->item_id) . '_' . $field->id;

                if ($option->checked) {
                    $checked = 'checked';
                }
                $block_class_name = 'twwf_filter_color_block_' . $view_type;
                ?>
                <div class="twwf_filter_color_block <?php echo esc_attr($block_class_name); ?>">
                    <input data-variation="<?php echo esc_attr($field->variation); ?>" data-field_slug="<?php echo esc_attr($field->slug); ?>" data-option_slug="<?php echo esc_attr($option->slug); ?>" data-title="<?php echo esc_attr($option->name); ?>" style="display: none;" class="twwf_color_checkbox tww_filter_element" <?php echo esc_attr($checked); ?> name="<?php echo esc_attr($field->inputName); ?>" id="<?php echo esc_attr($u_id); ?>" type="checkbox" value="<?php echo esc_attr($option->item_id); ?>">
                    <label class="twwf_color_checkbox_label <?php echo esc_attr($label_class); ?>" for="<?php echo esc_attr($u_id); ?>">
                        <span class="tww_color_input_flag"></span>
                    </label>
                    <?php
                       echo $name; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                </div>
                <?php
                    $color_style .= $label_class_css . '{background-color:' . $option->color . ';}';
                    $color_style .= $label_border_class_css . '{border: 1px solid ' . $option->color . ';}';
                ?>
            <?php }?>
            <style>
                <?php echo esc_attr($color_style); ?>
            </style>
        </div>
    </div>

<?php }?>