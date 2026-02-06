<?php
$user_info = get_site_option(TENWEB_PREFIX . '_user_info');
$trial_days = 3;
$is_reseller = false;
if( is_array($user_info) && !empty($user_info) &&
    isset($user_info['agreement_info']['plan']['services']['optimizer']['trial_period']['parameter_value']) ) {
    $trial_days = $user_info['agreement_info']['plan']['services']['optimizer']['trial_period']['parameter_value'];
    $is_reseller = $user_info['agreement_info']['plan_title'] === 'Free Reseller Trial' ? true : false;
}
?>
<script type="text/template" id="twbb-tf-tooltip-container">
    <div class="twbb-tf-tooltip-container hidden">
        <div class="twbb-tf-element">
            <div class="twbb-tf-element__left">
                <?php if ( !$is_reseller ) : ?>
                <div class="twbb-tf-element__credits twbb-tf-element__item active">
                    <div><span class="credits"><?php esc_html_e('0/30', 'tenweb-builder');?></span><?php esc_html_e(' AI credits used', 'tenweb-builder');?></div>
                    <div class="progress credits-progress">
                        <div class="fill credits-progress-fill"></div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="twbb-tf-element__days twbb-tf-element__item">
                    <div><span class="days-left"><?php esc_html_e($trial_days . ' days', 'tenweb-builder');?></span> <?php esc_html_e('left in trial', 'tenweb-builder');?></div>
                    <div class="progress days-progress">
                        <div class="fill days-progress-fill"></div>
                    </div>
                </div>
                <?php if ( !$is_reseller ) : ?>
                <div class="twbb-tf-element__cost"><?php esc_html_e('Any AI request costs 1 credit', 'tenweb-builder');?></div>
                <?php endif; ?>
            </div>
            <div class="twbb-tf-element__right">
                <a href="<?php echo esc_url( TENWEB_DASHBOARD . '/websites?showUpgradePopup'); ?>" target="_blank" class="upgrade-button" onclick="twbTrialFlowSendEvent(this, 'button')"><?php esc_html_e('Upgrade', 'tenweb-builder');?></a>
            </div>
        </div>
        <div class="twbb-tf-tooltip">
            <div class="twbb-tf-tooltip__content">
                <div class="twbb-tf-tooltip__days">
                    <div class="twbb-tf-tooltip__days-header">
                        <div class="twbb-tf-tooltip__days-title"><?php esc_html_e('10Web free trial', 'tenweb-builder');?></div>
                        <div class="twbb-tf-tooltip__days-left"><span class="days-left"><?php esc_html_e('2 days', 'tenweb-builder');?></span> <?php esc_html_e('left', 'tenweb-builder');?></div>
                    </div>
                    <div class="twbb-tf-tooltip__days-sub-title ht"><span><?php esc_html_e($trial_days);?></span> <?php esc_html_e(' days for free', 'tenweb-builder');?></div>
                    <div class="twbb-tf-tooltip__days-progress progress days-progress">
                        <div class="fill days-progress-fill"></div>
                    </div>
                    <div class="msg red one-day-left"><?php esc_html_e('Trial ends today—don’t lose your website.', 'tenweb-builder');?><br>
	                    <?php esc_html_e('Secure your site & get more credits with 10Web Pro.', 'tenweb-builder');?></div>
                </div>
                <?php if ( !$is_reseller ) : ?>
                <div class="twbb-tf-tooltip__credits">
                    <div class="twbb-tf-tooltip__credits-header">
                        <img src="<?php echo esc_url( TWBB_URL . '/Apps/TrialFlow/assets/images/star.svg' );?>"
                             class="star">
                        <div class="twbb-tf-tooltip__credits-title"><?php esc_html_e(' AI credits used', 'tenweb-builder');?></div>
                        <div class="twbb-tf-tooltip__credits-progress-container">
                            <div class="twbb-tf-tooltip__credits-progress progress credits-progress">
                                <div class="fill credits-progress-fill"></div>
                            </div>
                            <div class="credits"><?php esc_html_e('0/30', 'tenweb-builder');?></div>
                        </div>
                    </div>
                    <div class="twbb-tf-tooltip__credits-footer ht"><?php esc_html_e('Any AI request costs 1 credit', 'tenweb-builder');?></div>
                    <div class="msg orange half-used"><?php esc_html_e('Seems you’re enjoying building with AI.', 'tenweb-builder');?><br>
	                    <?php esc_html_e('Go Pro to get more credits & secure your site.', 'tenweb-builder');?></div>
                    <div class="msg red limitation-expired"><?php esc_html_e('You’ve used all your trial AI credits.', 'tenweb-builder');?><br>
	                    <?php esc_html_e('Get more and keep building with 10Web Pro.', 'tenweb-builder');?></div>
                </div>
                <?php endif; ?>
                <div class="text"><?php esc_html_e('Secure your site & get more credits with 10Web Pro.', 'tenweb-builder');?></div>
                <a href="<?php echo esc_url( TENWEB_DASHBOARD . '/websites?showUpgradePopup'); ?>" target="_blank" class="upgrade-button" onclick="twbTrialFlowSendEvent(this, 'tooltip')"><?php esc_html_e('Upgrade', 'tenweb-builder');?></a>
                <a href="#" class="got-it"><?php esc_html_e('Got it', 'tenweb-builder');?></a>
            </div>
        </div>
    </div>
    <div class="twbb-tf-tooltip-overlay"></div>
</script>

<script type="text/template" id="twbb-tf-edit-website-popup">
    <div class="twbb-tf-edit-website-popup-layer">
    <div class="twbb-tf-edit-website-popup-container">
            <div class="twbb-tf-edit-website-popup-container-steps">
                <div class="twbb-tf-edit-website-popup-steps-content">
                    <span class="twbb-tf-edit-website-popup-steps-title"><?php esc_html_e('Next steps to launch your site', 'tenweb-builder'); ?></span>
                    <div class="progress-container">
                        <div class="progress-line"></div>

                        <div class="step-circle-container" data-top="0">
                            <span class="step-circle"></span>
                            <div class="step-content">
                                <span class="step-title">Generate website</span>
                                <span class="step-description">Your AI-powered website is ready!</span>
                            </div>
                        </div>

                        <div class="step-circle-container" data-top="33">
                            <span class="step-circle"></span>
                            <div class="step-content">
                                <span class="step-title">Unlock free trial</span>
                                <span class="step-description">You’ve unlocked <?php esc_html_e($trial_days);?> days of full access</span>
                            </div>
                        </div>

                        <div class="step-circle-container" data-top="66">
                            <span class="step-circle"></span>
                            <div class="step-content">
                                <span class="step-title">Customize & edit</span>
                                <span class="step-description">Use AI to shape it your way, no code needed</span>
                            </div>
                        </div>

                        <div class="step-circle-container" data-top="100">
                            <span class="step-circle"></span>
                            <div class="step-content">
                                <span class="step-title">Upgrade & go live</span>
                                <span class="step-description">Upgrade & go live</span>
                            </div>
                        </div>
                    </div>
                    <div class="twbb-tf-edit-website-popup-steps-trustpilot-container">
                        <span class="twbb-tf-trustpilot-title">1.5M+ websites created  with 10Web AI.</span>
                    </div>
                </div>
            </div>
            <div class="twbb-tf-edit-website-popup-container_unlock">
                <div class="twbb-tf-edit-website-popup-unlock-content">

                </div>
            </div>
    </div>
    </div>
</script>

<?php
/* Check if option which set during the frontend canfetti popup */
if( get_option("twbb-trial-flow-canfetti") === '1' ) {
    $images_folder_url = TWBB_URL . '/Apps/TrialFlow/assets/images/';
?>
<div class="trial-upgrade-container-layer"></div>
<div id="trial-container" class="trial-upgrade-container">
    <div class="twbb-unlock-container">
        <h1 class="twbb-unlock-title"><?php esc_html_e("You have unlocked $trial_days-day free trial.",'tenweb-builder'); ?></h1>
        <div class="twbb-unlock-feature-item"><img class="icon" src="<?php echo esc_url($images_folder_url . 'chat_icon.svg'); ?>" alt=""><?php esc_html_e("Edit your website by simply chatting with AI",'tenweb-builder'); ?></div>
        <div class="twbb-unlock-feature-item"><img class="icon" src="<?php echo esc_url($images_folder_url . 'star.svg'); ?>" alt=""><?php esc_html_e("Generate and personalize text and images",'tenweb-builder'); ?></div>
        <div class="twbb-unlock-feature-item"><img class="icon" src="<?php echo esc_url($images_folder_url . 'section_icon.svg'); ?>" alt=""><?php esc_html_e("Create full sections with a single prompt",'tenweb-builder'); ?></div>
        <div class="twbb-unlock-feature-item"><img class="icon" src="<?php echo esc_url($images_folder_url . 'add_image_icon.svg'); ?>" alt=""><?php esc_html_e("Rebuild any section by uploading a screenshot",'tenweb-builder'); ?></div>
        <span class="twbb-unlock-feature-button"><?php esc_html_e("Start editing",'tenweb-builder'); ?></span>
    </div>
    <div class="twbb-steps-container">
        <h1 class="twbb-steps-container-title"><?php esc_html_e("Next steps to launch your site",'tenweb-builder'); ?></h1>

        <div class="twbb-timeline-steps">
            <div class="twbb-timeline-step">
                <div class="step-icon ds-checkmark-sharp">
                    <img src="<?php echo esc_url($images_folder_url . 'checkmark.svg'); ?>">
                </div>
                <div class="step-connector">
                    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="140" viewBox="0 0 2 140" fill="none">
                        <path d="M1 0L1 140" stroke="#C1C1C6"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="205" viewBox="0 0 2 205" fill="none">
                        <path d="M1 0L1.00001 205" stroke="black" stroke-opacity="0.2" stroke-dasharray="2 2"/>
                    </svg>
                </div>
                <div class="step-content">
                    <h3 class="step-title"><?php esc_html_e("Generate website",'tenweb-builder'); ?></h3>
                    <p class="step-description"><?php esc_html_e("Your AI-powered website is ready!",'tenweb-builder'); ?></p>
                </div>
            </div>

            <div class="twbb-timeline-step">
                <div class="step-icon ds-checkmark-sharp">
                    <img src="<?php echo esc_url($images_folder_url . 'checkmark.svg'); ?>">
                </div>
                <div class="step-content">
                    <h3 class="step-title"><?php esc_html_e("Unlock free trial",'tenweb-builder'); ?></h3>
                    <p class="step-description"><?php esc_html_e("You’ve unlocked $trial_days days of full access",'tenweb-builder'); ?></p>
                </div>
            </div>

            <div class="twbb-timeline-step">
                <div class="step-icon ds-icon-star">
                    <img src="<?php echo esc_url($images_folder_url . 'white_star.svg'); ?>">
                </div>
                <div class="step-content">
                    <h3 class="step-title"><?php esc_html_e("Customize & edit",'tenweb-builder'); ?></h3>
                    <p class="step-description"><?php esc_html_e("Use AI to shape it your way, no code needed",'tenweb-builder'); ?></p>
                </div>
            </div>

            <div class="twbb-timeline-step">
                <div class="step-icon ds-diamond-new">
                    <img src="<?php echo esc_url($images_folder_url . 'diamond.svg'); ?>">
                </div>
                <div class="step-content">
                    <h3 class="step-title"><?php esc_html_e("Upgrade & go live",'tenweb-builder'); ?></h3>
                    <p class="step-description"><?php esc_html_e("Your AI-powered website is ready!",'tenweb-builder'); ?></p>
                </div>
            </div>
        </div>
        <div class="twbb-steps-footer-container">
            <div class="twbb-steps-footer-title">
                <span>1.5M+</span> websites created with 10Web AI.
            </div>
            <div class="twbb-trustpilot-widget trustpilot-widget" data-locale="en-US" data-template-id="5419b6ffb0d04a076446a9af" data-businessunit-id="5da032d65a294d00014a14c3" data-style-height="20px" data-style-width="435px">
            </div>
        </div>
    </div>
</div>
<?php
    update_option("twbb-trial-flow-canfetti", "2");
} ?>
