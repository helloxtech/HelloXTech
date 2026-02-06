let twbTrialFlowElementsInterval = null;
let twbTrialUpgradeData = null; //Will change from Co-Pilot

jQuery(document).ready(async function () {
    twbAddTrialFlowTooltip('init');

    jQuery(document).on('click', '.twbb-tf-tooltip.twbb-tf-popup', function () {
        twbCloseTrialFlowPopup();
        twbTrialUpgradeData = null;
    });

    jQuery(document).on('click', '.twbb-tf-tooltip.twbb-tf-popup .got-it', function () {
        twbSendEventToPublicRouth( {
            eventCategory: 'Free trial paywalls',
            eventAction: 'Upgrade popup click',
            eventLabel: 'Got it button'
        } );
        twbCloseTrialFlowPopup();
        twbTrialUpgradeData = null;
    });

    jQuery(document).on('click', '.twbb-tf-tooltip.twbb-tf-popup .upgrade-button', function () {
        if (twbTrialUpgradeData !== null) {
            twbSendEventToPublicRouth(twbTrialUpgradeData);
        }
        twbTrialUpgradeData = null;
    });

    jQuery(document).on('click', 'body.twbb_tf_ends .twbb-tf-tooltip-overlay', function () {
        twbCloseTrialFlowTooltip('twbb_tf_ends');
    });

    jQuery(document).on('click', 'body.twbb_tf_credit .twbb-tf-tooltip-overlay', function () {
        twbCloseTrialFlowTooltip('twbb_tf_credit');
    });

    jQuery(document).on('click', '.twbb-tf-tooltip__content', function (e) {
        e.stopPropagation();
    });

    jQuery(document).on('mouseover', '.twbb-sg-sidebar-navigated-content .twbb-tf-tooltip-container', function (e) {
        // Fix tooltip position
        twbFixTooltipPositionInSectionGeneration();
    });

    jQuery(document).on('mouseleave', '.twbb-sg-sidebar-navigated-content .twbb-tf-element__left, .twbb-sg-sidebar-navigated-content .twbb-tf-element__right, .twbb-sg-sidebar-navigated-content .twbb-tf-tooltip', function (e) {
        jQuery(this).closest('.twbb-tf-tooltip-container').find('.twbb-tf-tooltip').hide();
    }).on('mouseover', '.twbb-sg-sidebar-navigated-content .twbb-tf-element__left, .twbb-sg-sidebar-navigated-content .twbb-tf-element__right, .twbb-sg-sidebar-navigated-content .twbb-tf-tooltip', function (e) {
        jQuery(this).closest('.twbb-tf-tooltip-container').find('.twbb-tf-tooltip').show();
    });

    if( jQuery(document).find(".trial-upgrade-container-layer").length ) {
        twbUpgradePopupAnimation();
        jQuery(document).on('click','.twbb-unlock-container .twbb-unlock-feature-button',function() {
            twbSendEventToPublicRouth( {
                eventCategory: 'Onboarding',
                eventAction: 'Section-based AI Flow - Start editing',
                eventLabel: 'Section-based/Recreation-Editor loading'
            } );

        });


    }
});

function twbTrialFlowSendEventFromWidgets(data) {
    twbSendEventToPublicRouth(data);
}

function twbTrialFlowSendEvent(el, type = '') {
    if (!jQuery(el).closest('.twbb-tf-popup').length) {
        const label = getLabelByClosestElement(el, type);

        twbSendEventToPublicRouth({
            eventCategory: 'Free trial paywalls',
            eventAction: 'Upgrade button click',
            eventLabel: label
        });
    }
}

function getLabelByClosestElement(el, type) {
    const sections = {
        'MuiBox-root': {
            'tooltip': 'Editor: Top bar tooltip',
            'button': 'Editor: Top bar button',
        },
        'twbb-sg-sidebar-navigated-content': {
            'tooltip': 'Editor: Sections tooltip',
            'button': 'Editor: Sections button',
        },
        'twbb-image-gen-container': {
            'tooltip': 'Editor: Image generation pop up tooltip',
            'button': 'Editor: Image generation pop up button',
        },
        'twbb-ai-popup-container': {
            'tooltip': 'Editor: Text generation pop up tooltip',
            'button': 'Editor: Text generation pop up button',
        },
    }
    for (const className in sections) {
        if (el.closest('.' + className)) {
            return sections[className][type];
        }
    }
    return null;
}

function twbCloseTrialFlowTooltip (state) {
    jQuery('.twbb-tf-tooltip').removeClass('active');
    jQuery('body').removeClass(state);
    localStorage.setItem(state + '_close', '1')
}

function twbCloseTrialFlowPopup () {
    jQuery('.twbb-tf-tooltip').removeClass('twbb-tf-popup');
    jQuery('body').removeClass('scroll_disclaimer');
    jQuery('.twbb-image-gen-layout, .twbb-image-gen-container, .twbb-ai-popup-layout, .twbb-ai-popup-container').removeClass('low-z-index');
}

async function twbAddTrialFlowTooltip (state = '') {
    const headerParent = jQuery('.MuiToolbar-root .MuiBox-root .MuiGrid-root:nth-child(2)');
    const generateWithAIGeneralDescription = jQuery('.twbb-generate-with-ai-general-description');
    const aiTextPopupHeader = jQuery('.twbb-ai-popup-container .twbb-ai-popup-header');
    const aiTextPopupHeaderLogo = aiTextPopupHeader.find('.twbb-ai-logo');
    const aiImagePopupHeader = jQuery('.twbb-image-gen-topbar-action');
    const TFTooltipContainer = jQuery('#twbb-tf-tooltip-container');
    try {
        let trial_limitation = await twbGetTrialLimitation();
        const { hosting_trial_expire_date } = trial_limitation || {};

        if (typeof hosting_trial_expire_date !== 'undefined' && hosting_trial_expire_date !== '') {
            if (TFTooltipContainer.length) {
                /*Trial flow element in the header*/
                if (headerParent.length
                  && !jQuery('.MuiToolbar-root .twbb-tf-tooltip-container').length) {
                    headerParent.after(TFTooltipContainer.html());
                }
                /*Trial flow element in the section generation*/
                if (generateWithAIGeneralDescription.length
                  && !jQuery('.twbb-sg-sidebar-navigated-content .twbb-tf-tooltip-container').length) {
                    generateWithAIGeneralDescription.after(jQuery('.MuiToolbar-root .twbb-tf-tooltip-container').clone());
                    jQuery('.twbb-sg-sidebar-navigated-content .twbb-tf-tooltip-container').addClass('big-size');
                }
                /*Trial flow element in text generation popup*/
                if (aiTextPopupHeaderLogo.length
                  && !aiTextPopupHeader.find('.twbb-tf-tooltip-container').length) {
                    aiTextPopupHeaderLogo.after(TFTooltipContainer.html());
                    jQuery('.twbb-ai-popup-container').addClass('with-tf-element');
                    aiTextPopupHeader.find('.twbb-tf-tooltip-container').addClass('big-size');
                }
                /*Trial flow element in image generation popup*/
                if (aiImagePopupHeader.length
                  && !aiImagePopupHeader.find('.twbb-tf-tooltip-container').length) {
                    aiImagePopupHeader.append(TFTooltipContainer.html());
                    jQuery('.twbb-image-gen-topbar-action').addClass('with-tf-element');
                    aiImagePopupHeader.find('.twbb-tf-tooltip-container').addClass('big-size');
                }

                twbUpdateTrialLimitationData(trial_limitation);
                twbTrialFlowSetAutoPlay();
                if (state === 'init') {
                    twbSendEventToPublicRouth({
                        eventCategory: 'Free trial paywalls',
                        eventAction: 'Editor: Open Editor',
                        eventLabel: 'Open Editor'
                    });
                }
            }
        }
    } catch (error) {
        console.log('Error fetching trial limitation:', error);
    }

}

function twbFixTooltipPositionInSectionGeneration() {
    const tooltipInSectionGeneration = jQuery('.twbb-sg-sidebar-navigated-content .twbb-tf-tooltip'),
      btn = jQuery('.twbb-sg-sidebar-navigated-content .twbb-tf-element__right .upgrade-button'),
      btnWidth = btn.outerWidth(),
      btnOffset = btn.offset(),
      scrollTop = jQuery(window).scrollTop(),
      containerWidth = jQuery('.twbb-sg-sidebar-navigated-content .twbb-tf-tooltip__content').outerWidth();

    // Position the fixed element near the button (same left, adjusted top)
    tooltipInSectionGeneration.css({
        top: btnOffset.top - 10 - scrollTop + 'px',
        left: (btnOffset.left - containerWidth + btnWidth) + 'px',
        right: 'auto',
        position: 'fixed'
    });
}

function twbUpdateTrialLimitationData(trial_limitation){
    const TFTooltip = jQuery('.twbb-tf-tooltip-container'),
      dayProgress = TFTooltip.find('.days-progress .fill'),
      creditProgress = TFTooltip.find('.credits-progress .fill'),
      dayLeft = TFTooltip.find('.days-left'),
      credits = TFTooltip.find('.credits');
    let tFClass = '';

    let { already_used, plan_limit, hosting_trial_expire_date } = trial_limitation || {};

    if (typeof already_used !== 'undefined' && typeof plan_limit !== 'undefined' && typeof hosting_trial_expire_date !== 'undefined' && hosting_trial_expire_date !== '') {
        const  now = new Date(),
          diffTime = new Date(hosting_trial_expire_date) - now,
          trialTime = new Date(hosting_trial_expire_date) - new Date(twbb_tf_tooltip.agreement_date),
          trialDays = Math.ceil(trialTime / (1000 * 60 * 60 * 24));
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        diffDays = diffDays < 1 ? 1 : diffDays;
        const days = diffDays > 1 ? ' days' : ' day';
        already_used = already_used > plan_limit ? plan_limit : already_used;


        jQuery('.twbb-tf-tooltip__days-sub-title span').html(trialDays);
        if (plan_limit <= already_used) {
            tFClass = 'limitation-expired';
            localStorage.setItem('twbb_tf_credit_expired', '1');
        } else if (plan_limit / 2 <= already_used) {
            tFClass = 'half-used';
            if (!localStorage.getItem('twbb_tf_credit_close') && diffDays > 1) {
                twbShowTrialFlowActiveTooltip();
                jQuery('body').addClass('twbb_tf_credit');
            }
        }

        if (diffDays <= 1) {
            tFClass += ' one-day-left';
        }

        TFTooltip.addClass(tFClass).removeClass('hidden');

        dayLeft.html(diffDays + days);
        credits.html(already_used + '/' + plan_limit);

        dayProgress.width((diffDays * 100 / 3) + '%');
        creditProgress.width((already_used / plan_limit * 100) + '%');

        if (diffDays < 2 && !localStorage.getItem('twbb_tf_ends_close')) {
            twbShowTrialFlowActiveTooltip();
            jQuery('body').addClass('twbb_tf_ends');
        }

        twbShowTrialFlowPopupOnceADay(hosting_trial_expire_date, diffDays);
    }
    else {
        localStorage.removeItem('twbb_tf_credit_expired');
    }
}

function twbShowTrialFlowActiveTooltip() {
    let parent = jQuery('.MuiBox-root');
    if (jQuery('twbb-image-gen-container').length){
        parent = jQuery('.twbb-image-gen-container');
    }
    else if (jQuery('.twbb-ai-popup-container:visible').length) {
        parent = jQuery('.twbb-ai-popup-container');
    }
    parent.find('.twbb-tf-tooltip').addClass('active');
}

async function twbUpdateTrialLimitation () {
    try {
        let trial_limitation = await twbGetTrialLimitation();
        twbUpdateTrialLimitationData(trial_limitation);
    } catch (error) {
        console.log('Error fetching trial limitation:', error);
    }
}


/**
 * Fetches trial limitation data via AJAX from the WordPress backend.
 *
 * This function sends a POST request using jQuery to retrieve trial limitation data.
 * It returns a Promise that resolves with the data if the request is successful,
 * or rejects with an error if something goes wrong.
 *
 * @function twbGetTrialLimitation
 * @returns {Promise<Object>} Resolves with the trial limitation data object.
 * @throws {Error} If required configuration is missing or an error occurs during the request.
 *
 */
function twbGetTrialLimitation() {
    return new Promise((resolve, reject) => {
        try {
            if (!twbb_tf_tooltip || !twbb_tf_tooltip.ajaxurl || !twbb_tf_tooltip.twbb_tf_nonce) {
                throw new Error("Missing AJAX configuration.");
            }

            jQuery.ajax({
                type: 'POST',
                url: twbb_tf_tooltip.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'twbb_get_trial_limits',
                    nonce: twbb_tf_tooltip.twbb_tf_nonce,
                },
                success: function (result) {
                    if (result && result.success && result.data && result.data.data) {
                        resolve(result.data.data);
                    } else {
                        reject(new Error("Unexpected response format or request not successful."));
                    }
                },
                error: function (xhr, status, error) {
                    reject(new Error(`AJAX error: ${status} - ${error}`));
                }
            });
        } catch (err) {
            reject(err);
        }
    });
}

function twbShowTrialFlowCreditsExpired ( from = '' ) {
    const expired = localStorage.getItem('twbb_tf_credit_expired');
    if (expired && expired === '1' && jQuery('.twbb-tf-tooltip-container').length) {
        jQuery('.MuiBox-root .twbb-tf-tooltip').addClass('twbb-tf-popup');
        jQuery('body').addClass('scroll_disclaimer');
        jQuery('.twbb-image-gen-layout, .twbb-image-gen-container, .twbb-ai-popup-layout, .twbb-ai-popup-container').addClass('low-z-index');
        twbTrialFlowSendEventFromWidgets({
            eventCategory: 'Free trial paywalls',
            eventAction: 'Upgrade popup view',
            eventLabel: 'Limitation'
        });
        return false;
    }
    return true;
}

function twbShowTrialFlowPopupOnceADay (expireDate, diffDays) {
    const endDate = new Date(expireDate);
    const now = new Date();

    if (now > endDate || diffDays > 2) return;

    let popupData = JSON.parse(localStorage.getItem('twbbTFPopup') || '{}');
    const today = new Date().toDateString();


    if (popupData.lastShown !== today && (popupData.count || 0) < 3) {

        popupData.lastShown = today;
        popupData.count = (popupData.count || 0) + 1;
        localStorage.setItem('twbbTFPopup', JSON.stringify(popupData));

        jQuery('.MuiBox-root  .twbb-tf-tooltip').addClass('twbb-tf-popup');
        jQuery('body').addClass('scroll_disclaimer');

        twbTrialFlowSendEventFromWidgets({
            eventCategory: 'Free trial paywalls',
            eventAction: 'Upgrade popup view',
            eventLabel: 'Editor: Auto-open'
        });
    }
}

const twbChangeTrialFlowElements = () => {
    jQuery('.twbb-tf-element').each(function () {
        const trialFlowElements = jQuery(this),
          active = trialFlowElements.find('.twbb-tf-element__item.active'),
          nextIndex = active.next('.twbb-tf-element__item').length ? active.next('.twbb-tf-element__item').index() : 0,
          _this = trialFlowElements.find('.twbb-tf-element__item').eq(nextIndex);
        if (!_this.hasClass('active')) {
            trialFlowElements.find('.twbb-tf-element__item').removeClass('active');
            _this.addClass('active');
        }
        if (twbTrialFlowElementsInterval === null) {
            twbTrialFlowSetAutoPlay();
        }
    })

}

const twbTrialFlowSetAutoPlay = () => {
    if (twbTrialFlowElementsInterval === null) {
        twbTrialFlowElementsInterval = setInterval(function () {
            twbChangeTrialFlowElements();
        }, 3000);
    }
}

function twbUpgradePopupAnimation() {

    jQuery(".twbb-unlock-feature-button").on("click", function() {
        jQuery(".trial-upgrade-container-layer, .trial-upgrade-container").remove();
    })

    let showFirstTime = true;
    let animateSinglePlan = false;

    // --- Class Binding ---
    jQuery(document).find(".trial-upgrade-container-layer").css('visibility','visible');
    const $container = jQuery('#trial-container');
    $container.css('visibility','visible');

    // --- Animate Timeline Steps Like Angular Animation ---
    function animateTimelineSteps() {
        const steps = jQuery('.twbb-timeline-step');
        steps.css({ opacity: 0, transform: 'translateY(-10px)' });

        steps.each(function (index) {
            setTimeout(() => {
                jQuery(this).animate({ opacity: 1, top: 0 }, {
                    step: function (now, fx) {
                        if (fx.prop === "top") {
                            jQuery(this).css("transform", `translateY(${(1 - now) * 33}px)`);
                        }
                    },
                    duration: 1000,
                    easing: 'swing'
                });
                if(index === 3) {
                    setTimeout(() => {
                        resizeModal(1000, 600);

                    },1000);
                }
            }, index * 700);
        });
    }

    // --- Simulate Modal Resize and Animation Flow ---
    function resizeModal(width, height) {
        jQuery('#trial-container')
            .css({
                width: width + 'px',
                height: height + 'px',
                transition: 'all 0.3s ease'
            })
            .one('transitionend webkitTransitionEnd oTransitionEnd', function () {
                // This will run AFTER the transition is done
                jQuery('.twbb-unlock-container').fadeIn();
            });
    }

    function simulateLifecycle() {
            animateSinglePlan = true;
            showFirstTime = false;
            animateTimelineSteps();
            updateTimelineContent()
    }

    simulateLifecycle();


    function updateTimelineContent() {
        const stepStatuses = [true, true, true, false];

        jQuery('.twbb-timeline-step').each(function (index) {
            const completed = stepStatuses[index];

            jQuery(this)
                .removeClass('completed pending')
                .addClass(completed ? 'completed' : 'pending');
        });
    }
}
