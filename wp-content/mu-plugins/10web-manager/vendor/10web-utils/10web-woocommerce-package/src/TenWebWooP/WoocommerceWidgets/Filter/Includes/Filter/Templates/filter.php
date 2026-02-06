<?php
$filter = $context['filter'];
$settings = $context['settings'];
$template_loader = $context['template_loader'];
$template_dir = $context['template_dir'];
$field_uid = 'twwf_filter_' . $filter->id;
$reset_url = '';
$currency = get_woocommerce_currency_symbol();
$tww_control_ajax_filtering = $settings['tww_control_ajax_filtering'];
$tww_control_ajax_beautify_url = $settings['tww_control_ajax_beautify_url'];
/*
 * this is for multisite
 * */
if (isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) {
    $site_url = '//' . sanitize_text_field($_SERVER['HTTP_HOST']) . sanitize_text_field($_SERVER['REQUEST_URI']);
    $reset_url = remove_query_arg(array('twwf', 'twwf_id', 'twwf_submit', 's1'), $site_url);
}

$tww_reset_button_text = __('Reset', 'tenweb-builder');
$tww_filter_button_text = __('Filter', 'tenweb-builder');
$twwf_nonce = wp_create_nonce('twwf_nonce');

if (!empty($settings['tww_filter_button_text'])) {
    $tww_filter_button_text = $settings['tww_filter_button_text'];
}

if (!empty($settings['tww_reset_button_text'])) {
    $tww_reset_button_text = $settings['tww_reset_button_text'];
}
$twwf_open_close_field_title = __('Collapse All', 'tenweb-builder');
$twwf_open_close_field_class = 'twwf_collapse_filter';
$field_state = 'collapsed';
$twwf_open_close_field_icon_class = 'icon-Minus_Icon';

if ($settings['field_state'] === 'collapsed') {
    $twwf_open_close_field_title = __('Expand All', 'tenweb-builder');
    $twwf_open_close_field_class = 'twwf_expand_filter';
    $twwf_open_close_field_icon_class = 'icon-Plus_Icon';
    $field_state = 'expanded';
}
$field_slugs_list = array();

foreach ($filter->fields as $field) {
    if (is_object($field) && isset($field->slug)) {
        $field_slugs_list[] = '_' . $field->slug;
    }
}
?>
<?php if (isset($filter->fields)) { ?>
<form data-url_beautify="<?php echo esc_attr($tww_control_ajax_beautify_url); ?>" data-ajax="<?php echo esc_attr($tww_control_ajax_filtering); ?>" name="tww_filter" data-id="<?php echo esc_attr($field_uid); ?>" id="<?php echo esc_attr($field_uid); ?>" class="tww_filter_form" method="get" action="#<?php echo esc_attr($field_uid); ?>">
    <?php if (!isset($_GET['twwf']) && is_array($_GET)) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended?>
        <?php foreach ($_GET as $key => $val) { // phpcs:ignore ?>
            <?php if (strpos($key, 'twwf') === false && !in_array($key, $field_slugs_list, true) && $key !== 'apply_filter' && $key !== 'product-page') {?>
                <input class="twwf_additional_params" type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>">
            <?php }?>
    <?php }?>
    <?php }?>

<div class="twwf_filter_form_fields">
    <span data-type="<?php echo esc_attr($field_state); ?>" data-title-expand="<?php echo __('Expand All', 'tenweb-builder');?>" data-title-collapsed="<?php echo __('Collapse All', 'tenweb-builder');?>" class="twwf_expand_collapse_filter <?php echo esc_attr($twwf_open_close_field_icon_class); ?> <?php echo esc_attr($twwf_open_close_field_class); ?>"><?php echo esc_html($twwf_open_close_field_title); ?></span>
    <?php
        $twwf_filtered_fields_style = 'display: none;';
        $twwf_checked_items = '';
        $twwf_has_parent = false;

        if ($tww_control_ajax_filtering === 'yes') {
            foreach ($filter->fields as $field) {
                if (is_object($field) && isset($field->options) && $field->type !== 'PriceSlider') {
                    foreach ($field->options as $option) {
                        if (isset($option->checked) && $option->checked === true) {
                            $twwf_filtered_fields_style = '';
                            $twwf_checked_items .= '<span data-type="input" data-name="' . $field->inputName . '" data-val="' . $option->item_id . '" class="twwf_filtered_field icon-Close_Icon">' . $option->name . '</span>';
                        }
                    }

                    if (isset($field->parent) && !$twwf_has_parent) {
                        $twwf_has_parent = true;
                        $category_or_shop_url = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::get_category_or_shop_url($field->parent->term_id);
                        $query_string = '';

                        if (isset($_SERVER['QUERY_STRING'])) {
                            $query_string = sanitize_text_field($_SERVER['QUERY_STRING']);
                        }
                        $main_url = $category_or_shop_url;

                        if (!empty($query_string)) {
                            $category_or_shop_url .= '?' . $query_string;
                        }
                        $twwf_filtered_fields_style = '';
                        $twwf_checked_items .= '<a data-main_url="' . $main_url . '" href="' . $category_or_shop_url . '" data-type="input" data-name="' . $field->inputName . '" data-val="' . $field->parent->item_id . '" class="twwf_filtered_field_parent icon-Close_Icon twwf_filtered_field twwf_filtered_field_not_remove">' . $field->parent->name . '</a>';
                    }
                } elseif (is_object($field) && $field->type === 'PriceSlider') {
                    $minPrice = $field->minPrice;
                    $maxPrice = $field->maxPrice;

                    $minPrice_s = $field->options['minPrice'];
                    $maxPrice_s = $field->options['maxPrice'];

                    if ($minPrice !== $minPrice_s || $maxPrice !== $maxPrice_s) {
                        $twwf_filtered_fields_style = '';
                        $twwf_checked_items .= '<span data-type="price" data-name="' . $field->inputName . '" data-val="" class="twwf_filtered_field icon-Close_Icon">' . $currency . $minPrice_s . '-' . $currency . $maxPrice_s . '</span>';
                    }
                }
            }
            echo '<div style="' . esc_attr($twwf_filtered_fields_style) . '" class="twwf_filtered_fields">' . wp_kses_post($twwf_checked_items) . '<span class="twwf_reset_filtered_fields icon-Reset_Icon">'.__("Reset All", 'tenweb-builder').'</span></div>';
        }
    ?>
<?php
    if (isset($filter->fields)) {
        $fields = $filter->fields;

        foreach ($fields as $field) {
            if ($field->state === 'on') {
                if ($field->template === 'Checkbox' && $field->variation === 'Category' && $field->value === 'all') {
                    $field->template = 'CheckboxHierarchy';
                }
                $template_loader->render_template($field->template . '.php', array(
                    'field' => $field,
                    'settings' => $settings
                ), $template_dir);
            }
        }
    }

?>
</div>
    <div class="twwf_filter_actions">
        <input type="hidden" name="twwf_id" value="<?php echo esc_attr($filter->id); ?>">
        <?php if ($tww_control_ajax_filtering !== 'yes') { ?>
            <input type="submit" class="twwf_submit" name="twwf_submit" value="<?php echo esc_attr($tww_filter_button_text); ?>" />
            <?php if (!empty($reset_url)) { ?>
                <a href="<?php echo esc_url($reset_url); ?>" class="twwf_reset_filter"><?php echo esc_html($tww_reset_button_text); ?></a>
            <?php }?>
        <?php }?>
    </div>
</form>
<script data-two-no-delay="">
    jQuery(document).ready(function () {
        twwf_calculate_form_height();
    });
    function twwf_calculate_form_height(){
        let tww_form_height = jQuery("#<?php echo esc_attr($field_uid); ?>").height();
        if(tww_form_height>749){
            jQuery("#<?php echo esc_attr($field_uid); ?>").addClass('twwf_scrollable');
        }else{
            jQuery("#<?php echo esc_attr($field_uid); ?>").removeClass('twwf_scrollable');
        }
    }


</script>
<?php }?>