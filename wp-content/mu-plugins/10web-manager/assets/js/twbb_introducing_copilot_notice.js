jQuery(document).ready(function() {
    let twbbm_updating_in_progress = false
    jQuery(document).on("click",".twbbm_introducing_copilot_notice_close", function() {
        jQuery(".twbbm_introducing_copilot_notice_container").remove();
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
    });
    jQuery(document).on("click",".twbbm_introducing_button", function() {
        if(twbbm_updating_in_progress){
            return;
        }
        twbbm_updating_in_progress = true;
        let _this = jQuery(this);
        _this.addClass('twbbm_introducing_button_loading');
        jQuery.ajax({
            type: 'POST',
            url: twbbm.ajaxurl,
            dataType: 'json',
            data: {
                action: "twbbm_update_plugins",
                twbbm_nonce: twbbm.ajaxnonce,
            }
        }).success(function(res){
            _this.removeClass('twbbm_introducing_button_loading');
            _this.addClass('twbbm_introducing_button_success');
            setTimeout(function (){
                jQuery(".twbbm_introducing_copilot_notice_container").remove();
            },1000);
        });
    });
});
