<?php
namespace Tenweb_Builder;
include_once (TWBB_DIR . '/widgets/traits/account_trait.php');
include_once (TWBB_DIR . '/widgets/traits/menuCart/menu_cart_trait.php');
include_once (TWBB_DIR . '/widgets/traits/button_trait.php');
include_once (TWBB_DIR . '/widgets/traits/Logo_Trait.php');

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Widget_Base;
use Tenweb_Builder\Widgets\Traits\Account_Trait;
use Tenweb_Builder\widgets\traits\menuCart\menuCart_Trait;
use Elementor\Icons_Manager;
use Tenweb_Builder\Widgets\Traits\Button_Trait;
use Tenweb_Builder\Widgets\Traits\Logo_Trait;

if ( ! defined( 'ABSPATH' ) ) exit;

class Header_Widget extends Widget_Base {
	  use Account_Trait;
      use menuCart_Trait;
      use Button_Trait;
      use Logo_Trait;

    protected $nav_menu_index = 1;

    public function get_name() {
        return Builder::$prefix . '-header-widget';
    }

    public function get_title() {
        return __( 'Header', 'tenweb-builder' );
    }

    public function get_icon() {
        return 'twbb-header twbb-widget-icon';
    }

    public function get_categories() {
        return [ 'tenweb-widgets' ];
    }

    public function get_script_depends() {
        return ['twbb-menu-cart-trait-script', 'twbb-smartmenus'];
    }

    public function get_style_depends() {
        return ['twbb-menu-cart-style', 'twbb-logo-style'];
    }


    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls( 'normal_' );
        $this->register_style_controls( 'scroll_', '{{WRAPPER}}.twbb-header-widget-scrolled' );
    }

    private function register_content_controls() {
        $this->start_controls_section(
            'section_layout',
            [
                'label' => __( 'Layout', 'tenweb-builder' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_responsive_control(
            'width',
            [
                'label' => __( 'Header Width', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'label_block' => true,
                'options' => [
                    'full-width' => __( 'Full Width', 'tenweb-builder' ),
                    'content-width' => __( 'Site Content Width', 'tenweb-builder' ),
                    'custom-width' => __( 'Custom Width', 'tenweb-builder' ),
                ],
                'default' => 'full-width',
                'prefix_class' => 'twbb-header-widget-',
            ]
        );

        $this->add_responsive_control(
            'custom_width',
            [
                'label' => __( 'Custom Width', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', /* '%',  */'vw' ],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1600,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}}.twbb-header-widget-custom-width .twbb-header-widget' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'width' => 'custom-width',
                ],
            ]
        );

        $this->add_responsive_control(
            'height',
            [
                'label' => __( 'Height', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 40,
                        'max' => 300,
                    ],
                    'vh' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-header-widget' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'header_headre_behavior',
            [
                'label' => __( 'Header Behavior', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( 'header_behavior_tabs' );

        $this->start_controls_tab(
            'header_behavior_normal_tab',
            [
                'label' => __( 'Normal', 'tenweb-builder' ),
            ]
        );

        $this->add_control(
            'sticky_header',
            [
                'label' => __( 'Sticky Header', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'prefix_class' => 'twbb-header-widget-sticky-',
            ]
        );

        $this->add_control(
            'floating_effect_normal',
            [
                'label' => __( 'Floating Effect', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'prefix_class' => 'twbb-header-widget-floating-',
            ]
        );

        $this->add_responsive_control(
            'floating_offset_normal',
            [
                'label' => __( 'Offset', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'condition' => [
                    'floating_effect_normal' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}}.twbb-header-widget-floating-yes' => 'top: {{SIZE}}{{UNIT}};--twbb-header-widget-floating-offset: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'floating_width_normal',
            [
                'label' => __( 'Width', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1600,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 90,
                ],
                'condition' => [
                    'floating_effect_normal' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}}.twbb-header-widget-floating-yes' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'header_behavior_scroll_tab',
            [
                'label' => __( 'On Scroll', 'tenweb-builder' ),
            ]
        );

        $this->add_control(
            'show_on_scroll',
            [
                'label' => __( 'Show On Scroll', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'prefix_class' => 'twbb-header-widget-show-on-scroll-',
                'condition' => [
                    'sticky_header' => '',
                ],
            ]
        );

        $this->add_control(
            'scroll_behavior',
            [
                'label' => __( 'Scroll Behavior', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'on_scroll_down',
                'options' => [
                    'on_scroll_up' => __( 'On Scroll Up', 'tenweb-builder' ),
                    'on_scroll_down' => __( 'On Scroll Down', 'tenweb-builder' ),
                ],
                'prefix_class' => 'twbb-header-widget-scroll-behavior-',
                'condition' => [
                    'sticky_header' => '',
                    'show_on_scroll' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'floating_effect_scroll',
            [
                'label' => __( 'Floating Effect', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'prefix_class' => 'twbb-header-widget-floating-on-scroll-',
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'show_on_scroll',
                            'operator' => '==',
                            'value' => 'yes',
                        ],
                        [
                            'name' => 'sticky_header',
                            'operator' => '==',
                            'value' => 'yes',
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'floating_offset_scroll',
            [
                'label' => __( 'Offset', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'conditions' => [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'floating_effect_scroll',
                            'operator' => '==',
                            'value' => 'yes',
                        ],
                        [
                            'relation' => 'or',
                            'terms' => [
                                [
                                    'name' => 'show_on_scroll',
                                    'operator' => '==',
                                    'value' => 'yes',
                                ],
                                [
                                    'name' => 'sticky_header',
                                    'operator' => '==',
                                    'value' => 'yes',
                                ],
                            ],
                        ],
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.twbb-header-widget-floating-on-scroll-yes.twbb-header-widget-floating' => 'top: {{SIZE}}{{UNIT}};--twbb-header-widget-floating-offset: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'floating_width_scroll',
            [
                'label' => __( 'Width', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1600,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 90,
                ],
                'conditions' => [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'floating_effect_scroll',
                            'operator' => '==',
                            'value' => 'yes',
                        ],
                        [
                            'relation' => 'or',
                            'terms' => [
                                [
                                    'name' => 'show_on_scroll',
                                    'operator' => '==',
                                    'value' => 'yes',
                                ],
                                [
                                    'name' => 'sticky_header',
                                    'operator' => '==',
                                    'value' => 'yes',
                                ],
                            ],
                        ],
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.twbb-header-widget-floating-on-scroll-yes.twbb-header-widget-floating' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'scroll_treshold',
            [
                'label' => __( 'Scroll Treshold', 'tenweb-builder' ),
                'description' => __( 'The Header Becomes Visible After Reaching a Scroll Threshold.', 'tenweb-builder' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 100,
                'frontend_available' => true,
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'show_on_scroll',
                            'operator' => '==',
                            'value' => 'yes',
                        ],
                        [
                            'relation' => 'and',
                            'terms' => [
                                [
                                    'name' => 'floating_effect_scroll',
                                    'operator' => '==',
                                    'value' => 'yes',
                                ],
                                [
                                    'relation' => 'or',
                                    'terms' => [
                                        [
                                            'name' => 'show_on_scroll',
                                            'operator' => '==',
                                            'value' => 'yes',
                                        ],
                                        [
                                            'name' => 'sticky_header',
                                            'operator' => '==',
                                            'value' => 'yes',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'floating_animation_speed',
            [
                'label' => __( 'Animation Speed', 'tenweb-builder' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 300,
                'frontend_available' => true,
				'selectors' => [
					'{{WRAPPER}}' => '--twbb-animation-duration: {{SIZE}}ms',
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'show_on_scroll',
                            'operator' => '==',
                            'value' => 'yes',
                        ],
                        [
                            'relation' => 'and',
                            'terms' => [
                                [
                                    'name' => 'floating_effect_scroll',
                                    'operator' => '==',
                                    'value' => 'yes',
                                ],
                                [
                                    'relation' => 'or',
                                    'terms' => [
                                        [
                                            'name' => 'show_on_scroll',
                                            'operator' => '==',
                                            'value' => 'yes',
                                        ],
                                        [
                                            'name' => 'sticky_header',
                                            'operator' => '==',
                                            'value' => 'yes',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'header_item_position',
            [
                'label' => __( 'Header Item Position', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'header_item_positions',
            [
                'label' => __( 'Header Item Positions', 'tenweb-builder' ),
                'label_block' => true,
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'logo-menu-cta' => __( 'Logo - Menu - CTA', 'tenweb-builder' ),
                    'menu-logo-cta' => __( 'Menu - Logo - CTA', 'tenweb-builder' ),
                ],
                'default' => 'logo-menu-cta',
                'prefix_class' => 'twbb-header-widget-',
            ]
        );

        $this->add_responsive_control(
            'menu_position',
            [
                'label' => __( 'Menu Position', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __( 'Left', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'flex-end' => [
                        'title' => __( 'Right', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'flex-start',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .twbb-header-widget-navigation' => 'justify-content: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();

	    $this->register_logo_content_section();

        $this->start_controls_section(
            'section_navigation',
            [
                'label' => __( 'Navigation', 'tenweb-builder' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        
        $menus = $this->get_available_menus();
        $navigation_link = esc_url(admin_url('nav-menus.php'));
        if ( !empty($menus) ) {
            $this->add_control('menu', [
                'label' => __('Menu', 'tenweb-builder'),
                'label_block' => true,
                'type' => Controls_Manager::SELECT,
                'options' => $menus,
                'default' => array_keys($menus)[0],
                'save_default' => TRUE,
                'description' => sprintf(__('Go to the <a class="twbb_nav_menu_widget_menu_link" href="%s" target="_blank">Menus screen</a> to manage your menus.', 'tenweb-builder'), esc_url($navigation_link)),
            ]);
        }
        else {
            $this->add_control('menu', [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => sprintf(__('<strong>There are no menus in your site.</strong><br>Go to the <a href="%s" target="_blank">Menus screen</a> to create one.', 'tenweb-builder'), admin_url('nav-menus.php?action=edit&menu=0')),
                'content_classes' => 'twbb-panel-alert twbb-panel-alert-info',
            ]);
        }

        $this->add_responsive_control(
            'menu_alignment',
            [
                'label' => __( 'Alignment', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
            ]
        );

        $this->add_control(
            'pointer',
            [
                'label' => __( 'Pointer', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'none' => __('None', 'tenweb-builder'),
                    'underline' => __('Underline', 'tenweb-builder'),
                    'overline' => __('Overline', 'tenweb-builder'),
                    'double-line' => __('Double Line', 'tenweb-builder'),
                    'framed' => __('Framed', 'tenweb-builder'),
                    'background' => __('Background', 'tenweb-builder'),
                    'text' => __('Text', 'tenweb-builder'),
                ],
                'default' => 'none',
            ]
        );

        $this->add_control('animation_line', [
            'label' => __('Animation', 'tenweb-builder'),
            'type' => Controls_Manager::SELECT,
            'default' => 'fade',
            'options' => [
              'fade' => 'Fade',
              'slide' => 'Slide',
              'grow' => 'Grow',
              'drop-in' => 'Drop In',
              'drop-out' => 'Drop Out',
              'none' => 'None',
            ],
            'condition' => [
              'pointer' => [ 'underline', 'overline', 'double-line' ],
            ],
          ]);
          $this->add_control('animation_framed', [
            'label' => __('Animation', 'tenweb-builder'),
            'type' => Controls_Manager::SELECT,
            'default' => 'fade',
            'options' => [
              'fade' => 'Fade',
              'grow' => 'Grow',
              'shrink' => 'Shrink',
              'draw' => 'Draw',
              'corners' => 'Corners',
              'none' => 'None',
            ],
            'condition' => [
              'pointer' => 'framed',
            ],
          ]);
          $this->add_control('animation_background', [
            'label' => __('Animation', 'tenweb-builder'),
            'type' => Controls_Manager::SELECT,
            'default' => 'fade',
            'options' => [
              'fade' => 'Fade',
              'grow' => 'Grow',
              'shrink' => 'Shrink',
              'sweep-left' => 'Sweep Left',
              'sweep-right' => 'Sweep Right',
              'sweep-up' => 'Sweep Up',
              'sweep-down' => 'Sweep Down',
              'shutter-in-vertical' => 'Shutter In Vertical',
              'shutter-out-vertical' => 'Shutter Out Vertical',
              'shutter-in-horizontal' => 'Shutter In Horizontal',
              'shutter-out-horizontal' => 'Shutter Out Horizontal',
              'none' => 'None',
            ],
            'condition' => [
              'pointer' => 'background',
            ],
          ]);
          $this->add_control('animation_text', [
            'label' => __('Animation', 'tenweb-builder'),
            'type' => Controls_Manager::SELECT,
            'default' => 'grow',
            'options' => [
              'grow' => 'Grow',
              'shrink' => 'Shrink',
              'sink' => 'Sink',
              'float' => 'Float',
              'skew' => 'Skew',
              'rotate' => 'Rotate',
              'none' => 'None',
            ],
            'condition' => [
              'pointer' => 'text',
            ],
          ]);

        $this->add_control(
            'hover_animation_speed',
            [
                'label' => __( 'Animation Speed', 'tenweb-builder' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 0.3,
                'selectors' => [
                    '{{WRAPPER}}' => '--twbb-hover-animation-duration: {{SIZE}}s',
                ],
            ]
        );

        $this->add_control(
            'submenu_indicator_icon',
            [
                'label' => __( 'Submenu Indicator Icon', 'tenweb-builder' ),
                'type' => Controls_Manager::ICONS,
                'frontend_available' => true,
                'default' => [
                    'value' => 'fas fa-chevron-down',
                ],
            ]
        );

        $this->add_control(
            'heading_mobile',
            [
                'label' => __( 'Mobile', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $breakpoints = \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();
        $breakpoint_options = [];
        
        foreach ($breakpoints as $breakpoint_key => $breakpoint) {
            $breakpoint_options[$breakpoint_key] = sprintf(
                '%s (%s)',
                $breakpoint->get_label(),
                $breakpoint->get_value() . 'px'
            );
        }

        $this->add_control(
            'breakpoint',
            [
                'label' => __( 'Breakpoint', 'tenweb-builder' ),
                'label_block' => true,
                'type' => Controls_Manager::SELECT,
                'default' => 'mobile',
                'options' => $breakpoint_options,
                'prefix_class' => 'twbb-header-widget-breakpoint-',
            ]
        );

        $this->add_control(
            'toggle_menu_icon',
            [
                'label' => __( 'Toggle Icon', 'tenweb-builder' ),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-bars',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_cta',
            [
                'label' => __( 'CTA', 'tenweb-builder' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->register_cta_content_contorls();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_ecommerce',
            [
                'label' => __( 'Ecommerce', 'tenweb-builder' ),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'woocommerce_active' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'woocommerce_active',
            [
                'type' => Controls_Manager::HIDDEN,
                'default' => class_exists('WooCommerce') ? 'yes' : 'no',
            ]
        );

        $this->add_control(
          'ecommerce_account_heading',
          [
            'label' => __( 'Account', 'tenweb-builder' ),
            'type' => Controls_Manager::HEADING,
          ]
        );

	      $this->register_account_content_controls();

        $this->end_controls_section();

        $this->register_menuCart_content_controls();
    }


	protected function register_logo_content_section() {
		$this->start_controls_section(
			'section_logo_content',
			[
				'label' => esc_html__( 'Logo', 'tenweb-builder' ),
			]
		);

		$this->register_logo_content_controls();

		$this->end_controls_section();
	}

    protected function register_cta_content_contorls() {

        //switcher to show or hide button 1
        $this->add_control(
            'show_button_1',
            [
                'label' => __('Button 1', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => __( 'Hide', 'tenweb-builder' ),
                'label_on' => __( 'Show', 'tenweb-builder' ),
            ]
        );

        $this->add_control(
            'heading_button_1',
            [
                'label' => __('Button 1', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => [
                    'show_button_1' => 'yes',
                ],
            ]
        );

        $this->register_button_content_controls([
            'button_default_text' => __('Button Text', 'tenweb-builder'),
            'text_control_label' => __('Button 1 Text', 'tenweb-builder'),
            'prefix' => 'button_1_',
            'section_condition' => [
                'show_button_1' => 'yes',
            ],
        ]);

        $this->add_control(
            'hr_1',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                'style' => 'thick',
            ]
        );

        //switcher to show or hide button 1
        $this->add_control(
            'show_button_2',
            [
                'label' => __('Button 2', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => __( 'Hide', 'tenweb-builder' ),
                'label_on' => __( 'Show', 'tenweb-builder' ),
            ]
        );

        $this->add_control(
            'heading_button_2',
            [
                'label' => __('Button 2', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => [
                    'show_button_2' => 'yes',
                ],
            ]
        );

        $this->register_button_content_controls([
            'button_default_text' => __('Button Text', 'tenweb-builder'),
            'text_control_label' => __('Button 2 Text', 'tenweb-builder'),
            'prefix' => 'button_2_',
            'section_condition' => [
                'show_button_2' => 'yes',
            ],
        ]);

        $this->add_control(
            'hr_2',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                'style' => 'thick',
            ]
        );

        //switcher to show or hide button 1
        $this->add_control(
            'show_button_3',
            [
                'label' => __('Button 3', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => __( 'Hide', 'tenweb-builder' ),
                'label_on' => __( 'Show', 'tenweb-builder' ),
            ]
        );

        $this->add_control(
            'heading_button_3',
            [
                'label' => __('Button 3', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => [
                    'show_button_3' => 'yes',
                ],
            ]
        );

        $this->register_button_content_controls([
            'button_default_text' => __('Button Text', 'tenweb-builder'),
            'text_control_label' => __('Button 3 Text', 'tenweb-builder'),
            'prefix' => 'button_3_',
            'section_condition' => [
                'show_button_3' => 'yes',
            ],
        ]);

    }

    protected function register_menuCart_content_controls() {
        $this->start_controls_section(
            'section_cart_menu_icon_content',
            [
                'label' => esc_html__( 'Menu Icon', 'tenweb-builder' ),
            ]
        );

        $this->register_menuCart_content_menuIcon_controls();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_cart',
            [
                'label' => esc_html__( 'Cart', 'tenweb-builder' ),
            ]
        );

        $this->register_menuCart_content_cart_controls();


        $this->end_controls_section();

        $this->start_controls_section(
            'section_additional_options',
            [
                'label' => esc_html__( 'Additional Options', 'tenweb-builder' ),
            ]
        );

        $this->register_menuCart_content_additionalOption_controls();

        $this->end_controls_section();
    }


    private function register_style_controls( $prefix = '', $selector = '{{WRAPPER}}' ) {
	    $this->start_controls_section(
            $prefix . 'section_layout_style',
            [
                'label' => __( 'Layout', 'tenweb-builder' ),
                'tab' => 'twbb_header_' . $prefix . 'style',
            ]
        );

        $this->add_responsive_control(
	        $prefix . 'header_padding',
            [
                'label' => __( 'Padding', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    $selector . ' .twbb-header-widget-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
	        $prefix . 'header_margin',
            [
                'label' => __( 'Margin', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
	                $selector . ' .elementor-widget-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
	        $prefix . 'header_space_between_items',
            [
                'label' => __( 'Space Between Items', 'tenweb-builder' ),
                'description' => __('Affect CTA & Ecommerce elements if available', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'condition' => [
                    'woocommerce_active' => 'yes',
                ],
                'selectors' => [
	                $selector . ' .twbb-header-widget .twbb-header-widget-ecommerce' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $prefix . 'header_box_background',
                'label' => __( 'Background', 'tenweb-builder' ),
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => '#ffffff00',
                    ],
                ],
                'types' => [ 'classic', 'gradient' ],
                'selector' => $selector . ' .twbb-header-widget-wrapper',
            ]
        );

        $this->add_control(
	        $prefix . 'blur_background',
          [
            'label' => esc_html__( 'Blur', 'tenweb-builder' ),
            'type' => Controls_Manager::SWITCHER,
            'label_on' => esc_html__( 'Yes', 'tenweb-builder' ),
            'label_off' => esc_html__( 'No', 'tenweb-builder' ),
            'return_value' => 'yes',
            'default' => 'no',
          ]
        );

        $this->add_control(
	        $prefix . 'blur_background_level',
          [
            'label' => esc_html__( 'Blur Level', 'tenweb-builder' ),
            'type' => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em', 'rem', '%', 'custom' ],
            'range' => [
              'px' => [
                'min' => 1,
                'max' => 15,
              ],
              '%' => [
                'max' => 100,
              ],
            ],
            'default' => [
              'size' => 7,
              'unit' => 'px',
            ],
            'selectors' => [
	            $selector . ' .twbb-header-widget-wrapper' => 'backdrop-filter: blur({{SIZE}}{{UNIT}});',
            ],
            'condition' => [
	            $prefix . 'blur_background' => 'yes',
            ],
          ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => $prefix . 'header_box_box_shadow',
                'label' => __( 'Box Shadow', 'tenweb-builder' ),
                'selector' => $selector . ' .twbb-header-widget-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => $prefix . 'header_box_border',
                'label' => __( 'Border', 'tenweb-builder' ),
                'selector' => $selector . ' .twbb-header-widget-wrapper',
            ]
        );

        $this->add_responsive_control(
	        $prefix . 'header_border_radius',
            [
                'label' => __( 'Border Radius', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
	                $selector . ' .twbb-header-widget-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

	    $this->register_logo_style_section( $prefix, $selector );

        $this->register_navigation_style_controls($prefix, $selector);

        $this->start_controls_section(
	        $prefix . 'section_cta_style',
            [
                'label' => __( 'CTA', 'tenweb-builder' ),
                'tab' => 'twbb_header_' . $prefix . 'style',
            ]
        );

        $this->register_cta_style_controls($prefix, $selector);

        $this->end_controls_section();

        $this->start_controls_section(
	        $prefix . 'section_ecommerce_style',
            [
                'label' => __( 'Ecommerce', 'tenweb-builder' ),
                'tab' => 'twbb_header_' . $prefix . 'style',
            ]
        );

        $this->add_control(
	        $prefix . 'ecommerce_account_style_heading',
          [
            'label' => __( 'Account', 'tenweb-builder' ),
            'type' => Controls_Manager::HEADING,
          ]
        );

	    $this->register_account_style_controls( $prefix, $selector );

        $this->end_controls_section();

        $this->register_menuCart_style_controls($prefix, $selector);
    }

    protected function register_logo_style_section( $prefix, $selector ) {
        $this->start_controls_section(
            $prefix . 'section_logo_style',
            [
                'label' => esc_html__( 'Logo', 'tenweb-builder' ),
                'tab' => 'twbb_header_' . $prefix . 'style',
            ]
        );

        $this->register_logo_style_controls($prefix, $selector);
        $this->end_controls_section();
    }

    protected function register_menuCart_style_controls( $prefix, $selector ) {
        $this->start_controls_section(
            $prefix . 'section_toggle_style',
            [
                'label' => esc_html__( 'Menu Icon', 'tenweb-builder' ),
                'tab' => 'twbb_header_' . $prefix . 'style',
            ]
        );

        $this->register_menuCart_style_menuIcon_controls($prefix, $selector);
        $this->end_controls_section();

        if( $prefix === 'normal_') {
            $this->start_controls_section(
                $prefix . 'section_cart_style',
                [
                    'label' => esc_html__('Cart', 'tenweb-builder'),
                    'tab' => 'twbb_header_' . $prefix . 'style',
                ]
            );

            $this->register_menuCart_style_cart_controls($prefix, $selector);
            $this->end_controls_section();

            $this->start_controls_section(
                $prefix . 'section_product_tabs_style',
                [
                    'label' => esc_html__('Products', 'tenweb-builder'),
                    'tab' => 'twbb_header_' . $prefix . 'style',
                ]
            );

            $this->register_menuCart_style_products_controls($prefix, $selector);
            $this->end_controls_section();

            $this->start_controls_section(
                $prefix . 'section_style_buttons',
                [
                    'label' => esc_html__('Buttons', 'tenweb-builder'),
                    'tab' => 'twbb_header_' . $prefix . 'style',
                ]
            );

            $this->register_menuCart_style_buttons_controls($prefix, $selector);
            $this->end_controls_section();

            $this->start_controls_section(
                $prefix . 'section_style_messages',
                [
                    'label' => esc_html__('Messages', 'tenweb-builder'),
                    'tab' => 'twbb_header_' . $prefix . 'style',
                ]
            );

            $this->register_menuCart_style_messages_controls($prefix, $selector);

            $this->end_controls_section();
        }
    }

    protected function register_navigation_style_controls($prefix, $selector) {
        $this->start_controls_section(
	        $prefix . 'section_navigation_style',
            [
                'label' => __( 'Navigation', 'tenweb-builder' ),
                'tab' => 'twbb_header_' . $prefix . 'style',
            ]
        );

        $this->navigation_main_controls($prefix, $selector);

        $this->mobile_navMenu_controls($prefix, $selector);

        $this->submenu_controls($prefix, $selector);

        $this->submenu_box_controls($prefix, $selector);

        $this->end_controls_section();
    }

    protected function register_cta_style_controls($prefix, $selector) {
        $this->add_control(
            $prefix . 'heading_button_1_style',
            [
                'label' => __('Button 1', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => [
                    'show_button_1' => 'yes',
                ],
            ]
        );

        $this->register_button_style_controls([
            'section_condition' => [
                'show_button_1' => 'yes',
            ],
            'prefix' => 'button_1_',
            'control_prefix' => $prefix,
            'selector' => $selector
        ]);

        $this->add_control(
            $prefix . 'heading_button_2_style',
            [
                'label' => __('Button 2', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_button_2' => 'yes',
                ],
            ]
        );

        $this->register_button_style_controls([
            'section_condition' => [
                'show_button_2' => 'yes',
            ],
            'prefix' => 'button_2_',
            'control_prefix' => $prefix,
            'selector' => $selector
        ]);

        $this->add_control(
            $prefix . 'heading_button_3_style',
            [
                'label' => __('Button 3', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_button_3' => 'yes',
                ],
            ]
        );

        $this->register_button_style_controls([
            'section_condition' => [
                'show_button_3' => 'yes',
            ],
            'prefix' => 'button_3_',
            'control_prefix' => $prefix,
            'selector' => $selector
        ]);

        $this->add_control(
            $prefix . 'heading_buttons_group_style',
            [
                'label' => __('Buttons Group', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'buttons_gap',
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
                    $selector . ' .twbb-header-widget-buttons-group ' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'buttons_alignment',
            [
                'label' => __( 'Buttons Alignment', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-left',
                        'value' => 'flex-start',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-center',
                        'value' => 'center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-right',
                        'value' => 'flex-end',
                    ],
                ],
                'selectors' => [
                    $selector . ' .twbb-header-widget-buttons-group' => 'align-items: {{VALUE}};',
                ],
            ]
        );
    }

    protected function navigation_main_controls($prefix, $selector) {
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_item_typography',
                'label' => __( 'Typography', 'tenweb-builder' ),
                'selector' => $selector . ' .twbb-nav-menu--main.twbb-nav-menu__container > ul > li > a.twbb-item',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'nav_menu_items_space_between',
            [
                'label' => __( 'Space between', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'nav_menu_padding',
            [
                'label' => __( 'Padding', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs($prefix . 'tabs_menu_item_style');
        $this->start_controls_tab($prefix . 'tab_menu_item_normal', [
            'label' => __('Normal', 'tenweb-builder'),
        ]);
        // Text Color
        $this->add_control(
            $prefix . 'menu_item_text_color_normal',
            [
                'label' => __( 'Text Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu > .menu-item a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $prefix . 'menu_item_background_type_normal',
                'types' => [ 'classic', 'gradient' ],
                'selector' => $selector . ' .twbb-nav-menu > .menu-item',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( $prefix . 'tab_menu_item_hover', [
            'label' => __('Hover', 'tenweb-builder'),
        ]);
        // Text Color
        $this->add_control(
            $prefix . 'menu_item_text_color_hover',
            [
                'label' => __( 'Text Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu > .menu-item:hover > a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $prefix . 'menu_item_background_type_hover',
                'types' => [ 'classic', 'gradient' ],
                'selector' => $selector . ' .twbb-nav-menu > .menu-item:hover',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( $prefix . 'tab_menu_item_active', [
            'label' => __('Active', 'tenweb-builder'),
        ]);
        // Text Color
        $this->add_control(
            $prefix . 'menu_item_text_color_active',
            [
                'label' => __( 'Text Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu > .current-menu-item a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $prefix . 'menu_item_background_type_active',
                'types' => [ 'classic', 'gradient' ],
                'selector' => $selector . ' .twbb-nav-menu > .current-menu-item',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        // Menu Item Padding (Responsive)
        $this->add_responsive_control(
            $prefix . 'menu_item_padding',
            [
                'label' => __( 'Menu Item Background Padding', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu > .menu-item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    $selector . ' .twbb-nav-menu--dropdown .twbb-header-widget-buttons-group' => 'padding-right: {{RIGHT}}{{UNIT}}; padding-left:{{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

    }

    protected function mobile_navMenu_controls($prefix, $selector) {
        //Mobile nav menu
        $this->add_control(
            $prefix . 'mobile_nav_menu',
            [
                'label' => __( 'Mobile', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        if( $prefix === 'normal_' ) {
            $this->add_control(
                $prefix . 'mobile_menu_animation',
                [
                    'label' => __( 'Mobile Menu Animation', 'tenweb-builder' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'top-to-bottom',
                    'options' => [
                        'left-to-right' => __( 'Left to right', 'tenweb-builder' ),
                        'top-to-bottom' => __( 'Apple Top to bottom', 'tenweb-builder' ),
                        'right-to-left' => __( 'Right to left', 'tenweb-builder' ),
                    ],
                    'prefix_class' => 'twbb-mobile-dropdown-menu-animation-',
                ]
            );
        }

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $prefix . 'mobile_menu_background_type',
                'types' => [ 'classic', 'gradient' ],
                'selector' => $selector . ' .twbb-nav-menu--dropdown',
            ]
        );

        $this->add_control(
            $prefix . 'menu_icon_color',
            [
                'label' => __( 'Menu Icon Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-menu-toggle:not(.twbb-active) .twbb-menu-toggle__icon--open' => 'color: {{VALUE}};fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_icon_size',
            [
                'label' => __( 'Menu Icon Size', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    $selector . ' .twbb-menu-toggle:not(.twbb-active) .twbb-menu-toggle__icon--open' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'close_icon_color',
            [
                'label' => __( 'Close Icon Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-menu-toggle.twbb-active .twbb-menu-toggle__icon--close' => 'color: {{VALUE}};fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'close_icon_size',
            [
                'label' => __( 'Close Icon Size', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    $selector . ' .twbb-menu-toggle.twbb-active .twbb-menu-toggle__icon--close' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
    }

    protected function submenu_controls($prefix, $selector) {
        //Submenu
        $this->add_control(
            $prefix . 'submenu_style',
            [
                'label' => __( 'Submenu', 'tenweb-builder' ),
            'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'submenu_item_typography',
                'label' => __( 'Submenu Item Typography', 'tenweb-builder' ),
                'selector' => $selector . ' .twbb-nav-menu__container .twbb-nav-menu ul.sub-menu a.twbb-sub-item,' .
                                $selector . ' .twbb-nav-menu--dropdown.twbb-nav-menu__container > ul > li > a.twbb-item',
            ]
        );
        // Menu Item Padding (Responsive)
        $this->add_responsive_control(
            $prefix . 'submenu_item_padding',
            [
                'label' => __( 'Submenu Item Background Padding', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu ul.sub-menu a.twbb-sub-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            $prefix . 'submenu_indicator_icon_size',
            [
                'label' => __( 'Submenu Indicator Icon Size', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu .sub-arrow' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'submenu_item_width',
            [
                'label' => __( 'Submenu Item Width', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu ul.sub-menu li.menu-item' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->start_controls_tabs($prefix . 'sub_menu_style');
        $this->start_controls_tab($prefix . 'sub_menu_normal', [
            'label' => __('Normal', 'tenweb-builder'),
        ]);

        $this->add_responsive_control(
            $prefix . 'submenu_item_text_color_normal',
            [
                'label' => __( 'Submenu Item Text Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu ul.sub-menu > li > a.twbb-sub-item' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'submenu_indicator_icon_color_normal',
            [
                'label' => __( 'Submenu Indicator Icon Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu .sub-arrow' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->start_controls_tab($prefix . 'sub_menu_hover', [
            'label' => __('Hover', 'tenweb-builder'),
        ]);

        $this->add_responsive_control(
            $prefix . 'submenu_item_text_color_hover',
            [
                'label' => __( 'Submenu Item Text Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu ul.sub-menu > li:hover > a.twbb-sub-item,' .
                    $selector . ' .twbb-nav-menu--dropdown ul > li:hover > a' => 'color: {{VALUE}};--twbb-menu-item-text-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'submenu_indicator_icon_color_hover',
            [
                'label' => __( 'Submenu Indicator Icon Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu .menu-item:hover > a > .sub-arrow' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
    }

    protected function submenu_box_controls($prefix, $selector) {
        //Submenu box
        $this->add_control(
            $prefix . 'submenu_box_style',
            [
                'label' => __( 'Submenu box', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->start_controls_tabs($prefix . 'sub_menu_box_style');
        $this->start_controls_tab($prefix . 'sub_menu_box_normal', [
            'label' => __('Normal', 'tenweb-builder'),
        ]);

        $this->add_control(
            $prefix . 'submenu_box_background_color_normal',
            [
                'label' => __( 'Background Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        //add controls for sub menu border type, border color, border width, border radius
        $this->add_control(
            $prefix . 'submenu_box_border_type_normal',
            [
                'label' => __( 'Border Type', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => [
                    'solid' => __( 'Solid', 'tenweb-builder' ),
                    'dashed' => __( 'Dashed', 'tenweb-builder' ),
                    'dotted' => __( 'Dotted', 'tenweb-builder' ),
                ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu' => 'border-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'submenu_box_border_color_normal',
            [
                'label' => __( 'Border Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'submenu_box_border_width_normal',
            [
                'label' => __( 'Border Width', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'submenu_box_border_radius_normal',
            [
                'label' => __( 'Border Radius', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px'],
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab($prefix . 'sub_menu_box_hover', [
            'label' => __('Hover', 'tenweb-builder'),
        ]);

        $this->add_control(
            $prefix . 'submenu_box_background_color_hover',
            [
                'label' => __( 'Background Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        //add controls for sub menu border type, border color, border width, border radius
        $this->add_control(
            $prefix . 'submenu_box_border_type_hover',
            [
                'label' => __( 'Border Type', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => [
                    'solid' => __( 'Solid', 'tenweb-builder' ),
                    'dashed' => __( 'Dashed', 'tenweb-builder' ),
                    'dotted' => __( 'Dotted', 'tenweb-builder' ),
                ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu:hover' => 'border-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'submenu_box_border_color_hover',
            [
                'label' => __( 'Border Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'submenu_box_border_width_hover',
            [
                'label' => __( 'Border Width', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'submenu_box_border_radius_hover',
            [
                'label' => __( 'Border Radius', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px'],
                'selectors' => [
                    $selector . ' .twbb-nav-menu--main .twbb-nav-menu .sub-menu:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
    }

    private function get_available_menus() {
        $menus = wp_get_nav_menus();
        $options = [];
        foreach ( $menus as $menu ) {
        $options[$menu->slug] = $menu->name;
        }

        return $options;
    }

    protected function render() {
        $settings = $this->get_controls_settings();
        ?>
        <div class="twbb-header-widget-wrapper">
            <div class="twbb-header-widget">
                <div class="twbb-header-widget-logo">
	                <?php $this->render_logo( $this ); ?>
                </div>
                <div class="twbb-header-widget-navigation">
                    <?php $this->render_nav_menu(); ?>
                </div>
                <div class="twbb-header-widget-cta">
                    <?php $this->render_cta( $settings ); ?>
                </div>
                <?php
                //check if woocommerce active
                if ( is_plugin_active('woocommerce/woocommerce.php' ) ) {
                    ?>
                    <div class="twbb-header-widget-ecommerce">
                        <?php $this->render_account( $this ); ?>
                    </div>
                    <div class="twbb-header-widget-menuCart">
                        <?php $this->render_menuCart( $this ); ?>
                    </div>
                    <?php
                }
                ?>
            </div>

        </div>
        <?php
    }

    protected function render_cta( $settings) {
        ?>
        <div class="twbb-header-widget-buttons-group">
            <?php
            if ('yes' === $settings[ 'show_button_1'] ) {
                $this->render_button($this, 'button_1_');
            }
            if ('yes' === $settings[ 'show_button_2'] ) {
                $this->render_button($this, 'button_2_');
            }
            if ('yes' === $settings[ 'show_button_3'] ) {
                $this->render_button($this, 'button_3_');
            }
            ?>
        </div>
        <?php
    }

    protected function get_nav_menu_index() {
        return $this->nav_menu_index++;
    }

    protected function render_nav_menu() {
        $settings = $this->get_active_settings();
        $wp_menu = isset($settings['menu']) ? wp_get_nav_menu_object( $settings['menu'] ) : '';
        $menu_term_id = 0;
        if ( is_array( $wp_menu ) || is_object($wp_menu) ) {
          $menu_term_id = $wp_menu->term_id;
        }
        $menu_ids = [
              'menu_id' => 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id(),
              'dropdown_id' => 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id()
        ];
        $args = [
          'echo' => FALSE,
          'menu' => isset($settings['menu']) ? $settings['menu'] : '',
          'menu_class' => 'twbb-nav-menu' . ' ' . 'twbb-menu_term_id-' . $menu_term_id,
          'menu_id' => $menu_ids['menu_id'],
          'fallback_cb' => '__return_empty_string',
          'container' => '',
        ];

        // Add custom filter to handle Nav Menu HTML output.
        add_filter('nav_menu_link_attributes', [ $this, 'handle_link_classes' ], 10, 4);
        add_filter('nav_menu_submenu_css_class', [ $this, 'handle_sub_menu_classes' ]);
        add_filter('nav_menu_item_id', '__return_empty_string');
        // General Menu.
        $menu_html = wp_nav_menu($args);
        // Dropdown Menu.
        $args['menu_id'] = $menu_ids['dropdown_id'];
        $dropdown_menu_html = wp_nav_menu($args);
        // Remove all our custom filters.
        remove_filter('nav_menu_link_attributes', [ $this, 'handle_link_classes' ]);
        remove_filter('nav_menu_submenu_css_class', [ $this, 'handle_sub_menu_classes' ]);
        remove_filter('nav_menu_item_id', '__return_empty_string');
        //check if this is edit mode
        if ( empty($menu_html) ) {
            if(\Elementor\Plugin::instance()->preview->is_preview_mode()){
                ?><div style="text-align:center;font-size:14px;">Selected Nav Menu is empty, please add Items to menu.</div><?php
            } else {
                return;
            }
        }

          $breakpoints = \Elementor\Plugin::$instance->breakpoints->get_breakpoints();

          $this->add_render_attribute('main-menu', 'class', [
            'twbb-nav-menu--main',
            'twbb-nav-menu__container',
          ]);
          if ( $settings['pointer'] ) :
            $this->add_render_attribute('main-menu', 'class', 'e--pointer-' . $settings['pointer']);
            foreach ( $settings as $key => $value ) :
              if ( 0 === strpos($key, 'animation') && $value ) :
                $this->add_render_attribute('main-menu', 'class', 'e--animation-' . $value);
                break;
              endif;
            endforeach;
          endif; ?>
            <nav <?php $this->print_render_attribute_string( 'main-menu' ); ?>>
                <?php
                // PHPCS - escaped by WordPress with "wp_nav_menu"
                echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </nav>
        <?php
        $this->render_menu_toggle( $settings );
        ?>
        <nav class="twbb-nav-menu--dropdown twbb-nav-menu__container">
            <?php
            // PHPCS - escaped by WordPress with "wp_nav_menu"
            echo $dropdown_menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $this->render_cta( $settings);
            ?>
        </nav>
        <?php
        if(defined('ELEMENTOR_PATH')) {
          include_once ELEMENTOR_PATH . 'includes/managers/icons.php';
          if(class_exists('\Elementor\Icons_Manager')) {
            \Elementor\Icons_Manager::enqueue_shim();
          }
        }
      }
    private function render_menu_toggle( $settings ) {

        $this->add_render_attribute( 'menu-toggle', [
            'class' => 'twbb-menu-toggle',
            'role' => 'button',
            'tabindex' => '0',
            'aria-label' => esc_html__( 'Menu Toggle', 'tenweb-builder' ),
            'aria-expanded' => 'false',
        ] );

        if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
            $this->add_render_attribute( 'menu-toggle', [
                'class' => 'twbb-clickable',
            ] );
        }

        ?>
        <div <?php $this->print_render_attribute_string( 'menu-toggle' ); ?>>
            <?php

            $open_class = 'twbb-menu-toggle__icon--open';
            $close_class = 'twbb-menu-toggle__icon--close';

            $normal_icon = ! empty( $settings['toggle_menu_icon']['value'] )
                ? $settings['toggle_menu_icon']
                : [
                    'library' => 'fa-solid',
                    'value' => 'far fa-bars',
                ];
            $is_normal_icon_svg = 'svg' === $normal_icon['library'];

            if ( $is_normal_icon_svg ) {
                echo '<span class="' . esc_attr( $open_class ) . '">';
            }

            Icons_Manager::render_icon(
                $normal_icon,
                [
                    'aria-hidden' => 'true',
                    'role' => 'presentation',
                    'class' => $open_class,
                ]
            );

            if ( $is_normal_icon_svg ) {
                echo '</span>';
            }

            $active_icon = [
                    'library' => 'fa-solid',
                    'value' => 'far fa-window-close',
                ];

            $is_active_icon_svg = 'svg' === $active_icon['library'];

            if ( $is_active_icon_svg ) {
                echo '<span class="' . esc_attr( $close_class ) . '">';
            }

            Icons_Manager::render_icon(
                $active_icon,
                [
                    'aria-hidden' => 'true',
                    'role' => 'presentation',
                    'class' => $close_class,
                ]
            );

            if ( $is_active_icon_svg ) {
                echo '</span>';
            }
            ?>
            <span class="elementor-screen-only"><?php echo esc_html__( 'Menu', 'tenweb-builder' ); ?></span>
        </div>
        <?php
    }

    public function handle_link_classes( $atts, $item, $args, $depth ) {
        $classes = $depth ? 'twbb-sub-item' : 'twbb-item';
        $is_anchor = false !== strpos( $atts['href'], '#' );

        if ( ! $is_anchor && in_array('current-menu-item', $item->classes, true) ) {
            $classes .= '  twbb-item-active';
        }
        if ( empty($atts['class']) ) {
            $atts['class'] = $classes;
        }
        else {
            $atts['class'] .= ' ' . $classes;
        }

        return $atts;
    }

    public function handle_sub_menu_classes( $classes ) {
        $classes[] = 'twbb-nav-menu--dropdown';

    return $classes;
    }
}

\Elementor\Plugin::instance()->widgets_manager->register( new Header_Widget() );
