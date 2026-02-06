<?php
namespace Tenweb_Builder\Widgets\Traits;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Button_Trait {

	/**
	 * @since 3.4.0
	 *
	 * @param array $args {
	 *     An array of values for the button adjustments.
	 *
	 *     @type array  $section_condition  Set of conditions to hide the controls.
	 *     @type string $button_text  Text contained in button.
	 *     @type array $icon_exclude_inline_options  Set of icon types to exclude from icon controls.
	 *     @type string $prefix  Prefix for the control names.
	 * }
	 */
	protected function register_button_content_controls( $args = [] ) {
		$default_args = [
			'section_condition' => [],
			'button_default_text' => esc_html__( 'Click here', 'tenweb-builder'),
			'icon_exclude_inline_options' => [],
			'prefix' => '',
		];

		$args = wp_parse_args( $args, $default_args );
		$prefix = $args['prefix'];

		$this->add_control(
			$prefix . 'text',
			[
				'label' => esc_html__( 'Text', 'tenweb-builder'),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => $args['button_default_text'],
				'placeholder' => $args['button_default_text'],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
			$prefix . 'link',
			[
				'label' => esc_html__( 'Link', 'tenweb-builder'),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'default' => [
					'url' => '#',
				],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
			$prefix . 'selected_icon',
			[
				'label' => esc_html__( 'Icon', 'tenweb-builder'),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
				'condition' => $args['section_condition'],
				'icon_exclude_inline_options' => $args['icon_exclude_inline_options'],
			]
		);

		$start = is_rtl() ? 'right' : 'left';
		$end = is_rtl() ? 'left' : 'right';

		$this->add_control(
			$prefix . 'icon_align',
			[
				'label' => esc_html__( 'Icon Position', 'tenweb-builder'),
				'type' => Controls_Manager::CHOOSE,
				'default' => is_rtl() ? 'row-reverse' : 'row',
				'options' => [
					'row' => [
						'title' => esc_html__( 'Start', 'tenweb-builder'),
						'icon' => "eicon-h-align-{$start}",
					],
					'row-reverse' => [
						'title' => esc_html__( 'End', 'tenweb-builder'),
						'icon' => "eicon-h-align-{$end}",
					],
				],
				'selectors_dictionary' => [
					'left' => is_rtl() ? 'row-reverse' : 'row',
					'right' => is_rtl() ? 'row' : 'row-reverse',
				],
				'selectors' => [
					'{{WRAPPER}} .' . $prefix . 'button-wrapper .elementor-button-content-wrapper' => 'flex-direction: {{VALUE}};',
				],
				'condition' => array_merge(
					$args['section_condition'],
					[
			      $prefix . 'text!' => '',
	          $prefix . 'selected_icon[value]!' => '',
					]
				),
			]
		);

		$this->add_control(
			$prefix . 'icon_indent',
			[
				'label' => esc_html__( 'Icon Spacing', 'tenweb-builder'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'max' => 50,
					],
					'em' => [
						'max' => 5,
					],
					'rem' => [
						'max' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .' . $prefix . 'button-wrapper .elementor-button .elementor-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => array_merge(
					$args['section_condition'],
					[
            $prefix . 'text!' => '',
            $prefix . 'selected_icon[value]!' => '',
					]
				),
			]
		);

		$this->add_control(
			$prefix . 'button_css_id',
			[
				'label' => esc_html__( 'Button ID', 'tenweb-builder'),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'ai' => [
					'active' => false,
				],
				'default' => '',
				'title' => esc_html__( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'tenweb-builder'),
				'description' => sprintf(
					esc_html__( 'Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows %1$sA-z 0-9%2$s & underscore chars without spaces.', 'tenweb-builder'),
					'<code>',
					'</code>'
				),
				'separator' => 'before',
				'condition' => $args['section_condition'],
			]
		);
	}

	/**
	 * @since 3.4.0
	 *
	 * @param array $args {
	 *     An array of values for the button adjustments.
	 *
	 *     @type array  $section_condition  Set of conditions to hide the controls.
	 *     @type string $alignment_default  Default position for the button.
	 *     @type string $alignment_control_prefix_class  Prefix class name for the button position control.
	 *     @type string $content_alignment_default  Default alignment for the button content.
	 *     @type string $prefix  Prefix for the control names.
	 * }
	 */
	protected function register_button_style_controls( $args = [] ) {
		$default_args = [
			'section_condition' => [],
			'alignment_default' => '',
			'alignment_control_prefix_class' => 'elementor%s-align-',
			'content_alignment_default' => '',
            'control_prefix' => '',
			'prefix' => '',
            'selector' => '{{WRAPPER}}',
		];

		$args = wp_parse_args( $args, $default_args );
		$control_prefix = $args['control_prefix'];
		$prefix = $args['prefix'];
        $selector = $args['selector'];

		$this->add_responsive_control(
			$control_prefix . $prefix . 'text_padding',
			[
				'label' => esc_html__( 'Padding', 'tenweb-builder'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'selectors' => [
                    $selector . ' .' . $prefix . 'button-wrapper .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
				'condition' => $args['section_condition'],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => $control_prefix . $prefix . 'typography',
				'global' => [
			    'default' => 'globals/typography?id=accent',
				],
				'selector' => $selector . ' .' . $prefix . 'button-wrapper .elementor-button',
				'condition' => $args['section_condition'],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => $control_prefix . $prefix . 'text_shadow',
				'selector' => $selector . ' .' . $prefix . 'button-wrapper .elementor-button',
				'condition' => $args['section_condition'],
			]
		);

		$this->start_controls_tabs( $control_prefix . $prefix . 'tabs_button_style', [
			'condition' => $args['section_condition'],
		] );

		$this->start_controls_tab(
            $control_prefix . $prefix . 'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'tenweb-builder'),
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
            $control_prefix . $prefix . 'button_text_color',
			[
				'label' => esc_html__( 'Text Color', 'tenweb-builder'),
				'type' => Controls_Manager::COLOR,
        'global' => [
          'default' => 'globals/colors?id=twbb_button_inv',
        ],
				'selectors' => [
					$selector . ' .' . $prefix . 'button-wrapper .elementor-button' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => $control_prefix . $prefix . 'background',
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ], //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
				'selector' => $selector . ' .' . $prefix . 'button-wrapper .elementor-button',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'global' => [
							'default' => 'globals/colors?id=accent',
						],
					],
				],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => $control_prefix . $prefix . 'button_box_shadow',
				'selector' => $selector . ' .' . $prefix . 'button-wrapper .elementor-button',
				'condition' => $args['section_condition'],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => $control_prefix . $prefix . 'border',
				'selector' => $selector . ' .' . $prefix . 'button-wrapper .elementor-button',
				'separator' => 'before',
				'condition' => $args['section_condition'],
			]
		);

		$this->add_responsive_control(
            $control_prefix . $prefix . 'border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					$selector . ' .' . $prefix . 'button-wrapper .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => $args['section_condition'],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
            $control_prefix . $prefix . 'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'tenweb-builder'),
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
            $control_prefix . $prefix . 'hover_color',
			[
				'label' => esc_html__( 'Text Color', 'tenweb-builder'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					$selector . ' .' . $prefix . 'button-wrapper .elementor-button:hover,' . $selector . ' .' . $prefix . 'button-wrapper .elementor-button:focus' => 'color: {{VALUE}};',
					$selector . ' .' . $prefix . 'button-wrapper .elementor-button:hover svg,' . $selector . ' .' . $prefix . 'button-wrapper .elementor-button:focus svg' => 'fill: {{VALUE}};',
				],
        'global' => [
          'default' => 'globals/colors?id=twbb_button_inv',
        ],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => $control_prefix . $prefix . 'button_background_hover',
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ], //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
				'selector' => $selector . ' .' . $prefix . 'button-wrapper .elementor-button:hover,' . $selector . ' .' . $prefix . 'button-wrapper .elementor-button:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => $control_prefix . $prefix . 'button_box_shadow_hover',
				'selector' => $selector . ' .' . $prefix . 'button-wrapper .elementor-button:hover',
				'condition' => $args['section_condition'],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => $control_prefix . $prefix . 'border_hover',
				'selector' => $selector . ' .' . $prefix . 'button-wrapper .elementor-button:hover',
				'separator' => 'before',
				'condition' => $args['section_condition'],
			]
		);

		$this->add_responsive_control(
            $control_prefix . $prefix . 'border_radius_hover',
			[
				'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					$selector . ' .' . $prefix . 'button-wrapper .elementor-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
            $control_prefix . $prefix . 'button_hover_transition_duration',
			[
				'label' => esc_html__( 'Transition Duration', 'tenweb-builder'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 's', 'ms', 'custom' ],
				'default' => [
					'unit' => 's',
				],
				'selectors' => [
					$selector . ' .' . $prefix . 'button-wrapper .elementor-button' => 'transition-duration: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
            $control_prefix . $prefix . 'hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'tenweb-builder'),
				'type' => Controls_Manager::HOVER_ANIMATION,
				'condition' => $args['section_condition'],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();
	}

	/**
	 * Render button widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param \Elementor\Widget_Base|null $instance
	 * @param string $prefix Prefix for the control names.
	 *
	 * @since  3.4.0
	 * @access protected
	 */
	protected function render_button( Widget_Base $instance = null, $prefix = '', $skin = '' ) {
		if ( empty( $instance ) ) {
			$instance = $this;
		}

		$settings = $instance->get_settings();

		if ( empty( $settings[$skin . $prefix . 'text'] ) && empty( $settings[$skin . $prefix . 'selected_icon']['value'] ) ) {
			return;
		}

		$optimized_markup = Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );

		$instance->add_render_attribute( $prefix . 'wrapper', 'class', 'elementor-button-wrapper ' . $prefix . 'button-wrapper' );

		// Add alignment classes with responsive prefixes
		if ( ! empty( $settings[$skin . $prefix . 'align'] ) ) {
			$instance->add_render_attribute( $prefix . 'wrapper', 'class', 'elementor-align-' . $settings[$skin . $prefix . 'align'] );
		}

		// Add responsive alignment classes
		$responsive_alignments = [
			'tablet' => $skin . $prefix . 'align_tablet',
			'mobile' => $skin . $prefix . 'align_mobile',
		];

		foreach ( $responsive_alignments as $device => $setting ) {
			if ( ! empty( $settings[$setting] ) ) {
				$instance->add_render_attribute( $prefix . 'wrapper', 'class', 'elementor-' . $device . '-align-' . $settings[$setting] );
			}
		}

		$instance->add_render_attribute( $prefix . 'button', 'class', 'elementor-button' );

		// Handle link attributes - ensure we don't add duplicate href
		$link_setting = $settings[$skin . $prefix . 'link'] ?? [];
		if ( ! empty( $link_setting['url'] ) ) {
			// Clear any existing link attributes first
			$instance->remove_render_attribute( $prefix . 'button', 'href' );
			$instance->remove_render_attribute( $prefix . 'button', 'target' );
			$instance->remove_render_attribute( $prefix . 'button', 'rel' );

			// Add link attributes properly
			$instance->add_link_attributes( $prefix . 'button', $link_setting );
			$instance->add_render_attribute( $prefix . 'button', 'class', 'elementor-button-link' );
		} else {
			$instance->add_render_attribute( $prefix . 'button', 'role', 'button' );
		}

		if ( ! empty( $settings[$skin . $prefix . 'button_css_id'] ) ) {
			$instance->add_render_attribute( $prefix . 'button', 'id', $settings[$skin . $prefix . 'button_css_id'] );
		}

		$instance->add_render_attribute( $prefix . 'button', 'class', 'elementor-size-sm' ); // BC, to make sure the class is always present

		if ( ! empty( $settings[$skin . $prefix . 'hover_animation'] ) ) {
			$instance->add_render_attribute( $prefix . 'button', 'class', 'elementor-animation-' . $settings[$skin . $prefix . 'hover_animation'] );
		}
		?>

        <div <?php $instance->print_render_attribute_string( $prefix . 'wrapper' ); ?>>
            <a <?php $instance->print_render_attribute_string( $prefix . 'button' ); ?>>
                <?php $this->render_text( $instance, $prefix, $skin ); ?>
            </a>
		</div>

		<?php
	}

	/**
	 * Render button text.
	 *
	 * Render button widget text.
	 *
	 * @param \Elementor\Widget_Base|null $instance
	 * @param string $prefix Prefix for the control names.
	 *
	 * @since  3.4.0
	 * @access protected
	 */
	protected function render_text( Widget_Base $instance = null, $prefix = '', $skin = '' ) {
		// The default instance should be `$this` (a Button widget), unless the Trait is being used from outside of a widget (e.g. `Skin_Base`) which should pass an `$instance`.
		if ( empty( $instance ) ) {
			$instance = $this;
		}

		$settings = $instance->get_settings();

		$migrated = isset( $settings[$skin . $prefix . '__fa4_migrated']['selected_icon'] );
		$is_new = empty( $settings[$skin . $prefix . 'icon'] ) && Icons_Manager::is_migration_allowed();

		$instance->add_render_attribute( [
			$prefix . 'content-wrapper' => [
				'class' => 'elementor-button-content-wrapper',
			],
			$prefix . 'icon' => [
				'class' => 'elementor-button-icon',
			],
			$prefix . 'text' => [
				'class' => 'elementor-button-text',
			],
		] );

		?>
		<span <?php $instance->print_render_attribute_string( $prefix . 'content-wrapper' ); ?>>
			<?php if ( ! empty( $settings[$skin . $prefix . 'icon'] ) || ! empty( $settings[$skin . $prefix . 'selected_icon']['value'] ) ) : ?>
				<span <?php $instance->print_render_attribute_string( $prefix . 'icon' ); ?>>
				<?php if ( $is_new || $migrated ) :
					Icons_Manager::render_icon( $settings[$skin . $prefix . 'selected_icon'], [ 'aria-hidden' => 'true' ] );
				else : ?>
					<i class="<?php echo esc_attr( $settings[$skin . $prefix . 'icon'] ); ?>" aria-hidden="true"></i>
				<?php endif; ?>
			</span>
			<?php endif; ?>
			<?php if ( ! empty( $settings[$skin . $prefix . 'text'] ) ) : ?>
				<span <?php $instance->print_render_attribute_string( $prefix . 'text' ); ?>><?php echo esc_html($settings[$skin . $prefix . 'text']); ?></span>
			<?php endif; ?>
		</span>
		<?php
	}
}
