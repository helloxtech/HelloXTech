jQuery(document).ready(function() {
    let TwbbmChangeInterval = null;
    let templateLayer = jQuery(document).find("#twbbm-copilot-tour-template").html();
    jQuery("body").append(templateLayer);
    TwbbmRunAutoChange();
    jQuery(document).on("click",".twbbm-copilot-tour_remind_button", function() {
        if(TwbbmChangeInterval != null){
            jQuery(document).find(".twbbm-copilot-tour-layer, .twbbm-copilot-tour-container").remove();
            clearInterval(TwbbmChangeInterval);
            jQuery.ajax({
                type: 'POST',
                url: twbbm.ajaxurl,
                dataType: 'json',
                data: {
                    action: "twbbm_set_copilot_introduction_remind",
                    twbbm_introduction_remind: "1",
                    twbbm_nonce: twbbm.ajaxnonce,
                }
            }).success(function(res){

            });
        }
    });
    let twbbm_updating_in_progress = false
    jQuery(document).on("click",".twbbm-copilot-tour-button", function() {
        if(twbbm_updating_in_progress){
            return;
        }
        twbbm_updating_in_progress = true;
        let _this = jQuery(this);
        _this.addClass('twbbm-copilot-tour-button_loading');
        jQuery.ajax({
            type: 'POST',
            url: twbbm.ajaxurl,
            dataType: 'json',
            data: {
                action: "twbbm_update_plugins",
                twbbm_nonce: twbbm.ajaxnonce,
            }
        }).success(function(res){
            _this.removeClass('twbbm-copilot-tour-button_loading');
            _this.addClass('twbbm-copilot-tour-button_success');
            setTimeout(function (){
                jQuery(document).find(".twbbm-copilot-tour-layer, .twbbm-copilot-tour-container").remove();
                location.reload(true);
            },1000);
        });
    });


    function TwbbmRunAutoChange() {
        TwbbmChangeInterval = setInterval(function() {
            let nextItem = '';
            let index = jQuery(document).find(".twbbm-copilot-images-row .twbbm-copilot-image-item.twbbm-copilot-image-item-active").index();
            if( index == 2 ) {
                nextItem = jQuery(document).find(".twbbm-copilot-images-row .twbbm-copilot-image-item").eq(0);
            } else {
                nextItem = jQuery(document).find(".twbbm-copilot-images-row .twbbm-copilot-image-item").eq(index+1);
            }
            TwbbmChangeTourImage(nextItem);
        }, 3000);
    }
    function TwbbmChangeTourImage(that) {
        jQuery(that).find('.twbbm-copilot-progress-circle').css('stroke-dashoffset', '283');
        jQuery(document).find(".twbbm-copilot-images-row .twbbm-copilot-image-item").removeClass("twbbm-copilot-image-item-active");
        jQuery(that).addClass("twbbm-copilot-image-item-active");
        let name = jQuery(that).attr("data-name");
        jQuery(document).find(".twbbm-copilot-tour-video-cont img").hide();
        jQuery(document).find(".twbbm-copilot-tour-video-cont #twbbm-copilot-img-"+name).show();


        jQuery(document).find(".twbbm-copilot-circle-loader").hide();
        jQuery(that).find(".twbbm-copilot-circle-loader").show();
        const $progressCircle = jQuery(that).find('.twbbm-copilot-progress-circle');
        // Set strokeDashoffset to 0 to animate the fill using jQuery's css method
        $progressCircle.css('stroke-dashoffset', '0');
    }
});
