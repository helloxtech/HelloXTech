var tenweb_data = {
    action: 'check_site_state',
    screen_id: tenweb_state.screen_id ? tenweb_state.screen_id : null,
    current_blog_id: tenweb_state.current_blog_id ? tenweb_state.current_blog_id : 1,
};
jQuery.ajax({
    type: "POST",
    url: tenweb_state.ajaxurl,
    data: tenweb_data,
    success: function (response) {

    },
    error: function (error) {
        console.log(error);
    }
});