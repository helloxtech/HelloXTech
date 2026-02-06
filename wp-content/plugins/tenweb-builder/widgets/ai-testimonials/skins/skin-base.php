<?php
namespace Tenweb_Builder\Widgets\AI_Testimonials\Skins;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Elementor\Widget_Base;
use Tenweb_Builder\Modules\Helper;
use Tenweb_Builder\Widget_Slider;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base {
    protected function _register_controls_actions() {
        add_action( 'elementor/element/twbb_ai_testimonials/section_view_type/before_section_end', [ $this, 'register_view_type_controls' ], 11 );
        add_action( 'elementor/element/twbb_ai_testimonials/section_view_type/after_section_end', [ $this, 'register_content_controls' ] );
        add_action( 'elementor/element/twbb_ai_testimonials/section_general_style/before_section_end', [ $this, 'register_gen_style_sections' ] );
        add_action( 'elementor/element/twbb_ai_testimonials/section_general_style/after_section_end', [ $this, 'register_other_style_sections' ] );
       
        //inject widget slider in skins
        add_action( 'elementor/element/twbb_ai_testimonials/section_view_type/before_section_end', [ $this, 'register_slider_control' ], 10 );
        add_action( 'elementor/element/twbb_ai_testimonials/section_view_type/after_section_end', [ $this, 'register_slider_content_controls' ] );
        add_action( 'elementor/element/twbb_ai_testimonials/section_general_style/after_section_end', [ $this, 'register_slider_style_controls' ] );
    }
    public function register_slider_control( Widget_Base $widget ) {
        $this->parent = $widget;
        Widget_Slider::add_general_controls($this, 'no', true);
    }
    public function register_slider_content_controls( Widget_Base $widget ) {
        $this->parent = $widget;
        Widget_Slider::add_content_controls($this, true);
    }
    public function register_slider_style_controls( Widget_Base $widget ) {
        $this->parent = $widget;
        Widget_Slider::add_style_controls($this, true);

        $this->update_slider_related_controls();
    }

    public function register_view_type_controls( Widget_Base $widget ) {
        $this->parent = $widget;

        // In the Testimonial View Type section, add this control first
        $this->add_control(
            'view_type',
            [
                'label' => __('View Type', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid' => __('Grid', 'tenweb-builder'),
                    'masonry' => __('Masonry', 'tenweb-builder'),
                ],
                'render_type' => 'template',
                'prefix_class' => 'elementor-grid-view-',
            ]
        );

        // After the view type control, update the columns control
        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'prefix_class' => 'elementor-grid%s-',
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-grid' => '--columns: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-masonry-wrapper' => '--columns: {{VALUE}};',
                ],
            ]
        );

    }

    public function register_content_controls( Widget_Base $widget ) {
        $this->parent = $widget;
        // Remove the slider-specific controls from Layout Options section
        $this->start_controls_section(
            'section_layout_options',
            [
                'label' => __('Layout Options', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Add content alignment control first
        $this->add_responsive_control(
            'content_alignment',
            [
                'label' => __('Content Alignment', 'tenweb-builder'),
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
                'tablet_default' => 'left',
                'mobile_default' => 'center',
                'selectors_dictionary' => [
                    'left' => 'start',
                    'center' => 'center',
                    'right' => 'end',
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-content2' => 'align-items: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-stars' => 'justify-content: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-quote' => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-avatar' => 'justify-content: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-avatar-content' => 'align-items: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-quote-icon' => 'justify-content: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-quote-wrapper' => 'width: 100%;',
                    '{{WRAPPER}} .twbb-testimonial-logo-above' => 'justify-content: {{VALUE}}; width: 100%; display: flex;',
                    '{{WRAPPER}} .twbb-testimonial-logo-right' => 'justify-content: {{VALUE}}; display: flex;',
                ],
            ]
        );

        // In the Layout Options section, add this control after content_alignment
        $this->add_responsive_control(
            'show_graphic_element',
            [
                'label' => __('Graphic Element', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'default' => 'no',
                'tablet_default' => 'no',
                'mobile_default' => 'no',
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-graphic-element' => 'display: {{VALUE}};',
                ],
                'selectors_dictionary' => [
                    'yes' => 'flex',
                    'no' => 'none',
                ],
                'render_type' => 'template',
                'frontend_available' => true,
            ]
        );

        // Update the graphic_element_position control
        $this->add_responsive_control(
            'graphic_element_position',
            [
                'label' => __('Graphic Element Position', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'tenweb-builder'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'top' => [
                        'title' => __('Top', 'tenweb-builder'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'right' => [
                        'title' => __('Right', 'tenweb-builder'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'bottom' => [
                        'title' => __('Bottom', 'tenweb-builder'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'left',
                'tablet_default' => 'left',
                'mobile_default' => 'top',
                'toggle' => false,
                'condition' => [
                    $this->get_control_id('show_graphic_element') => 'yes',
                ],
                'selectors_dictionary' => [
                    'left' => 'row',
                    'right' => 'row-reverse',
                    'top' => 'column',
                    'bottom' => 'column-reverse',
                ],
                'prefix_class' => 'elementor-graphic-position%s-',
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-content' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        // Keep these controls but remove slider-specific conditions
        $this->add_control(
            'show_stars',
            [
                'label' => __('Rating stars', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'default' => 'yes',
            ]
        );

        // Add stars_location control right after show_stars
        $this->add_control(
            'stars_location',
            [
                'label' => __('Stars Location', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'above',
                'options' => [
                    'above' => __('Above Content', 'tenweb-builder'),
                    'below' => __('Below Content', 'tenweb-builder'),
                ],
                'condition' => [
                    $this->get_control_id('show_stars') => 'yes',
                ],
                'prefix_class' => 'twbb-testimonial-stars-',
            ]
        );

        // Move show_quote_icon control here and rename it
        $this->add_control(
            'show_quote_icon',
            [
                'label' => __('Quote Icon', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'default' => 'no',
            ]
        );

        // Move quote_icon control here and hide its label
        $this->add_control(
            'quote_icon',
            [
                'label' => '', // Hide the label
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-quote-left',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    $this->get_control_id('show_quote_icon') => 'yes',
                ],
            ]
        );

        // After quote_icon_size control, add this heading control
        $this->add_control(
            'author_info_heading',
            [
                'label' => __('Author Info', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'show_author_image',
            [
                'label' => __('Author Image', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'default' => 'yes',
            ]
        );

        $this->add_responsive_control(
            'author_image_position',
            [
                'label' => __('Author Image Position', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'tenweb-builder'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'top' => [
                        'title' => __('Top', 'tenweb-builder'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'right' => [
                        'title' => __('Right', 'tenweb-builder'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'bottom' => [
                        'title' => __('Bottom', 'tenweb-builder'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'left',
                'prefix_class' => 'twbb-testimonial-author-image%s-',
                'condition' => [
                    $this->get_control_id('show_author_image') => 'yes',
                ],
            ]
        );

        // Replace the existing author_text_alignment control with this responsive version
        $this->add_responsive_control(
            'author_text_alignment',
            [
                'label' => __('Author Text Alignment', 'tenweb-builder'),
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
                'tablet_default' => 'left',
                'mobile_default' => 'center',
                'selectors_dictionary' => [
                    'left' => 'flex-start',
                    'center' => 'center',
                    'right' => 'flex-end',
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-avatar-wrapper' => 'align-items: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-avatar-wrapper .twbb-testimonial-avatar-content' => 'align-items: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-text' => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-text2' => 'text-align: {{VALUE}};',
                ],
                'prefix_class' => 'twbb-testimonial-author-text%s-',
            ]
        );

        $this->add_control(
            'show_company_logo',
            [
                'label' => __('Company Logo', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'company_logo_location',
            [
                'label' => __('Company Logo Location', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'side',
                'options' => [
                    'side' => __('Show with Author info', 'tenweb-builder'),
                    'above_quote' => __('Show above content', 'tenweb-builder'),
                ],
                'prefix_class' => 'twbb-testimonial-company-logo-',
                'condition' => [
                    $this->get_control_id('show_company_logo') => 'yes',
                ],
                'render_type' => 'template',
            ]
        );

        $this->add_responsive_control(
            'company_logo_position',
            [
                'label' => __('Company Logo Position', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'tenweb-builder'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'top' => [
                        'title' => __('Top', 'tenweb-builder'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'right' => [
                        'title' => __('Right', 'tenweb-builder'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'bottom' => [
                        'title' => __('Bottom', 'tenweb-builder'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'render_type' => 'template',
                'default' => 'right',
                'tablet_default' => 'right',
                'mobile_default' => 'bottom',
                'prefix_class' => 'twbb-testimonial-company-logo-pos%s-',
                'condition' => [
                    $this->get_control_id('show_company_logo') => 'yes',
                    $this->get_control_id('company_logo_location') => 'side',
                ],
            ]
        );

        $this->add_control(
            'show_divider',
            [
                'label' => __('Divider', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'default' => 'yes',
                'render_type' => 'template',
                'condition' => [
                    $this->get_control_id('show_company_logo') => 'yes',
                    $this->get_control_id('company_logo_location') => 'side',
                    $this->get_control_id('company_logo_position') => ['left', 'right'],
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function register_gen_style_sections( Widget_Base $widget )
    {
        $this->parent = $widget;
        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial' => '--background-color-primary: {{VALUE}};',
                ],
            ]
        );

        // Add Column Gap control here
        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __('Column Gap', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-grid, {{WRAPPER}} .twbb-testimonial-grid .twbb-testimonial-masonry-wrapper' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
                'separator' => 'before',
                'frontend_available' => true,
                'render_type' => 'template',
            ]
        );

        // Add Row Gap control here
        $this->add_responsive_control(
            'row_gap',
            [
                'label' => __('Row Gap', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twbb-testimonial-grid .twbb-testimonial-masonry-wrapper .elementor-grid-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
    }
    public function register_other_style_sections( Widget_Base $widget )
    {
        // Add this after the General section and before the Stars Style section
        $this->start_controls_section(
            'section_testimonial_card',
            [
                'label' => __('Testimonial Card', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Add Card Padding control
        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Card Padding', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => '32',
                    'right' => '32',
                    'bottom' => '32',
                    'left' => '32',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Add Card Border Radius control
        $this->add_responsive_control(
            'card_border_radius',
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
                    '{{WRAPPER}} .twbb-testimonial-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Normal/Hover state controls
        $this->start_controls_tabs('card_style_tabs');

        // Normal state
        $this->start_controls_tab(
            'card_style_normal',
            [
                'label' => __('Normal', 'tenweb-builder'),
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'card_background',
                'label' => __('Background', 'tenweb-builder'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .twbb-testimonial-item',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'label' => __('Border', 'tenweb-builder'),
                'selector' => '{{WRAPPER}} .twbb-testimonial-item',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'label' => __('Box Shadow', 'tenweb-builder'),
                'selector' => '{{WRAPPER}} .twbb-testimonial-item',
            ]
        );

        $this->end_controls_tab();

        // Hover state
        $this->start_controls_tab(
            'card_style_hover',
            [
                'label' => __('Hover', 'tenweb-builder'),
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'card_background_hover',
                'label' => __('Background', 'tenweb-builder'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .twbb-testimonial-item:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'card_border_hover',
                'label' => __('Border', 'tenweb-builder'),
                'selector' => '{{WRAPPER}} .twbb-testimonial-item:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow_hover',
                'label' => __('Box Shadow', 'tenweb-builder'),
                'selector' => '{{WRAPPER}} .twbb-testimonial-item:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        // Hover Animation Controls
        $this->add_control(
            'hover_animation_heading',
            [
                'label' => __('Hover Animation', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => __('Animation Type', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'tenweb-builder'),
                    'float' => __('Float', 'tenweb-builder'),
                    'scale' => __('Scale', 'tenweb-builder'),
                    'float-scale' => __('Float & Scale', 'tenweb-builder'),
                ],
                'prefix_class' => 'testimonial-hover-animation-',
            ]
        );

        // Add transition duration control
        $this->add_control(
            'card_transition_duration',
            [
                'label' => __('Transition Duration', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 0.3,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-item' => '--transition-duration: {{SIZE}}s;transition: all {{SIZE}}s ease-in-out;',
                ],
                'condition' => [
                    $this->get_control_id('hover_animation!') => 'none'
                ],
            ]
        );

        $this->end_controls_section();

        // Stars Style Section
        $this->start_controls_section(
            'section_stars_style',
            [
                'label' => __('Stars', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'stars_color',
            [
                'label' => __('Stars Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_ACCENT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-stars path' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'stars_size',
            [
                'label' => __('Stars Size', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 23,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-stars svg' => 'width: {{SIZE}}{{UNIT}}; height: calc({{SIZE}}{{UNIT}} * 0.826);',
                ],
            ]
        );

        $this->add_responsive_control(
            'stars_gap',
            [
                'label' => __('Gap', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 2,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-stars' => '--stars-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'stars_spacing_bottom',
            [
                'label' => __('Space Below Stars', 'tenweb-builder'),
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
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 32,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-stars' => '--stars-spacing-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_stars') => 'yes',

                ],
            ]
        );

        $this->add_control(
            'stars_unmarked_color',
            [
                'label' => __('Unmarked Stars Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#CCCCCC',
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-stars path[fill="#CCCCCC"]' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Text Style Section
        $this->start_controls_section(
            'section_text_style',
            [
                'label' => __('Quote', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Content Style
        $this->add_control(
            'quote_heading',
            [
                'label' => __('Quote Text', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'quote_color',
            [
                'label' => __('Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-quote' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'quote_typography',
                'label' => __('Typography', 'tenweb-builder'),
                'global' => [
                    'default' => 'globals/typography?id=twbb_h6',
                ],
                'selector' => '{{WRAPPER}} .twbb-testimonial-quote',
            ]
        );

        // Add this new control after the quote typography control
        $this->add_responsive_control(
            'quote_spacing_bottom',
            [
                'label' => __('Space Below', 'tenweb-builder'),
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
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 32,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-quote' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quote_icon_heading',
            [
                'label' => __('Quote Icon', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    $this->get_control_id('show_quote_icon') => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'quote_icon_size',
            [
                'label' => __('Size', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 32,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-quote-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twbb-testimonial-quote-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twbb-testimonial-quote-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_quote_icon') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'quote_icon_color',
            [
                'label' => __('Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-quote-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-quote-icon svg' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('show_quote_icon') => 'yes',
                ],
            ]
        );

        // Keep quote_icon_spacing control after size
        $this->add_responsive_control(
            'quote_icon_spacing',
            [
                'label' => __('Bottom Spacing', 'tenweb-builder'),
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
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-quote-icon' => '--quote-icon-spacing: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_quote_icon') => 'yes',
                ],
            ]
        );

        // Author Name Style
        $this->add_control(
            'author_heading',
            [
                'label' => __('Author Name', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'author_name_color',
            [
                'label' => __('Author Name Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-text' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'author_typography',
                'label' => __('Author Typography', 'tenweb-builder'),
                'global' => [
                    'default' => 'globals/typography?id=twbb_bold',
                ],
                'selector' => '{{WRAPPER}} .twbb-testimonial-text',
            ]
        );

        $this->add_responsive_control(
            'author_info_space_below',
            [
                'label' => __('Space Below Author Info', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 1000],
                    '%' => ['min' => 0, 'max' => 100],
                    'vw' => ['min' => 0, 'max' => 100],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-avatar-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Position & Company Style
        $this->add_control(
            'position_heading',
            [
                'label' => __('Position & Company', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'position_company_color',
            [
                'label' => __('Position & Company Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-text2' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'position_typography',
                'label' => __('Position & Company Typography', 'tenweb-builder'),
                'global' => [
                    'default' => 'globals/typography?id=twbb_p3',
                ],
                'selector' => '{{WRAPPER}} .twbb-testimonial-text2',
            ]
        );

        $this->end_controls_section();

        // Image Style Section
        $this->start_controls_section(
            'section_image_style',
            [
                'label' => __('Author Image', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Image Size
        $this->add_responsive_control(
            'image_size',
            [
                'label' => __('Size', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 1000],
                    '%' => ['min' => 0, 'max' => 100],
                    'vw' => ['min' => 0, 'max' => 100],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 56,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-avatar-image' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twbb-testimonial-avatar-image img' => 'width: 100%; height: 100%; object-fit: cover;',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_space_below',
            [
                'label' => __('Space Below/Between', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 1000],
                    '%' => ['min' => 0, 'max' => 100],
                    'vw' => ['min' => 0, 'max' => 100],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-avatar-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Image Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'label' => __('Border', 'tenweb-builder'),
                'selector' => '{{WRAPPER}} .twbb-testimonial-avatar-image img',
            ]
        );

        // Image Border Radius
        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Border Radius', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-avatar-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Image Effects
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'label' => __('Box Shadow', 'tenweb-builder'),
                'selector' => '{{WRAPPER}} .twbb-testimonial-avatar-image img',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name' => 'image_css_filters',
                'selector' => '{{WRAPPER}} .twbb-testimonial-avatar-image img',
            ]
        );

        $this->end_controls_section();

        // Spacing Section
        $this->start_controls_section(
            'section_divider_style',
            [
                'label' => __('Divider', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'divider_spacing',
            [
                'label' => __('Divider Spacing', 'tenweb-builder'),
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
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 24,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-avatar-divider' => 'margin: 0 {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_divider') => 'yes',
                    $this->get_control_id('show_company_logo') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label' => __('Divider Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-avatar-divider' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('show_divider') => 'yes',
                    $this->get_control_id('show_company_logo') => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Add new section for Company Logo Style
        $this->start_controls_section(
            'section_company_logo_style',
            [
                'label' => __('Company Logo', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    $this->get_control_id('show_company_logo') => 'yes',
                ],
            ]
        );

        // Logo Width
        $this->add_responsive_control(
            'company_logo_width',
            [
                'label' => __('Logo Width', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 300,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 140,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-logos' => '--logo-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'company_logo_height',
            [
                'label' => __('Logo Height', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 200,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 20,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 56,
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-logos' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twbb-testimonial-logos img' => 'height: {{SIZE}}{{UNIT}}; width: auto;',
                    '{{WRAPPER}} .twbb-testimonial-logos svg' => 'height: {{SIZE}}{{UNIT}}; width: 100%;',
                ],
            ]
        );

        $this->add_control(
            'company_logo_color',
            [
                'label' => __('Logo Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-logos svg path' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .twbb-testimonial-logos svg' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'company_logo_spacing_bottom',
            [
                'label' => __('Space Below Logo', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-logo-right, {{WRAPPER}} .twbb-testimonial-logo-above' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Add this new section after the Image Style section and before the Divider section
        $this->start_controls_section(
            'section_graphic_element_style',
            [
                'label' => __('Graphic Element', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    $this->get_control_id('show_graphic_element') => 'yes',
                ],
            ]
        );

        // Width
        $this->add_responsive_control(
            'graphic_element_width',
            [
                'label' => __('Width', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 1000],
                    '%' => ['min' => 0, 'max' => 100],
                    'vw' => ['min' => 0, 'max' => 100],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 61,
                    'sizes' => []
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-graphic-element-inner,
                    {{WRAPPER}} .twbb-testimonial-graphic-element' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Height
        $this->add_responsive_control(
            'graphic_element_height',
            [
                'label' => __('Height', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vh'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 1000],
                    '%' => ['min' => 0, 'max' => 100],
                    'vh' => ['min' => 0, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-graphic-element-inner,
                    {{WRAPPER}} .twbb-testimonial-graphic-element' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Add this after the Border Width control and before the Border Radius control
        $this->add_control(
            'graphic_element_border_style',
            [
                'label' => __('Border Style', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'tenweb-builder'),
                    'solid' => __('Solid', 'tenweb-builder'),
                    'dashed' => __('Dashed', 'tenweb-builder'),
                    'dotted' => __('Dotted', 'tenweb-builder'),
                    'double' => __('Double', 'tenweb-builder'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-graphic-element-inner' => 'border-style: {{VALUE}};',
                ],
            ]
        );

        // Border Width
        $this->add_responsive_control(
            'graphic_element_border_width',
            [
                'label' => __('Border Width', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-graphic-element-inner' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('graphic_element_border_style!') => 'none',
                ],
            ]
        );

        // Border Color
        $this->add_control(
            'graphic_element_border_color',
            [
                'label' => __('Border Color', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-graphic-element-inner' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('graphic_element_border_style!') => 'none',
                ],
            ]
        );

        // Border Radius
        $this->add_responsive_control(
            'graphic_element_border_radius',
            [
                'label' => __('Border Radius', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-graphic-element-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Gap
        $this->add_responsive_control(
            'graphic_element_gap',
            [
                'label' => __('Gap', 'tenweb-builder'),
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
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb-testimonial-content' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function update_slider_related_controls() {
        $this->parent->update_control(
            $this->get_control_id('slides_per_view'),
            [
                'label' => __( 'Testimonials per Slide', 'tenweb-builder')
            ]
        );
        $view_type_conditions = $this->parent->get_controls($this->get_control_id('view_type'))['condition'];
        $view_type_conditions[$this->get_control_id('slider_view!')] = 'yes';

        $this->parent->update_control(
            $this->get_control_id('view_type'),
            [
                'condition' => $view_type_conditions
            ]
        );

        $columns_conditions = $this->parent->get_controls($this->get_control_id('columns'))['condition'];
        $columns_conditions[$this->get_control_id('slider_view!')] = 'yes';

        $this->parent->update_control(
            $this->get_control_id('columns'),
            [
                'condition' => $columns_conditions
            ]
        );

    }

    public function render() {
        $settings = $this->parent->get_settings();
        $container_classes = $this->get_container_classes($settings);
        $this->parent->add_render_attribute( 'twbb-testimonial-view-type', [
                'class'         => 'twbb-testimonial-grid',
            ]
        );
        $items_count = count( $settings['testimonial_items'] );
        if ('yes' === $settings[$this->get_control_id('slider_view')]) {
            $settings['space_between'] = $settings[$this->get_control_id('column_gap')];
            $elementorBreakpoints = \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();
            foreach ($elementorBreakpoints as $breakpointName => $breakpointValue) {
                $settings['space_between_' . $breakpointName] = $settings[$this->get_control_id('column_gap_' . $breakpointName)];
            }
            $this->parent->add_render_attribute( 'twbb-testimonial-item', [
                    'class'         => Widget_Slider::ITEM_CLASS,
                ]
            );
            $this->parent->add_render_attribute( 'twbb-testimonial-view-type', Widget_Slider::get_slider_attributes($settings, $items_count, 'columns', true) );
        }
        else {
            $this->parent->add_render_attribute( 'twbb-testimonial-view-type', [
                    'class'         => 'elementor-grid',
                ]
            );
            $this->parent->add_render_attribute( 'twbb-testimonial-item', [
                    'class'         => 'elementor-grid-item',
                ]
            );
        }
        ?>
        <div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
            <?php if (!empty($settings['testimonial_items'])) { ?>
                <div <?php $this->parent->print_render_attribute_string('twbb-testimonial-view-type'); ?>>
                    <?php
                    if ('yes' === $settings[$this->get_control_id('slider_view')]) {
                        Widget_Slider::slider_wrapper_start();
                    }
                    else if ($settings[$this->get_control_id('view_type')] === 'masonry') {
                        echo '<div class="twbb-testimonial-masonry-wrapper">';
                    }

                    foreach ($settings['testimonial_items'] as $item) {
                        $this->render_testimonial_item($item, $settings);
                    }

                    if ('yes' === $settings[$this->get_control_id('slider_view')]) {
                        $arrows_icon = isset($settings[$this->get_control_id('arrows_icon')]) ? $settings[$this->get_control_id('arrows_icon')] : 'arrow2';
                        Widget_Slider::slider_wrapper_end(['items_count' => $items_count, 'arrows_icon' => $arrows_icon]);
                    }
                    else if ($settings[$this->get_control_id('view_type')] === 'masonry') {
                        echo '</div>';
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    protected function get_container_classes($settings) {
        $classes = [
            'twbb-testimonial',
            'twbb-testimonial-view-grid'
        ];

        if ('yes' === $settings[$this->get_control_id('show_company_logo')]) {
            $classes[] = 'company-logo-' . $settings[$this->get_control_id('company_logo_location')];
        }

        return $classes;
    }

    protected function render_testimonial_item($item, $settings) {
        $this->parent->add_render_attribute( 'twbb-testimonial-item', [
                'class'         => 'twbb-testimonial-content twbb-testimonial-item elementor-repeater-item-' . $item['_id'],
            ]
        );
        ?>
        <div <?php $this->parent->print_render_attribute_string('twbb-testimonial-item'); ?>>
            <?php
            $this->render_graphic_element($item, $settings);
            ?>
            <div class="twbb-testimonial-content2">
                <?php
                $this->render_stars($item, $settings, 'above');
                $this->render_company_logo($item, $settings, 'above_quote');
                $this->render_quote($item, $settings);
                $this->render_stars($item, $settings, 'below');
                $this->render_author_info($item, $settings);
                ?>
            </div>
        </div>
        <?php
    }

    protected function render_graphic_element($item, $settings) {
        if ('yes' !== $settings[$this->get_control_id('show_graphic_element')]) {
            return;
        }

        $type = $item['graphic_element_type'];
        $media_url = '';

        if ($type === 'image' && !empty($item['graphic_element_image']['url'])) {
            $media_url = $item['graphic_element_image']['url'];
        } elseif ($type === 'video' && !empty($item['graphic_element_video']['url'])) {
            $media_url = $item['graphic_element_video']['url'];
        }

        $this->render_graphic_element_wrapper($type, $media_url);
    }

    protected function render_graphic_element_wrapper($type, $url) {
        ?>
        <div class="twbb-testimonial-graphic-element">
            <div class="twbb-testimonial-graphic-element-inner">
                <?php
                if ($type === 'video') {
                    $this->render_graphic_element_video($url);
                } else {
                    $this->render_graphic_element_image($url);
                }
                ?>
            </div>
        </div>
        <?php
    }

    protected function render_graphic_element_video($url) {
        if (empty($url)) {
            // Render placeholder for video
            ?>
            <div class="twbb-testimonial-video-placeholder">
                <i class="eicon-video-camera"></i>
            </div>
            <?php
        } else {
        ?>
            <video class="twbb-testimonial-graphic-element-video" autoplay loop muted playsinline>
                <source src="<?php echo esc_url($url); ?>" type="video/mp4">
            </video>
        <?php
        }
    }

    protected function render_graphic_element_image($url) {
        //the text GE is added to be sure that div has content and will be displayed properly in some cases(position top)
        ?>
        <div class="twbb-testimonial-graphic-element-image" style="background-image: url('<?php echo esc_url($url); ?>');">GE</div>
        <?php
    }

    protected function render_stars($item, $settings, $location) {
        if ('yes' !== $settings[$this->get_control_id('show_stars')] || $settings[$this->get_control_id('stars_location')] !== $location) {
            return;
        }
        ?>
        <div class="twbb-testimonial-stars">
            <?php
            $num_stars = $item['number_of_stars'];
            $full_stars = floor($num_stars);
            $partial = $num_stars - $full_stars;

            for ($i = 0; $i < 5; $i++) {
                $this->render_single_star($i, $full_stars, $partial, $item);
            }
            ?>
        </div>
        <?php
    }

    protected function render_single_star($index, $full_stars, $partial, $item) {
        $fill_color = ($index < $full_stars) ? 'currentColor' : '#CCCCCC';
        $star_path = 'M9.07088 0.668008C9.41462 -0.148451 10.5854 -0.14845 10.9291 0.66801L12.9579 5.4869C13.1029 5.8311 13.4306 6.06628 13.8067 6.09606L19.0727 6.51314C19.9649 6.58381 20.3267 7.6838 19.6469 8.25906L15.6348 11.6543C15.3482 11.8969 15.223 12.2774 15.3106 12.64L16.5363 17.7167C16.744 18.5768 15.7969 19.2567 15.033 18.7958L10.5245 16.0752C10.2025 15.8809 9.7975 15.8809 9.47548 16.0752L4.96699 18.7958C4.20311 19.2567 3.25596 18.5768 3.46363 17.7167L4.68942 12.64C4.77698 12.2774 4.65182 11.8969 4.36526 11.6543L0.353062 8.25906C-0.326718 7.6838 0.0350679 6.58381 0.927291 6.51314L6.19336 6.09606C6.5695 6.06628 6.89716 5.8311 7.04207 5.4869L9.07088 0.668008Z';
        ?>
        <svg width="23" height="19" viewBox="0 0 23 19" fill="none" xmlns="http://www.w3.org/2000/svg">
            <?php if ($index === $full_stars && $partial > 0) : ?>
                <defs>
                    <clipPath id="partial-clip-<?php echo esc_attr($item['_id']); ?>-<?php echo esc_attr($index); ?>">
                        <rect width="<?php echo esc_attr($partial * 100); ?>%" height="100%"/>
                    </clipPath>
                </defs>
            <?php endif; ?>
            <path d="<?php echo esc_attr($star_path); ?>" fill="<?php echo esc_attr($fill_color); ?>"/>
        </svg>
        <?php
    }

    protected function render_company_logo($item, $settings, $location) {
        if ('yes' !== $settings[$this->get_control_id('show_company_logo')] ||
            $settings[$this->get_control_id('company_logo_location')] !== $location ) {
            return;
        }

        $wrapper_class = $location === 'side' ? 'twbb-testimonial-logo-right' : 'twbb-testimonial-logo-above';
        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>">
            <?php
            $logo_url = $item['company_logo']['url'];

            // Check if it's Elementor's default placeholder image
            if (empty($logo_url) || strpos($logo_url, 'placeholder.png') !== false) {
                $logo_url = TWBB_URL . '/assets/images/ai_testimonial_default_logo.svg';
            }
            $is_svg = isset($logo_url) && pathinfo($logo_url, PATHINFO_EXTENSION) === 'svg';
            if ($is_svg) {
                ?>
                <div class="twbb-testimonial-logos">
                  <?php
                  Helper::print_svg_image($logo_url);
                  ?>
                </div>
                <?php
            } else {
                echo '<img class="twbb-testimonial-logos" src="' . esc_url($logo_url) . '" alt="' . esc_attr($item['company_name']) . '" />';
            }
            ?>
        </div>
        <?php
    }

    protected function render_quote($item, $settings) {
        ?>
        <div class="twbb-testimonial-quote-wrapper">
            <?php if ('yes' === $settings[$this->get_control_id('show_quote_icon')]) : ?>
                <div class="twbb-testimonial-quote-icon">
                    <?php \Elementor\Icons_Manager::render_icon($settings[$this->get_control_id('quote_icon')], ['aria-hidden' => 'true']); ?>
                </div>
            <?php endif; ?>
            <div class="twbb-testimonial-quote"><?php echo esc_html($item['quote_text']); ?></div>
        </div>
        <?php
    }

    protected function render_author_info($item, $settings) {
        ?>
        <div class="twbb-testimonial-avatar">
            <div class="twbb-testimonial-avatar-wrapper">
                <?php $this->render_author_image($item, $settings); ?>
                <div class="twbb-testimonial-avatar-content">
                    <div class="twbb-testimonial-text"><?php echo esc_html($item['author_name']); ?></div>
                    <div class="twbb-testimonial-text2">
                        <?php
                        echo esc_html($item['author_position']);
                        echo $item['company_name'] ? ', ' . esc_html($item['company_name']) : '';
                        ?>
                    </div>
                </div>
            </div>

            <?php
            // Only show divider if company logo is shown and position is side
            if ('yes' === $settings[$this->get_control_id('show_company_logo')] &&
                'side' === $settings[$this->get_control_id('company_logo_location')] &&
                'yes' === $settings[$this->get_control_id('show_divider')] &&
                !empty($item['company_logo']['url']) &&
                in_array($settings[$this->get_control_id('company_logo_position')], ['left', 'right'], true)) {
                echo '<div class="twbb-testimonial-avatar-divider">&nbsp;</div>';
            }

            if ('side' === $settings[$this->get_control_id('company_logo_location')]) {
                $this->render_company_logo($item, $settings, 'side');
            }
            ?>
        </div>
        <?php
    }

    protected function render_author_image($item, $settings) {
        if ('yes' === $settings[$this->get_control_id('show_author_image')]) {
            $author_image_url = $item['author_image']['url'];
            // Check if it's Elementor's default placeholder image
            if (empty($author_image_url) || strpos($author_image_url, 'placeholder.png') !== false) {
                $author_image_url = TWBB_URL . '/assets/images/ai_testimonial_avatar.jpg';
            }?>
            <div class="twbb-testimonial-avatar-image">
                <img src="<?php echo esc_url($author_image_url); ?>" alt="<?php echo esc_attr($item['author_name']); ?>">
            </div>
            <?php
        }
    }
}
