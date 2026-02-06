<?php
$sources = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getSources();
$attributes_list = wc_get_attribute_taxonomies();

$template_loader = $context['template_loader'];
$context['field_view'] = 'checkbox';
$fields_data = $context['field_data'];
?>


<div class="filter_popup_section filter_popup_section_filed tww_add_field_settings tww_Checkbox tww_disable">
    <div class="filter_popup_section_left">
        <div class="tww_filter_section_head">
            <span data-back="tww_add_filter_fields" class="tww_back_button"></span>
            <div>
                <p class="tww_filter_container_title">Checkbox list</p>
                <p class="tww_filter_container_desc">Choose and create elements for your filter</p>
            </div>
        </div>
        <div class="tww_filter_field_settings">
            <div class="tww_input_block">
                <label class="tww_filter_admin_label" for="tww_filter_field_name">Title*</label>
                <input type="text" data-el_id="tww_button_apply_checkbox" name="fieldName" class="tww_filter_admin_form_field tww_required_input tww_filter_field_name" id="tww_filter_field_name">
            </div>
            <div class="tww_input_block">
                <label class="tww_filter_admin_label" for="tww_filter_field_variations_checkbox">Source of options*</label>
                <select name="fieldVariation" class="tww_filter_admin_form_field tww_filter_field_variations" id="tww_filter_field_variations_checkbox">
                    <?php foreach ($sources as $source) { ?>
                        <option value="<?php echo esc_attr($source['id']); ?>"><?php echo esc_html($source['title']); ?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <div class="tww_variation_view">

        <?php
        $template_loader->render_template('attribute.php', $context, dirname(__DIR__) . '/fields/variations');
        $template_loader->render_template('category.php', $context, dirname(__DIR__) . '/fields/variations');
        $template_loader->render_template('tag.php', $context, dirname(__DIR__) . '/fields/variations');
        $template_loader->render_template('stock_status.php', $context, dirname(__DIR__) . '/fields/variations');
        $template_loader->render_template('sale.php', $context, dirname(__DIR__) . '/fields/variations');
        ?>




        </div>

        <div class="tww_button_container">
            <span id="tww_button_apply_checkbox" class="tww_button tww_button_apply tww_button_active">Apply</span>
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