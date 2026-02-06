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
$currency = get_woocommerce_currency_symbol();
$field_uid = uniqid('twwf_price_slider');

?>

<?php if (!empty($options)) { ?>
    <div class="tww_filter_field_block">
        <div class="twwf_field_header">
            <span class="tww_filter_field_title"><?php _e($field->name, 'tenweb-builder'); ?></span>
            <span data-field_id="<?php echo esc_attr($field_uid); ?>" class="twwf_open_close_field <?php echo esc_attr($twwf_open_close_field_class); ?>"></span>
        </div>
        <div id="<?php echo esc_attr($field_uid); ?>" class="twwf_price_slider_container twwf_filter_field <?php echo esc_attr($collapsed_class); ?>">
            <div data-currency="<?php echo esc_attr($currency); ?>" data-min="<?php echo esc_attr($options['minPrice']); ?>" data-max="<?php echo esc_attr($options['maxPrice']); ?>" data-min_price="<?php echo esc_attr($field->minPrice); ?>" data-max_price="<?php echo esc_attr($field->maxPrice); ?>" data-step="<?php echo esc_attr($field->step); ?>" class="twwf_price_slider"></div>
            <span class="tww_price_item twwf_min_price"><?php echo esc_html($field->minPrice . $currency); ?></span>
            <span class="tww_price_item twwf_max_price"><?php echo esc_html($field->maxPrice . $currency); ?></span>
            <input class="twwf_min_price_input" data-variation="<?php echo esc_attr($field->variation); ?>" data-field_slug="<?php echo esc_attr($field->slug); ?>" data-price="<?php echo esc_attr($field->minPrice); ?>" data-min="<?php echo esc_attr($field->minPrice); ?>" class="twwf_min_price_input tww_filter_element" type="hidden" name="<?php echo esc_attr($field->inputName); ?>[min]" value="<?php echo esc_attr($options['minPrice']); ?>">
            <input class="twwf_max_price_input" data-variation="<?php echo esc_attr($field->variation); ?>" data-field_slug="<?php echo esc_attr($field->slug); ?>" data-price="<?php echo esc_attr($field->maxPrice); ?>>" data-max="<?php echo esc_attr($field->maxPrice); ?>" class="twwf_max_price_input tww_filter_element" type="hidden" name="<?php echo esc_attr($field->inputName); ?>[max]" value="<?php echo esc_attr($options['maxPrice']); ?>">
        </div>
        <script data-two-no-delay="">
            jQuery( function() {
                let slider = jQuery( '#<?php echo esc_attr($field_uid); ?> .twwf_price_slider' );
                let min_price= parseInt(slider.data('min_price'));
                let max_price= parseInt(slider.data('max_price'));
                let currency= slider.data('currency');
                let step = parseInt(slider.data('step'));
                let values_min_price = parseInt(slider.data('min'))
                let values_max_price = parseInt(slider.data('max'))

                let slider_container = slider.closest('.twwf_price_slider_container');
                let min_price_input = slider_container.find('.twwf_min_price_input');
                let max_price_input = slider_container.find('.twwf_max_price_input');
                slider.slider({
                    range: true,
                    min: min_price,
                    max: max_price,
                    step: step,
                    values: [values_min_price, values_max_price],
                    slide: function (e, ui) {
                        let min = ui.values[0];
                        let max = ui.values[1];
                        min_price_input.val(min);
                        max_price_input.val(max);
                        slider.find('.ui-slider-handle').find('.tww_handle_price').last().find('.price').html(max);
                        slider.find('.ui-slider-handle').find('.tww_handle_price').first().find('.price').html(min);
                        let tww_handle_price_min = slider.find('.ui-slider-handle').find('.tww_handle_price_min');
                        let tww_handle_price_max = slider.find('.ui-slider-handle').find('.tww_handle_price_max');
                        if(tww_handle_price_min.length>0 && tww_handle_price_max.length>0){
                            let tww_handle_price_min_offset_left = tww_handle_price_min.offset().left;
                            let tww_handle_price_max_offset_left = tww_handle_price_max.offset().left;

                            let tww_handle_price_min_width = tww_handle_price_min.outerWidth(true);
                            let tww_handle_price_max_width = tww_handle_price_max.outerWidth(true);
                            if(tww_handle_price_max_offset_left - tww_handle_price_min_width <= tww_handle_price_min_offset_left){
                                tww_handle_price_min.addClass('change_top');
                                tww_handle_price_max.addClass('change_top');
                            }else{
                                tww_handle_price_min.removeClass('change_top');
                                tww_handle_price_max.removeClass('change_top');
                            }
                        }
                    },
                    create: function( event, ui ) {
                        slider.find('.ui-slider-handle').last().html('<span class="tww_handle_price tww_handle_price_max">'+currency+'<span class="price">'+values_max_price+'</span></span>');
                        slider.find('.ui-slider-handle').first().html('<span class="tww_handle_price tww_handle_price_min">'+currency+'<span class="price">'+values_min_price+'</span></span>');
                        let newValues = [values_min_price, values_max_price]; // Adjust as needed
                        slider.slider("values", newValues);

                        // Retrieve the slide function and call it manually
                        let slideFunc = slider.slider("option", "slide");
                        if (slideFunc) {
                            slideFunc.call(slider, {}, { values: newValues });
                        }
                    }
                });
            } );
        </script>
    </div>
<?php }?>
