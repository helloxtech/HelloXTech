interval = '';
jQuery(document).ready(function() {

    if ( tour_data.tour_status === 'not_started' && tour_data.show_tour == true && jQuery('#elementor-loading').length > 0 ) {
        interval = setInterval(function() {
            if( jQuery('.twbb-top-bar-icon-parent').length > 0 ) {
                clearInterval(interval);
                jQuery('.twbb-top-bar-icon-parent').addClass('twbb-tour-not-passed');
            }
        }, 400);
    }

    jQuery(document).on('click', '.twbb-copilot-tour-button', function() {
        let tour_notif = jQuery('#twbb-editor-tour-small-notif-template').html();
        jQuery('.twbb-top-bar-icon-parent').append(tour_notif);
    });

    jQuery(document).on('click','#elementor-editor-wrapper-v2 .twbb-top-bar-icon', function() {
        setTimeout(function () {
            if (!jQuery('a.twbb-main-menu-editor-tour').length ) {
                let tour_class_attr = '';
                let tour_html = '';
                if (tour_data.tour_status !== 'passed') {
                    tour_class_attr = 'twbb-tour-not-passed';
                }
                if (tour_data.show_tour == true) {
                    tour_html = '<a class="MuiButtonBase-root MuiMenuItem-root MuiMenuItem-gutters MuiMenuItem-root ' +
                        'MuiMenuItem-gutters eui-menu-item twbb-main-menu-items twbb-main-menu-editor-tour ' + tour_class_attr + '"' +
                        ' href="#" onclick="twbbStartTour()">Editor Tour</a>'
                }

                if( jQuery('.twbb_website_structure-footer').length ) {
                    jQuery('.twbb_website_structure-footer').before(tour_html);
                } else {
                    jQuery('#elementor-v2-app-bar-main-menu .MuiMenu-list').append(tour_html);
                }

            }
            //timeout for adding class to the element should be more then adding 10Web dashboard element in editor_v2.js
        },70);
    });

    jQuery(document).on('click', '.twbb-tour-guide__button-done', function() {
        jQuery('#elementor-v2-app-bar-main-menu .MuiMenu-list .twbb-main-menu-editor-tour').removeClass('twbb-tour-not-passed');
        jQuery('.twbb-top-bar-icon-parent').removeClass('twbb-tour-not-passed');
    });
});

function deleteNotification() {
    jQuery('.twbb-editor-tour-small-notif-main-container').remove();
}
let twbb_builder_tour;
function builderTourInitialFunction() {
    // Initialize the tour after the script is loaded

    twbb_builder_tour =  new  BuilderTour();
    twbb_builder_tour.twbbStartTour();
}

function twbbStartTour() {
    deleteNotification();
    //not to send when we open tour
    if ( tour_data.tour_status !== 'not_started' ) {
        analyticsDataPush('Top Bar', 'Editor Tour');
    }

    if( !twbb_builder_tour ) {
        let tour_styles_list = [
            '/Apps/BuilderTour/assets/style/editor/tour'
        ];
        let tour_scripts_list = [
            '/Apps/BuilderTour/assets/script/editor/tour'
        ];
        enqueueNeededAssets(tour_styles_list, tour_scripts_list, builderTourInitialFunction, true);
    } else {
        twbb_builder_tour.twbbStartTour();
    }

}
