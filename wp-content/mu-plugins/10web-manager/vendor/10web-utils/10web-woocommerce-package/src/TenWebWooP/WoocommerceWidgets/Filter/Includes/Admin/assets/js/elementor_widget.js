jQuery( document ).ready(function() {
    jQuery(document).on('click', '.tww_open_filter_popup_button button', function(){
        let src = jQuery('.tww_open_filter_popup_label').data('src');
        jQuery("body").append("<iframe class='tww_elementor_popup_iframe' src='"+src+"'></iframe>");
    });

    jQuery(document).on('click', '.tww_elementor_edit_filter', function(){
        let src = jQuery('.tww_open_filter_popup_label').data('src');
        let filter_id = jQuery('.tww_control_filter').find('select').val();
        src = src+'&filter_id='+filter_id
        jQuery("body").append("<iframe class='tww_elementor_popup_iframe' src='"+src+"'></iframe>");
    });
});

function twwf_render_view(){
    jQuery('.tww_control_filter select', parent.document.body).trigger("change");
}