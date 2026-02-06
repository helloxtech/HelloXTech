jQuery(document).ready(function () {
    let twwf_current_field = ''
    let twwf_current_variation = ''
    let twwf_current_variation_attribute = ''
    let twwf_field_action = 'insert';
    let tww_edit_color_list = {};
    let edit_field_key = '';
    let tww_filter_field_val = '';
    let tww_field_id = '';
    let edit_obj_id = '';

    if(tww_admin_vars.delete_button_class.length>0){
        jQuery('.'+tww_admin_vars.delete_button_class).click(function (e){
            analyticsDataPush ( 'Filter Deleted', 'Filter Deleted',  tww_admin_vars.analytics_data);
        });
    }

    if(jQuery('.tww_filter_container').length>0){
        let tww_filter_field = 'Checkbox';
        let tww_href = null;
        let twwFilterData = {
            'fieldsData': {}
        };

        jQuery(".tww_required_input").on("change paste keyup", function () {
            twwf_current_variation_attribute = jQuery('.filter_popup_section.tww_'+twwf_current_field).find('.tww_filter_field_variation_attribute').val();
            let el_id = jQuery(this).data('el_id');
            if (jQuery(this).val() === '') {
                jQuery('#' + el_id).addClass('twwf_disabled_button');
            } else {
                if(twwf_current_variation === 'Attribute' && (twwf_current_variation_attribute === "" || twwf_current_variation_attribute === null)){
                    jQuery('#' + el_id).addClass('twwf_disabled_button');
                    return;
                }else if(el_id === 'tww_button_apply_color_list' && twwf_current_variation_attribute === null){
                    jQuery('#' + el_id).addClass('twwf_disabled_button');
                    return;
                }
                jQuery('#' + el_id).removeClass('twwf_disabled_button');
            }
        });

        jQuery('.tww_required_input').each(function () {
            if (jQuery(this).val() === '' || jQuery(this).val() === null) {
                let el_id = jQuery(this).data('el_id');
                jQuery('#' + el_id).addClass('twwf_disabled_button');
            }
        });


        jQuery(".tww_fields_list").sortable({
            handle: ".tww_field_drag_drop",
            update: function( event, ui ) {
                let moved_element =  jQuery(ui.item);
                let field_preview = moved_element.data('preview');
                let preview_image = jQuery('.tww_fields_list_preview_item[data-preview="'+field_preview+'"]');


                let next_field = moved_element.next();
                let next_field_index = next_field.index();
                next_field_index = next_field_index-1;
                preview_image.remove();
                if(next_field.length>0 && preview_image.length>0){
                    jQuery('.tww_fields_list_preview_item:eq('+next_field_index+')').before(preview_image);
                }else{
                    jQuery('.preview_data_list').append(preview_image);
                }
            }
        });

        jQuery("body").on("click", ".tww_filter_add_field", function () {
            jQuery(".tww_filter_field_box").removeClass("tww_filter_field_box_selected");
            jQuery('.tww_button_next').removeClass('tww_button_active');
            jQuery('.tww_filter_container').addClass('tww_filter_container_min');
            let price_slider_field = jQuery('.tww_fields_list_item[data-type="PriceSlider"]');
            if(price_slider_field.length>0){
                jQuery('.tww_filter_field_box[data-type="PriceSlider"]').addClass('twwf_disable_actions');
            }else{
                jQuery('.tww_filter_field_box[data-type="PriceSlider"]').removeClass('twwf_disable_actions');
            }
            jQuery('.tww_add_filter_fields').removeClass('tww_disable');
            jQuery('.tww_add_new_filter').addClass('tww_disable');
        });
        jQuery("body").on("click", ".tww_button_next.tww_button_active", function () {
            tww_reset_field();
            twwf_field_action = 'insert';
            twwf_current_variation = jQuery('.filter_popup_section.tww_'+twwf_current_field).find('.tww_filter_field_variations').val();
            twwf_current_variation_attribute = jQuery('.filter_popup_section.tww_'+twwf_current_field).find('.tww_filter_field_variation_attribute').val();
            jQuery('.tww_filter_container').removeClass('tww_filter_container_min');
            jQuery('.filter_popup_section').addClass('tww_disable');
            jQuery('.tww_' + tww_filter_field).removeClass('tww_disable');
        });
        jQuery("body").on("click", ".tww_edit_field", function () {
            twwf_field_action = 'update';
            tww_filter_field = jQuery(this).closest('.tww_fields_list_item').data('type');
            tww_field_id = jQuery(this).closest('.tww_fields_list_item').data('field_id');
            let field_popup = jQuery('.tww_' + tww_filter_field);
            let field_data = get_field_data(tww_field_id, field_popup);
            let field = field_data['field'];
            edit_obj_id = field_data['obj_id'];
            edit_field_key = jQuery(this).closest('.tww_fields_list_item').data('field_key');
            edit_field(field, field_popup);
            field_popup.find('.tww_required_input').trigger("change");
            jQuery('.filter_popup_section').addClass('tww_disable');
            field_popup.removeClass('tww_disable');
        });

        function get_field_data(id){
            let field = '';
            let obj_id = '';
            if(typeof tww_admin_vars.fields === "object"){

                jQuery.each( tww_admin_vars.fields, function( key, value ) {
                    if(parseInt(value.id) === parseInt(id)){
                        obj_id = key;
                        field = value;
                        return false;
                    }
                });
            }
            return {
                'obj_id':obj_id,
                'field':field
            };
        }
        function edit_field(field_data, field_popup){
            let fields = {
                    'name':{
                        'type':'input',
                        'selector':'.tww_filter_field_name'
                    },
                    'variation':{
                        'type':'select',
                        'selector':'.tww_filter_field_variations'
                    },
                    'value':{
                        'type':'select',
                        'selector':'.tww_filter_field_variation_attribute'
                    },
                    'maxPrice':{
                        'type':'input',
                        'selector':'#tww_filter_field_max_price'
                    },
                    'minPrice':{
                        'type':'input',
                        'selector':'#tww_filter_field_min_price'
                    },
                    'step':{
                        'type':'input',
                        'selector':'#tww_filter_field_step'
                    },
                    'fieldView':{
                        'type':'select',
                        'selector':'#tww_filter_field_view'
                    },
                    'options':{
                        'inStock':{
                            'type':'input',
                            'selector':'#tww_filter_in_stock'
                        },
                        'outOfStock':{
                            'type':'input',
                            'selector':'#tww_filter_out_of_stock'
                        },
                        'onBackorder':{
                            'type':'input',
                            'selector':'#tww_filter_on_backorder'
                        },
                        'tww_product_in_sale':{
                            'type':'input',
                            'selector':'#tww_product_in_sale'
                        },
                        'tww_all_products':{
                            'type':'input',
                            'selector':'#tww_all_products'
                        },
                    },
                };

            if(field_data['variation'] === 'StockStatus' && typeof field_data['options'] === 'object'){
                jQuery.each( field_data['options'], function( key, value ) {
                    if(typeof fields['options'][value['item_id']] != "undefined"){
                        let item = fields['options'][value['item_id']];
                        let element = field_popup.find(item['selector']);
                        element.val(value['name']);

                        if(value['fieldState'] === 'off'){
                            let tww_on_off_checkbox = element.closest('div').find('.tww_on_off_checkbox');
                            tww_on_off_checkbox.prop( "checked", false );
                        }
                    }
                });
            }
            if(field_data['type'] === 'ColorList' && typeof field_data['options'] === 'object'){
                jQuery.each( field_data['options'], function( key, value ) {
                    if(typeof value['slug'] != "undefined" && value['color'] != "undefined"){
                        tww_edit_color_list[value["slug"]] = value['color'];
                    }
                });
            }
            jQuery.each( field_data, function( key, value ) {
                if(typeof fields[key] != "undefined"){
                    let field_option = fields[key];

                    if(field_option['type'] === 'input'){
                        field_popup.find(field_option['selector']).val(value);
                    }else if(field_option['type'] === 'select'){
                        let element = field_popup.find(field_option['selector']);
                        if(key !== 'value'){
                            element.val(value);
                        }else if(value !== null && typeof value!== "undefined" && key === 'value' && value.length>0){
                            element.val(value);
                        }
                        element.trigger('change');
                    }
                }
            });
        }

        function tww_reset_field(){
            jQuery('.tww_filter_field_name').val('');
            jQuery('#tww_filter_in_stock').val('In stock');
            jQuery('#tww_filter_out_of_stock').val('Out of stock');
            jQuery('#tww_filter_on_backorder').val('On backorder');
            jQuery('#tww_filter_field_variations_checkbox').val("Attribute").trigger('change');
            jQuery('#tww_filter_field_variations_dropdown').val("Attribute").trigger('change');
            jQuery('#tww_filter_field_variations_pillbox').val("Attribute").trigger('change');
            jQuery('#tww_filter_field_radioList').val("Attribute").trigger('change');
            jQuery('#tww_filter_field_variations_box').val("Attribute").trigger('change');
            jQuery('#tww_filter_field_value_colorList').val("Attribute").trigger('change');
            jQuery('.tww_required_input').each(function () {
                if (jQuery(this).val() === '') {
                    let el_id = jQuery(this).data('el_id');
                    jQuery('#' + el_id).addClass('twwf_disabled_button');
                }
            });
        }



        jQuery('.tww_fields_list_item').each(function () {
            let field_id = jQuery(this).data('field_id');
            let field_state = jQuery(this).data('field_state');
            let key = jQuery(this).data('field_key');
            twwFilterData["fieldsData"][key] = {
                'fieldId': field_id,
                'fieldState': field_state,
                'position': 1
            }
        });


        jQuery(".tww_button_save").click(function () {
            let filter_name = jQuery("#tww_filter_name").val();
            let tww_filter_Id = jQuery("#tww_filter_Id").val();
            twwFilterData['filterName'] = filter_name;
            twwFilterData['filterId'] = tww_filter_Id;
            tww_sort_fields();
            tww_save_filter(twwFilterData);
        });

        function tww_sort_fields() {
            let position = 0;
            jQuery('.tww_fields_list_item').each(function () {
                position++;
                let key = jQuery(this).data('field_key')
                twwFilterData["fieldsData"][key].position = position;
            });
        }

        jQuery(".tww_button_cancel, .tww_close_popup").click(function () {
            jQuery('.tww_leave_block').removeClass('twwf_display_none');
            jQuery('.twwf_overlay').addClass('tww_full_overlay');
            tww_href = jQuery(this).data('href');
        });
        jQuery('.tww_leave').click(function (){
            if(typeof tww_href != null){
                tww_render_view(tww_href);
            }
        });
        jQuery('.tww_leave_cancel').click(function (){
            jQuery('.tww_leave_block').addClass('twwf_display_none');
            jQuery('.twwf_overlay').removeClass('tww_full_overlay');
        });



        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
            tww_href = document.referrer;
            jQuery('.tww_leave_block').removeClass('twwf_display_none');
            jQuery('.twwf_overlay').addClass('tww_full_overlay');
        };

        function tww_render_view(href = null) {
            if (tww_admin_vars.view_type === 'elementor') {
                parent.twwf_render_view();
                jQuery('.tww_control_filter select', parent.document.body).trigger("change");
                jQuery('.tww_elementor_popup_iframe', parent.document.body).remove();
            } else if (href) {
                window.location.href = href
            }

        }


        jQuery('.tww_filter_field_box').click(function () {
            jQuery(this).find('input').prop("checked", true);
            tww_filter_field = jQuery(this).find('input').val();
            twwf_current_field = tww_filter_field;
            jQuery('.tww_filter_field_box').removeClass('tww_filter_field_box_selected');
            jQuery('.tww_button_next').addClass('tww_button_active');
            jQuery(this).addClass('tww_filter_field_box_selected');
        });

        jQuery('.tww_button_apply').click(function () {
            jQuery('.tww_add_filter_fields').addClass('tww_disable');
            jQuery('.tww_add_field_settings').addClass('tww_disable');
            jQuery('.tww_add_new_filter').removeClass('tww_disable');
            let apply_data = tww_set_apply_data();
            if(twwf_field_action === 'insert'){
                tww_add_filter_field(apply_data);
            }else if(typeof apply_data['key'] != "undefined" && typeof apply_data['fieldName'] != "undefined"){
                jQuery('.tww_fields_list_item[data-field_key="'+apply_data['key']+'"]').find('.tww_fields_list_item_title ').html(apply_data['fieldName'])
            }
            twwf_field_action = 'insert';
        });

        function tww_save_filter(twwFilterData) {
            jQuery('.twwf_field_error').addClass('twwf_display_none');
            jQuery('#tww_filter_name').removeClass('twwf_field_error_input');
            var ajax_data = {
                'action': 'tww_save_filter',
                'data': {
                    twwFilterData
                },
                'nonce': tww_admin_vars.ajaxnonce,
            };
            jQuery.ajax({
                type: "POST",
                dataType: 'json',
                url: tww_admin_vars.ajaxurl,
                data: ajax_data,
            }).done(function (data) {
                if(typeof data.analyticsData != "undefined"){
                    analyticsDataPush ( 'Filter Saved', 'Filter Saved',  data.analyticsData);
                }
                if (!data.success) {
                    jQuery('.twwf_field_error').html(data.error);
                    jQuery('.twwf_field_error').removeClass('twwf_display_none');
                    jQuery('#tww_filter_name').addClass('twwf_field_error_input');
                }else{
                    let tww_control_filter = jQuery('.tww_control_filter', parent.document.body).find('select');
                    if(tww_control_filter.length>0 && typeof data.filter.id != "undefined" && typeof data.type != "undefined"){
                        if(data.type === 'update'){
                            jQuery('.tww_control_filter select option[value="'+data.filter.id+'"]', parent.document.body).html(data.filter.title);
                        }else if (data.type === 'insert'){
                            tww_control_filter.append('<option value="'+data.filter.id+'">'+data.filter.title+'</option>');
                        }
                    }
                    let href = jQuery(".tww_button_save").data('href');
                    tww_render_view(href);
                }
            });
        }


        function tww_add_filter_field(apply_data) {
            let key = apply_data['key'];
            let fieldName = apply_data['fieldName'];
            let fieldType = apply_data['fieldType'];
            let preview_key = fieldType+'_'+key;
            let filter_field_html = '<div data-preview="'+preview_key+'" data-type="' + fieldType + '" data-field_key="' + key + '" class="tww_fields_list_item" data-field_id="' + key + '">\n' +
                '            <input checked class="tww_on_off_checkbox tww_on_off_field" id="' + key + '" type="checkbox">\n' +
                '            <label class="tww_on_off_checkbox_label tww_on_off_field_label" for="' + key + '">Toggle</label>\n' +
                ' <span class="tww_field_drag_drop"></span>\n' +
                '            <span class="tww_fields_list_item_title">' + fieldName + '</span>\n' +
                '            <div class="twwf_field_actions">\n' +
                '                    <span class="tww_edit_field"></span>\n' +
                '                    <span class="tww_delete_field"></span>\n' +
                '                </div>\n' +
                '        </div>'
            jQuery(".tww_fields_list").append(filter_field_html);
            let preview_image = tww_admin_vars.fields_data[fieldType].preview_image;
            if(typeof apply_data.fieldView != "undefined" && typeof preview_image === "object"){
                preview_image = preview_image[apply_data.fieldView];
            }

            let filter_field_preview_html = '<img class="tww_fields_list_preview_item" data-preview="'+preview_key+'" src="'+preview_image+'">'
            jQuery(".preview_data_list").append(filter_field_preview_html);
        }

        function tww_set_apply_data() {
            let apply_data = {};
            let field_block = jQuery(".tww_add_field_settings.tww_" + tww_filter_field);

            let input = null;
            if (tww_filter_field === 'PriceSlider' || tww_filter_field === 'ColorList') {
                input = field_block.find('input, select');
            } else {
                var variation = field_block.find('.tww_filter_field_variations').val();
                input = field_block.find('.tww_variation_view_' + variation).find('input, select');
                let field_name = field_block.find('#tww_filter_field_name').val();
                apply_data['fieldName'] = field_name;
                apply_data['fieldVariation'] = variation;
            }

            let options = {};
            input.each(function (e) {
                let field_name = jQuery(this).attr("name");
                let field_type = jQuery(this).attr("type");
                if(tww_filter_field === 'ColorList' && field_type==='color'){
                    options[e] = {
                        'color' : jQuery(this).val(),
                        'slug' : jQuery(this).data('slug'),
                    }
                }else if(tww_filter_field === 'PriceSlider'){
                    if(field_name === 'maxPrice' || field_name==='minPrice' || field_name==='step'){
                        options[field_name] = parseInt(jQuery(this).val());
                    }
                }else if(variation === 'StockStatus'){
                    if(field_name === 'inStock' || field_name==='outOfStock' || field_name==='onBackorder') {
                        let fieldState = (jQuery(this).closest('.tww_stock_status_block').find('input[name="'+field_name+'Check"]').is(':checked') ? "on" : "off")
                        options[e] = {
                            'name': jQuery(this).val(),
                            'value': field_name,
                            'item_id': field_name,
                            'fieldState': fieldState,
                        }
                    }
                }

                if (field_type === 'checkbox') {
                    apply_data[field_name] = (this.checked ? "on" : "off");
                } else {
                    apply_data[field_name] = jQuery(this).val();
                }

            });

            apply_data["fieldState"] = 'on';
            let key = '';
            if(twwf_field_action === 'update'){
                key = edit_field_key;
                let edited_field = twwFilterData["fieldsData"][key];
                if(typeof edited_field != "undefined"){
                    apply_data["fieldType"] = tww_filter_field;
                    if(typeof edited_field.position != "undefined"){
                        apply_data["position"] = edited_field.position;
                        apply_data["fieldId"] = tww_field_id;
                        apply_data["update"] = true;
                    }
                    let fields_data = twwf_convert_apply_data(apply_data);
                    fields_data['id'] = tww_admin_vars.fields[edit_obj_id]['id'];
                    fields_data['options'] = options;
                    tww_admin_vars.fields[edit_obj_id] = fields_data;
                }
            }else{
                tww_filter_field_val = jQuery('.tww_filter_field:checked').val();
                apply_data["fieldType"] = tww_filter_field_val;
                key = Date.now();
                let fields_data = twwf_convert_apply_data(apply_data);
                fields_data['id'] = key;
                fields_data['options'] = options;
                tww_admin_vars.fields[key] = fields_data;
            }
            twwFilterData["fieldsData"][key] = apply_data;
            apply_data["key"] = key;
            return apply_data;
        }

        function twwf_convert_apply_data(apply_data){
            let new_data = {};
            new_data['name'] = apply_data['fieldName'];
            new_data['type'] = apply_data['fieldType'];
            new_data['value'] = apply_data['fieldValue'];
            new_data['variation'] = apply_data['fieldVariation'];
            new_data['state'] = apply_data['fieldState'];
            if(typeof apply_data.position != "undefined"){
                new_data['position'] = apply_data.position;
            }
            return new_data;
        }


        jQuery('.tww_filter_field_variations').on('change', function () {
            tww_stock_status_disable_apply(this);
            twwf_current_variation = jQuery('.filter_popup_section.tww_'+twwf_current_field).find('.tww_filter_field_variations').val();
            twwf_current_variation_attribute = jQuery('.filter_popup_section.tww_'+twwf_current_field).find('.tww_filter_field_variation_attribute').val();
            jQuery('.filter_popup_section.tww_'+twwf_current_field).find('.tww_required_input').trigger("change");
            let tww_variation_view_name = this.value;

            let variation_view_class = '.tww_variation_view_' + tww_variation_view_name;
            tww_change_variation_view(variation_view_class);
        });

        function tww_change_variation_view(variation_view_class) {
            jQuery('.tww_variation_view_block').addClass('tww_disable');
            jQuery(variation_view_class).removeClass('tww_disable');

        }

        jQuery("body").on("change", ".tww_variation_view_StockStatus .tww_on_off_checkbox", function () {
            tww_stock_status_disable_apply(this);
        });
        function tww_stock_status_disable_apply(_this){
            let tww_on_off_checkbox = jQuery(_this).closest('.filter_popup_section').find('.tww_on_off_checkbox');
            let apply_button = jQuery(_this).closest('.filter_popup_section').find('.tww_button_apply');
            let tww_filter_field_variation = jQuery(_this).closest('.filter_popup_section').find('.tww_filter_field_variations').val();
            if(tww_filter_field_variation !== 'StockStatus'){
                apply_button.removeClass('twwf_disabled_button_1');
                return;
            }
            let checked = false;
            tww_on_off_checkbox.each(function (i, obj){
                if(jQuery(obj).is(':checked')){
                    checked = true;
                    return;
                }
            });
            if(checked){
                apply_button.removeClass('twwf_disabled_button_1');
            }else{
                apply_button.addClass('twwf_disabled_button_1')
            }
        }
        jQuery("body").on("change", ".tww_on_off_field", function () {
            let key = jQuery(this).closest('div').data('field_key')
            if (this.checked) {
                twwFilterData["fieldsData"][key].fieldState = 'on';
            } else {
                twwFilterData["fieldsData"][key].fieldState = 'off';
            }

        });
        jQuery("body").on("click", ".tww_delete_field", function () {
            let field_item = jQuery(this).closest('.tww_fields_list_item');
            let key = field_item.data('field_key');
            let field_preview = field_item.data('preview');
            jQuery('.tww_fields_list_preview_item[data-preview="'+field_preview+'"]').remove();
            twwFilterData["fieldsData"][key].delete = '1';
            field_item.remove();
        });

        let attr_name = 'pa_' + jQuery('.tww_color_attr').val();
        let terms_obj = tww_admin_vars.terms[attr_name];
        tww_draw_colors_list(terms_obj)
        jQuery("body").on("change", ".tww_color_attr", function () {
            attr_name = 'pa_' + jQuery(this).val();
            terms_obj = tww_admin_vars.terms[attr_name];
            tww_draw_colors_list(terms_obj)
        });


        function tww_draw_colors_list(terms) {
            jQuery('.tww_field_colors_list').html('');
            jQuery.each(terms, function (key, value) {
                let input_name = 'tww_color_attr_input_' + value['term_id'];
                let color = '#1A1919';
                let not_selected_class = 'not_selected';
                if(tww_edit_color_list != ''){
                    if(typeof tww_edit_color_list[value['slug']] !="undefined"){
                        color = tww_edit_color_list[value['slug']];
                        not_selected_class = '';
                    }
                }
                jQuery('.tww_field_colors_list').append(
                    '<div class="tww_color_item">\n' +
                    '            <input type="color" data-slug="'+value['slug']+'" class="tww_color_attr_input '+not_selected_class+'" id="' + input_name + '" name="' + input_name + '"  value="'+color+'">\n' +
                    '            <label class="tww_color_attr_label" for="' + input_name + '">' + value['name'] + '</label>\n' +
                    '        </div>'
                );
            });
        }

        jQuery("body").on("change", ".tww_color_attr", function () {
            let attr_name = 'pa_' + jQuery(this).val();
            let terms_obj = tww_admin_vars.terms[attr_name];
            tww_draw_colors_list(terms_obj)
        });

        jQuery("body").on("click", ".tww_color_attr_input", function () {
            jQuery(this).removeClass('not_selected');
        });

        jQuery('#tww_filter_field_variations_checkbox').select2({
            minimumResultsForSearch: -1
        });
        jQuery('#tww_filter_field_variations_dropdown').select2({
            minimumResultsForSearch: -1
        });
        jQuery('#tww_filter_field_variations_pillbox').select2({
            minimumResultsForSearch: -1
        });
        jQuery('#tww_filter_field_radioList').select2({
            minimumResultsForSearch: -1
        });
        jQuery('#tww_filter_field_variations_box').select2({
            minimumResultsForSearch: -1
        });
        jQuery('#tww_filter_field_value_colorList').select2({
            minimumResultsForSearch: -1
        });
        jQuery('.tww_back_button').click(function (){
            jQuery(this).closest('.filter_popup_section').addClass('tww_disable');
            let back_section_class = jQuery(this).data('back');
            if(twwf_field_action === 'update'){
                back_section_class = 'tww_add_new_filter';
            }
            jQuery('.'+back_section_class).removeClass('tww_disable');
            if(back_section_class === 'tww_add_filter_fields'){
                jQuery('.tww_filter_container').addClass('tww_filter_container_min');
            }else{
                jQuery('.tww_filter_container').removeClass('tww_filter_container_min');
            }
        });


        jQuery("body").on("change", ".tww_on_off_field", function () {
            let field_div  = jQuery(this).closest('div');

            let field_preview = field_div.data('preview');
            let preview_image = jQuery('.tww_fields_list_preview_item[data-preview="'+field_preview+'"]');
            let title = field_div.find('.tww_fields_list_item_title');

            if(this.checked){
                preview_image.removeClass('twwf_hide_preview');
                title.removeClass('tww_inactive_title');
            }else{
                preview_image.addClass('twwf_hide_preview');
                title.addClass('tww_inactive_title');
            }
        });

    }
    function analyticsDataPush ( action, eventName = '', info = '' ) {
        if ( typeof dataLayer != "undefined" ) {
            dataLayer.push({
                event: '10web-event',
                'eventName': eventName,
                'eventAction': action,
                'info': info,
                'domain_id': tww_admin_vars.domain_id
            });
        }
    }
});