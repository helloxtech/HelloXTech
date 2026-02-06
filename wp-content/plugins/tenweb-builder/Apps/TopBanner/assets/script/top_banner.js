jQuery(document).ready(function() {
    // Target date: December 3, 2025 at 24:00 GMT+0000 (which is December 4, 2024 00:00:00 UTC)
    // Create target date explicitly in UTC
    var targetDate = new Date('2025-12-06T00:00:00.000Z').getTime();
    let now = new Date().getTime();
    var timeLeft = targetDate - now;

    // Only show banner if countdown hasn't expired
    if (timeLeft <= 0) {
        return; // Don't prepend HTML if countdown has expired
    }

    // Get the script content by ID
    var topBannerHtml = jQuery('#twbb-top-banner-container').html();

    // Append the HTML content to the body
    jQuery('body').prepend(topBannerHtml);

    // Countdown timer functionality
    function updateCountdown() {
        let now = new Date().getTime();
        var timeLeft = targetDate - now;

        // If countdown has expired, hide the banner
        if (timeLeft <= 0) {
            jQuery('.twbb-top-banner-main-container').remove();
            return;
        }

        // Calculate days, hours, and minutes (no seconds)
        var days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        var hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

        // Format with leading zeros
        days = String(days).padStart(2, '0');
        hours = String(hours).padStart(2, '0');
        minutes = String(minutes).padStart(2, '0');
        seconds = String(seconds).padStart(2, '0');

        // Update the countdown display (DD:HH:MM format)
        jQuery('.twbb-banner-countdown').text(days + ':' + hours + ':' + minutes + ':' + seconds);
    }

    // Update countdown immediately
    updateCountdown();

    // Update countdown every second
    setInterval(updateCountdown, 1000);
});
