<?php
namespace Tenweb_Builder\Widgets\Traits;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Account_Trait {

    public function register_account_styles() {
	    wp_register_style( 'twbb-woocommerce-account-style', TWBB_URL . '/widgets/woocommerce/account/assets/style.css', [], TWBB_VERSION);
    }

	/**
	 *
	 * @since 3.4.0
	 *
	 * @param string $prefix
	 */
	protected function register_account_content_controls() {

		$this->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icon', 'tenweb-builder' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-user',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'user-circle',
						'id-badge',
						'address-card',
					]
				],
			]
		);
	}

	/**
	 * @since 3.4.0
	 *
	 * @param string $prefix
	 */
	protected function register_account_style_controls( $prefix = '', $selector = '{{WRAPPER}}' ) {

		$this->add_control(
			$prefix . 'icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'tenweb-builder' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'min' => 5,
						'max' => 50,
					],
					'em' => [
						'min' => 0.5,
						'max' => 5,
					],
					'rem' => [
						'min' => 0.5,
						'max' => 5,
					],
				],
				'default' => [
					'size' => 12,
				],
				'selectors' => [
					$selector . ' .twbb-account i' => 'font-size: {{SIZE}}{{UNIT}}',
		      $selector . ' .twbb-account svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->start_controls_tabs( $prefix . 'icon_style' );

		$this->start_controls_tab( $prefix . 'icon_colors', [ 'label' => esc_html__( 'Normal', 'tenweb-builder' ) ] );

		$this->add_control(
			$prefix . 'icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'tenweb-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
          $selector . ' .twbb-account i' => 'color: {{VALUE}}',
          $selector . ' .twbb-account svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( $prefix . 'toggle_button_hover_colors', [ 'label' => __( 'Hover', 'tenweb-builder' ) ] );

		$this->add_control(
			$prefix . 'hover_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'tenweb-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
			    $selector . ' .twbb-account:hover i' => 'color: {{VALUE}}',
	        $selector . ' .twbb-account:hover svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();
	}

	/**
	 * Render account widget output on the frontend.
	 *
	 * @param \Elementor\Widget_Base|null $instance
	 *
	 * @since  3.4.0
	 * @access protected
	 */
	protected function render_account( Widget_Base $instance = null ) {
		if ( empty( $instance ) ) {
			$instance = $this;
		}

		$settings = $instance->get_settings_for_display();

        //check if wc_get_page_permalink is available
        if ( ! function_exists( 'wc_get_page_permalink' ) || ! wc_get_page_permalink( 'myaccount' ) ) {
            return;
        }
		?>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="twbb-account">
            <?php \Elementor\Icons_Manager::render_icon($settings['icon'], ['aria-hidden' => 'true']); ?>
        </a>
		<?php
	}
}
