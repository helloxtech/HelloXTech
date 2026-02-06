
<div class="twbb-ai-builder hidden" data-type="websiteType">
    <div class="twbb-ai-builder-overlay hidden">
        <div class="twbb-ai-builder__popup">
            <div class="twbb-ai-builder__popup-info">
                <div class="title"></div>
                <div class="desc"></div>
            </div>
            <div class="twbb-ai-builder__popup-btn-container">
                <a class="twbb-ai-builder__btn grey cancel"><?php esc_html_e('Cancel', 'tenweb-builder'); ?></a>
                <a class="twbb-ai-builder__btn black leave"><?php esc_html_e('Leave', 'tenweb-builder'); ?></a>
                <a class="twbb-ai-builder__btn black preview-edit"><?php esc_html_e('Preview & edit', 'tenweb-builder'); ?></a>
            </div>
        </div>
    </div>
	<span class="twbb-ai-builder__close"></span>
	<?php
    require_once(TWBB_DIR . '/Apps/AIBuilder/templates/website-type.php');
    require_once(TWBB_DIR . '/Apps/AIBuilder/templates/business-type.php');
    require_once(TWBB_DIR . '/Apps/AIBuilder/templates/about-website.php');
    require_once(TWBB_DIR . '/Apps/AIBuilder/templates/ecommerce-data.php');
    require_once(TWBB_DIR . '/Apps/AIBuilder/templates/outline.php');
    require_once(TWBB_DIR . '/Apps/AIBuilder/templates/website-style.php');
    require_once(TWBB_DIR . '/Apps/AIBuilder/templates/generation.php');
    ?>
	<div class="twbb-ai-builder__bottom">
		<a class="twbb-ai-builder__btn white back"><?php esc_html_e('Back', 'tenweb-builder');?></a>
        <ul class="steps">
            <li>
                <span>SITE INFO</span>
            </li>
            <li>
                <span>SITE STRUCTURE</span>
            </li>
            <li>
                <span>COLORS &amp; FONTS</span>
            </li>
        </ul>
		<a class="twbb-ai-builder__btn black disabled next"><?php esc_html_e('Next', 'tenweb-builder');?></a>
		<a class="twbb-ai-builder__btn blue generate next"><?php esc_html_e('Generate', 'tenweb-builder');?></a>
	</div>
</div>
