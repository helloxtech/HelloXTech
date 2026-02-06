<?php
$attributes_list = wc_get_attribute_taxonomies();
$uid = uniqid('tww_filter_field_value');

?>
<div class="tww_variation_view_block tww_variation_view_Attribute">
    <label class="tww_filter_admin_label tww_filter_field_value_label" for="<?php echo esc_attr($uid); ?>">Option values*</label>
    <select name="fieldValue" class="tww_filter_admin_form_field tww_filter_field_variation_attribute" id="<?php echo esc_attr($uid); ?>">
        <?php foreach ($attributes_list as $attribute) { ?>
            <option value="<?php echo esc_attr($attribute->attribute_name); ?>"><?php echo esc_html($attribute->attribute_label); ?></option>
        <?php }?>
    </select>
</div>
<script>
    jQuery('#<?php echo esc_attr($uid); ?>').select2({
        minimumResultsForSearch: -1
    });
</script>