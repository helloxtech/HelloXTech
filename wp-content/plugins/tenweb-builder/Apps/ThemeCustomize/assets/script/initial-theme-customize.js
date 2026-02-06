let theme_Customize;
function themeCustomizeInitialFunction() {
    theme_Customize = new ThemeCustomize();
    theme_Customize.init();
}

/* Elementor editor topbar button*/
jQuery(document).on("click", ".twbb-customize-button", function() {
    if( jQuery(this).hasClass('selected') && jQuery(document).find(".twbb-customize-layout").hasClass('twbb-animated-sidebar-show') ) {
        twbb_animate_sidebar('close', jQuery(document).find(".twbb-customize-layout"), 300, 'twbb-customization-sidebar-opened', theme_Customize.close_customization);
        return;
    }
    analyticsDataPush(
        '10Web Styles',
        '10Web Styles'
    );
    if( !theme_Customize ) {
       let theme_styles_list = [
            '/Apps/ThemeCustomize/assets/style/theme-customize'
        ];
        let theme_scripts_list = [
            '/Apps/ThemeCustomize/assets/script/theme-customize'
        ];
        enqueueNeededAssets(theme_styles_list, theme_scripts_list, themeCustomizeInitialFunction);
    }
    if ( twbb_options.show_ultimate_kit ) {
        jQuery(this).addClass('selected');
        let header_add_element_button = jQuery('#elementor-editor-wrapper-v2 .MuiButtonBase-root[aria-label="Add Element"]');
        header_add_element_button.removeClass('Mui-selected');
        if (jQuery(document).find(".twbb-customize-layout").length) {
            jQuery('#elementor-preview-iframe').contents().find('#elementor-add-new-section').hide();
            twbb_animate_sidebar('open', jQuery(document).find(".twbb-customize-layout"), 300, 'twbb-customization-sidebar-opened', theme_Customize.close_customization);
            jQuery("#elementor-preview-iframe").contents().find("body").find(".twbb-customize-preview-layout").show();
        } else {
            let template = jQuery(document).find("#twbb-customize-template").html();
            jQuery(document).find("#elementor-editor-wrapper-v2").append(template);
            let layout_template = jQuery(document).find("#twbb-customize-preview-layout-template").html();
            let iframeBody = jQuery("#elementor-preview-iframe").contents().find("body");
            iframeBody.append(layout_template);
            jQuery('#elementor-preview-iframe').contents().find('#elementor-add-new-section').hide();
            twbb_animate_sidebar('open', jQuery(document).find(".twbb-customize-layout"), 300, 'twbb-customization-sidebar-opened', theme_close_customization);
        }

        setTimeout(function () {
            /* Set active color */
            jQuery(document).find(".twbb-color-item").removeClass("twbb-color-active");
            jQuery(document).find(".twbb-color-item[data-pallet_id='" + self.active_color + "']").addClass("twbb-color-active");
        }, 500)
    }
    else {
        self.kitEnablePopupOpened = 1;
        self.openCustomizeEnablePopup();
    }

})

function theme_close_customization() {
    jQuery(document).find('.twbb-customize-button').removeClass('selected');
    jQuery(document).find('.MuiButtonBase-root[aria-label="Add Element"]').addClass('Mui-selected');
    jQuery(document).find(".twbb-customize-layout").removeClass('twbb-animated-sidebar-show').addClass('twbb-animated-sidebar-hide');
    jQuery('#elementor-preview-iframe').contents().find('#elementor-add-new-section').show();
    let iframeBody = jQuery("#elementor-preview-iframe").contents().find("body");
    iframeBody.find(".twbb-customize-preview-layout").hide();
    //window.ultimateKitSaved is to be sure that variable is changed correctly in time only ultimateKitSaved was not working properly
    if( this && !this.ultimateKitSaved && window && !window.ultimateKitSaved ) {
        this.changeThemeStyle( this, 'color', 'remove' );
        this.changeThemeStyle( this, 'font', 'remove' );
    }
}
