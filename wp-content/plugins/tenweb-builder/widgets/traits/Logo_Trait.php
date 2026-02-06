<?php
namespace Tenweb_Builder\Widgets\Traits;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use Tenweb_Builder\Modules\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Logo_Trait {

	public function register_logo_styles() {
		wp_register_style( 'twbb-logo-style', TWBB_URL . '/widgets/logo/assets/style.css', [], TWBB_VERSION);
	}

	/**
	 *
	 * @since 3.4.0
	 *
	 * @param string $prefix
	 */
	protected function register_logo_content_controls() {

		$this->add_responsive_control(
			'show_logo',
			[
				'label' => esc_html__( 'Logo Icon', 'tenweb-builder' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'tenweb-builder' ),
				'label_off' => __( 'Hide', 'tenweb-builder' ),
                'default' => 'yes',
				'prefix_class' => 'twbb-logo%s-',
			]
		);
		$this->add_control(
			'logo',
			[
				'label' => __( 'Logo Icon', 'tenweb-builder' ),
				'description' => __( 'Choose an image or SVG for your logo', 'tenweb-builder' ),
				'type' => Controls_Manager::MEDIA,
				'media_types' => ['image', 'svg'],
			]
		);

		$this->add_responsive_control(
			'show_logo_text',
			[
				'label' => esc_html__( 'Logo Text', 'tenweb-builder' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'tenweb-builder' ),
				'label_off' => __( 'Hide', 'tenweb-builder' ),
				'default' => 'yes',
				'prefix_class' => 'twbb-logo-text%s-',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'logo_text',
			[
				'label' => __( 'Logo Text', 'tenweb-builder' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'logo_icon_position',
			[
				'label' => __( 'Logo Icon Position', 'tenweb-builder' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'tenweb-builder' ),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'Right', 'tenweb-builder' ),
						'icon' => 'eicon-h-align-right',
					],
					'top' => [
						'title' => __( 'Top', 'tenweb-builder' ),
						'icon' => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'tenweb-builder' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'prefix_class' => 'elementor%s-button-align-',
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .twbb-logo' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'left' => 'flex-direction: row; align-items:center;',
					'right' => 'flex-direction: row-reverse; align-items:center;',
					'top' => 'flex-direction: column; align-items: flex-start;',
					'bottom' => 'flex-direction: column-reverse; align-items: flex-start;',
				],
			]
		);
	}

	/**
	 * @since 3.4.0
	 *
	 * @param string $prefix
	 */
	protected function register_logo_style_controls( $prefix = '', $selector = '{{WRAPPER}}' ) {

		$this->add_responsive_control(
			$prefix . 'logo_size',
			[
				'label' => esc_html__( 'Logo Icon Size', 'tenweb-builder' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'min' => 5,
						'max' => 200,
					],
					'em' => [
						'min' => 0.5,
						'max' => 20,
					],
					'rem' => [
						'min' => 0.5,
						'max' => 20,
					],
				],
				'default' => [
					'size' => 50,
				],
				'selectors' => [
					$selector . ' .twbb-logo__image' => 'width: {{SIZE}}{{UNIT}};',
					$selector . ' .twbb-logo__image svg' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			$prefix . 'svg_color',
			[
				'label' => __( 'SVG Color Options', 'tenweb-builder' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			$prefix . 'svg_fill_color',
			[
				'label' => esc_html__( 'SVG Fill Color', 'tenweb-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					$selector .' .twbb-logo__image svg *' => 'fill: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			$prefix . 'svg_stroke_color',
			[
				'label' => esc_html__( 'SVG Stroke Color', 'tenweb-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					$selector .' .twbb-logo__image svg *' => 'stroke: {{VALUE}}',
				],
			]
		);

		$this->add_control(
		$prefix . 'logo_text_heading',
			[
				'label' => __( 'Logo Text', 'tenweb-builder' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => $prefix . 'logo_text_typography',
				'selector' => $selector .' .twbb-logo__text',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			$prefix . 'logo_text_color',
			[
				'label' => esc_html__( 'Logo Text Color', 'tenweb-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					$selector .' .twbb-logo__text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			$prefix . 'space_between',
			[
				'label' => __( 'Space Between Logo Icon & Text', 'tenweb-builder' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 100,
					],
				],
				'desktop_default' => [
					'size' => 10,
				],
				'tablet_default' => [
					'size' => 10,
				],
				'mobile_default' => [
					'size' => 10,
				],
				'render_type' => 'ui',
				'frontend_available' => true,
				'selectors' => [
					$selector .' .twbb-logo' => 'gap: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);
	}

	/**
	 * Render logo widget output on the frontend.
	 *
	 * @param \Elementor\Widget_Base|null $instance
	 *
	 * @since  3.4.0
	 * @access protected
	 */
	protected function render_logo( Widget_Base $instance = null ) {
		if ( empty( $instance ) ) {
			$instance = $this;
		}

		$settings = $instance->get_settings_for_display();
		$logo_url = $settings['logo']['url'];
		$is_svg = isset($logo_url) && pathinfo($logo_url, PATHINFO_EXTENSION) === 'svg';
		?>
        <a href="<?php echo esc_url( home_url('/') ); ?>" rel="<?php __('Home', 'tenweb-builder'); ?>"
           class="twbb-logo"
           aria-label="<?php __('Home', 'tenweb-builder'); ?>">
           <?php
            if ( $logo_url ) {
                if ($is_svg) {
                  ?>
                  <div class="twbb-logo__image">
                  <?php
                    Helper::print_svg_image($logo_url);
                  ?>
                  </div>
                  <?php
                } else {
                    echo '<img class="twbb-logo__image" src="' . esc_url($logo_url) . '" alt="" />';
                }
            }
            ?>
            <div  class="twbb-logo__text" <?php $this->print_render_attribute_string( 'logo_text' ); ?>>
                                <?php $this->print_unescaped_setting( 'logo_text' ); ?></div>
		</a>
		<?php
	}
}
