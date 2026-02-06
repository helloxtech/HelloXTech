<?php
$field_view = $context['field_view'];

?>
<div class="tww_variation_view_block tww_variation_view_StockStatus tww_disable">
    <label class="tww_filter_admin_label" for="tww_filter_in_stock">In stock</label>
    <div class="tww_stock_status_block">
        <input name="inStock" value="In stock" class="tww_filter_admin_form_field tww_stock_status_field" id="tww_filter_in_stock">

        <input checked name="inStockCheck" class="tww_on_off_checkbox" id="tww_status_in_stock_<?php echo esc_attr($field_view); ?>" value="1" type="checkbox">
        <label class="tww_on_off_checkbox_label" for="tww_status_in_stock_<?php echo esc_attr($field_view); ?>">Toggle</label>
    </div>


    <label class="tww_filter_admin_label" for="tww_filter_out_of_stock">Out of stock</label>
    <div class="tww_stock_status_block">
        <input name="outOfStock" value="Out of stock" class="tww_filter_admin_form_field tww_stock_status_field" id="tww_filter_out_of_stock">

        <input checked name="outOfStockCheck" class="tww_on_off_checkbox" id="tww_status_out_of_stock_<?php echo esc_attr($field_view); ?>" value="1" type="checkbox">
        <label class="tww_on_off_checkbox_label" for="tww_status_out_of_stock_<?php echo esc_attr($field_view); ?>">Toggle</label>
    </div>


    <label class="tww_filter_admin_label" for="tww_filter_on_backorder">On backorder</label>
    <div class="tww_stock_status_block">
        <input name="onBackorder" value="On backorder" class="tww_filter_admin_form_field tww_stock_status_field" id="tww_filter_on_backorder">

        <input checked name="onBackorderCheck" class="tww_on_off_checkbox" id="tww_status_on_backorder_<?php echo esc_attr($field_view); ?>" value="1" type="checkbox">
        <label class="tww_on_off_checkbox_label" for="tww_status_on_backorder_<?php echo esc_attr($field_view); ?>">Toggle</label>
    </div>
</div>