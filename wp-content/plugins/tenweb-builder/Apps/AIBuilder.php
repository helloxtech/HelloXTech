<?php

namespace Tenweb_Builder\Apps;

use Elementor\Utils;
use Tenweb_Builder\Import;

class AIBuilder extends BaseApp
{
	private string $prefix = 'twbb_ai_builder';
	protected static $instance = null;

	public static function getInstance(){
		if(is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct()
	{
		$this->process();
	}

	private function process()
	{
		if ( self::visibilityCheck() ) {
			$this->addActions();
		}
	}

	private function addActions()
	{
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_footer', array( $this, 'add_custom_button_to_pages_list' ) );
		add_action( 'wp_ajax_twbb_import_template', array( $this, 'import_template' ) );
		add_action( 'wp_ajax_twbb_check_woocommerce', array( $this, 'check_woocommerce' ) );
	}

	public function check_woocommerce() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/misc.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

		$plugin_slug = 'woocommerce';
		$plugin_main_file = $plugin_slug . '/' . $plugin_slug . '.php';

		if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_main_file)) {
			// Get plugin info from WordPress.org
			$api = \plugins_api('plugin_information', [
				'slug' => $plugin_slug,
				'fields' => ['sections' => false],
			]);

			if (is_wp_error($api)) {
				wp_send_json_error(['error' => 'Failed to install plugin.']);
				wp_die();
			}

			$upgrader = new \Plugin_Upgrader(new \WP_Ajax_Upgrader_Skin());
			$installed = $upgrader->install($api->download_link);

			if (!$installed) {
				wp_send_json_error(['error' => 'Failed to install plugin.']);
				wp_die();
			}
		}

		if (!is_plugin_active($plugin_main_file)) {
			activate_plugin($plugin_main_file);
		}
		wp_send_json_success(['status' => 'success', 'msg' => $plugin_slug . ' installed successfully.']);
		wp_die();
	}

	public function import_template() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		if ( !wp_verify_nonce( $nonce, $this->prefix . '_nonce' ) ) {
			wp_send_json_error( 'invalid_nonce' );
			wp_die();
		}
		if ( ! isset( $_POST[ 'url' ] ) || ! isset( $_POST[ 'ai2_action' ] )) {
			wp_send_json_error();
		}
		$template_url = sanitize_text_field( $_POST[ 'url' ] );
		$ai2_action = sanitize_text_field( $_POST[ 'ai2_action' ] );
		$website_data = isset( $_POST['website_data'] ) ? sanitize_text_field( $_POST[ 'website_data' ] ) : '';

		if ($ai2_action !== 'build_secondary_page') {
			update_option( 'twbb-import-website-data', $website_data );
		}
		$delete_last_imported_data = ! ( $ai2_action === 'build_secondary_page' );
		include_once TWBB_DIR . '/templates/import/import.php';
		$import = new Import('ai_regenerate', $delete_last_imported_data, null, null, true);
		$result = $import->import_template( ['template_url' => $template_url, 'ai2_action' => $ai2_action] );
        $import->finalize_import(0, 'bulk');
		wp_send_json_success( $result );
		wp_die();
	}

	public function add_custom_button_to_pages_list(){
		require_once(TWBB_DIR . '/Apps/AIBuilder/templates/popup.php');
		$screen = get_current_screen();

		if (!current_user_can('manage_options')) return;

		if ($screen->id !== 'edit-page') return;

		$this->enqueue_scripts();
		$this->enqueue_styles();
	}

	public function admin_menu(){
		$admin_page = add_menu_page(
            esc_html__( 'AI Website Builder', 'tenweb-builder'),
            esc_html__( 'AI Builder', 'tenweb-builder'),
			'manage_options',
			$this->prefix ,
			array( $this, 'admin_page' ),
			TWBB_URL . '/Apps/AIBuilder/assets/images/icons/ai-builder.svg',
			2
		);

		add_action( 'admin_print_scripts-' . $admin_page, array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page, array( $this, 'enqueue_styles' ) );

	}

	public function admin_page() {
		require_once(TWBB_DIR . '/Apps/AIBuilder/templates/builder-page.php');
	}

	public function enqueue_scripts() {
		$twbb_ai_builder_nonce = wp_create_nonce( $this->prefix . '_nonce' );
		$posts_count = wp_count_posts()->publish;
		$woocommerce_active = class_exists('WooCommerce');
		$shop_page_id = get_option('woocommerce_shop_page_id');
		$products_count = isset(wp_count_posts('product')->publish) ? wp_count_posts('product')->publish : 0;

        $domainId = get_site_option('tenweb_domain_id');
        $clients_id = 0;
        if ( class_exists( '\Tenweb_Manager\Manager' ) ) {
            $user_agreements_info = \Tenweb_Manager\Helper::get_tenweb_user_info()[ 'agreement_info'];
            if ( is_array($user_agreements_info) && !empty($user_agreements_info) ) {
                $clients_id = isset( $user_agreements_info['clients_id'] ) ? $user_agreements_info['clients_id'] : 0;
            }
        }
        wp_enqueue_script( 'twbb-editor-helper-script', TWBB_URL . '/assets/editor/js/helper-script.js', array('jquery'), TWBB_VERSION, TRUE );
        wp_localize_script( 'twbb-editor-helper-script', 'twbb_helper', array(
            'domain_id' => $domainId,
            'send_ga_event' => defined('TENWEB_SEND_GA_EVENT') ? TENWEB_SEND_GA_EVENT : 'https://core.10web.io/api/send-ga-event',
            'clients_id' => $clients_id
        ));

		wp_enqueue_script( $this->prefix . '_outline_js', TWBB_URL . '/Apps/AIBuilder/assets/elements/outline.js', ['twbb-editor-helper-script'], TWBB_VERSION, true );
		if ( TWBB_DEV === true ) {
			wp_enqueue_script( $this->prefix . '_requests', TWBB_URL . '/Apps/AIBuilder/assets/script/requests.js', [ 'jquery' ], TWBB_VERSION );
			wp_enqueue_script( $this->prefix . '_script', TWBB_URL . '/Apps/AIBuilder/assets/script/script.js', [], TWBB_VERSION );

			wp_enqueue_script( $this->prefix . '_builder_page', TWBB_URL . '/Apps/AIBuilder/assets/script/builder-page.js', [ 'jquery' ], TWBB_VERSION, true );
			wp_enqueue_script( $this->prefix . '_business_type', TWBB_URL . '/Apps/AIBuilder/assets/script/business-type.js', [ 'jquery' ], TWBB_VERSION, true );
			wp_enqueue_script( $this->prefix . '_about_website', TWBB_URL . '/Apps/AIBuilder/assets/script/about-website.js', [ 'jquery' ], TWBB_VERSION, true );
			wp_enqueue_script( $this->prefix . '_ecommerce_data', TWBB_URL . '/Apps/AIBuilder/assets/script/ecommerce-data.js', [ 'jquery' ], TWBB_VERSION, true );
			wp_enqueue_script( $this->prefix . '_website_style', TWBB_URL . '/Apps/AIBuilder/assets/script/website-style.js', [ 'jquery' ], TWBB_VERSION, true );
			wp_enqueue_script( $this->prefix . '_outline', TWBB_URL . '/Apps/AIBuilder/assets/script/outline.js', [ 'jquery' ], TWBB_VERSION, true );
			wp_enqueue_script( $this->prefix . '_generation', TWBB_URL . '/Apps/AIBuilder/assets/script/generation.js', [ 'jquery' ], TWBB_VERSION, true );
			wp_enqueue_script( $this->prefix . '_website_type', TWBB_URL . '/Apps/AIBuilder/assets/script/website-type.js', [ 'jquery', $this->prefix . '_script' ], TWBB_VERSION, true );
			wp_enqueue_script( $this->prefix . '_builder_script', TWBB_URL . '/Apps/AIBuilder/assets/script/TwbbAIBuilder.js', [ 'jquery', $this->prefix . '_business_type' ], TWBB_VERSION, true );
		} else {
			wp_enqueue_script(
				$this->prefix . '_script',
				TWBB_URL . '/Apps/AIBuilder/assets/script/TwbbAIBuilder.min.js',
				[ 'jquery', 'twbb-editor-helper-script'],
				TWBB_VERSION,
				true
			);
		}

        $builder_api = defined('TENWEB_AI_ASSISTANT') ? TENWEB_AI_ASSISTANT : '';
        if( TWBB_RESELLER_MODE ) {
            $builder_api = defined('TENWEB_BUILDER_API') ? TENWEB_BUILDER_API : 'https://api.ai-website-builder.net/';
        }
		wp_localize_script(
			$this->prefix . '_script',
			$this->prefix,
			[
				'prefix' => $this->prefix,
				'twbb_generate_nonce' => $twbb_ai_builder_nonce,
				'send_ga_event' => $builder_api . 'send-ga-event',
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'admin_post_url' => admin_url( 'post.php' ),
                'elements_path' => TWBB_URL . '/Apps/AIBuilder/assets/elements/',
				'home_url' => site_url(),
                'recaptcha_key' => TENWEB_GOOGLE_RECAPTCHA,
				'posts_count' => $posts_count,
				'woocommerce_active' => $woocommerce_active,
				'shop_page_id' => $shop_page_id,
				'products_count' => $products_count,
				'imported_site_data' => get_option('twbb-import-website-data'),
				'builder_api' => $builder_api,
                'reseller_mode' => TWBB_RESELLER_MODE,
                'twbb_fe_service' => TENWEB_FE_SERVICE,
                'access_token' => \Tenweb_Builder\Modules\ai\Utils::get_access_token(),
                'domain_id' => \Tenweb_Builder\Modules\ai\Utils::get_domain_id(),
                'workspace_id' => \Tenweb_Builder\Modules\ai\Utils::get_workspace_id(),
                'lang' => get_locale(),
				'builder_kit_api_key' => defined('AI_BUILDER_KIT_API_KEY') ? AI_BUILDER_KIT_API_KEY : '',
				'image_url' => esc_url( TWBB_URL . '/Apps/AIBuilder/assets/images/' ),
				'leave_popup_title' => esc_html__('Are you sure you want to leave this page?', 'tenweb-builder'),
				'retry_button' => esc_html__('Retry', 'tenweb-builder'),
				'retry_title' => esc_html__('Something went wrong :/', 'tenweb-builder'),
				'retry_description' => esc_html__('We encountered an error while generating your website. <br>Please retry the generation again.', 'tenweb-builder'),
				'retry_single_page_description' => esc_html__('We encountered an error while generating your page. <br>Please retry the generation again.', 'tenweb-builder'),
				'preview_edit' => esc_html__('Preview & edit', 'tenweb-builder'),
				'leave_popup_desc' => esc_html__('Changes you made may not be saved.', 'tenweb-builder'),
				'congrats_popup_title' => esc_html__('Congrats!', 'tenweb-builder'),
				'congrats_popup_desc' => esc_html__('Your brand-new website has been successfully created with AI. <br>Take a moment to preview and make any final edits before going live.', 'tenweb-builder'),
				'congrats_popup_desc_mobile' => esc_html__('Your brand-new website has been successfully created with AI. <br>To edit your website, please visit it on Desktop.', 'tenweb-builder'),
				'congrats_popup_single_page_desc' => esc_html__('Your new page has been successfully created with AI. <br>Take a moment to preview  and make any final edits before going live.', 'tenweb-builder'),
				'congrats_popup_button_mobile' => esc_html__('Got It', 'tenweb-builder'),
				'business_type_basic_title' => __('What type of website are you creating?', 'tenweb-builder'),
				'business_type_ecommerce_title' => __('What type of online store are you creating?', 'tenweb-builder'),
				'business_type_basic_label' => __('Please select your business type', 'tenweb-builder'),
				'business_type_ecommerce_label' => __('Please select your store type', 'tenweb-builder'),
				'business_type_basic_placeholder' => __('Search for your business or site type', 'tenweb-builder'),
				'business_type_ecommerce_placeholder' => __('Search for your store type', 'tenweb-builder'),
				'about_website_ecommerce_title' => __('What’s your store about?', 'tenweb-builder'),
				'about_website_ecommerce_desc' => __('Add details about your products and brand.', 'tenweb-builder'),
				'about_website_basic_title' => __('What’s your site about?', 'tenweb-builder'),
				'about_website_basic_desc' => __('Add details about your products and brand.', 'tenweb-builder'),
				'about_website_basic_example' =>  __('<div class="fade-text">“An online music school providing an extensive 
				selection of courses,<br> live interactive lessons, and personalized instruction from expert<br> musicians, 
				designed to cater to learners of all skill levels and musical interests.”</div>
                    <div class="fade-text">“MediaGo is a startup that provides a mobile app for instant<br> access to
                        news and entertainment. The app is designed to be<br> easy to navigate and streamlined for
			quick use.”</div>
                    <div class="fade-text">“Our cozy family restaurant serves delicious home-style Italian meals,
                        accompanied by warm hospitality, creating the perfect<br> atmosphere for enjoying quality time
		with loved ones.”</div>
                    <div class="fade-text">“Our tech blog covering the latest trends, innovations, and gadgets,<br>
                                                                                                                          offering expert insights and analysis to keep readers informed<br> on the evolving tech
                        landscape.”</div>
                    <div class="fade-text">“As a full stack developer with over 5 years of experience in web<br>
                        development, I specialize in creating responsive, user-friendly<br> websites and stay passionate
                        about exploring new web technologies.”</div>', 'tenweb-builder'),
				'about_website_ecommerce_example' =>  __('<div class="fade-text ecommerce ">“We sell handmade items, 
from artisan jewelry to unique home decor, each crafted with care and creativity.”</div><div class="fade-text ecommerce ">
“We provide products for pets, including comfortable beds, nutritious food, and engaging toys to support pet wellness.”
</div><div class="fade-text ecommerce ">“We offer tech essentials like laptops, smart devices, and accessories, all 
designed for everyday functionality and innovation.”</div><div class="fade-text ecommerce ">“We sell wellness 
products, including skincare and aromatherapy items, to promote relaxation and self-care.”</div>', 'tenweb-builder'),
				'steps' => [
				      [
					      "label" => __("Creating Home page", 'tenweb-builder')
				      ],
				      [
					      "label" => __("Creating additional pages", 'tenweb-builder'),
				          "isEcommerce" => false
				      ],
				      [
					      "label" => __("Creating product pages", 'tenweb-builder'),
				          "isEcommerce" => true
				      ],
				      [
					      "label" => __("Preparing layout", 'tenweb-builder')
				      ],
		              [
					      "label" => __("Generating the content of the website", 'tenweb-builder')
				      ],
					  [
					      "label" => __("Generating the images of the website", 'tenweb-builder')
				      ],
					  [
					      "label" => __("Making your website mobile friendly", 'tenweb-builder')
				      ],
					  [
					      "label" => __("Finalizing", 'tenweb-builder')
				      ]
	           ],
				'mobile_steps' => [
				      [
				          "label" => __("Preparing layout", 'tenweb-builder')
				      ],
				      [
					      "label" => __("Generating the content of the page", 'tenweb-builder')
				      ],
				      [
					      "label" => __("Generating the images of the page", 'tenweb-builder')
				      ],
		              [
					      "label" => __("Making your website mobile friendly", 'tenweb-builder')
				      ],
					  [
					      "label" => __("Finalizing", 'tenweb-builder')
				      ]
	           ],
				'generating_title' => __('Generating your personalized AI website', 'tenweb-builder'),
				'generating_single_page_title' => __('Generating your personalized AI page', 'tenweb-builder'),
				'about_website_single_page_title' => __('What\'s your page about?', 'tenweb-builder'),
				'about_website_single_page_desc' => __('This helps us guide your experience.', 'tenweb-builder'),
				'about_website_single_page_title_label' => __('Enter page title', 'tenweb-builder'),
				'about_website_single_page_title_placeholder' => __('Enter your page title', 'tenweb-builder'),
				'about_website_single_page_desc_label' => __('Describe your page', 'tenweb-builder'),
				'about_website_single_page_desc_placeholder' => __('Describe your page
Ex. A brief company introduction. Include team member bios and their roles, with a friendly and professional tone.', 'tenweb-builder'),
				'outline_single_page_title' => __('AI is creating your page structure', 'tenweb-builder'),
				'outline_modify_single_page_title' => __('Modify your page\'s structure', 'tenweb-builder'),
				'outline_modify_title' => __('Modify your site\'s structure', 'tenweb-builder'),
				'outline_modify_desc' => __('Help AI generate a personalized page.', 'tenweb-builder'),
			]
		);
	}

	public function enqueue_styles() {

		wp_enqueue_style( $this->prefix . '_outline_style', TWBB_URL . '/Apps/AIBuilder/assets/elements/style.css', [], TWBB_VERSION );
		wp_enqueue_style( $this->prefix . '_outline_styles', TWBB_URL . '/Apps/AIBuilder/assets/elements/styles.css', [], TWBB_VERSION );
		wp_enqueue_style( $this->prefix . 'fonts', TWBB_URL . '/assets/frontend/css/fonts.css', array(), TWBB_VERSION );
		if ( TWBB_DEV === true ) {
			wp_enqueue_style( $this->prefix . '_style', TWBB_URL . '/Apps/AIBuilder/assets/style/builder-page.css', [], TWBB_VERSION );
			wp_enqueue_style( $this->prefix . '_popup', TWBB_URL . '/Apps/AIBuilder/assets/style/popup.css', [], TWBB_VERSION );
			wp_enqueue_style( $this->prefix . '_website_type', TWBB_URL . '/Apps/AIBuilder/assets/style/website-type.css', [], TWBB_VERSION );
			wp_enqueue_style( $this->prefix . '_business_type', TWBB_URL . '/Apps/AIBuilder/assets/style/business-type.css', [], TWBB_VERSION );
			wp_enqueue_style( $this->prefix . '_about_website', TWBB_URL . '/Apps/AIBuilder/assets/style/about-website.css', [], TWBB_VERSION );
			wp_enqueue_style( $this->prefix . '_ecommerce_data', TWBB_URL . '/Apps/AIBuilder/assets/style/ecommerce-data.css', [], TWBB_VERSION );
			wp_enqueue_style( $this->prefix . '_outline', TWBB_URL . '/Apps/AIBuilder/assets/style/outline.css', [], TWBB_VERSION );
			wp_enqueue_style( $this->prefix . '_website_style', TWBB_URL . '/Apps/AIBuilder/assets/style/website-style.css', [], TWBB_VERSION );
			wp_enqueue_style( $this->prefix . '_generation', TWBB_URL . '/Apps/AIBuilder/assets/style/generation.css', [], TWBB_VERSION );
		} else {
			wp_enqueue_style(
				$this->prefix . '_style',
				TWBB_URL . '/Apps/AIBuilder/assets/style/TwbbAIBuilder.min.css',
				[],
				TWBB_VERSION
			);
		}
	}

    private static function visibilityCheck(): bool{
       return (defined('AI_BUILDER_KIT_API_KEY') ||
           get_option('twbb_allow_import_from_wordpress', false));
    }

}
