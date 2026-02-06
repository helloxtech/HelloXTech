<?php
$tour_status = get_option('twbb-coPilot-tour-status');
$show_tour = false;
$chat_additional_class = '';
$twbb_show_co_pilot_tour = get_option('twbb_show_co_pilot_tour');
if( $tour_status === false ) {
    update_option('twbb-coPilot-tour-status',1);
    $chat_additional_class = 'twbb_open_chat';
    if($twbb_show_co_pilot_tour === 'yes'){
        $show_tour = true;
        $chat_additional_class ='';
    }
}
$show_trial_notif = \Tenweb_Builder\Modules\Utils::visibilityCheck();

$copilot_chat_title = "10Web AI Copilot";
if(TENWEB_WHITE_LABEL){
    $copilot_chat_title = "AI Copilot";
}
?>

<!--Empty chat container template-->
<script type="text/template" id="twbb-copilot-main-icon-template">
    <div class="twbb-copilot-minimized-container-tooltip">
        <span class="twbb-copilot-minimized-tooltip-text">An error occurred during execution. Please try again.</span>
        <span class="twbb-copilot-minimized-tooltip-close-button"></span>
    </div>

    <div id="twbb-copilot-minimized-container">
        <?php if( !empty($chat_additional_class) ) :?>
            <div class="twbb_copilot_chat_introduction_popup">
                <span class="twbb_copilot_chat_introduction_popup_icon"></span>
                <span class="twbb_copilot_chat_introduction_popup_title">Hi, ready to edit your page?</span>
                <span class="twbb_copilot_chat_introduction_popup_desc">Just let me know what changes you need, and I’ll take care of it!</span>
            </div>
        <?php endif;?>
        <?php if($show_trial_notif) :?>
            <div class="twbb_copilot_credits_notif">
                <span class="twbb_copilot_credits_notif_icon"></span>
                <div class="twbb_copilot_credits_used_info">
                    <span class="twbb_copilot_credits_count_info"><span class="twbb_copilot_credits_count">0/0</span> AI credits used</span>
                    <span class="twbb_copilot_more_credits_notif">Need more AI credits?</span>
                    <a class="twbb_copilot_upgrade_pro_link" href="<?php echo esc_url( TENWEB_DASHBOARD . '/websites?showUpgradePopup'); ?>" target="_blank">Upgrade to Pro</a>
                </div>
                <div class="twbb_copilot_credits_notif_close_container">
                    <span class="twbb_copilot_credits_notif_close"></span>
                </div>
            </div>
        <?php endif;?>



        <div class="twbb-copilot-minimized-chat_container">
            <div id="twbb-copilot-last-message_history" class="twbb-copilot-message_history scrolled_top"></div>
        </div>
        <span class="twbb-copilot-main-icon"></span>
        <span id="twbb-copilot-minimized-text">How can I help you?</span>
        <span id="twbb-copilot-minimized-chat-right-icon">
            <span class="twbb-full-chat-tooltip">Full chat</span>
        </span>
    </div>
</script>


    <!--Empty chat container template-->
<script type="text/template" id="twbb-copilot-template" >
    <div id="twbb-copilot-chat_container"  style="display: none">
        <input type="text" hidden id="twbb_temp_input_for_copy" value="">
        <div id="twbb-copilot-header">
            <div class="twbb-copilot-header-logo"><?php echo esc_html($copilot_chat_title);?><span class="twbb-copilot-alpha">Beta</span></div>
            <div class="twbb-copilot-header-settings">
                <span class="twbb-copilot-tooltip">Menu</span>
                <div class="twbb-copilot-header-settings-menu-container">
                    <div class="twbb-copilot-header-settings-menu">
                        <span class="twbb-copilot-header-settings-menu-item twbb-copilot-header-settings-menu-item-inactive twbb-copilot-clear-chat">Clear Chat</span>
                    </div>
                </div>
            </div>
            <div class="twbb-copilot-header-minimize"><span class="twbb-copilot-tooltip">Collapse</span></div>
        </div>
        <div id="twbb-copilot-message_history" class="twbb-copilot-message_history">
            <div class="twbb-copilot-feedback-highlights-blure-container"></div>
        </div>
        <div class="twbb-copilot-no-widget-container">
            <div class="twbb-copilot-no-widget-content">
                <span class="twbb-copilot-no-widget-icon"></span>
                <span class="twbb-copilot-no-widget-title">Hi, ready to edit your page?</span>
                <span class="twbb-copilot-no-widget-message">Just let me know what changes you need, and I’ll take care of it!</span>
            </div>
            </span>
        </div>

        <?php if($show_trial_notif) :?>
        <div class="twbb_copilot_credits_notif">
            <span class="twbb_copilot_credits_notif_icon"></span>
            <div class="twbb_copilot_credits_used_info">
                <span class="twbb_copilot_credits_count_info"><span class="twbb_copilot_credits_count">0/0</span> AI credits used</span>
                <span class="twbb_copilot_more_credits_notif">Need more AI credits?</span>
                <a class="twbb_copilot_upgrade_pro_link" href="<?php echo esc_url( TENWEB_DASHBOARD . '/websites?showUpgradePopup'); ?>" target="_blank">Upgrade to Pro</a>
            </div>
            <div class="twbb_copilot_credits_notif_close_container">
                <span class="twbb_copilot_credits_notif_close"></span>
            </div>
        </div>
        <?php endif;?>
        <div id="twbb-copilot-info-row">
            <span class="twbb-copilot-file-input-error">Unsupported file type.<br> Only JPG and PNG are accepted.</span>
        </div>
        <div id="twbb-copilot-footer" >
            <div class="twbb_copilot_images_preview"></div>
            <div class="twbb-copilot_input_container"><textarea id="twbb-copilot-user_input" placeholder="How can AI Copilot help you?"></textarea></div>
            <div class="twbb-copilot_actions_container">
                <span class="twbb-copilot-main-icon"></span>
                <div id="twbb-copilot-actions_container" class="twbb-copilot-actions_container">
                    <label class="twbb-copilot-upload_image_button">
                        <input type="file" id="twbb-copilot-file-input" hidden multiple>
                        <span class="twbb-copilot-upload_image_button-tooltip">Add image</span>
                    </label>
                    <div class="twbb-copilot-add_section_button">
                        <span>Sections</span>
                        <span class="twbb-copilot-add_section_button-tooltip">Add section</span>
                    </div>
                </div>

                <div class="twbb-copilot_buttons_container">
                    <div id="twbb-copilot-speech-to-text-container">
                        <div class="twbb-copilot-speech-to-text-view">
                            <span class="twbb_recording_timer_container">
                                 <span class="twbb_recording_timer_icon"></span>
                                 <span class="twbb_recording_timer">00:00</span>
                            </span>
                            <div class="twbb-speech-to-text-waveform"></div>
                            <span class="twbb_cancel_recording">

                            </span>
                        </div>

                        <span class="twbb_stop_recording">
                            <span class="twbb_stop_recording-tooltip">Done</span>
                        </span>
                    </div>
                    <div class="twbb-copilot_buttons">
                        <span id="twbb-copilot-speech-to-text">
                            <span class="twbb_voice_enable_permission_notice">Access restricted. Change<br>browser settings to enable<br>permission.</span>
                            <span class="twbb-copilot-speech-to-text-tooltip">Voice chat</span>
                        </span>
                        <div id="twbb-copilot-chat_button" class="twbb-copilot-chat_button-inactive">
                            <span class="twbb-copilot-chat_button-tooltip">Send</span>
                            <span class="twbb-copilot-chat_button-cancel-tooltip">Stop</span>
                        </div>
                        <div class="twbb-copilot-inprogress-message-container">
                        <p class="twbb-copilot-inprogress-message-title">Another request is in progress.</p>
                        <p>Please wait until the current process finishes.</p>
                    </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="twbb-copilot-feedback-reason-container" style="display: none">
            <i class="twbb-copilot-feedback-reason-close"></i>
            <div class="twbb-copilot-feedback-reason-title">How can we improve?</div>
            <textarea class="twbb-copilot-feedback-reason-description" placeholder="Tell us what went wrong..."></textarea>
            <div class="twbb-copilot-feedback-reason-button-row">
                <span class="twbb-copilot-feedback-reason-button twbb-copilot-feedback-reason-button-deactive">Submit</span>
            </div>
        </div>

</script>

<!--Feedback form template-->
<script type="text/template" id="twbb-copilot-feedback-template">
    <div class="twbb-copilot_chat_feedback">
        <div class="twbb-copilot-feedback-good twbb-copilot-feedback-score"><span class="twbb-copilot-tooltip">Good</span></div>
        <div class="twbb-copilot-feedback-bad-container twbb-copilot-feedback-score">
            <i class="twbb-copilot-feedback-bad"></i>
            <span class="twbb-copilot-tooltip">Bad</span>
        </div>
        <div class="twbb-copilot-undo twbb-copilot-feedback-score"><span class="twbb-copilot-tooltip">Undo</span></div>
    </div>
</script>

<!--Message assistant template -->
<script type="text/template" id="twbb-copilot-message-assistant-template">
    <div class="twbb-copilot-message twbb-ai-message twbb-copilot-message-row twbb-copilot-message-row-empty">
        <div class="twbb-copilot-message-container">
            <div class="twbb-copilot-steps">
                <div class="twbb-copilot-steps-head">
                    <span class="twbb-copilot-steps-head-icon"></span>
                    <span class="twbb-copilot-steps-head-title twbb-copilot-steps-title-actions">Actions</span>
                    <span class="twbb-copilot-steps-head-title twbb-copilot-steps-title-summary">Summary</span>
                </div>
                <ul class="twbb-copilot-steps-list"></ul>
            </div>
            <div class="twbb-copilot-message-text"></div>
        </div>
    </div>
</script>

<!--Message user template -->
<script type="text/template" id="twbb-copilot-message-user-template">
    <div class="twbb-copilot-message twbb-user-message twbb-copilot-message-row twbb-copilot-message-row-empty">
        <div class="twbb-copilot-message-images"></div>
        <div class="twbb-copilot-message-container">
            <div class="twbb-copilot-message-text"></div>
            <span class="twbb-user-message_copy">
                <span class="twbb-user-message_copy-tooltip twbb_copilot_tooltip">Copy</span>
            </span>
        </div>
    </div>
</script>

<!--Copilot request loadind template -->
<script type="text/template" id="twbb-copilot-request-loading-template">
    <div class="twbb-copilot-request-loading">
        <span class="twbb-copilot-request-loading-icon"></span>
        <span class="twbb-copilot-request-loading-text">
            <span>T</span>
            <span>h</span>
            <span>i</span>
            <span>n</span>
            <span>k</span>
            <span>i</span>
            <span>n</span>
            <span>g</span>
            <span>...</span>
        </span>
    </div>
</script>

<?php
if( $show_tour ) {
?>
<!--Copilot tour template -->
<script type="text/template" id="twbb-copilot-tour-template">
    <div class="twbb-copilot-tour-layer"></div>
    <div class="twbb-copilot-tour-container">
        <div class="twbb-copilot-tour-descr-cont">
            <div class="twbb-copilot-tour-welcome">Welcome to</div>
            <?php
            $copilot_title = "10Web AI Co-Pilot";
            if(TENWEB_WHITE_LABEL){
                $copilot_title = "AI Co-Pilot";
            }
            ?>
            <div class="twbb-copilot-tour-title"><?php echo esc_html($copilot_title);?><span class="twbb-copilot-alpha">Beta</span></div>
            <div class="twbb-copilot-tour-description">You're one of the first to try our new Co-Pilot, built to simplify website editing and page creation.</div>

            <div class="twbb-copilot-images-row">
                <div class="twbb-copilot-image-item" data-name="select">Select
                    <div class="twbb-copilot-circle-loader" style="display: none">
                        <!-- Embed the SVG you uploaded -->
                        <svg width="14" height="12" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" stroke="#3498db" stroke-width="10" fill="none" />
                            <!-- This is the blue progress circle -->
                            <circle cx="50" cy="50" r="45" stroke="#ffffff" stroke-width="10" fill="none" class="twbb-copilot-progress-circle" />
                        </svg>
                    </div>
                </div>
                <div class="twbb-copilot-image-item" data-name="describe">Describe
                    <div class="twbb-copilot-circle-loader" style="display: none">
                        <!-- Embed the SVG you uploaded -->
                        <svg width="14" height="12" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" stroke="#3498db" stroke-width="10" fill="none" />
                            <!-- This is the blue progress circle -->
                            <circle cx="50" cy="50" r="45" stroke="#ffffff" stroke-width="10" fill="none" class="twbb-copilot-progress-circle" />
                        </svg>
                    </div>
                </div>
                <div class="twbb-copilot-image-item" data-name="evaluate">Evaluate the result
                    <div class="twbb-copilot-circle-loader" style="display: none">
                        <!-- Embed the SVG you uploaded -->
                        <svg width="14" height="12" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" stroke="#3498db" stroke-width="10" fill="none" />
                            <!-- This is the blue progress circle -->
                            <circle cx="50" cy="50" r="45" stroke="#ffffff" stroke-width="10" fill="none" class="twbb-copilot-progress-circle" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="twbb-copilot-tour-subtitle">Edit visual elements</div>
            <div class="twbb-copilot-tour-subdescr">Select an element, describe the changes you want (text,images, or styles), and the Co-Pilot will take care of it for you.</div>
            <div class="twbb-copilot-tour-subtitle">Add sections or visual elements</div>
            <?php
            $copilot_title = "10Web AI Co-Pilot";
            if(TENWEB_WHITE_LABEL){
                $copilot_title = "AI Co-Pilot";
            }
            ?>
            <div class="twbb-copilot-tour-subdescr">Describe the section or element you want to add, and <?php echo esc_html($copilot_title);?> will seamlessly create it for you.</div>
            <div class="twbb-copilot-tour-button-descr">Your feedback is vital in this Beta phase, and we truly appreciate your support!</div>
            <div class="twbb-copilot-tour-button">Let’s Try</div>
        </div>
        <div class="twbb-copilot-tour-video-cont">
            <img src="<?php echo esc_url(TWBB_URL.'/Apps/CoPilot/assets/images/select.jpg'); ?>" id="twbb-copilot-img-select" style="display: none">
            <img src="<?php echo esc_url(TWBB_URL.'/Apps/CoPilot/assets/images/describe.jpg'); ?>" id="twbb-copilot-img-describe" style="display: none">
            <img src="<?php echo esc_url(TWBB_URL.'/Apps/CoPilot/assets/images/evaluate.jpg'); ?>" id="twbb-copilot-img-evaluate" style="display: none">
        </div>
    </div>
</script>
<?php } ?>
