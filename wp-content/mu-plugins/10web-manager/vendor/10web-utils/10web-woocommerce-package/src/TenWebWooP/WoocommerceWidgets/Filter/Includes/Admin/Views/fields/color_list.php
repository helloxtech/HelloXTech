<?php
$sources = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getSources();
$attributes_list = wc_get_attribute_taxonomies();
$fields_data = $context['field_data'];
$selected = 'color_with_text';
?>


<div class="filter_popup_section filter_popup_section_filed tww_add_field_settings tww_ColorList tww_disable">
    <div class="filter_popup_section_left">
        <div class="tww_filter_section_head">
            <span data-back="tww_add_filter_fields" class="tww_back_button"></span>
            <div>
                <p class="tww_filter_container_title">Color list</p>
                <p class="tww_filter_container_desc">Choose and create elements for your filter</p>
            </div>
        </div>
        <div class="tww_filter_field_settings">
            <div class="tww_input_block">
                <label class="tww_filter_admin_label" for="tww_filter_field_name">Title*</label>
                <input type="text" data-el_id="tww_button_apply_color_list" name="fieldName" class="tww_filter_admin_form_field tww_required_input tww_filter_field_name" id="tww_filter_field_name">
            </div>
            <div class="tww_input_block">
                <label class="tww_filter_admin_label tww_filter_field_value_label" for="tww_filter_field_value">Choose color options*</label>
                <select data-el_id="tww_button_apply_color_list" name="fieldValue" class="tww_filter_admin_form_field tww_color_attr tww_filter_field_variation_attribute tww_required_input" id="tww_filter_field_value_colorList">
                    <?php foreach ($attributes_list as $attribute) { ?>
                        <option value="<?php echo esc_attr($attribute->attribute_name); ?>"><?php echo esc_html($attribute->attribute_label); ?></option>
                    <?php }?>
                </select>
                <input type="hidden" name="fieldVariation" value="Attribute">
            </div>
            <input name="fieldView" type="hidden" value="color_with_text" >
        </div>


        <div class="tww_field_colors_list">

        </div>
        <div class="tww_button_container">
        <span id="tww_button_apply_color_list" class="tww_button tww_button_apply tww_button_active">Apply</span>
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
        <div class="preview_data">
            <?php if (is_array($fields_data['preview_image'])) { ?>
                <?php foreach ($fields_data['preview_image'] as $key => $image_url) { ?>
                    <img data-preview="<?php echo esc_attr($key); ?>" style="<?php echo ($selected === $key) ? '' : 'display:none;'; ?>" src="<?php echo esc_url($image_url); ?>">
                <?php }?>
            <?php } else { ?>
                <img src="<?php echo esc_url($fields_data['preview_image']); ?>">
            <?php }?>
        </div>
    </div>
</div>