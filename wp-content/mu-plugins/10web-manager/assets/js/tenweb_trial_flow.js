let twbTrialFlowElementsInterval = null;

jQuery(document).ready(function($) {
  if (!$('#twbb-tf-tooltip-container').length) {
    const tooltip = `<div id="twbb-tf-tooltip-container" class="hidden">
        <div class="twbb-tf-element">
            <div class="twbb-tf-element__left">
                <div class="twbb-tf-element__days twbb-tf-element__item active">
                    <div><span class="days-left">3 days</span> left in trial</div>
                    <div class="progress days-progress">
                        <div class="fill days-progress-fill"></div>
                    </div>
                </div>
                <div class="twbb-tf-element__credits twbb-tf-element__item">
                    <div><span class="credits">0/30</span> AI credits used</div>
                    <div class="progress credits-progress">
                        <div class="fill credits-progress-fill"></div>
                    </div>
                </div>
                <div class="twbb-tf-element__cost">Any AI request costs 1 credit</div>
            </div>
            <div class="twbb-tf-element__right">
                <a href="${tenweb_trial_flow.dashboard}/websites?showUpgradePopup" target="_blank" class="upgrade-button"  onclick="tenwebTrialFlowSendEvent(this, 'button')">Upgrade</a>
            </div>
        </div>
        <div class="twbb-tf-tooltip">
            <div class="twbb-tf-tooltip__content">
                <div class="twbb-tf-tooltip__days">
                    <div class="twbb-tf-tooltip__days-header">
                        <div class="twbb-tf-tooltip__days-title">10Web free trial</div>
                        <div class="twbb-tf-tooltip__days-left"><span class="days-left">2 days</span> left</div>
                    </div>
                    <div class="twbb-tf-tooltip__days-sub-title ht"><span>3</span> days for free</div>
                    <div class="twbb-tf-tooltip__days-progress progress days-progress">
                        <div class="fill days-progress-fill"></div>
                    </div>
                    <div class="msg red one-day-left">Trial ends today—don’t lose your website.<br>
\t                    Secure your site & get more credits with 10Web Pro.</div>
                </div>
                <div class="twbb-tf-tooltip__credits">
                    <div class="twbb-tf-tooltip__credits-header">
                       <img src="${tenweb_trial_flow.plugin_url}/assets/images/star.svg"
                             class="star">
                        <div class="twbb-tf-tooltip__credits-title"> AI credits used</div>
                        <div class="twbb-tf-tooltip__credits-progress-container">
                            <div class="twbb-tf-tooltip__credits-progress progress credits-progress">
                                <div class="fill credits-progress-fill"></div>
                            </div>
                            <div class="credits">0/30</div>
                        </div>
                    </div>
                    <div class="twbb-tf-tooltip__credits-footer ht">Any AI request costs 1 credit</div>
                    <div class="msg orange half-used">Seems you’re enjoying building with AI.<br>
\t                    Go Pro to get more credits & secure your site.</div>
                    <div class="msg red limitation-expired">You’ve used all your trial AI credits.<br>
\t                    Get more and keep building with 10Web Pro.</div>
                </div>
                <div class="text">Secure your site & get more credits with 10Web Pro.</div>
                <a href="${tenweb_trial_flow.dashboard}/websites?showUpgradePopup" target="_blank" class="upgrade-button"  onclick="tenwebTrialFlowSendEvent(this, 'tooltip')">Upgrade</a>
                <a href="#" class="got-it">Got it</a>
            </div>
        </div>
    </div>
    <div class="twbb-tf-tooltip-overlay"></div>`;
    $('#wp-admin-bar-tenweb-trial-flow').append(tooltip);
  }
  tenwebUpdateLimitation();

  jQuery(document).on('click', '.twbb-tf-tooltip.twbb-tf-popup', function () {
    tenwebCloseTrialFlowPopup();
  });
  jQuery(document).on('click', '.twbb-tf-tooltip.twbb-tf-popup .got-it', function () {
    tenwebSendEventToPublicRouth({
      eventCategory: 'Free trial paywalls',
      eventAction: 'Upgrade popup view',
      eventLabel: 'WPAdmin: paywall pop up'
    });
    tenwebCloseTrialFlowPopup();
  });
  if (matchMedia('screen and (max-width: 1160px)').matches) {
    tenwebTrialFlowSetAutoPlay();
  }
});

function tenwebUpdateLimitation () {
  const TFTooltip = jQuery('#twbb-tf-tooltip-container'),
    dayProgress = TFTooltip.find('.days-progress .fill'),
    creditProgress = TFTooltip.find('.credits-progress .fill'),
    dayLeft = TFTooltip.find('.days-left'),
    credits = TFTooltip.find('.credits');
  let tFClass = '';

  jQuery.ajax({
    type: 'POST',
    url: tenweb_trial_flow.ajaxurl,
    dataType: 'json',
    data: {
      'action': 'tenweb_get_trial_limits',
      'nonce': tenweb_trial_flow.ajaxnonce,
    }
  }).success(function (result) {
    if (result.success) {
      const data = result.data.data,
        now = new Date();
      if (data.hosting_trial_expire_date) {
        const diffTime = new Date(data.hosting_trial_expire_date) - now, // in milliseconds
          trialTime = new Date(data.hosting_trial_expire_date) - new Date(tenweb_trial_flow.agreement_date),
          trialDays = Math.ceil(trialTime / (1000 * 60 * 60 * 24));
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        diffDays = diffDays < 1 ? 1 : diffDays;
        const days = diffDays > 1 ? ' days' : ' day';

        const already_used = data.already_used > data.plan_limit ? data.plan_limit : data.already_used;

        jQuery('.twbb-tf-tooltip__days-sub-title span').html(trialDays);
        if (data.plan_limit <= already_used) {
          tFClass = 'limitation-expired';
        } else if (data.plan_limit / 2 <= already_used) {
          tFClass = 'half-used';
        }
        if (diffDays <= 1) {
          tFClass += ' one-day-left';
        }
        TFTooltip.addClass(tFClass).removeClass('hidden');
        jQuery('#wp-admin-bar-tenweb-trial-flow').removeClass('hidden');
        jQuery('html').addClass('with-trial-flow');

        // Update days and credits
        dayLeft.html(diffDays + days);
        credits.html(already_used + '/' + data.plan_limit);

        // Progress width
        dayProgress.width((diffDays * 100 / 3) + '%');
        creditProgress.width((already_used / data.plan_limit * 100) + '%');

        tenwebShowTrialFlowPopupOnceADay(data.hosting_trial_expire_date, diffDays);
      }
    }
  }).error(function () {
    return false;
  });

}

function tenwebShowTrialFlowPopupOnceADay (expireDate, diffDays) {
  const endDate = new Date(expireDate);
  const now = new Date();

  if (now > endDate || diffDays > 2) return;

  let popupData = JSON.parse(localStorage.getItem('tenwebTFPopup') || '{}');
  const today = new Date().toDateString();


  if (popupData.lastShown !== today && (popupData.count || 0) < 3) {

    popupData.lastShown = today;
    popupData.count = (popupData.count || 0) + 1;
    localStorage.setItem('tenwebTFPopup', JSON.stringify(popupData));
    jQuery('.twbb-tf-tooltip, .twbb-tf-tooltip-overlay').addClass('twbb-tf-popup');
    jQuery('body').addClass('scroll_disclaimer');
  }
}

function tenwebCloseTrialFlowPopup () {
  jQuery('.twbb-tf-tooltip, .twbb-tf-tooltip-overlay').removeClass('twbb-tf-popup');
  jQuery('body').removeClass('scroll_disclaimer');
}

const tenwebChangeTrialFlowElements = () => {
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
      tenwebTrialFlowSetAutoPlay();
    }
  })

}

const tenwebTrialFlowSetAutoPlay = () => {
  if (twbTrialFlowElementsInterval === null) {
    twbTrialFlowElementsInterval = setInterval(function () {
      tenwebChangeTrialFlowElements();
    }, 3000);
  }
}

function tenwebTrialFlowSendEvent(el, type = '') {
  let label = '';
  if (!jQuery(el).closest('.twbb-tf-popup').length) {
    label = type === 'tooltip' ? 'WPAdmin: top bar tooltip' : 'WPAdmin: top bar button';
  }
  else {
    label = 'WPAdmin: paywall pop up';
  }

  tenwebSendEventToPublicRouth({
    eventCategory: 'Free trial paywalls',
    eventAction: 'Upgrade button click',
    eventLabel: label
  });
}

function tenwebSendEventToPublicRouth(data){
  try {
    const sendData = Object.keys(data).reduce((newEntities, k) => {
      const newKey = k.split(/(?=[A-Z])/).join('_').toLowerCase();
      newEntities[newKey] = data[k];
      return newEntities;
    }, {});
    sendData.client_id = tenweb_trial_flow.clients_id;
    jQuery.ajax({
      type: 'POST',
      headers: { Accept: 'application/x.10webcore.v1+json' },
      url: tenweb_trial_flow.send_ga_event,
      dataType: 'json',
      data: sendData,
      success: function (result) {
      },
      error: function (xhr, status, error) {
        reject(new Error(`AJAX error: ${status} - ${error}`));
      }
    });
  }
  catch (error) {
    console.log('Error sending the events: ', error);
  }
}

