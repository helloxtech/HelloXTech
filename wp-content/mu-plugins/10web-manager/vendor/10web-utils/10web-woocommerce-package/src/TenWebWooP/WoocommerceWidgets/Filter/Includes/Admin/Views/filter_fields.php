<?php
    $fieldsList = $context['fields_list'];
    $getFieldsList = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getFieldsList();
?>
<div class="filter_popup_section tww_add_filter_fields tww_disable">
    <div class="tww_filter_section_head">
        <span data-back="tww_add_new_filter" class="tww_back_button"></span>
        <div>
            <p class="tww_filter_container_title">Add filter fields</p>
            <p class="tww_filter_container_desc">Choose the field type of your filter</p>
        </div>
    </div>
    <div class="tww_filter_fields_list">
        <?php foreach ($getFieldsList as $field) { ?>
            <div class="tww_filter_field_box <?php echo (!empty($field['checked'])) ? 'tww_filter_field_box_selected' : ''; ?>" data-type='<?php echo esc_attr($field['id']); ?>'>
                <input <?php echo esc_attr($field['checked']); ?> class="tww_filter_field" type="radio" name="tww_filter_field" value="<?php echo esc_attr($field['id']); ?>">
                <img class="tww_field_image" src="<?php echo esc_url($field['image']); ?>">
                <span class="tww_filter_field_title"><?php _e($field['title'], 'tenweb-builder'); ?></span>
            </div>
        <?php }?>
    </div>
    <div class="tww_button_container">
        <span class="tww_button tww_button_next">Next</span>
    </div>
</div>