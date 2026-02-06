<?php
namespace Tenweb_Builder;
include_once (TWBB_DIR . '/widgets/traits/Logo_Trait.php');

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Tenweb_Builder\Widgets\Traits\Logo_Trait;
use Elementor\Widget_Base;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Logo extends Widget_Base {
	use Logo_Trait;

	public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);
		$this->register_logo_styles();
	}

	public function on_save( $data ){
		$url = isset($data['logo']['url']) ? $data['logo']['url'] : '';
		$id = isset($data['logo']['id']) ? $data['logo']['id'] : 0;
		if ( $url && $id) {
			set_theme_mod( 'custom_logo', $id );
		}
		return $data;
	}


	public function get_name() {
		return 'twbb_logo';
	}

	public function get_title() {
		return esc_html__( 'Logo', 'tenweb-builder' );
	}

	public function get_icon() {
		return 'twbb-logo twbb-widget-icon';
	}

	public function get_style_depends() {
		return ['twbb-logo-style'];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'tenweb-builder' ),
			]
		);

		$this->register_logo_content_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'content_style',
			[
				'label' => esc_html__( 'Content', 'tenweb-builder' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->register_logo_style_controls();

		$this->end_controls_section();
	}

	public function render() {
		$this->render_logo( $this );
	}
}

\Elementor\Plugin::instance()->widgets_manager->register( new Logo() );
