<div class="twbb-tf-top-bar" style="display:none">
    <div class="twbb-tf-top-bar__container">
        <div class="twbb-tf-top-bar__left">
            <div class="twbb-tf-top-bar__logo">
                <img src="<?php echo esc_url( TWBB_URL . '/Apps/TrialFlow/assets/images/logo.svg' );?>">
            </div>
            <div class="twbb-tf-top-bar__responsive" data-active="desktop">
                <div class="twbb-tf-top-bar__desktop device active" data-id="desktop">
                    <img src="<?php echo esc_url( TWBB_URL . '/Apps/TrialFlow/assets/images/desktop.svg' );?>"
                         class="desktop">
                </div>
                <div class="twbb-tf-top-bar__mobile device" data-id="mobile">
                    <img src="<?php echo esc_url( TWBB_URL . '/Apps/TrialFlow/assets/images/mobile.svg' );?>"
                         class="mobile">
                </div>
            </div>
        </div>
        <div class="twbb-tf-top-bar__right">
            <div class="text">
                <div class="twbb-tf-people-animation-description"><?php echo esc_html(wp_rand(200,300)); ?> people already</div>
                <div class="twbb-tf-people-description">editing websites — join them!</div>
            </div>
            <div class="twbb-tf-people-images">
                <span class="twbb-tf-people-item"></span>
                <span class="twbb-tf-people-item"></span>
                <span class="twbb-tf-people-item"></span>
                <span class="twbb-tf-people-item"></span>
                <span class="twbb-tf-people-item"></span>
            </div>
            <span class="twbb-tf-top-bar__edit-button">Edit your website</span>
        </div>
    </div>
</div>
<div id="twbb-tf-mobile-iframe-container">
    <iframe id="twbb-tf-mobile-iframe" src=""></iframe>
</div>

<div class="twbb-tf-wesite-ready-layer" style="display:none">
    <div class="twbb-tf-wesite-ready-container">
        <span class="twbb-tf-wesite-ready-title"><?php esc_html_e('Your website is ready!', 'tenweb-builder'); ?></span>
        <span class="twbb-tf-wesite-ready-description"><?php esc_html_e('Successfully created with AI.', 'tenweb-builder'); ?></span>
        <span class="twbb-tf-wesite-ready-button"><?php esc_html_e('Preview my website', 'tenweb-builder'); ?></span>
    </div>
</div>

