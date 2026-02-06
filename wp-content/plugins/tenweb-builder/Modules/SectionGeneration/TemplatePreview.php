<?php

namespace Tenweb_Builder\Modules\SectionGeneration;

class TemplatePreview {
  protected static $instance = null;

  protected $templateId = null;
  protected $templatePreviewFrom = null;
  protected $isTemplatePreviewActive = false;

  public function updateElementorCss($postObj) {
     $postObj->update();
  }

  public function loadHeader($name){
      \Tenweb_Builder\HeaderTemplate::print_twbb_template(0, $name, true);
  }

  public function loadFooter($name){
      \Tenweb_Builder\FooterTemplate::print_twbb_template(0, $name, true);
  }

  public function templateRedirect($template){
    return TWBB_DIR . '/templates/views/preview_template.php';
  }

  public function addBodyClass($classes){
    $classes[] = 'twbb_template_preview_page';
    if(get_option('elementor_experiment-co_pilot') === 'active') {
      $classes[] = 'twbb_template_preview_page--co_pilot_active';
    }
    return $classes;
  }

  public function validateGetParams(){

    if(empty($_GET['twbb_template_preview']) || empty($_GET['twbb_template_preview_from']) || empty($_GET['twbb_template_preview_nonce'])) {
      return false;
    }

    if(!wp_verify_nonce(
        sanitize_text_field( $_GET['twbb_template_preview_nonce'] ),
        'twbb_template_preview')
    ) {
      return false;
    }

    $this->templateId = sanitize_text_field($_GET['twbb_template_preview']);
    $this->templatePreviewFrom = sanitize_text_field($_GET['twbb_template_preview_from']);

    return true;
  }

  public function isTemplatePreviewActive(){
    return $this->isTemplatePreviewActive;
  }

  /**
   * This function checks can user edit post/page with id from $_GET['template_preview_from']
   */
  public function userCanEdit(){
    $user_id = get_current_user_id();
    if(!$user_id) {
      return false;
    }

    $post = get_post($this->templatePreviewFrom);
    $post_type = get_post_type_object($post->post_type);
    // For elementor_library, check if user can edit Elementor templates
    if($post->post_type === 'elementor_library') {
      return current_user_can('edit_posts') && current_user_can('edit_pages');
    }
    if( empty($post_type) ) {
        return false;
    }

    $cap = ($user_id === $post->post_author && !empty($post_type->cap)) ? $post_type->cap->edit_posts : $post_type->cap->edit_others_posts;

    return current_user_can($cap);
  }

  public function getNonce(){
    return wp_create_nonce('twbb_template_preview');
  }

  public static function getInstance(){
    if(is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  protected function __construct(){
    if($this->validateGetParams() && $this->userCanEdit()) {
        $this->init();
    }
  }

  protected function init(){
    $this->isTemplatePreviewActive = true;

    \Elementor\Utils::do_not_cache();

    // add empty header and footer templates
    add_action('get_header', [$this, 'loadHeader'], 999999);
    add_action('get_footer', [$this, 'loadFooter'], 999999);

    add_action( 'elementor/frontend/after_register_scripts', array( $this, 'enqueueEmbedScripts' ) );
    add_action( 'elementor/frontend/after_register_styles', array( $this, 'enqueueEmbedStyles' ) );
    // load wp template for preview. It contains only header, the_content(), and footer
    add_filter('template_include', [$this, 'templateRedirect'], 999999);
    add_filter('body_class', [$this, 'addBodyClass']);
    add_action( 'elementor/css-file/before_enqueue', [$this,'updateElementorCss'] );
    add_filter('show_admin_bar', '__return_false');//phpcs:ignore WordPressVIPMinimum.UserExperience.AdminBarRemoval.RemovalDetected
    // remove query monitor from sections preview
    add_filter('qm/dispatch/html', '__return_false');
  }
    public function enqueueEmbedStyles() {
        if ( TWBB_DEV === TRUE ) {
            wp_enqueue_style(
                'twbb-section-generation-embed-style',
                TWBB_URL . '/Apps/SectionGeneration/assets/style/section_generation_embed.css',
                [],
                TWBB_VERSION
            );
        } else {
            wp_enqueue_style(
                'twbb-section-generation-embed-style',
                TWBB_URL . '/Apps/SectionGeneration/assets/style/section_generation_embed.min.css',
                [],
                TWBB_VERSION
            );
        }
    }

    public function enqueueEmbedScripts() {
	    $clients_id = 0;
	    if ( class_exists( '\Tenweb_Manager\Manager' ) ) {
		    $user_agreements_info = \Tenweb_Manager\Helper::get_tenweb_user_info()[ 'agreement_info'];
		    if ( is_array($user_agreements_info) && !empty($user_agreements_info) ) {
			    $clients_id = isset( $user_agreements_info['clients_id'] ) ? $user_agreements_info['clients_id'] : 0;
			 }
	    }
        $twbb_sg_nonce = wp_create_nonce('twbb-sg-nonce');
        wp_register_script( 'twbb-editor-helper-script', TWBB_URL . '/assets/editor/js/helper-script.js', array('jquery'), TWBB_VERSION);
        wp_localize_script( 'twbb-editor-helper-script', 'twbb_helper', array(
                'domain_id' => get_option('tenweb_domain_id'),
                'send_ga_event' => defined('TENWEB_SEND_GA_EVENT') ? TENWEB_SEND_GA_EVENT : 'https://core.10web.io/api/send-ga-event',
	            'clients_id' => $clients_id
	        )
        );
        if ( TWBB_DEV === TRUE ) {
            //TWBB_URL . '/ai/assets/js/request.js' is not enqueueing because it called from main body of the page
            wp_enqueue_script(
                'twbb-section-generation-embed-script',
                TWBB_URL . '/Apps/SectionGeneration/assets/script/section_generation_embed.js',
                ['jquery','twbb-editor-helper-script'],
                TWBB_VERSION,
                TRUE
            );
        } else {
            wp_enqueue_script(
                'twbb-section-generation-embed-script',
                TWBB_URL . '/Apps/SectionGeneration/assets/script/section_generation_embed.min.js',
                ['jquery','twbb-editor-helper-script'],
                TWBB_VERSION,
                TRUE
            );
        }
        $site_description = get_option('twbb_site_description', []);
        $business_description = isset($site_description['description']) ? $site_description['description'] : '';
        wp_localize_script(
            'twbb-section-generation-embed-script',
            'twbb_sg_embed',
            array(
                'twbb_sg_nonce' => $twbb_sg_nonce,
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'business_description' => $business_description,
                'ecommerce_label' => esc_html__('Best for stores', 'tenweb-builder'),
            )
        );
    }
}
