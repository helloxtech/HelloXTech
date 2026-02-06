jQuery( document ).ready(function() {

    if (jQuery('.tenweb-after-migrate').length > 0) {
    jQuery.ajax({
        type: "POST",
        url: tenweb_pagespeed.ajaxurl,
        data: {action: "tenweb_send_pagespeed", label: 1},
        success: function (response) {
            console.log(response)
        },
        error: function (error) {
            console.log(error);
        }
    });
    }

jQuery('.tenweb-pagespeed').click(function(e){
    let label = jQuery(this).data("label");
    if (label === 2) {
        e.preventDefault();
        let href = jQuery(this).attr("href");
        jQuery.ajax({
            type: "POST",
            url: tenweb_pagespeed.ajaxurl,
            data: {action: "tenweb_send_pagespeed", label: label},
            success: function (response) {
                console.log(response)
            },
            error: function (error) {
                console.log(error);
            }
        });
        window.open(href, '_blank')
    } else {
        jQuery.ajax({
            type: "POST",
            url: tenweb_pagespeed.ajaxurl,
            data: {action: "tenweb_notice_after_migrate_dismissed", label: label},
            success: function (response) {
                jQuery(".tenweb-after-migrate").remove();
            },
            error: function (error) {
                console.log("errr");
                console.log(error);
            }
        });
    }
});
});