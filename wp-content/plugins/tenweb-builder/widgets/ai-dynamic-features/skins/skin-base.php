<?php
namespace Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins;

include_once (TWBB_DIR . '/widgets/traits/button_trait.php');

use Elementor\Group_Control_Background;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Elementor\Widget_Base;
use Tenweb_Builder\Widgets\Traits\Button_Trait;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base {
	use Button_Trait;

  private $feature_styles;

  private function get_feature_control_id( $control_id, $settings ) {
    $style = $settings[$this->get_control_id( 'feature_list_style_choice' )];
    return $this->get_control_id( $style . '_' . $control_id );
  }

	protected function _register_controls_actions() {
	  $this->feature_styles = [
		  'style_1' => __('Style 1', 'tenweb-builder'),
		  'style_2' => __('Style 2', 'tenweb-builder'),
		  'style_3' => __('Style 3', 'tenweb-builder'),
		  'style_4' => __('Style 4', 'tenweb-builder'),
		  'style_5' => __('Style 5', 'tenweb-builder'),
		  'style_6' => __('Style 6', 'tenweb-builder'),
		  'style_7' => __('Style 7', 'tenweb-builder'),
	  ];

		add_action( 'elementor/element/twbb_dynamic_features/section_layout/before_section_end', [ $this, 'register_controls_layout' ] );
		add_action( 'elementor/element/twbb_dynamic_features/section_content/before_section_end', [ $this, 'register_controls_content_before' ] );
		add_action( 'elementor/element/twbb_dynamic_features/section_feature_list/before_section_end', [ $this, 'register_controls_feature_list_after' ] );
		add_action( 'elementor/element/twbb_dynamic_features/section_buttons/before_section_end', [ $this, 'register_controls_buttons_after' ] );
		add_action( 'elementor/element/twbb_dynamic_features/section_feature_list/after_section_end', [ $this, 'register_controls_content' ] );
	}

	public function register_controls_layout( Widget_Base $widget ) {
		$this->parent = $widget;

	  $this->add_control(
		  'feature_list_style_choice',
		  [
			  'label' => __('Feature List Design', 'tenweb-builder'),
			  'type' => \Elementor\Controls_Manager::SELECT,
			  'default' => 'style_1',
			  'options' => $this->feature_styles,
        'prefix_class' => 'twbb-dynamic-features-'
		  ]
	  );

		$this->add_control(
			'media_position',
			[
				'label' => __('Media Position', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'image-left' => [
						'title' => __('Left', 'tenweb-builder'),
						'icon' => 'eicon-h-align-left',
					],
					'image-right' => [
						'title' => __('Right', 'tenweb-builder'),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'image-right',
				'prefix_class' => 'twbb-dynamic-features-layout-',
				'render_type' => 'template',
				'toggle' => false,
			]
		);

		$this->add_responsive_control(
			'content_alignment',
			[
				'label' => __('Alignment', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __('Left', 'tenweb-builder'),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __('Center', 'tenweb-builder'),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __('Right', 'tenweb-builder'),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'left',
				'selectors_dictionary' => [
					'left' => 'text-align: left; justify-content: flex-start;',
					'center' => 'text-align: center; justify-content: center;',
					'right' => 'text-align: right; justify-content: flex-end;',
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-title-field' => '{{VALUE}};',
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-description-field' => '{{VALUE}};',
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-list .twbb-dynamic-features-text' => '{{VALUE}};',
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-list .twbb-dynamic-features-list-item-title' => '{{VALUE}};',
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-buttons-group' => '{{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'justify_content',
			[
				'label' => __('Justify Content', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => __('Start', 'tenweb-builder'),
						'icon' => 'eicon-justify-start-v',
					],
					'center' => [
						'title' => __('Center', 'tenweb-builder'),
						'icon' => 'eicon-justify-center-v',
					],
					'flex-end' => [
						'title' => __('End', 'tenweb-builder'),
						'icon' => 'eicon-justify-end-v',
					],
					'space-between' => [
						'title' => __('Space Between', 'tenweb-builder'),
						'icon' => 'eicon-justify-space-between-v',
					],
				],
				'default' => 'space-between',
        'prefix_class' => 'twbb-dynamic-features-justify-content-',
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-content' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'buttons_position',
			[
				'label' => __('Buttons Position', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'under_features',
				'options' => [
					'under_heading' => __('Under Heading', 'tenweb-builder'),
					'under_features' => __('Under Features', 'tenweb-builder'),
				],
        'prefix_class' => 'twbb-dynamic-features-buttons-position-',
			]
		);
	}

	public function register_controls_content_before( Widget_Base $widget ) {
		$this->parent = $widget;

	  $this->parent->start_injection( [
		  'at' => 'before',
		  'of' => 'title_field',
	  ] );

		$this->add_control(
			'show_title',
			[
				'label' => __('Title', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'tenweb-builder'),
				'label_off' => __('Hide', 'tenweb-builder'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

	  $this->parent->end_injection();

	  $this->parent->start_injection( [
		  'at' => 'before',
		  'of' => 'description_field',
	  ] );

		$this->add_control(
			'show_description',
			[
				'label' => __('Description', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'tenweb-builder'),
				'label_off' => __('Hide', 'tenweb-builder'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

	  $this->parent->end_injection();
	}

  public function register_controls_feature_list_after( Widget_Base $widget ) {
	  $this->parent = $widget;
	  $this->register_features_description_control();
  }

  public function register_controls_buttons_after( Widget_Base $widget ) {
	  $this->parent = $widget;

	  $this->parent->start_injection( [
		  'at' => 'after',
		  'of' => 'heading_button_1',
	  ] );

	  $this->add_control(
		  'show_button_1',
		  [
			  'label' => __('Button 1', 'tenweb-builder'),
			  'type' => \Elementor\Controls_Manager::SWITCHER,
			  'label_on' => __('Show', 'tenweb-builder'),
			  'label_off' => __('Hide', 'tenweb-builder'),
			  'return_value' => 'yes',
			  'default' => 'yes',
		  ]
	  );

	  $this->parent->end_injection();

	  $this->parent->start_injection( [
		  'at' => 'after',
		  'of' => 'heading_button_2',
	  ] );

	  $this->add_control(
		  'show_button_2',
		  [
			  'label' => __('Button 2', 'tenweb-builder'),
			  'type' => \Elementor\Controls_Manager::SWITCHER,
			  'label_on' => __('Show', 'tenweb-builder'),
			  'label_off' => __('Hide', 'tenweb-builder'),
			  'return_value' => 'yes',
			  'default' => 'no',
		  ]
	  );

	  $this->parent->end_injection();
  }

	public function register_controls_content( Widget_Base $widget ) {
		$this->parent = $widget;

    $this->register_progress_indicator_controls();

		$this->start_controls_section(
			'section_media',
			[
				'label' => __('Media', 'tenweb-builder'),
			]
		);

		$this->add_control(
			'image_animation',
			[
				'label' => __('Animation', 'tenweb-builder'),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'fade',
				'options' => [
					'fade' => __('Fade', 'tenweb-builder'),
					'soft_shift' => __('Soft Shift', 'tenweb-builder'),
					'zoom' => __('Zoom', 'tenweb-builder'),
					'vertical_slider' => __('Vertical Slider', 'tenweb-builder'),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'image_animation_duration',
			[
				'label' => __('Animation Duration', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
        'frontend_available' => true,
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 2000,
						'step' => 50,
					],
				],
				'default' => [
					'size' => 500,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"]' => '--twbb-animation-duration: {{SIZE}}ms',
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_layout_style',
			[
				'label' => __('Layout', 'tenweb-builder'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_box_style',
			[
				'label' => __('Box', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'content_media_gap',
			[
				'label' => __('Gap Between Content & Media', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -300,
						'max' => 300,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 100,
				],
				// I know the -sign is now working for negative values but it's not a problem.
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"].twbb-dynamic-features-layout-image-left .twbb-dynamic-features-media' => 'margin-left: -{{SIZE}}{{UNIT}};margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"].twbb-dynamic-features-layout-image-right .twbb-dynamic-features-media' => 'margin-right: -{{SIZE}}{{UNIT}};margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'box_background_color',
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ], //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
				'selector' => '{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-container',
        'render_type' => 'ui',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
          'color' => [
            'global' => [
              'default' => 'globals/colors?id=twbb_transparent',
            ],
          ],
				],
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label' => __('Padding', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_box_border',
			[
				'label' => __('Border', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->start_controls_tabs('box_border_tabs');

		$this->start_controls_tab(
			'box_border_normal',
			[
				'label' => __('Normal', 'tenweb-builder'),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'box_border',
				'label' => __('Box Border', 'tenweb-builder'),
				'selector' => '{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-container',
			]
		);

		$this->add_responsive_control(
			'box_border_radius',
			[
				'label' => __('Border Radius', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'box_border_hover',
			[
				'label' => __('Hover', 'tenweb-builder'),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'box_border_hover',
				'label' => __('Box Border', 'tenweb-builder'),
				'selector' => '{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-container:hover',
			]
		);

		$this->add_responsive_control(
			'box_border_radius_hover',
			[
				'label' => __('Border Radius', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-container:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'heading_content_style',
			[
				'label' => __('Content', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::RAW_HTML,
        'raw' => __('Content includes Title & description, features and buttons', 'tenweb-builder'),
		    'content_classes' => 'elementor-control-field-description',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'content_background',
			[
				'label' => __('Background Color', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-content' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_width',
			[
				'label' => __('Width', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'vw'],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1200,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 500,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-content' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label' => __('Padding', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_content_border',
			[
				'label' => __('Border', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->start_controls_tabs('content_border_tabs');

		$this->start_controls_tab(
			'content_border_normal',
			[
				'label' => __('Normal', 'tenweb-builder'),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'content_border',
				'label' => __('Content Border', 'tenweb-builder'),
				'selector' => '{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-content',
			]
		);

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label' => __('Border Radius', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'content_border_hover',
			[
				'label' => __('Hover', 'tenweb-builder'),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'content_border_hover',
				'label' => __('Content Border', 'tenweb-builder'),
				'selector' => '{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-content:hover',
			]
		);

		$this->add_responsive_control(
			'content_border_radius_hover',
			[
				'label' => __('Border Radius', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-content:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => __('Title & Description', 'tenweb-builder'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_content_title_description_style',
			[
				'label' => __('Title & Description', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_field_typography',
				'selector' => '{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-title-field',
				'render_type' => 'ui',
				'label' => __('Title Typography', 'tenweb-builder'),
				'fields_options' => [
					'typography' => ['default' => 'yes'],
					'font_size' => [
						'default' => [
							'unit' => 'px',
							'size' => 32,
						],
						'mobile_default' => [
							'unit' => 'px',
							'size' => 24,
						],
					],
					'font_weight' => [
						'default' => '700',
					],
					'line_height' => [
						'default' => [
              'unit' => '%',
              'size' => 120,
            ],
					],
				],
			]
		);

		$this->add_control(
			'title_field_color',
			[
				'label' => __('Title Color', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-title-field' => 'color: {{VALUE}};',
				],
				'global' => ['default' => 'globals/colors?id=primary'],
				'render_type' => 'ui'
			]
		);

		$this->add_responsive_control(
			'title_space_below',
			[
				'label' => __('Title Space Below', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 16,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-title-field' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'description_field_typography',
				'selector' => '{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-description-field',
				'render_type' => 'ui',
				'label' => __('Description Typography', 'tenweb-builder'),
				'fields_options' => [
					'typography' => ['default' => 'yes'],
					'font_size' => [
						'default' => [
							'unit' => 'px',
							'size' => 18,
						],
						'mobile_default' => [
							'unit' => 'px',
							'size' => 14,
						],
					],
          'line_height' => [
            'default' => [
              'unit' => '%',
              'size' => 150,
            ],
          ],
				],
			]
		);

		$this->add_control(
			'description_field_color',
			[
				'label' => __('Description Color', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-description-field' => 'color: {{VALUE}};',
				],
				'render_type' => 'ui'
			]
		);

		$this->add_responsive_control(
			'description_space_below',
			[
				'label' => __('Description Space Below', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 48,
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 32,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-description-field' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	  $this->register_features_list_style_controls();

		$this->register_progress_indicator_style_controls();

		$this->start_controls_section(
			'section_media_style',
			[
				'label' => __('Media', 'tenweb-builder'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'media_width',
			[
				'label' => __('Width', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'vw'],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1200,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 200,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-media' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'media_height',
			[
				'label' => __('Height', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'vh'],
        'frontend_available' => true,
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1200,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 228,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-media .twbb-dynamic-features-media-carousel' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'media_alignment',
			[
				'label' => __('Justify Media', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => __('Start', 'tenweb-builder'),
						'icon' => 'eicon-justify-start-v',
					],
					'center' => [
						'title' => __('Center', 'tenweb-builder'),
						'icon' => 'eicon-justify-center-v',
					],
					'flex-end' => [
						'title' => __('End', 'tenweb-builder'),
						'icon' => 'eicon-justify-end-v',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-media' => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'media_background_size',
			[
				'label' => __('Media Size', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover' => __('Cover', 'tenweb-builder'),
					'contain' => __('Contain', 'tenweb-builder'),
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-slide-image' => 'background-size: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'media_background_position',
			[
				'label' => __('Media Position', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'center center',
				'options' => [
					'left top' => __('Left Top', 'tenweb-builder'),
					'left center' => __('Left Center', 'tenweb-builder'),
					'left bottom' => __('Left Bottom', 'tenweb-builder'),
					'center top' => __('Center Top', 'tenweb-builder'),
					'center center' => __('Center Center', 'tenweb-builder'),
					'center bottom' => __('Center Bottom', 'tenweb-builder'),
					'right top' => __('Right Top', 'tenweb-builder'),
					'right center' => __('Right Center', 'tenweb-builder'),
					'right bottom' => __('Right Bottom', 'tenweb-builder'),
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-slide-image' => 'background-position: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_media_border_style',
			[
				'label' => __('Border', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'media_border',
				'label' => __('Border', 'tenweb-builder'),
				'selector' => '{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-media',
			]
		);

		$this->add_responsive_control(
			'media_border_radius',
			[
				'label' => __('Border Radius', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'slides_spacing',
			[
				'label' => __('Slides Spacing', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
        'frontend_available' => true,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'condition' => [
			    $this->get_control_id( 'image_animation' ) => 'vertical_slider',
				],
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_buttons_style',
			[
				'label' => __('Buttons', 'tenweb-builder'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_button_1_style',
			[
				'label' => __('Button 1', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'condition' => [
			    $this->get_control_id( 'show_button_1' ) => 'yes',
				],
			]
		);

		$this->register_button_style_controls([
			'section_condition' => [
		    $this->get_control_id( 'show_button_1' ) => 'yes',
			],
			'prefix' => 'button_1_',
		]);

		$this->add_control(
			'heading_button_2_style',
			[
				'label' => __('Button 2', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
			    $this->get_control_id( 'show_button_2' ) => 'yes',
				],
			]
		);

		$this->register_button_style_controls([
			'section_condition' => [
		    $this->get_control_id( 'show_button_2' ) => 'yes',
			],
			'prefix' => 'button_2_',
		]);

		$this->add_control(
			'heading_buttons_group_style',
			[
				'label' => __('Buttons Group', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'buttons_group_space_below',
			[
				'label' => __('Space Below', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 48,
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-buttons-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'buttons_gap',
			[
				'label' => __('Gap Between', 'tenweb-builder'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 10,
					],
					'rem' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}}[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"] .twbb-dynamic-features-buttons-group' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

  public function register_features_list_style_controls() {
	  foreach ($this->feature_styles as $style => $name) {
	    $condition = [ $this->get_control_id( 'feature_list_style_choice' ) => $style ];
      $selector = '{{WRAPPER}}.twbb-dynamic-features-' . $style . '[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"]';

	    $this->start_controls_section(
	      $style . '_' . 'section_features_list_style_controls',
		    [
			    'label' => __('Features List', 'tenweb-builder'),
			    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
          'condition' => $condition,
		    ]
	    );

      $defaults = [
        'style_1' => ['default' => 48, 'mobile_default' => 48],
        'style_2' => ['default' => 48, 'mobile_default' => 48],
        'style_3' => ['default' => 24, 'mobile_default' => 24],
        'style_4' => ['default' => 176, 'mobile_default' => 32],
        'style_5' => ['default' => 48, 'mobile_default' => 48],
        'style_6' => ['default' => 48, 'mobile_default' => 48],
        'style_7' => ['default' => 28, 'mobile_default' => 28],
      ];

      $this->add_responsive_control(
        $style . '_' . 'feature_list_space_below',
        [
          'label'      => __( 'Space Below Feature List', 'tenweb-builder'),
          'type'       => \Elementor\Controls_Manager::SLIDER,
          'size_units' => [ 'px', 'em', 'rem' ],
          'range'      => [
            'px'  => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
            'em'  => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ],
            'rem' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ],
          ],
          'default'    => [
            'unit' => 'px',
            'size' => $defaults[$style]['default']
          ],
          'mobile_default' => [
            'unit' => 'px',
            'size' => $defaults[$style]['mobile_default']
          ],
          'selectors'  => [
            $selector . ' .twbb-dynamic-features-list' => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $defaults = [
        'style_1' => ['default' => 20, 'mobile_default' => 20],
        'style_2' => ['default' => 0, 'mobile_default' => 0],
        'style_3' => ['default' => 0, 'mobile_default' => 0],
        'style_4' => ['default' => 0, 'mobile_default' => 0],
        'style_5' => ['default' => 0, 'mobile_default' => 0],
        'style_6' => ['default' => 0, 'mobile_default' => 0],
        'style_7' => ['default' => 0, 'mobile_default' => 0],
      ];

      $this->add_control(
        $style . '_' . 'feature_list_spacing',
        [
          'label'      => __( 'Gap Between Features', 'tenweb-builder'),
          'type'       => \Elementor\Controls_Manager::SLIDER,
          'size_units' => [ 'px', 'em', 'rem' ],
          'range'      => [
            'px'  => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
            'em'  => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ],
            'rem' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ],
          ],
          'default'    => [
            'unit' => 'px',
            'size' => $defaults[$style]['default']
          ],
          'mobile_default' => [
            'unit' => 'px',
            'size' => $defaults[$style]['mobile_default']
          ],
          'selectors'  => [
            $selector . ' .twbb-dynamic-features-list-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

	    $defaults = [
        'style_1' => ['default' => 12, 'mobile_default' => 12],
        'style_2' => ['default' => 0, 'mobile_default' => 0],
        'style_3' => ['default' => 0, 'mobile_default' => 0],
        'style_4' => ['default' => 12, 'mobile_default' => 8],
        'style_5' => ['default' => 12, 'mobile_default' => 12],
        'style_6' => ['default' => 12, 'mobile_default' => 12],
        'style_7' => ['default' => 12, 'mobile_default' => 12],
      ];

	    $this->add_responsive_control(
		    $style . '_' . 'feature_list_title_description_gap',
		    [
			    'label'      => __( 'Gap Between Title & Description', 'tenweb-builder'),
			    'type'       => \Elementor\Controls_Manager::SLIDER,
			    'size_units' => [ 'px', 'em', 'rem' ],
			    'range'      => [
				    'px'  => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
				    'em'  => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ],
				    'rem' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ],
			    ],
			    'default'    => [
            'unit' => 'px',
            'size' => $defaults[$style]['default']
          ],
          'mobile_default' => [
            'unit' => 'px',
            'size' => $defaults[$style]['mobile_default']
          ],
			    'selectors'  => [
				    $selector . ' .twbb-dynamic-features-list-item.active .twbb-dynamic-features-list-item-description' => 'margin-top: {{SIZE}}{{UNIT}};',
			    ],
		    ]
	    );

	    $defaults = [
		    'style_1' => [
          'global' => [],
          'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 24 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 18 ] ],
            'font_weight' => [ 'default' => '500' ],
            'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 140 ] ],
          ]
        ],
		    'style_2' => [
          'global' => [],
          'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 14 ] ],
            'font_weight' => [ 'default' => '500' ],
            'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 150 ] ],
          ]
        ],
		    'style_3' => [
          'global' => [],
          'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 20 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 20 ] ],
            'font_weight' => [ 'default' => '500' ],
            'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 150 ] ],
          ]
        ],
		    'style_4' => [
          'global' => [],
          'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 24 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 18 ] ],
            'font_weight' => [ 'default' => '500' ],
          ]
        ],
		    'style_5' => [
          'global' => [],
          'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 14 ] ],
            'font_weight' => [ 'default' => '500' ],
            'line_height' => [ 'default' => [ 'unit' => 'px', 'size' => 24 ], 'mobile_default' => [ 'unit' => '%', 'size' => 140 ] ],
          ]
        ],
		    'style_6' => [
          'global' => [],
          'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 24 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 18 ] ],
            'font_weight' => [ 'default' => '500' ],
            'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 140 ] ],
          ]
        ],
		    'style_7' => [
          'global' => [
            'default' => 'globals/typography?id=twbb_h6',
          ],
          'fields_options' => [],
        ],
	    ];

	    $this->add_group_control(
		    \Elementor\Group_Control_Typography::get_type(),
		    [
			    'name'           => $style . '_' . 'feature_title_typography',
			    'selector'       => $selector . ' .twbb-dynamic-features-list-item-title',
			    'render_type'    => 'ui',
			    'label'          => __( 'Title Typography', 'tenweb-builder'),
			    'fields_options' => $defaults[$style]['fields_options'],
          'global' => $defaults[$style]['global'],
		    ]
	    );

	    $this->start_controls_tabs( $style . '_' . 'feature_title_color_tabs' );

	    $this->start_controls_tab(
		    $style . '_' . 'tab_feature_title_color_normal',
		    [ 'label' => __( 'Normal', 'tenweb-builder') ]
	    );

	    $defaults = [
		    'style_1' => ['default' => '#00000080'],
		    'style_2' => ['default' => '#00000080'],
		    'style_3' => ['default' => '#00000080'],
		    'style_4' => ['default' => ''],
		    'style_5' => ['default' => '#00000080'],
		    'style_6' => ['default' => ''],
		    'style_7' => ['default' => '#00000080'],
	    ];

	    $this->add_control(
		    $style . '_' . 'feature_title_color',
		    [
			    'label'       => __( 'Title Color', 'tenweb-builder'),
			    'type'        => \Elementor\Controls_Manager::COLOR,
			    'selectors'   => [
				    $selector . ' .twbb-dynamic-features-list-item-title' => 'color: {{VALUE}};',
				    $selector . ' .twbb-dynamic-features-tab-icon svg'    => 'color: {{VALUE}}; fill: {{VALUE}};',
			    ],
			  	'default' => $defaults[$style]['default'],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->end_controls_tab();

	    $this->start_controls_tab(
		    $style . '_' . 'tab_feature_title_color_hover',
		    [ 'label' => __( 'Hover', 'tenweb-builder') ]
	    );

	    $defaults = [
		    'style_1' => ['default' => '#000000ff', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_2' => ['default' => '#000000ff', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_3' => ['default' => '', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_4' => ['default' => '#00000080', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_5' => ['default' => '', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_6' => ['default' => '#00000080', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_7' => ['default' => '', 'global' => ['default' => 'globals/colors?id=primary']],
	    ];

	    $this->add_control(
		    $style . '_' . 'feature_title_color_hover',
		    [
			    'label'       => __( 'Title Color', 'tenweb-builder'),
			    'type'        => \Elementor\Controls_Manager::COLOR,
			    'selectors'   => [
				    $selector . ' .twbb-dynamic-features-list-item:hover .twbb-dynamic-features-list-item-title' => 'color: {{VALUE}};',
				    $selector . ' .twbb-dynamic-features-list-item:hover .twbb-dynamic-features-tab-icon svg'    => 'color: {{VALUE}}; fill: {{VALUE}};',
			    ],
				  'default' => $defaults[$style]['default'],
          'global' => $defaults[$style]['global'],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->end_controls_tab();

	    $this->start_controls_tab(
		    $style . '_' . 'tab_feature_title_color_active',
		    [ 'label' => __( 'Active', 'tenweb-builder') ]
	    );

	    $defaults = [
		    'style_1' => ['default' => '', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_2' => ['default' => '', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_3' => ['default' => '', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_4' => ['default' => '', 'global' => ['default' => 'globals/colors?id=accent']],
		    'style_5' => ['default' => '', 'global' => ['default' => 'globals/colors?id=primary']],
		    'style_6' => ['default' => '', 'global' => ['default' => 'globals/colors?id=accent']],
		    'style_7' => ['default' => '', 'global' => ['default' => 'globals/colors?id=primary']],
	    ];

	    $this->add_control(
		    $style . '_' . 'feature_title_color_active',
		    [
			    'label'       => __( 'Title Color', 'tenweb-builder'),
			    'type'        => \Elementor\Controls_Manager::COLOR,
			    'selectors'   => [
				    $selector . ' .twbb-dynamic-features-list-item.active .twbb-dynamic-features-text .twbb-dynamic-features-list-item-title' => 'color: {{VALUE}};',
				    $selector . ' .twbb-dynamic-features-list-item.active .twbb-dynamic-features-text .twbb-dynamic-features-tab-icon svg'    => 'color: {{VALUE}}; fill: {{VALUE}};',
			    ],
          'default' => $defaults[$style]['default'],
          'global' => $defaults[$style]['global'],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->end_controls_tab();
	    $this->end_controls_tabs();

	    $this->add_control(
		    $style . '_' . 'hr_feature_title_description',
		    [
			    'type'      => \Elementor\Controls_Manager::DIVIDER,
			    'style'     => 'thick',
		    ]
	    );

	    $defaults = [
		    'style_1' => [
			    'global' => [],
			    'fields_options' => [
            'typography' => [ 'default' => 'yes' ],
            'font_size'  => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 12 ] ],
            'font_weight' => [ 'default' => '400' ],
            'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 150 ] ],
          ]
		    ],
		    'style_2' => [
			    'global' => [],
			    'fields_options' => [
				    'typography'  => [ 'default' => 'yes' ],
				    'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 16 ] ],
				    'font_weight' => [ 'default' => '500' ],
			    ]
		    ],
		    'style_3' => [
			    'global' => [],
			    'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 16 ] ],
            'font_weight' => [ 'default' => '500' ],
          ]
		    ],
		    'style_4' => [
			    'global' => [],
			    'fields_options' => [
				    'typography'  => [ 'default' => 'yes' ],
				    'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 12 ] ],
				    'font_weight' => [ 'default' => '400' ],
		        'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 150 ] ],
			    ]
		    ],
		    'style_5' => [
			    'global' => [],
			    'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 12 ] ],
            'font_weight' => [ 'default' => '400' ],
            'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 150 ] ],
          ]
		    ],
		    'style_6' => [
			    'global' => [],
			    'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 12 ] ],
            'font_weight' => [ 'default' => '400' ],
            'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 150 ] ],
          ]
		    ],
		    'style_7' => [
			    'global' => [],
			    'fields_options' => [
            'typography'  => [ 'default' => 'yes' ],
            'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ], 'mobile_default' => [ 'unit' => 'px', 'size' => 12 ] ],
            'font_weight' => [ 'default' => '400' ],
            'line_height' => [ 'default' => [ 'unit' => '%', 'size' => 150 ] ],
          ],
		    ],
	    ];

	    $this->add_group_control(
		    \Elementor\Group_Control_Typography::get_type(),
		    [
			    'name'           => $style . '_' . 'feature_description_typography',
			    'selector'       => $selector . ' .twbb-dynamic-features-list-item-description',
			    'render_type'    => 'ui',
			    'label'          => __( 'Description Typography', 'tenweb-builder'),
          'fields_options' => $defaults[$style]['fields_options'],
          'global' => $defaults[$style]['global'],
		    ]
	    );

	    $this->start_controls_tabs( $style . '_' . 'feature_description_color_tabs' );

	    $this->start_controls_tab(
		    $style . '_' . 'tab_feature_description_color_normal',
		    [ 'label' => __( 'Normal', 'tenweb-builder') ]
	    );

	    $this->add_control(
		    $style . '_' . 'feature_description_color',
		    [
			    'label'       => __( 'Description Color', 'tenweb-builder'),
			    'type'        => \Elementor\Controls_Manager::COLOR,
			    'selectors'   => [
				    $selector . ' .twbb-dynamic-features-list-item-description' => 'color: {{VALUE}};',
			    ],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->end_controls_tab();

	    $this->start_controls_tab(
		    $style . '_' . 'tab_feature_description_color_hover',
		    [ 'label' => __( 'Hover', 'tenweb-builder') ]
	    );

	    $this->add_control(
		    $style . '_' . 'feature_description_color_hover',
		    [
			    'label'       => __( 'Description Color', 'tenweb-builder'),
			    'type'        => \Elementor\Controls_Manager::COLOR,
			    'selectors'   => [
				    $selector . ' .twbb-dynamic-features-list-item:hover .twbb-dynamic-features-list-item-description' => 'color: {{VALUE}};',
			    ],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->end_controls_tab();
	    $this->end_controls_tabs();

	    $this->add_control(
		    $style . '_' . 'heading_content_feature_background_style',
		    [
			    'label'     => __( 'Background', 'tenweb-builder'),
			    'type'      => \Elementor\Controls_Manager::HEADING,
			    'separator' => 'before',
		    ]
	    );

	    $defaults = [
		    'style_1' => ['top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px', 'isLinked' => true],
		    'style_2' => ['top' => '16', 'right' => '16', 'bottom' => '16', 'left' => '16', 'unit' => 'px', 'isLinked' => true],
		    'style_3' => ['top' => '12', 'right' => '0', 'bottom' => '12', 'left' => '0', 'unit' => 'px', 'isLinked' => true],
		    'style_4' => ['top' => '12', 'right' => '0', 'bottom' => '12', 'left' => '0', 'unit' => 'px', 'isLinked' => true],
		    'style_5' => ['top' => '16', 'right' => '24', 'bottom' => '16', 'left' => '24', 'unit' => 'px', 'isLinked' => true],
		    'style_6' => ['top' => '24', 'right' => '0', 'bottom' => '24', 'left' => '0', 'unit' => 'px', 'isLinked' => true],
		    'style_7' => ['top' => '24', 'right' => '24', 'bottom' => '24', 'left' => '24', 'unit' => 'px', 'isLinked' => true],
	    ];

	    $this->add_responsive_control(
		    $style . '_' . 'feature_text_padding',
		    [
			    'label'      => __( 'Padding', 'tenweb-builder'),
			    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			    'size_units' => [ 'px', 'em', '%' ],
			    'default'    => $defaults[$style],
			    'selectors'  => [
				    $selector . ' .twbb-dynamic-features-list-item .twbb-dynamic-features-list-item-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				    $selector . ' .twbb-dynamic-features-tab-progress-indicator svg.bar'                     => 'height: calc(100% - {{TOP}}{{UNIT}} - {{BOTTOM}}{{UNIT}});',
			    ],
		    ]
	    );

	    $this->start_controls_tabs( $style . '_' . 'feature_background_tabs' );

	    $this->start_controls_tab(
		    $style . '_' . 'feature_background_normal',
		    [ 'label' => __( 'Normal', 'tenweb-builder') ]
	    );

	    $this->add_control(
		    $style . '_' . 'feature_background_color_normal',
		    [
			    'label'       => __( 'Background Color', 'tenweb-builder'),
			    'type'        => \Elementor\Controls_Manager::COLOR,
			    'selectors'   => [
				    $selector . ' .twbb-dynamic-features-list-item .twbb-dynamic-features-list-item-content' => 'background-color: {{VALUE}};',
			    ],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->add_control(
		    $style . '_' . 'heading_content_feature_border_style',
		    [
			    'label'     => __( 'Border', 'tenweb-builder'),
			    'type'      => \Elementor\Controls_Manager::HEADING,
			    'separator' => 'before',
		    ]
	    );

	    $defaults = [
		    'style_1' => [],
		    'style_2' => [],
		    'style_3' => [],
		    'style_4' => [],
		    'style_5' => [],
		    'style_6' => ['border' => ['default' => 'solid'], 'width' => ['default' => ['top' => 0, 'right' => 0, 'bottom' => 1, 'left' => 0, 'unit' => 'px']]],
		    'style_7' => [],
	    ];

	    $this->add_group_control(
		    \Elementor\Group_Control_Border::get_type(),
		    [
			    'name'      => $style . '_' . 'feature_border_normal',
			    'label'     => __( 'Border', 'tenweb-builder'),
			    'selector'  => $selector . ' .twbb-dynamic-features-list-item .twbb-dynamic-features-list-item-content',
          'fields_options' => $defaults[$style],
		    ]
	    );

	    $this->add_responsive_control(
		    $style . '_' . 'feature_border_radius_normal',
		    [
			    'label'      => __( 'Border Radius', 'tenweb-builder'),
			    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			    'size_units' => [ 'px', '%' ],
			    'selectors'  => [
				    $selector . ' .twbb-dynamic-features-list-item .twbb-dynamic-features-list-item-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			    ],
		    ]
	    );

	    $this->end_controls_tab();

	    $this->start_controls_tab(
		    $style . '_' . 'feature_background_hover',
		    [ 'label' => __( 'Hover', 'tenweb-builder') ]
	    );

	    $this->add_control(
		    $style . '_' . 'feature_background_color_hover',
		    [
			    'label'       => __( 'Background Color', 'tenweb-builder'),
			    'type'        => \Elementor\Controls_Manager::COLOR,
			    'selectors'   => [
				    $selector . ' .twbb-dynamic-features-list-item .twbb-dynamic-features-list-item-content:hover' => 'background-color: {{VALUE}};',
			    ],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->add_control(
		    $style . '_' . 'heading_content_feature_border_hover_style',
		    [
			    'label'     => __( 'Border', 'tenweb-builder'),
			    'type'      => \Elementor\Controls_Manager::HEADING,
			    'separator' => 'before',
		    ]
	    );

	    $this->add_group_control(
		    \Elementor\Group_Control_Border::get_type(),
		    [
			    'name'      => $style . '_' . 'feature_border_hover',
			    'label'     => __( 'Border', 'tenweb-builder'),
			    'selector'  => $selector . ' .twbb-dynamic-features-list-item .twbb-dynamic-features-list-item-content:hover',
		    ]
	    );

	    $this->add_responsive_control(
		    $style . '_' . 'feature_border_radius_hover',
		    [
			    'label'      => __( 'Border Radius', 'tenweb-builder'),
			    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			    'size_units' => [ 'px', '%' ],
			    'selectors'  => [
				    $selector . ' .twbb-dynamic-features-list-item .twbb-dynamic-features-list-item-content:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			    ],
		    ]
	    );

	    $this->end_controls_tab();

	    $this->start_controls_tab(
		    $style . '_' . 'feature_background_active',
		    [ 'label' => __( 'Active', 'tenweb-builder') ]
	    );

      // Do not pass an empty array to 'global' as it breaks the color selector.
	    $defaults = [
		    'style_1' => ['default' => '', 'global' => ['default' => 'globals/colors?id=twbb_transparent']],
		    'style_2' => ['default' => '', 'global' => ['default' => 'globals/colors?id=twbb_bg_3']],
		    'style_3' => ['default' => '', 'global' => ['default' => 'globals/colors?id=twbb_transparent']],
		    'style_4' => ['default' => '', 'global' => ['default' => 'globals/colors?id=twbb_transparent']],
		    'style_5' => ['default' => '', 'global' => ['default' => 'globals/colors?id=twbb_bg_3']],
		    'style_6' => ['default' => '', 'global' => ['default' => 'globals/colors?id=twbb_transparent']],
		    'style_7' => ['default' => '', 'global' => ['default' => 'globals/colors?id=twbb_bg_primary']],
	    ];

	    $this->add_control(
		    $style . '_' . 'feature_background_color_active',
		    [
			    'label'       => __( 'Background Color', 'tenweb-builder'),
			    'type'        => \Elementor\Controls_Manager::COLOR,
			    'selectors'   => [
				    $selector . ' .twbb-dynamic-features-list-item.active .twbb-dynamic-features-list-item-content' => 'background-color: {{VALUE}};',
			    ],
          'default' => $defaults[$style]['default'],
          'global' => $defaults[$style]['global'],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->add_control(
		    $style . '_' . 'heading_content_feature_border_active_style',
		    [
			    'label'     => __( 'Border', 'tenweb-builder'),
			    'type'      => \Elementor\Controls_Manager::HEADING,
			    'separator' => 'before',
		    ]
	    );

	    $this->add_group_control(
		    \Elementor\Group_Control_Border::get_type(),
		    [
			    'name'      => $style . '_' . 'feature_border_active',
			    'label'     => __( 'Border', 'tenweb-builder'),
			    'selector'  => $selector . ' .twbb-dynamic-features-list-item.active .twbb-dynamic-features-list-item-content',
		    ]
	    );

	    $defaults = [
		    'style_1' => [],
		    'style_2' => ['top' => '16', 'right' => '16', 'bottom' => '16', 'left' => '16', 'unit' => 'px', 'isLinked' => true],
		    'style_3' => ['top' => '16', 'right' => '16', 'bottom' => '16', 'left' => '16', 'unit' => 'px', 'isLinked' => true],
		    'style_4' => ['top' => '16', 'right' => '16', 'bottom' => '16', 'left' => '16', 'unit' => 'px', 'isLinked' => true],
		    'style_5' => ['top' => '16', 'right' => '16', 'bottom' => '16', 'left' => '16', 'unit' => 'px', 'isLinked' => true],
		    'style_6' => [],
		    'style_7' => ['top' => '16', 'right' => '16', 'bottom' => '16', 'left' => '16', 'unit' => 'px', 'isLinked' => true],
	    ];

	    $this->add_responsive_control(
		    $style . '_' . 'feature_border_radius_active',
		    [
			    'label'      => __( 'Border Radius', 'tenweb-builder'),
			    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			    'size_units' => [ 'px', '%' ],
          'default' => $defaults[$style],
			    'selectors'  => [
				    $selector . ' .twbb-dynamic-features-list-item.active .twbb-dynamic-features-list-item-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			    ],
		    ]
	    );

	    $this->end_controls_tab();
	    $this->end_controls_tabs();


	    $this->add_control(
	      $style . '_' . 'heading_content_feature_mobile_view_style',
		    [
			    'label' => __('Mobile View', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::HEADING,
			    'separator' => 'before',
		    ]
	    );

	    $defaults = [
		    'style_1' => 'tab',
		    'style_2' => 'tab',
		    'style_3' => 'slider',
		    'style_4' => 'tab',
		    'style_5' => 'tab',
		    'style_6' => 'tab',
		    'style_7' => 'tab',
	    ];

	    $this->add_control(
	      $style . '_' . 'mobile_view',
		    [
			    'label' => __('Features View Type', 'tenweb-builder'),
			    'description' => __('Choose how to show your features in mobile view', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SELECT,
			    'options' => [
				    'slider' => __('Slider', 'tenweb-builder'),
				    'tab' => __('Tab', 'tenweb-builder'),
			    ],
			    'default' => $defaults[$style],
			    'prefix_class' => 'twbb-dynamic-features-mobile-view-',
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'slider_pagination_background_color',
		    [
			    'label' => __('Background Color', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::COLOR,
          'default' => '#0000001A',
			    'selectors' => [
				    $selector . '.twbb-dynamic-features-mobile-view-slider .twbb-dynamic-features-swiper-pagination' => 'background-color: {{VALUE}};',
			    ],
			    'render_type' => 'ui',
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'mobile_view' ) => 'slider',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'slider_pagination_dot_color',
		    [
			    'label' => __('Dot Color', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::COLOR,
			    'default' => '#000000',
			    'selectors' => [
				    $selector . '.twbb-dynamic-features-mobile-view-slider .twbb-dynamic-features-swiper[data-autoplay=yes] .twbb-dynamic-features-swiper-pagination .swiper-pagination-bullet' => 'background-color: {{VALUE}}80;',
				    $selector . '.twbb-dynamic-features-mobile-view-slider .twbb-dynamic-features-swiper[data-autoplay=yes] .twbb-dynamic-features-swiper-pagination .swiper-pagination-bullet-active' => 'background-color: {{VALUE}}4D;',
				    $selector . '.twbb-dynamic-features-mobile-view-slider .twbb-dynamic-features-swiper[data-autoplay=yes] .twbb-dynamic-features-swiper-pagination .swiper-pagination-bullet-active .progress-indicator' => 'background-color: {{VALUE}};',
			    ],
			    'render_type' => 'ui',
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'mobile_view' ) => 'slider',
			    ],
		    ]
	    );

	    $this->add_group_control(
		    \Elementor\Group_Control_Border::get_type(),
		    [
			    'name' => $style . '_' . 'slider_pagination_border',
			    'label' => __('Border', 'tenweb-builder'),
			    'selector' => $selector . ' .twbb-dynamic-features-swiper-pagination',
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'mobile_view' ) => 'slider',
			    ],
		    ]
	    );

	    $this->add_responsive_control(
	      $style . '_' . 'slider_pagination_border_radius',
		    [
			    'label' => __('Border Radius', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::DIMENSIONS,
			    'size_units' => ['px', '%'],
			    'default' => [
				    'top' => '50',
				    'right' => '50',
				    'bottom' => '50',
				    'left' => '50',
				    'unit' => 'px',
				    'isLinked' => true,
			    ],
			    'selectors' => [
				    $selector . ' .twbb-dynamic-features-swiper-pagination' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			    ],
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'mobile_view' ) => 'slider',
			    ],
		    ]
	    );

      $this->end_controls_section();
    }
  }

  public function register_progress_indicator_style_controls() {
    foreach ($this->feature_styles as $style => $name) {
	    $condition = [ $this->get_control_id( 'feature_list_style_choice' ) => $style ];
	    $selector = '{{WRAPPER}}.twbb-dynamic-features-' . $style . '[data-widget_type="twbb_dynamic_features.' . $this->get_id() . '"]';

	    $this->start_controls_section(
	      $style . '_' . 'section_progress_indicator_style',
		    [
			    'label' => __('Progress Indicator', 'tenweb-builder'),
			    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
          'condition' => $condition,
		    ]
	    );

	    $defaults = [
		    'style_1' => ['default' => 24, 'mobile_default' => 24],
		    'style_2' => ['default' => 12, 'mobile_default' => 12],
		    'style_3' => ['default' => 12, 'mobile_default' => 12],
		    'style_4' => ['default' => 12, 'mobile_default' => 12],
		    'style_5' => ['default' => 12, 'mobile_default' => 12],
		    'style_6' => ['default' => 12, 'mobile_default' => 12],
		    'style_7' => ['default' => 12, 'mobile_default' => 12],
	    ];

	    $this->add_responsive_control(
	      $style . '_' . 'progress_indicator_distance',
		    [
			    'label' => __('Gap Between Feature Title', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SLIDER,
			    'size_units' => ['px', 'em'],
			    'range' => [
				    'px' => [
					    'min' => 0,
					    'max' => 100,
					    'step' => 1,
				    ],
				    'em' => [
					    'min' => 0,
					    'max' => 10,
					    'step' => 0.1,
				    ],
			    ],
          'default'    => [
            'unit' => 'px',
            'size' => $defaults[$style]['default']
          ],
          'mobile_default' => [
            'unit' => 'px',
            'size' => $defaults[$style]['mobile_default']
          ],
			    'selectors' => [
            $selector . '.twbb-dynamic-features-progress-bar-position-left .twbb-dynamic-features-tab-progress-indicator' => 'margin-right: {{SIZE}}{{UNIT}};',
            $selector . '.twbb-dynamic-features-progress-bar-position-right .twbb-dynamic-features-tab-progress-indicator' => 'margin-left: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'progress_bar_height',
		    [
			    'label' => __('Height', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SLIDER,
			    'size_units' => ['px', '%'],
			    'range' => [
				    'px' => [
					    'min' => 50,
					    'max' => 250,
				    ],
				    '%' => [
					    'min' => 10,
					    'max' => 100,
				    ],
			    ],
			    'default' => [
				    'unit' => '%',
				    'size' => 100,
			    ],
          'condition' => [
            $this->get_control_id( $style . '_' . 'progress_indicator_type' ) => 'bar',
          ],
			    'selectors' => [
            $selector . ' .twbb-dynamic-features-tab-progress-indicator' => 'height: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg.bar .progress-bar' => 'height: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg.bar .progress-bar' => '--height: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg.bar .progress-bar-bg' => 'height: {{SIZE}}{{UNIT}};',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'progress_bar_width',
		    [
			    'label' => __('Width', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SLIDER,
			    'size_units' => ['px', '%'],
			    'range' => [
				    'px' => [
					    'min' => 1,
					    'max' => 20,
				    ],
				    '%' => [
					    'min' => 1,
					    'max' => 100,
				    ],
			    ],
			    'default' => [
				    'unit' => 'px',
				    'size' => 6,
			    ],
          'condition' => [
            $this->get_control_id( $style . '_' . 'progress_indicator_type' ) => 'bar',
          ],
			    'selectors' => [
            $selector . ' .twbb-dynamic-features-tab-progress-indicator' => 'width: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg' => 'width: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg .progress-bar' => 'width: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg .progress-bar-bg' => 'width: {{SIZE}}{{UNIT}};',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'progress_bar_border_radius',
		    [
			    'label' => __('Border Radius', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SLIDER,
			    'size_units' => ['px'],
			    'range' => [
				    'px' => [
					    'min' => 0,
					    'max' => 10,
					    'step' => 1,
				    ],
			    ],
			    'default' => [
				    'unit' => 'px',
				    'size' => 0,
			    ],
          'condition' => [
            $this->get_control_id( $style . '_' . 'progress_indicator_type' ) => 'bar',
          ],
			    'selectors' => [
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg .progress-bar' => 'rx: {{SIZE}}{{UNIT}}; ry: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg .progress-bar-bg' => 'rx: {{SIZE}}{{UNIT}}; ry: {{SIZE}}{{UNIT}};',
			    ],
		    ]
	    );

	    $defaults = [
		    'style_1' => 30,
		    'style_2' => 30,
		    'style_3' => 30,
		    'style_4' => 30,
		    'style_5' => 30,
		    'style_6' => 40,
		    'style_7' => 30,
	    ];

	    $this->add_control(
	      $style . '_' . 'circle_progress_size',
		    [
			    'label' => __('Size', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SLIDER,
			    'size_units' => ['px'],
			    'range' => [
				    'px' => [
					    'min' => 20,
					    'max' => 100,
					    'step' => 1,
				    ],
			    ],
			    'default' => [
				    'unit' => 'px',
				    'size' => $defaults[$style],
			    ],
          'condition' => [
            $this->get_control_id( $style . '_' . 'progress_indicator_type' ) => 'circle',
          ],
			    'selectors' => [
            $selector . ' .twbb-dynamic-features-tab-progress-indicator' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-list-item-title' => 'line-height: {{SIZE}}{{UNIT}} !important;',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'circle_progress_stroke_width',
		    [
			    'label' => __('Width', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SLIDER,
			    'size_units' => ['px'],
			    'range' => [
				    'px' => [
					    'min' => 1,
					    'max' => 30,
					    'step' => 1,
				    ],
			    ],
			    'default' => [
				    'unit' => 'px',
				    'size' => 14,
			    ],
          'condition' => [
            $this->get_control_id( $style . '_' . 'progress_indicator_type' ) => 'circle',
          ],
			    'selectors' => [
            $selector . ' .twbb-dynamic-features-tab-progress-indicator .progress-circle' => 'stroke-width: {{SIZE}}{{UNIT}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator .progress-circle-bg' => 'stroke-width: {{SIZE}}{{UNIT}};',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'progress_indicator_color',
		    [
			    'label' => __('Color', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::COLOR,
			    'default' => '#0000001A',
			    'selectors' => [
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg .progress-circle-bg' => 'stroke: {{VALUE}};',
            $selector . ' .twbb-dynamic-features-tab-progress-indicator svg .progress-bar-bg' => 'fill: {{VALUE}};',
			    ],
			    'render_type' => 'ui',
		    ]
	    );

	    $defaults = [
		    'style_1' => 'globals/colors?id=twbb_button',
		    'style_2' => 'globals/colors?id=twbb_button',
		    'style_3' => 'globals/colors?id=primary',
		    'style_4' => 'globals/colors?id=accent',
		    'style_5' => 'globals/colors?id=twbb_button',
		    'style_6' => 'globals/colors?id=twbb_button',
		    'style_7' => 'globals/colors?id=twbb_button',
	    ];

	    $this->add_control(
	      $style . '_' . 'progress_indicator_progress_color',
		    [
			    'label' => __('Progress Color', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::COLOR,
			    'selectors' => [
		        $selector . ' .twbb-dynamic-features-tab-progress-indicator svg .progress-circle' => 'stroke: {{VALUE}};',
		        $selector . ' .twbb-dynamic-features-tab-progress-indicator svg .progress-bar' => 'fill: {{VALUE}};',
			    ],
				  'global' => [
            'default' => $defaults[$style],
          ],
			    'render_type' => 'ui',
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'heading_icon_style',
		    [
			    'label' => __('Icon', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::HEADING,
			    'separator' => 'before',
		    ]
	    );

	    $defaults = [
		    'style_1' => 8,
		    'style_2' => 8,
		    'style_3' => 8,
		    'style_4' => 8,
		    'style_5' => 8,
		    'style_6' => 12,
		    'style_7' => 8,
	    ];

	    $this->add_responsive_control(
	      $style . '_' . 'feature_icon_size',
		    [
			    'label' => __('Size', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SLIDER,
			    'size_units' => ['px'],
			    'range' => [
				    'px' => [
					    'min' => 6,
					    'max' => 50,
					    'step' => 1,
				    ],
			    ],
			    'default' => [
				    'unit' => 'px',
				    'size' => $defaults[$style],
			    ],
			    'selectors' => [
		        $selector . ' .twbb-dynamic-features-tab-icon svg' => 'width: {{SIZE}}{{UNIT}};',
			    ],
		    ]
	    );

	    $this->end_controls_section();
    }
  }

  public function register_features_description_control() {
	  foreach ($this->feature_styles as $style => $name) {
	    $condition = [ $this->get_control_id( 'feature_list_style_choice' ) => $style ];

	    $defaults = [
		    'style_1' => 'yes',
		    'style_2' => '',
		    'style_3' => '',
		    'style_4' => 'yes',
		    'style_5' => 'yes',
		    'style_6' => 'yes',
		    'style_7' => 'yes',
	    ];

	    $this->add_control(
		    $style . '_' . 'show_feature_description',
		    [
			    'label'        => __( 'Features Description', 'tenweb-builder'),
			    'type'         => \Elementor\Controls_Manager::SWITCHER,
			    'label_on'     => __( 'Show', 'tenweb-builder'),
			    'label_off'    => __( 'Hide', 'tenweb-builder'),
			    'return_value' => 'yes',
			    'default'      => $defaults[$style],
			    'condition'    => $condition,
		    ]
	    );
    }
  }

  public function register_progress_indicator_controls() {
    foreach ($this->feature_styles as $style => $name) {
	    $condition = [ $this->get_control_id( 'feature_list_style_choice' ) => $style ];

	    $this->start_controls_section(
	      $style . '_' . 'section_progress_indicator',
		    [
			    'label' => __('Progress Indicator', 'tenweb-builder'),
          'condition' => $condition,
		    ]
	    );

	    $defaults = [
		    'style_1' => 'yes',
		    'style_2' => 'yes',
		    'style_3' => 'yes',
		    'style_4' => 'yes',
		    'style_5' => 'yes',
		    'style_6' => '',
		    'style_7' => '',
	    ];

	    $this->add_control(
	      $style . '_' . 'autoplay',
		    [
			    'label' => __('Autoplay', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SWITCHER,
			    'label_on' => __('On', 'tenweb-builder'),
			    'label_off' => __('Off', 'tenweb-builder'),
			    'return_value' => 'yes',
			    'default' => $defaults[$style],
			    'frontend_available' => true,
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'heading_progress_indicator',
		    [
			    'label' => __('Progress Indicator', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::HEADING,
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'autoplay' ) => 'yes',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'loading_duration',
		    [
			    'label' => __('Loading Duration', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::NUMBER,
			    'default' => 5000,
			    'min' => 1000,
			    'max' => 20000,
			    'step' => 500,
			    'frontend_available' => true,
			    'render_type' => 'none',
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'autoplay' ) => 'yes',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'show_progress',
		    [
			    'label' => __('Progress Indicator', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SWITCHER,
			    'label_on' => __('On', 'tenweb-builder'),
			    'label_off' => __('Off', 'tenweb-builder'),
			    'return_value' => 'yes',
			    'default' => 'yes',
			    'frontend_available' => true,
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'autoplay' ) => 'yes',
			    ],
		    ]
	    );

	    $defaults = [
		    'style_1' => 'bar',
		    'style_2' => 'circle',
		    'style_3' => 'circle',
		    'style_4' => 'circle',
		    'style_5' => 'circle',
		    'style_6' => 'circle',
		    'style_7' => 'bar',
	    ];

	    $this->add_control(
	      $style . '_' . 'progress_indicator_type',
		    [
			    'label' => __('Progress Indicator Type', 'tenweb-builder'),
			    'label_block' => true,
			    'type' => \Elementor\Controls_Manager::SELECT,
			    'default' => $defaults[$style],
			    'options' => [
				    'bar' => __('Bar', 'tenweb-builder'),
				    'circle' => __('Circle', 'tenweb-builder'),
			    ],
			    'frontend_available' => true,
			    'render_type' => 'template',
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'show_progress' ) => 'yes',
				    $this->get_control_id( $style . '_' . 'autoplay' ) => 'yes',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'progress_indicator_position',
		    [
			    'label' => __('Progress Indicator Position', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::CHOOSE,
			    'options' => [
				    'left' => [
					    'title' => __('Left', 'tenweb-builder'),
					    'icon' => 'eicon-h-align-left',
				    ],
				    'right' => [
					    'title' => __('Right', 'tenweb-builder'),
					    'icon' => 'eicon-h-align-right',
				    ],
			    ],
			    'default' => 'left',
			    'toggle' => false,
			    'render_type' => 'template',
			    'prefix_class' => 'twbb-dynamic-features-progress-bar-position-',
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'show_progress' ) => 'yes',
				    $this->get_control_id( $style . '_' . 'autoplay' ) => 'yes',
			    ],
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'heading_icon',
		    [
			    'label' => __('Icon', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::HEADING,
		    ]
	    );

	    $defaults = [
		    'style_1' => '',
		    'style_2' => 'yes',
		    'style_3' => 'yes',
		    'style_4' => 'yes',
		    'style_5' => 'yes',
		    'style_6' => '',
		    'style_7' => '',
	    ];

	    $this->add_control(
	      $style . '_' . 'show_feature_icon',
		    [
			    'label' => __('Icon', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::SWITCHER,
			    'label_on' => __('On', 'tenweb-builder'),
			    'label_off' => __('Off', 'tenweb-builder'),
			    'return_value' => 'yes',
			    'default' => $defaults[$style],
			    'frontend_available' => true,
		    ]
	    );

	    $this->add_control(
	      $style . '_' . 'feature_icon',
		    [
			    'label' => __('Choose Icon', 'tenweb-builder'),
			    'type' => \Elementor\Controls_Manager::ICONS,
			    'default' => [
				    'value' => 'fas fa-chevron-right',
				    'library' => 'fa-solid',
			    ],
			    'condition' => [
				    $this->get_control_id( $style . '_' . 'show_feature_icon' ) => 'yes',
			    ],
			    'recommended' => [
				    'fa-solid' => [
					    'chevron-right',
					    'angle-right',
					    'arrow-right',
					    'caret-right',
					    'play',
				    ],
			    ],
		    ]
	    );

	    $this->end_controls_section();
    }
  }

	public function render() {
		$settings = $this->parent->get_settings_for_display();

		if (!isset($settings['features_list']) || !is_array($settings['features_list'])) {
			$settings['features_list'] = [];
		}
		?>
		<div class="twbb-dynamic-features-container">
			<div class="twbb-dynamic-features-inner-container">
				<?php $this->render_features_content($settings); ?>
				<?php $this->render_media_section($settings); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get widget data attributes
	 *
	 * @param array $settings Widget settings
	 * @return array Data attributes
	 */
	protected function get_widget_data_attributes($settings) {
		$animation_duration = isset($settings[ $this->get_control_id( 'image_animation_duration' ) ]['size']) ?
			$settings[ $this->get_control_id( 'image_animation_duration' ) ]['size'] : 500;

		$slides_spacing = isset($settings[ $this->get_control_id( 'slides_spacing' ) ]['size']) ?
			$settings[$this->get_control_id( 'slides_spacing' )]['size'] : 20;

		$interval = isset($settings[$this->get_feature_control_id( 'loading_duration', $settings )]) ? $settings[$this->get_feature_control_id( 'loading_duration', $settings )] : 5000;
		$animation = isset($settings[$this->get_control_id( 'image_animation' )]) ? $settings[$this->get_control_id( 'image_animation' )] : 'fade';
		$autoplay = isset($settings[$this->get_feature_control_id( 'autoplay', $settings )]) ? $settings[$this->get_feature_control_id( 'autoplay', $settings )] : 'yes';
		$direction = ($animation === 'vertical_slider') ? 'vertical' : 'horizontal';
		$adaptive_media = (isset($settings[$this->get_control_id( 'media_height' )]) && $settings[$this->get_control_id( 'media_height' )]['unit'] === '%') ? 'true' : 'false';
		$attributes = [
			'data-interval' => esc_attr($interval),
			'data-animation' => esc_attr($animation),
			'data-animation-duration' => esc_attr($animation_duration),
			'data-slides-spacing' => esc_attr($slides_spacing),
			'data-autoplay' => esc_attr($autoplay),
			'data-direction' => esc_attr($direction),
			'data-adaptive-media' => esc_attr($adaptive_media),
		];

		return $attributes;
	}

	protected function render_features_content($settings) {
		?>
		<div class="twbb-dynamic-features-content">
		<?php
		$this->render_heading($settings);
		$this->render_features_list($settings);
	  $this->render_buttons($settings);
		?>
		</div>
		<?php
	}

	/**
	 * Render the heading section
	 *
	 * @param array $settings Widget settings
	 */
	protected function render_heading($settings) {
		?>
		<div class="twbb-dynamic-features-heading">
			<?php if ('yes' === $settings[ $this->get_control_id('show_title') ]) { ?>
				<div class="twbb-dynamic-features-title-field"><?php echo esc_html($settings[ 'title_field' ]); ?></div>
			<?php } ?>
			<?php if ('yes' === $settings[ $this->get_control_id('show_description') ]) { ?>
				<div class="twbb-dynamic-features-description-field"><?php echo esc_html($settings[ 'description_field' ]); ?></div>
			<?php } ?>
		</div>
		<?php
	}

	protected function render_features_list($settings) {
		if (!isset($settings['features_list']) || empty($settings['features_list']) || !is_array($settings['features_list'])) {
			return;
		}
		?>
		<div class="twbb-dynamic-features-list">
			<?php
			foreach ($settings['features_list'] as $index => $tab) {
				$this->render_single_tab($settings, $tab, $index);
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render a single tab
	 *
	 * @param array $settings Widget settings
	 * @param array $tab Tab data
	 * @param int $index Tab index
	 */
	protected function render_single_tab($settings, $tab, $index) {
		?>
		<div class="twbb-dynamic-features-list-item">
			<?php $this->render_tab_content($settings, $tab, $index); ?>
		</div>
		<?php
	}

	/**
	 * Render tab icon if enabled
	 *
	 * @param array $settings Widget settings
	 */
	protected function render_tab_icon($settings) {
		if ($settings[$this->get_feature_control_id( 'show_feature_icon', $settings )] === 'yes') {
			$icon_class = 'twbb-dynamic-features-tab-icon';
			if ($settings[$this->get_feature_control_id( 'show_progress', $settings )] !== 'yes' || $settings[$this->get_feature_control_id( 'autoplay', $settings )] !== 'yes' || $settings[$this->get_feature_control_id( 'progress_indicator_position', $settings )] !== 'left') {
				$icon_class .= ' twbb-dynamic-features-tab-icon-show';
			}
			?>
			<div class="<?php echo esc_attr($icon_class); ?>">
				<?php \Elementor\Icons_Manager::render_icon($settings[ $this->get_feature_control_id('feature_icon', $settings) ], ['aria-hidden' => 'true']); ?>
			</div>
			<?php
		}
	}

	protected function render_tab_progress_indicator($settings) {
		if ($settings[$this->get_feature_control_id( 'show_progress', $settings )] === 'yes') {
			$icon_class = 'twbb-dynamic-features-tab-progress-indicator';
			if ($settings[$this->get_feature_control_id( 'show_feature_icon', $settings )] === 'yes') {
				$icon_class .= ' twbb-dynamic-features-tab-progress-indicator-hidden';
			}
			else {
				$icon_class .= ' twbb-dynamic-features-tab-progress-indicator-invisible';
			}
			?>
			<div class="<?php echo esc_attr($icon_class); ?>">
				<?php if ($settings[ $this->get_feature_control_id('progress_indicator_type', $settings) ] === 'circle') { ?>
					<svg class="circle" viewBox="-12.5 -12.5 125 125">
						<circle class="progress-circle-bg" r="40" cx="50" cy="50"></circle>
						<circle class="progress-circle" r="40" cx="50" cy="50"></circle>
					</svg>
				<?php } else { ?>
					<svg class="bar">
						<rect class="progress-bar-bg" x="0" y="0" />
						<rect class="progress-bar" x="0" y="0" />
					</svg>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Render tab content
	 *
	 * @param array $settings Widget settings
	 * @param array $tab Tab data
	 * @param int $index Tab index
	 */
	protected function render_tab_content($settings, $tab, $index) {
		?>
		<div class="twbb-dynamic-features-list-item-content">
			<?php $this->render_tab_icon($settings); ?>
			<?php
			if ($settings[ $this->get_feature_control_id('progress_indicator_position', $settings) ] === 'left') {
				$this->render_tab_progress_indicator($settings);
			}
			?>
			<div class="twbb-dynamic-features-text">
				<div class="twbb-dynamic-features-list-item-title">
					<span class="twbb-dynamic-features-list-item-title-span"><?php echo esc_html($tab['feature_title']); ?></span>
				</div>
				<?php if ($settings[ $this->get_feature_control_id('show_feature_description', $settings) ]) { ?>
					<div class="twbb-dynamic-features-list-item-description">
						<?php echo esc_html($tab['feature_description']); ?>
					</div>
				<?php } ?>
			</div>
			<?php
			if ($settings[ $this->get_feature_control_id('progress_indicator_position', $settings) ] === 'right') {
				$this->render_tab_progress_indicator($settings);
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render buttons
	 *
	 * @param array $settings Widget settings
	 */
	protected function render_buttons($settings) {
		?>
		<div class="twbb-dynamic-features-buttons-group">
			<?php
			if ('yes' === $settings[ $this->get_control_id('show_button_1') ]) {
				$this->render_button($this->parent, 'button_1_');
			}
			if ('yes' === $settings[ $this->get_control_id('show_button_2') ]) {
				$this->render_button($this->parent, 'button_2_');
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render media section
	 *
	 * @param array $settings Widget settings
	 */
	protected function render_media_section($settings) {
		$attributes = $this->get_widget_data_attributes($settings);
		?>
		<div class="twbb-dynamic-features-media twbb-dynamic-features-swiper" <?php foreach ($attributes as $name => $value) { echo esc_attr($name) . '="' . esc_attr($value) . '"'; } ?>>
			<div class="twbb-dynamic-features-media-carousel twbb-dynamic-features-swiper-wrapper <?php echo ($settings[ $this->get_control_id('image_animation') ] === 'vertical_slider') ? 'twbb-dynamic-features-media-carousel-vertical' : 'twbb-dynamic-features-media-carousel-horizontal'; ?>">
				<?php
				foreach ($settings['features_list'] as $index => $tab) {
					?>
					<div class="twbb-dynamic-features-slide-item twbb-dynamic-features-swiper-slide">
						<?php
						if ($tab['media_type'] === 'video') {
							$video_url = $this->get_video_url($tab);
							if (!empty($video_url)) {
								$this->create_video_element($video_url, $tab['video_type']);
							}
						} else {
							?>
							<div class="twbb-dynamic-features-slide-image" style="background-image: url(<?php echo esc_attr($tab['feature_image']['url']); ?>)"></div>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
      <div class="twbb-dynamic-features-swiper-pagination"></div>
		</div>
		<?php
	}

	/**
	 * Get video URL from tab data
	 *
	 * @param array $tab Tab data
	 * @return string Video URL or empty string
	 */
	protected function get_video_url($tab) {
		if ($tab['video_type'] === 'file' && !empty($tab['video_file']['url'])) {
			return $tab['video_file']['url'];
		}
		return !empty($tab['video_url']) ? $tab['video_url'] : '';
	}

	/**
	 * Create video element based on video type and URL
	 *
	 * @param string $video_url Video URL
	 * @param string $video_type Video type (youtube, vimeo, file, url)
	 * @return string HTML for video element
	 */
	protected function create_video_element($video_url, $video_type) {
		if (empty($video_url)) {
			return '';
		}

		$video_id = '';
		$video_html = '';

		switch ($video_type) {
			case 'youtube':
				$video_id = $this->get_video_id_from_url($video_url, 'youtube');
				if ($video_id) {
					?>
					<iframe class="twbb-dynamic-features-slide-video" src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>?autoplay=1&mute=1&loop=1&playlist=<?php echo esc_attr($video_id); ?>&controls=0&showinfo=0&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					<?php
				}
				break;

			case 'vimeo':
				$video_id = $this->get_video_id_from_url($video_url, 'vimeo');
				if ($video_id) {
					?>
					<iframe class="twbb-dynamic-features-slide-video" src="https://player.vimeo.com/video/<?php echo esc_attr($video_id); ?>?autoplay=1&muted=1&loop=1&background=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
					<?php
				}
				break;

			case 'file':
			case 'url':
				?>
				<video class="twbb-dynamic-features-slide-video" autoplay muted loop playsinline>
					<source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
				</video>
				<?php
				break;
		}

		return $video_html;
	}

	/**
	 * Extract video ID from YouTube or Vimeo URL
	 *
	 * @param string $url Video URL
	 * @param string $type Video type (youtube or vimeo)
	 * @return string|false Video ID or false if not found
	 */
	protected function get_video_id_from_url($url, $type) {
		if (empty($url)) {
			return false;
		}

		$video_id = false;

		switch ($type) {
			case 'youtube':
				// Handle various YouTube URL formats
				if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
					$video_id = $matches[1];
				}
				break;

			case 'vimeo':
				// Handle various Vimeo URL formats
				if (preg_match('/(?:vimeo\.com\/(?:channels\/|groups\/[^\/]*\/videos\/|album\/\d+\/video\/|video\/|))(\d+)/', $url, $matches)) {
					$video_id = $matches[1];
				}
				break;
		}

		return $video_id;
	}
}
