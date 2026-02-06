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
$uid = uniqid('twwf_dropdown_');
$field_uid = uniqid('twwf_dropdown_list');

?>

<?php if (!empty($options)) { ?>
    <div class="tww_filter_field_block">
        <div class="twwf_field_header">
            <span class="tww_filter_field_title"><?php _e($field->name, 'tenweb-builder'); ?></span>
            <span data-field_id="<?php echo esc_attr($field_uid); ?>" class="twwf_open_close_field <?php echo esc_attr($twwf_open_close_field_class); ?>"></span>
        </div>
        <div id="<?php echo esc_attr($field_uid); ?>" class="twwf_dropdown_list twwf_filter_field <?php echo esc_attr($collapsed_class); ?>">
            <select data-variation="<?php echo esc_attr($field->variation); ?>" class="twwf_dropdown tww_filter_element" id="<?php echo esc_attr($uid); ?>" name="<?php echo esc_attr($field->inputName); ?>">
                <option value=""><?php echo __('All','tenweb-builder')?></option>
                <?php foreach ($options as $option) { ?>
                <?php
                if (isset($option->fieldState) && $option->fieldState === 'off') {
                    continue;
                }
                $selected = '';
                $u_id = $option->name . '_' . $option->item_id . '_' . $field->id;

                if (isset($option->checked) && $option->checked) {
                    $selected = 'selected';
                }
                ?>
                <option data-title="<?php echo esc_attr($option->name); ?>" data-field_slug="<?php echo esc_attr($field->slug); ?>" data-option_slug="<?php echo esc_attr($option->slug); ?>" value="<?php echo esc_attr($option->item_id); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($option->name); ?></option>
            <?php }?>
            </select>
            <script data-two-no-delay="">
                jQuery(document).ready(function() {
                    jQuery('#<?php echo esc_attr($uid); ?>').select2({
                        minimumResultsForSearch: -1,
                    });
                });
            </script>
        </div>
    </div>
<?php }?>
