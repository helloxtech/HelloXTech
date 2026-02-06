<?php
$uid = uniqid('tww_filter_field_value');
?>
<div class="tww_variation_view_block tww_variation_view_Category tww_disable">
    <label class="tww_filter_admin_label tww_filter_field_value_label" for="<?php echo esc_attr($uid); ?>">Choose field value*</label>
    <select name="fieldValue" class="tww_filter_admin_form_field tww_filter_field_variation_attribute tww_filter_admin_form_field_disabled" id="<?php echo esc_attr($uid); ?>">
<!--        <option disabled selected>All categories</option>-->
        <option value="all"><?php echo __('All','tenweb-builder')?></option>
        <option value="categories">Categories</option>
        <option value="subcategories">Subcategories</option>
    </select>
</div>
<script>
    jQuery('#<?php echo esc_attr($uid); ?>').select2({
        minimumResultsForSearch: -1
    });
</script>