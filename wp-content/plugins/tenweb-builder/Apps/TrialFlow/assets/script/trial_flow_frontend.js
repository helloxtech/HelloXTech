jQuery(function ($) {
    // Don't run if inside Elementor preview/editor iframe
    const isElementorPreview = window.location !== window.parent.location &&
      document.referrer.includes('action=elementor');

    if (!isElementorPreview) {
        $(document).ready(function () {
            const url = new URL(window.location.href);

            if (url.searchParams.has('trial_hosted_flow')) {
                const cleanUrl = url.origin + url.pathname;

                // Replace URL in the address bar (no reload)
                window.history.replaceState({}, document.title, cleanUrl);
            }

            $(document).on('click', '.twbb-tf-top-bar__responsive .device', function(){
                changeDevice($(this));
            });
            let currentUrl = window.location.href;
            currentUrl = currentUrl + (currentUrl.includes("?") ? '&' : '?') + 'in_iframe=1';
            $('#twbb-tf-mobile-iframe').attr('src', currentUrl);
            if (window.self === window.top){
                $('.twbb-tf-top-bar').removeClass('hidden').show();
                $('body').addClass('margin-top');
            }
            checkHeaderPosition();


            $(document).on('click', '.twbb-tf-top-bar__edit-button', function(e){
                e.preventDefault();

                // Open target URL in new tab
                window.open(twbb_trial_flow.twbb_edit_url, '_blank');

                // Redirect current tab
                window.location.href = twbb_trial_flow.dashboard_url;
            });

            $(document).on('click', '.twbb-tf-wesite-ready-button', function(){
                $(this).closest(".twbb-tf-wesite-ready-layer").remove();
            });

            if( typeof confetti === 'function' && $('.twbb-tf-wesite-ready-layer').length ) {
                $('.twbb-tf-wesite-ready-layer').show();
                initCanfettiPopup();
                jQuery(document).on('click','.twbb-tf-wesite-ready-container .twbb-tf-wesite-ready-button',function() {
                    twbSendEventToPublicRouth( {
                        eventCategory: 'Onboarding',
                        eventAction: 'Section-based AI Flow - Preview',
                        eventLabel: 'Section-based/Recreation'
                    } );
                });
            }

        });


        $(window).scroll(function () {
            checkHeaderPosition();
        });

        function checkHeaderPosition(){
            if (window.self === window.top){
                const header = $('#header');
                if (header.length) {
                    if(header.hasClass('elementor-sticky--active')){
                        header.css('top','66px');
                    }
                    else{
                        header.css('top','0');
                    }
                }
            }
        }

        function changeDevice(deviceElement) {
            const device = deviceElement.attr('data-id');
            const iframeContainer = $('#twbb-tf-mobile-iframe-container');
            $('.twbb-tf-top-bar__responsive .device').removeClass('active');
            $('.twbb-tf-top-bar__responsive').attr('data-active', device);
            deviceElement.addClass('active');
            if (device === 'mobile'){
                iframeContainer.addClass('active');
                $('body').addClass('scroll_disclaimer');
            }
            else {
                iframeContainer.removeClass('active');
                $('body').removeClass('scroll_disclaimer');
            }
        }

        function initCanfettiPopup() {
            const count = 1000;
            const defaults = {
                origin: { y: 0, x: 50 }, // start from bottom
                gravity: 1,
                ticks: 100,
                spread: 500,
                startVelocity: 12, // go up about 200px then fall
                decay: 0.95,
                zIndex: 999999999,
                shapes: ['square'],
                colors: ['#FFD700', '#3B6EF6', '#F2F2F2', '#928FF2'] // yellow, blue, white, light purple
            };

            function fire(particleRatio, opts) {
                confetti(Object.assign({}, defaults, opts, {
                    particleCount: Math.floor(count * particleRatio),
                }));
            }

            // Burst across bottom width
            fire(0.2, { origin: { x: 0.1, y: 1 } });
            fire(0.2, { origin: { x: 0.2, y: 1 } });
            fire(0.2, { origin: { x: 0.3, y: 1 } });
            fire(0.2, { origin: { x: 0.4, y: 1 } });
            fire(0.2, { origin: { x: 0.5, y: 1 } });
            fire(0.2, { origin: { x: 0.6, y: 1 } });
            fire(0.2, { origin: { x: 0.7, y: 1 } });
            fire(0.2, { origin: { x: 0.8, y: 1 } });
            fire(0.2, { origin: { x: 0.9, y: 1 } });

        }
    }


});

