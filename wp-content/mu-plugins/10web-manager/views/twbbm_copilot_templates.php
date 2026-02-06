<script type="text/template" id="twbbm-copilot-tour-template">
    <div class="twbbm-copilot-tour-layer"></div>
    <div class="twbbm-copilot-tour-container">
        <div class="twbbm-copilot-tour-descr-cont">
            <div class="twbbm-copilot-tour-welcome">Introducing</div>
            <div class="twbbm-copilot-tour-title">10Web AI Co-Pilot</div>
            <div class="twbbm-copilot-tour-description">You're one of the first to try our new Co-Pilot, <br>built to simplify website editing and page creation.</div>

            <div class="twbbm-copilot-images-row">
                <div class="twbbm-copilot-image-item" data-name="select">Select
                    <div class="twbbm-copilot-circle-loader" style="display: none">
                        <!-- Embed the SVG you uploaded -->
                        <svg width="14" height="12" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" stroke="#3498db" stroke-width="10" fill="none" />
                            <!-- This is the blue progress circle -->
                            <circle cx="50" cy="50" r="45" stroke="#ffffff" stroke-width="10" fill="none" class="twbbm-copilot-progress-circle" />
                        </svg>
                    </div>
                </div>
                <div class="twbbm-copilot-image-item" data-name="describe">Describe
                    <div class="twbbm-copilot-circle-loader" style="display: none">
                        <!-- Embed the SVG you uploaded -->
                        <svg width="14" height="12" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" stroke="#3498db" stroke-width="10" fill="none" />
                            <!-- This is the blue progress circle -->
                            <circle cx="50" cy="50" r="45" stroke="#ffffff" stroke-width="10" fill="none" class="twbbm-copilot-progress-circle" />
                        </svg>
                    </div>
                </div>
                <div class="twbbm-copilot-image-item" data-name="evaluate">Evaluate the result
                    <div class="twbbm-copilot-circle-loader" style="display: none">
                        <!-- Embed the SVG you uploaded -->
                        <svg width="14" height="12" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" stroke="#3498db" stroke-width="10" fill="none" />
                            <!-- This is the blue progress circle -->
                            <circle cx="50" cy="50" r="45" stroke="#ffffff" stroke-width="10" fill="none" class="twbbm-copilot-progress-circle" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="twbbm-copilot-tour-subtitle">Edit visual elements</div>
            <div class="twbbm-copilot-tour-subdescr">Select an element, describe the changes you want (text,<br>images, or styles), and the Co-Pilot will take care of it for you.</div>

            <div class="twbbm-copilot-tour-subtitle">Add sections or visual elements</div>
            <div class="twbbm-copilot-tour-subdescr">Describe the section or element you want to add, and <br>10Web Co-Pilot will seamlessly create it for you.</div>


            <div class="twbbm-copilot-tour-subdescr twbbm-copilot-tour-update-text">To access 10Web AI Co-Pilot, please update to the latest version <br>of the 10Web Builder plugin.</div>
            <div class="twbbm-copilot-tour-button"><span class="twbbm-copilot-tour-button_text">Update the 10Web Builder Now</span></div>
            <div class="twbbm-copilot-tour-button-descr">If your Elementor plugin is outdated, it will be updated <br>automatically.</div>
        </div>
        <div class="twbbm-copilot-tour-video-cont">
            <div class="twbbm-copilot-tour_remind_button"><span class="twbbm-copilot-tour_remind_button_icon"></span><span class="twbbm-copilot-tour_remind_button_text">Remind me later</span></div>
            <img src="<?php echo esc_url(TENWEB_URL_IMG . '/CoPilot/select.jpg'); ?>" id="twbbm-copilot-img-select" style="display: none">
            <img src="<?php echo esc_url(TENWEB_URL_IMG . '/CoPilot/describe.jpg'); ?>" id="twbbm-copilot-img-describe" style="display: none">
            <img src="<?php echo esc_url(TENWEB_URL_IMG . '/CoPilot/evaluate.jpg'); ?>" id="twbbm-copilot-img-evaluate" style="display: none">
        </div>
    </div>
</script>