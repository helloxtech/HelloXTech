jQuery(window).on('elementor/frontend/init', function() {




    jQuery(document).on('mouseenter', '[data-element_type="widget"], [data-element_type="container"]', function () {
        if (jQuery(window).width() <= 1025) {
            return; // Exit if screen width is 1024px or less (tablet or mobile)
        }
        let twbb_smart_scale_active = jQuery(this).find('.twbb_smart_scale_active');
        if(twbb_smart_scale_active.length>0){
            jQuery(this).addClass('twbb_smart_scale_active_flag');
        }
        if(jQuery(this).closest('[data-element_type="section"]').length>0){
            return;
        }
        if(!jQuery(this).hasClass('elementor-element-editable')){
            twbb_position_label(jQuery(this));
        }
        let element_id = jQuery(this).data('id');
        element_id = element_id.toString();
        let element = jQuery(this);
        let element_obg = window.parent.$e.components.get('document').utils.findContainerById(element_id);
        let element_title = "";
        if(element_obg && typeof element_obg.label !== "undefined"){
            element_title = element_obg.label;
        }
        if (
            element_obg &&
            element_obg.settings &&
            element_obg.settings.attributes &&
            typeof element_obg.settings.attributes._title !== "undefined" &&
            element_obg.settings.attributes._title !== ''
        ) {
            element_title = element_obg.settings.attributes._title;
        }

        if(jQuery(this).data('element_type') === 'widget'){
            let labelElement = element.find('.twbb_widget_label').first();
            if (labelElement.length) {
                // If the label already exists, change its text
                labelElement.text(element_title);
            } else {
                // If the label doesn't exist, prepend it to the widget
                element.find('.elementor-editor-element-setting').first().prepend('<span class="twbb_widget_label">'+element_title+'</span>');
            }
        }else if(jQuery(this).data('element_type') === 'container' && jQuery(this).data('nesting-level') == 0){
            let elementor_editor_element_add = jQuery(this).find('.elementor-editor-element-add');
            let twbb_new_section_label = elementor_editor_element_add.find('.twbb_new_section_label');
            if(twbb_new_section_label.length < 1){
                elementor_editor_element_add.append('<span class="twbb_new_section_label">New section</span>')
            }
            let labelElement = element.find('.twbb_container_label').first();
            if (labelElement.length) {
                // If the label already exists, change its text
                labelElement.text(element_title+' section');
            } else {
                // If the label doesn't exist, prepend it to the widget
                element.find('.elementor-element-overlay').first().prepend('<span class="twbb_container_label">'+element_title+' section</span>');
            }
        }else if(jQuery(this).data('element_type') === 'container' && jQuery(this).data('nesting-level') > 0){
            let labelElement = element.find('.elementor-element-overlay').first().find('.twbb_nested_container_label').first();
            if (labelElement.length) {
                // If the label already exists, change its text
                labelElement.text(element_title);
            } else {
                // If the label doesn't exist, prepend it to the widget
                element.find('.elementor-element-overlay .elementor-editor-element-edit').first().append('<span class="twbb_nested_container_label">'+element_title+'</span>');
            }
        }
    });
    jQuery(document).on('click', '.twbb_container_label', function () {
        jQuery(this).closest('.elementor-element-overlay').find('.elementor-editor-element-edit').trigger('click');
    });
});
window.twbb_trigger_events = function (element, event){
    setTimeout(function(){
        jQuery(element).trigger(event);
    },100)
}

function twbb_position_label(el) {
    let label = el.find('.elementor-element-overlay').first();
    let MuiToolbar_height = 0;
    if (window.parent && window.parent.jQuery) {
        MuiToolbar_height = window.parent.jQuery('.MuiToolbar-root').outerHeight(true);
    }


    if (label[0] !== undefined && label[0].getBoundingClientRect().top < MuiToolbar_height) {
        el.addClass('twbb_label_bottom');
    }else{
        el.closest('body').find('.twbb_label_bottom').not('.elementor-element-editable').removeClass('twbb_label_bottom');
    }
}