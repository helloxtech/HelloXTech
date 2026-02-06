<script type="text/template" id="twbb-top-banner-container">
    <div class="twbb-top-banner-main-container">
        <div class="twbb-banner-left-image-container">
            <img src="<?php echo esc_url( TWBB_URL . '/Apps/TopBanner/assets/images/top_banner_left_image.png' ); ?>" alt="Top Banner Left Image">
        </div>
        <div class="twbb-banner-right-content-container">
            <div class="twbb-banner-content-text">
                <?php echo esc_html( isset( $banner_data['content_text'] ) ? $banner_data['content_text'] : '' ); ?>
                <span class="twbb-banner-countdown">00:00:00</span>
            </div>
            <div class="twbb-banner-upgrade-link">
                <a href="<?php echo esc_url( isset( $banner_data['button_link'] ) ? $banner_data['button_link'] : '#' ); ?>">
                    <?php echo esc_html( isset( $banner_data['button_text'] ) ? $banner_data['button_text'] : 'Upgrade & save 30%' ); ?>
                </a>
            </div>
        </div>
    </div>
</script>
