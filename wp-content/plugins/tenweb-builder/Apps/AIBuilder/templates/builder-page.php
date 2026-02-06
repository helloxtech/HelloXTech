<?php
$generated = (int) get_option('twbb-generated-count', 0);
$items = [
   'generate' => [
       'title' => esc_html__('Generate new website', 'tenweb-builder'),
       'desc' => esc_html__('Create an entirely new website from scratch using AI. Just describe what you need, and the AI will 
generate layouts,', 'tenweb-builder') . '<br/>' . esc_html__('apply your design preferences, and build a complete, functional site in minutes.', 'tenweb-builder'),
       'button_text' => esc_html__('Generate website', 'tenweb-builder'),
       'button_class' => 'blue',
   ]
];
if ($generated > 0 || get_option('twbb_imported_site_data_generated', false)) {
	$items = [
		'single_page' => [
			'title' => esc_html__('Add new page with AI', 'tenweb-builder'),
			'desc' => esc_html__('Add a new page to your site effortlessly. Describe what it should include, and AI will design and 
build it to match', 'tenweb-builder') . '<br/>' . esc_html__('your current site’s look and feel.', 'tenweb-builder'),
			'button_text' => esc_html__('Generate new page', 'tenweb-builder'),
			'button_class' => 'blue',
		],
		'generate' => [
			'title' => esc_html__('Regenerate the entire website', 'tenweb-builder'),
			'desc' => esc_html__('Start fresh by regenerating your entire website. ', 'tenweb-builder') . '<br/><b class="note"><span class="star">*</span>' . esc_html__(' Please note that your existing website will be permanently removed during this process.', 'tenweb-builder') . '</b>',
			'button_text' => esc_html__('Regenerate', 'tenweb-builder'),
			'button_class' => 'white',
			'generation_count' => $generated
		]
	]; 
}

$logo = defined('TENWEB_COMPANY_RESELLER_LOGO') ? TENWEB_COMPANY_RESELLER_LOGO : TWBB_URL . '/Apps/AIBuilder/assets/images/icons/ai.svg';
$company_name = (defined('TENWEB_COMPANY_NAME') && TENWEB_COMPANY_NAME !== 'AI') ?  TENWEB_COMPANY_NAME : '';

?>

<div class="twbb-ai-builder-page">
    <div class="twbb-ai-builder-page__container">
        <h1><img src="<?php echo esc_url($logo); ?>"
                 class="ai"> <?php echo esc_html__($company_name, 'tenweb-builder'); ?> <?php esc_html_e('AI Website Builder', 'tenweb-builder');?></h1>
        <div class="twbb-ai-builder-page__content">
            <?php foreach ($items as $key => $item) { ?>
                <div class="twbb-ai-builder-page__item <?php echo $key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
                    <div class="twbb-ai-builder-page__item-left">
                        <h3 class="twbb-ai-builder-page__item-title"><?php echo $item['title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
                        <p class="twbb-ai-builder-page__item-desc"><?php echo $item['desc']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                    </div>

                    <div class="twbb-ai-builder-page__item-right">
                        <?php if ($key === 'generate' && TWBB_RESELLER_MODE) { ?>
                            <span class="generation-count hidden">
                                <span class="text"><?php esc_html_e('Generation count:', 'tenweb-builder');?></span>
                                <span id="generated_count"><?php echo isset($item['generation_count']) ? esc_html__($item['generation_count'], 'tenweb-builder') : ''; ?></span>
                                <?php esc_html_e('of ', 'tenweb-builder');?>
                                <span id="generation_count"></span>
                            </span>
	                    <?php } ?>
                        <div class="twbb-ai-builder-page__button-container  <?php esc_attr_e($key); ?>">
                            <span class="reached_text"><?php esc_html_e('You\'ve reached your generation limit. Contact your hosting provider for assistance.', 'tenweb-builder');?></span>
                            <a href="#" class=" <?php esc_attr_e($item['button_class']);?> twbb-ai-builder-page__button"
                               data-id="<?php esc_attr_e($key);?>"><?php esc_html_e($item['button_text'], 'tenweb-builder');?></a>
                        </div>
                    </div>
                </div>
	        <?php } ?>
        </div>
    </div>
</div>
<?php require_once(TWBB_DIR . '/Apps/AIBuilder/templates/popup.php'); ?>
