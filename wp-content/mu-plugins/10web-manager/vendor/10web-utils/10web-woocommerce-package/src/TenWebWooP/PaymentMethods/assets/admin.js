jQuery(document).ready(function ($) {
    // Disable the switch and redirect to the dashboard if the merchant is not active
    if (twwp_admin_config.payment_method_disabled) {
        $('tr [data-gateway_id="tenweb_payments"] td.status a').attr('disabled', 'disabled').css('pointer-events', 'none');
        $('tr [data-gateway_id="tenweb_payments"] td.action a.button').attr('target', '_blank').attr('href', twwp_admin_config.dashboard_url);
    }
});
