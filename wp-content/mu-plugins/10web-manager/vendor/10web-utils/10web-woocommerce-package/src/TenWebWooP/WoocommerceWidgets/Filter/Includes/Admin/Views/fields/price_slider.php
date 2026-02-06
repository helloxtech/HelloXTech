<?php
$sources = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getSources();
$attributes_list = wc_get_attribute_taxonomies();
$fields_data = $context['field_data'];
?>


<div class="filter_popup_section filter_popup_section_filed tww_add_field_settings tww_PriceSlider tww_disable">
    <div class="filter_popup_section_left">
        <div class="tww_filter_section_head">
            <span data-back="tww_add_filter_fields" class="tww_back_button"></span>
            <div>
                <p class="tww_filter_container_title">Price slider</p>
                <p class="tww_filter_container_desc">Choose and create elements for your filter</p>
            </div>
        </div>
        <div class="tww_filter_field_settings">
            <div class="tww_input_block_section">
                <div class="tww_input_block">
                    <label class="tww_filter_admin_label" for="tww_filter_field_name">Title*</label>
                    <input type="text" data-el_id="tww_button_apply_price_slider" name="fieldName" class="tww_filter_field_name tww_filter_admin_form_field tww_required_input tww_filter_field_name" id="tww_filter_field_name">
                </div>
                <div class="tww_input_block">
                    <label class="tww_filter_admin_label" for="tww_filter_field_step">Step value</label>
                    <input type="number" name="step" class="tww_filter_field_step tww_filter_admin_form_field" id="tww_filter_field_step">
                    <p>Step amount in the slider</p>
                </div>
            </div>
            <div class="tww_input_block_section">
                <div class="tww_input_block">
                    <label class="tww_filter_admin_label" for="tww_filter_field_min_price">Minimum price</label>
                    <input type="number" name="minPrice" class="tww_filter_admin_form_field" id="tww_filter_field_min_price">
                    <p>Choose a minimum price value if you want to have a specific range for your price slider. If not, it will be set automatically based on your products’ prices.</p>
                </div>
                <div class="tww_input_block">
                    <label class="tww_filter_admin_label" for="tww_filter_field_max_price">Maximum price</label>
                    <input type="number" name="maxPrice" class="tww_filter_admin_form_field" id="tww_filter_field_max_price">
                    <p>Choose a maximum price value if you want to have a specific range for your price slider. If not, it will be set automatically based on your products’ prices.</p>
                </div>
            </div>
        </div>
        <div class="tww_button_container">
        <span id="tww_button_apply_price_slider" class="tww_button tww_button_apply tww_button_active">Apply</span>
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
            <img src="<?php echo esc_url($fields_data['preview_image']); ?>">
        </div>
    </div>
</div>