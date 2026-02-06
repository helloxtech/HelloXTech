<div class="twbb-ai-builder__container hidden" data-step="1" data-type="websiteType">
    <div class="twbb-ai-builder__left">
        <div class="twbb-ai-builder__left-content slide_up_text">
            <img src="<?php echo esc_url( TWBB_URL . '/Apps/AIBuilder/assets/images/icons/ai-blue.svg' );?>"
                 class="ai-blue">
            <div class="twbb-ai-builder__left-title text-slide-up" data-animate="slideUp1"><?php esc_html_e('Let’s start creating ', 'tenweb-builder');?><br><?php esc_html_e('your website with AI', 'tenweb-builder');?></div>
            <p class="twbb-ai-builder__left-desc text-slide-up" data-animate="slideUp2"><?php esc_html_e('Answer a few questions and our AI will generate a personalized website ', 'tenweb-builder');?><br><?php esc_html_e('with tailored content and images.', 'tenweb-builder') ?></p>
        </div>
    </div>
    <div class="twbb-ai-builder__right custom-scroll">
        <h2 class="twbb-ai-builder__title big"> <?php esc_html_e('What kind of website ','tenweb-builder');?><br><?php esc_html_e('do you want to create?',
                'tenweb-builder');?></h2>
        <div class="twbb-ai-builder__content">
            <div class="choose-website-type">
                <div>
                    <input type="radio" id="business" value="basic" name="website_type" class="radio twbb-input twbb-radio">
                    <label for="business"><?php esc_html_e('I want to create an informational website', 'tenweb-builder');?></label>
                </div>
                <div>
                    <input type="radio" id="ecommerce" value="ecommerce" name="website_type" class="radio twbb-input twbb-radio">
                    <label for="ecommerce"><?php esc_html_e('I want to create a website with an online store', 'tenweb-builder');?></label>
                </div>
            </div>
        </div>
    </div>
</div>
