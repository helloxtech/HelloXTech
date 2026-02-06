<?php
namespace Tenweb_Builder;

include_once(TWBB_DIR . '/widgets/ai-dynamic-features/skins/skin-base.php');
include_once(TWBB_DIR . '/widgets/ai-dynamic-features/skins/skin-1.php');
include_once(TWBB_DIR . '/widgets/ai-dynamic-features/skins/skin-2.php');
include_once(TWBB_DIR . '/widgets/ai-dynamic-features/skins/skin-3.php');
include_once(TWBB_DIR . '/widgets/ai-dynamic-features/skins/skin-4.php');
include_once(TWBB_DIR . '/widgets/ai-dynamic-features/skins/skin-5.php');
include_once(TWBB_DIR . '/widgets/ai-dynamic-features/skins/skin-6.php');
include_once(TWBB_DIR . '/widgets/ai-dynamic-features/skins/skin-7.php');

use Elementor\Widget_Base;
use Tenweb_Builder\Widgets\Traits\Button_Trait;

if ( ! defined( 'ABSPATH' ) ) exit;

class AI_Dynamic_Features extends Widget_Base {
	use Button_Trait;

	protected $_has_template_content = false;

	public function get_name() {
		return 'twbb_dynamic_features';
	}

	public function get_title() {
		return __('Dynamic Features', 'tenweb-builder');
	}

	public function get_icon() {
		return 'twbb-dynamic-features twbb-widget-icon';
	}

	public function get_categories() {
		return ['tenweb-widgets'];
	}

	public function get_keywords() {
		return ['services', 'products', 'benefits', 'tabs', 'features', 'showcase'];
	}

	public function get_script_depends() {
		return ['twbb-ai-dynamic-features-scripts', 'twbb-ai-dynamic-features-swiper-scripts'];
	}

	public function get_style_depends() {
		return ['ai-dynamic-features'];
	}

	protected function register_skins() {
	  $this->add_skin(new \Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins\Skin_1( $this ));
	  $this->add_skin(new \Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins\Skin_2( $this ));
	  $this->add_skin(new \Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins\Skin_3( $this ));
	  $this->add_skin(new \Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins\Skin_4( $this ));
	  $this->add_skin(new \Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins\Skin_5( $this ));
	  $this->add_skin(new \Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins\Skin_6( $this ));
	  $this->add_skin(new \Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins\Skin_7( $this ));
    }
	protected function register_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __('Layout', 'tenweb-builder'),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content',
			[
				'label' => __('Title & Description', 'tenweb-builder'),
			]
		);

		$this->add_control(
			'title_field',
			[
				'label' => __('Title Field', 'tenweb-builder'),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __('Short benefit oriented headline placeholder', 'tenweb-builder'),
			]
		);

		$this->add_control(
			'description_field',
			[
				'label' => __('Description Field', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __('A user-friendly tool that uses advanced technology to simplify a complex task. It helps users create something professional in just a few minutes.', 'tenweb-builder'),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_feature_list',
			[
				'label' => __('Feature List', 'tenweb-builder'),
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'feature_title',
			[
				'label' => __('Title', 'tenweb-builder'),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __('Feature Title', 'tenweb-builder'),
				'required' => true,
			]
		);

		$repeater->add_control(
			'feature_description',
			[
				'label' => __('Description', 'tenweb-builder'),
				'show_label' => false,
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __('Feature Description', 'tenweb-builder'),
			]
		);

		$repeater->add_control(
			'media_type',
			[
				'label' => __('Media Type', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => __('Image', 'tenweb-builder'),
					'video' => __('Video', 'tenweb-builder'),
				],
				'condition' => ['media_type[value]!' => 'image'],
			]
		);

		$repeater->add_control(
			'feature_image',
			[
				'label' => __('Feature Image', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'media_type' => 'image',
				],
			]
		);

		$repeater->add_control(
			'video_type',
			[
				'label' => __('Video Type', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'file',
				'options' => [
					'file' => __('File', 'tenweb-builder'),
					'url' => __('Direct URL', 'tenweb-builder'),
					'youtube' => __('YouTube', 'tenweb-builder'),
					'vimeo' => __('Vimeo', 'tenweb-builder'),
				],
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$repeater->add_control(
			'video_file',
			[
				'label' => __('Upload Video', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'media_type' => 'video',
				'condition' => [
					'media_type' => 'video',
					'video_type' => ['file'],
				],
			]
		);

		$repeater->add_control(
			'video_url',
			[
				'label' => __('Video URL', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __('Enter your video URL', 'tenweb-builder'),
				'description' => __('YouTube or Vimeo URL, or direct video file URL', 'tenweb-builder'),
				'condition' => [
					'media_type' => 'video',
					'video_type' => ['url', 'youtube', 'vimeo'],
				],
			]
		);

		$this->add_control(
			'features_list',
			[
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'feature_title' => __('Feature 1', 'tenweb-builder'),
						'feature_description' => __('Briefly describe the first feature here. Add one more sentence to explain its benefit or how it works.', 'tenweb-builder'),
					],
					[
						'feature_title' => __('Feature 2', 'tenweb-builder'),
						'feature_description' => __('Briefly describe the second feature here. Add one more sentence to explain its benefit or how it works.', 'tenweb-builder'),
					],
					[
						'feature_title' => __('Feature 3', 'tenweb-builder'),
						'feature_description' => __('Briefly describe the third feature here. Add one more sentence to explain its benefit or how it works.', 'tenweb-builder'),
					],
					[
						'feature_title' => __('Feature 4', 'tenweb-builder'),
						'feature_description' => __('Briefly describe the fourth feature here. Add one more sentence to explain its benefit or how it works.', 'tenweb-builder'),
					],
					[
						'feature_title' => __('Feature 5', 'tenweb-builder'),
						'feature_description' => __('Briefly describe the fifth feature here. Add one more sentence to explain its benefit or how it works.', 'tenweb-builder'),
					],
				],
				'title_field' => '{{{ feature_title }}}', //phpcs:ignore WordPressVIPMinimum.Security.Mustache.OutputNotation
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_buttons',
			[
				'label' => __('Buttons', 'tenweb-builder'),
			]
		);

		$this->add_control(
			'heading_button_1',
			[
				'label' => __('Button 1', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->register_button_content_controls([
			'button_default_text' => __('Button Text', 'tenweb-builder'),
			'text_control_label' => __('Button 1 Text', 'tenweb-builder'),
			'prefix' => 'button_1_',
		]);

		$this->add_control(
			'hr_1',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_control(
			'heading_button_2',
			[
				'label' => __('Button 2', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->register_button_content_controls([
			'button_default_text' => __('Button Text', 'tenweb-builder'),
			'text_control_label' => __('Button 2 Text', 'tenweb-builder'),
			'prefix' => 'button_2_',
		]);

		$this->end_controls_section();
	}
}

\Elementor\Plugin::instance()->widgets_manager->register( new AI_Dynamic_Features() );
