<?php
$filter = array();
$fields = array();
$FilterId = '';

    if (isset($context['filter'])) {
        $filter = $context['filter'];

        if (isset($filter->fields)) {
            $fields = $filter->fields;
        }
    }
    $fields_list = $context['fields_list'];

    if (isset($context['post_status']) && $context['post_status'] === 'publish') {
        $FilterId = $filter->id;
    }
$tww_post_type_url = add_query_arg(array('post_type' => 'tww_filter'), admin_url() . 'edit.php');
$preview_images = '';
?>
<div class="filter_popup_section tww_add_new_filter">
    <div class="filter_popup_section_left">
        <div>
            <p class="tww_filter_container_title">Add new filter</p>
            <p class="tww_filter_container_desc">Add fields to your filter</p>
        </div>
        <div class="twwf_filter_name_block">
            <label class="tww_filter_admin_label" for="tww_filter_name">Filter name*</label>
            <input id="tww_filter_name"
                   data-el_id="tww_button_save"
                   name="filterName"
                   type="text"
                   class="tww_filter_name tww_filter_admin_form_field tww_required_input"
                   placeholder="Add filter name"
                   value="<?php echo isset($filter->name) ? esc_attr($filter->name) : ''; ?>">
            <input id="tww_filter_Id" name="filterId" type="hidden" value="<?php echo esc_attr($FilterId); ?>">
            <span class="twwf_field_error twwf_display_none">This filter name already exists. Please input a different name.</span>
        </div>
        <span class="tww_filter_add_field"><span>Add field</span></span>
        <div class="tww_fields_list">
            <?php foreach ($fields as $field) { ?>
                <?php
                $preview_key = $field->type . '_' . $field->id;
                $disable_preview_class = '';

                $checked = '';

                if ($field->state === 'on') {
                    $checked = 'checked';
                } else {
                    $disable_preview_class = 'twwf_hide_preview';
                }

                if (isset($fields_list[$field->type])) {
                    $field_data = $fields_list[$field->type];

                    if (isset($field->fieldView) && is_array($field_data['preview_image'])) {
                        $preview_image_url = $field_data['preview_image'][$field->fieldView];
                    } else {
                        $preview_image_url = $field_data['preview_image'];
                    }
                    $preview_images .= '<img class="tww_fields_list_preview_item ' . $disable_preview_class . '" data-preview="' . esc_attr($preview_key) . '" src="' . esc_url($preview_image_url) . '">';
                }

            ?>
            <div data-type='<?php echo esc_attr($field->type); ?>' data-preview='<?php echo esc_attr($preview_key); ?>' class="tww_fields_list_item" data-field_state='<?php echo esc_attr($field->state); ?>' data-field_id='<?php echo esc_attr($field->id); ?>' data-field_key='tww_<?php echo esc_attr($field->id); ?>'>
                <input <?php echo esc_attr($checked); ?> class="tww_on_off_checkbox tww_on_off_field" name="tww_on_off_field_<?php echo esc_attr($field->getId()); ?>" value="<?php echo esc_attr($field->getId()); ?>" id="tww_on_off_field_<?php echo esc_attr($field->getId()); ?>" type="checkbox">
                <label class="tww_on_off_checkbox_label tww_on_off_field_label" for="tww_on_off_field_<?php echo esc_attr($field->getId()); ?>">Toggle</label>
                <span class="tww_field_drag_drop"></span>
                <span class="tww_fields_list_item_title <?php echo (!empty($checked)) ? '' : 'tww_inactive_title'; ?>"><?php echo esc_html($field->getName()); ?></span>
                <div class="twwf_field_actions">
                    <span class="tww_edit_field"></span>
                    <span class="tww_delete_field"></span>
                </div>
            </div>
        <?php }?>
    </div>
    <div class="tww_button_container">
        <span data-href="<?php echo esc_url($tww_post_type_url); ?>" class="tww_button tww_button_cancel">Cancel</span>
        <span id="tww_button_save" data-href="<?php echo esc_url($tww_post_type_url); ?>" class="tww_button tww_button_save tww_button_active ">Save</span>
    </div>
    </div>
    <div class="filter_popup_section_right">
        <div class="right_section_head">
            <p class="preview_title">Preview
                <span class="twwf_field_info">
                    <span class="twwf_field_info_text">
                       The names and options will be updated on your website.
                    </span>
                </span>
            </p>
            <p class="preview_desc">This is a sample preview.</p>
        </div>
        <div class="preview_data preview_data_list">
            <?php echo $preview_images; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
</div>

