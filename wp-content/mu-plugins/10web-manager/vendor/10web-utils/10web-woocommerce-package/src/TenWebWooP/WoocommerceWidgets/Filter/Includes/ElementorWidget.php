<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;
use Tenweb_Builder\Classes\Woocommerce\Woocommerce;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Filter\FilterBuilder;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Query\QueryBuilder;

class ElementorWidget extends \Elementor\Widget_Base {

    private $filter;

    public function get_name() {
        return 'tenweb-woo-filter';
    }

    public function get_title() {
        return 'Filter';
    }

    public function get_icon() {
        return 'twbb-widget-icon twbb-filtera';
    }

    public function get_categories() {
        return array( Woocommerce::WOOCOMMERCE_GROUP );
    }

    /**
     * Register widget controls.
     */
    public function register_controls() {
        $filters = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getFilters();
        $filter_control_options = array();
        $tww_post_type_url = add_query_arg(array('post_type' => 'tww_filter'), admin_url() . 'edit.php');
        $default_filter = '';

        if (is_array($filters)) {
            foreach ($filters as $filter) {
                if (isset($filter->post_title, $filter->ID)) {
                    if (empty($default_filter)) {
                        $default_filter = $filter->ID;
                    }
                    $filter_control_options[$filter->ID] = $filter->post_title;
                }
            }
        }
        $url = add_query_arg(array('action' => 'tww_get_popup', 'elementor_callback' => 1, 'TB_iframe' => '1', 'width' => 600, 'height' => 700), admin_url('admin-ajax.php'));

        $this->start_controls_section(
            'tww_general',
            array(
                'label' => __('General', 'tww-filter'),
            )
        );
        $this->add_control(
            'tww_control_new_filter',
            array(
                'label' => '<span data-src="' . $url . '" class="tww_open_filter_popup_label">Add new filter</span>',
                'type' => \Elementor\Controls_Manager::BUTTON,
                'button_type' => 'default',
                'text' => 'New filter',
                'classes' => 'tww_open_filter_popup_button',
                'description' => ''
            )
        );
        $this->add_control(
            'tww_expand_filter',
            array(
                'label' => 'Expand filter',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        $this->add_control(
            'tww_control_filter',
            array(
                'label' => 'Select filter <span class="tww_elementor_edit_filter">Edit filter</span>',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $filter_control_options,
                'description' => '',
                'classes' => 'tww_control_filter',
                'default' => $default_filter
            )
        );

        $this->end_controls_section();
        $this->start_controls_section(
            'content_section_style_radio_checkbox',
            array(
                'label' => 'Filter without Page Reloading',
                'tab' => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'tww_control_ajax_filtering',
            array(
                'label' => 'Filter without Page Reloading',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        $this->add_control(
            'tww_control_ajax_beautify_url',
            array(
                'label' => 'Pretty URLs',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => array('tww_control_ajax_filtering' => 'yes'),
            )
        );
        $this->add_control(
            'tww_filter_button_text',
            array(
                'label' => 'Filter button text',
                'type' => \Elementor\Controls_Manager::TEXT,
                'classes' => '',
                'description' => '',
                'input_type' => 'text',
                'classes' => 'tww_filter_button_text',
                'default' => 'Filter',
                'condition' => array('tww_control_ajax_filtering' => ''),
            )
        );
        $this->add_control(
            'tww_reset_button_text',
            array(
                'label' => 'Reset button text',
                'type' => \Elementor\Controls_Manager::TEXT,
                'text' => 'Reset',
                'classes' => '',
                'description' => '',
                'input_type' => 'text',
                'default' => 'Reset',
                'condition' => array('tww_control_ajax_filtering' => ''),
            )
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_general',
            array(
                'label' => 'General',
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
        $this->add_control(
            'filter_title_alignment',
            array(
                'label' => 'Title Alignment',
                'type' => Controls_Manager::CHOOSE,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_field_title' => 'text-align: {{VALUE}}',
                ),
                'options' => array(
                    'left' => array(
                        'title' => esc_html__('Left', 'elementor'),
                        'icon' => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => esc_html__('Center', 'elementor'),
                        'icon' => 'eicon-text-align-center',
                    ),
                    'right' => array(
                        'title' => esc_html__('Right', 'elementor'),
                        'icon' => 'eicon-text-align-right',
                    ),
                ),
                'default' => 'left',
            )
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_field_title_typography',
                'label' => 'Field title Typography',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_p3',
                ),
                'selector' => '{{WRAPPER}} .tww_filter_field_title',
            )
        );

        $this->start_controls_tabs('filter_title_colors');
        $this->start_controls_tab('filter_title_color_normal', array( 'label' => 'Normal' ));
        $this->add_control(
            'filter_title_color',
            array(
                'label' => 'Title color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_field_title' => 'color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();

        $this->start_controls_tab('filter_title_color_hover', array( 'label' => 'Hover' ));
        $this->add_control(
            'filter_title_hover_color',
            array(
                'label' => 'Title hover color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_field_title:hover' => 'color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'filter_field_spacing',
            array(
                'label' => 'Field spacing',
                'type' => Controls_Manager::SLIDER,
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 200,
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_field_block, {{WRAPPER}} .twwf_filter_actions ' => 'margin-top: {{SIZE}}{{UNIT}}',
                ),
                'default' => array(
                    'size' => 15,
                    'unit' => 'px',
                ),
            )
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'collapse_expand_reset_typography',
                'label' => 'Collapse, expand and reset all typography ',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_bold',
                ),
                'selector' => '{{WRAPPER}} .twwf_expand_collapse_filter, {{WRAPPER}} .twwf_reset_filtered_fields',
            )
        );

        $this->start_controls_tabs('collapse_expand_reset_colors');
        $this->start_controls_tab('collapse_expand_reset_color_normal', array( 'label' => 'Normal' ));
        $this->add_control(
            'collapse_expand_reset_color',
            array(
                'label' => 'Collapse, expand and reset all text color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .twwf_expand_collapse_filter, {{WRAPPER}} .twwf_reset_filtered_fields' => 'color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('collapse_expand_reset_color_hover', array( 'label' => 'Hover' ));
        $this->add_control(
            'collapse_expand_reset_hover_color',
            array(
                'label' => 'Collapse, expand and reset all hover text color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .twwf_expand_collapse_filter:hover, {{WRAPPER}} .twwf_reset_filtered_fields:hover' => 'color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'collapse_expand_reset_icon_color',
            array(
                'label' => 'Collapse, expand and reset all icon color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .twwf_collapse_filter:after, {{WRAPPER}} .twwf_expand_filter:after, {{WRAPPER}} .twwf_reset_filtered_fields:after' => 'color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->add_control(
            'filter_label_border_radius',
            array(
                'label' => 'Label border radius',
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_filtered_fields span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'default' => array(
                    'top' => 0,
                    'right' => 0,
                    'left' => 0,
                    'bottom' => 0,
                    'unit' => 'px',
                ),
            )
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_filtered_tag_typography',
                'label' => 'Filtered tag typography',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_p3',
                ),
                'selector' => '{{WRAPPER}} .tww_filter_form .twwf_filtered_fields .twwf_filtered_field',
            )
        );
        $this->add_control(
            'filter_filtered_tag_text_color',
            array(
                'label' => 'Filtered tag text color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_filtered_fields .twwf_filtered_field' => 'color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->add_control(
            'filter_filtered_tag_background_color',
            array(
                'label' => 'Filtered tag background color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_filtered_field' => 'background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_primary',
                ),
            )
        );
        $this->add_control(
            'filter_filtered_icon_color',
            array(
                'label' => 'Filtered icon color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_filtered_field:after' => 'color: {{VALUE}} !important',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ),
            )
        );

        $this->end_controls_section();

        /*Fields Styles*/
        /*Radio & Checkbox*/
        $this->start_controls_section(
            'section_style_radio_checkbox',
            array(
                'label' => 'Radio & Checkbox',
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_radio_checkbox_typography',
                'label' => 'Typography',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_p4',
                ),
                'selector' => '{{WRAPPER}} .tww_filter_form .radio_field_option_title, {{WRAPPER}} .tww_filter_form .checkbox_field_option_title',
            )
        );
        $this->start_controls_tabs('filter_radio_checkbox_colors');
        $this->start_controls_tab('filter_radio_checkbox_color_normal', array( 'label' => 'Normal' ));
        $this->add_control(
            'filter_radio_checkbox_color',
            array(
                'label' => 'Color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .radio_field_option_title, .tww_filter_form .checkbox_field_option_title' => 'color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('filter_radio_checkbox_color_hover', array( 'label' => 'Hover' ));
        $this->add_control(
            'filter_radio_checkbox_hover_color',
            array(
                'label' => 'Color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .radio_field_option_title:hover, .tww_filter_form .checkbox_field_option_title:hover' => 'color: {{VALUE}}',
                ),
                'default' => '#707072',
            )
        );
        $this->end_controls_tab();

        $this->start_controls_tab('filter_radio_checkbox_color_active', array( 'label' => 'Active' ));
        $this->add_control(
            'filter_radio_checkbox_active_color',
            array(
                'label' => 'Color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .twwf_radio_list .container input:checked ~ .radio_field_option_title, {{WRAPPER}} .twwf_checkbox_list .container input:checked ~ .checkbox_field_option_title' => 'color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control(
            'filter_radio_icon_color',
            array(
                'label' => 'Radio icon color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .twwf_radio_list .container input:checked ~ .checkmark' => 'background: {{VALUE}} 0% 0% no-repeat padding-box',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ),
            )
        );
        $this->add_control(
            'filter_checkbox_icon_color',
            array(
                'label' => 'Checkbox icon color',
                'type' => Controls_Manager::COLOR,

                'selectors' => array(
                    '{{WRAPPER}} .twwf_checkbox_list .container .checkmark:after' => 'border-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_primary',
                ),
            )
        );
        $this->start_controls_tabs('filter_radio_checkbox_icon_background_colors');
        $this->start_controls_tab('filter_radio_checkbox_icon_background_color_normal', array( 'label' => 'Normal' ));
        $this->add_control(
            'filter_radio_checkbox_icon_background_color',
            array(
                'label' => 'Icon background color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .twwf_checkbox_list .checkmark' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_primary',
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('filter_radio_checkbox_icon_background_color_hover', array( 'label' => 'Hover' ));
        $this->add_control(
            'filter_radio_checkbox_icon_hover_background_color',
            array(
                'label' => 'Icon background color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .twwf_checkbox_list .container:hover input ~ .checkmark' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_3',
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('filter_radio_checkbox_icon_background_color_active', array( 'label' => 'Active' ));
        $this->add_control(
            'filter_radio_checkbox_icon_active_background_color',
            array(
                'label' => 'Icon background color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .twwf_checkbox_list .container input:checked ~ .checkmark' => ' background: {{VALUE}} 0% 0% no-repeat padding-box',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ),
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

        /*Price slider */
        $this->start_controls_section(
            'section_style_price_slider',
            array(
                'label' => 'Price slider',
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_price_slider_typography',
                'label' => 'Typography',
                'selector' => '{{WRAPPER}} .tww_filter_form .tww_handle_price, {{WRAPPER}} .tww_filter_form .tww_price_item',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_p4',
                ),
            )
        );
        $this->add_control(
            'filter_price_text_color',
            array(
                'label' => 'Text color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_filter_field_block .ui-slider-handle .tww_handle_price' => ' color: {{VALUE}}',
                    '{{WRAPPER}} .tww_filter_form .tww_price_item' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->start_controls_tabs('filter_price_line_colors');
        $this->start_controls_tab('filter_price_line_color_active', array( 'label' => 'Active' ));
        $this->add_control(
            'filter_price_line_active_color',
            array(
                'label' => 'Line color ',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_filter_field_block .ui-slider-range' => ' background: {{VALUE}} 0% 0% no-repeat padding-box',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('filter_price_line_color_secondary', array( 'label' => 'Secondary' ));
        $this->add_control(
            'filter_price_line_secondary_color',
            array(
                'label' => 'Line color ',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_price_slider.ui-slider' => ' background: {{VALUE}} 0% 0% no-repeat padding-box',
                ),
                'default' => '#E4E4E4',
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control(
            'filter_price_thumb_color',
            array(
                'label' => 'Thumb color ',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_filter_field_block .ui-slider-handle' => ' background: {{VALUE}} 0% 0% no-repeat padding-box',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_primary',
                ),
            )
        );
        $this->end_controls_section();

        /*Color filter*/
        $this->start_controls_section(
            'section_style_color_filter',
            array(
                'label' => 'Color filter',
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
        $this->add_control(
            'filter_color_view_type',
            array(
                'label' => 'View type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'grid' => 'Grid',
                    'list' => 'List',
                ),
                'default' => 'list',
            )
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_color_text_typography',
                'label' => 'Typography',
                'selector' => '{{WRAPPER}} .tww_filter_form .twwf_filter_color_block .tww_color_name',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_p4',
                ),
            )
        );
        $this->add_control(
            'filter_color_text_color',
            array(
                'label' => 'Text color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_filter_color_block .tww_color_name' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->add_control(
            'filter_color_border_radius',
            array(
                'label' => 'Border radius',
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_color_checkbox_label .tww_color_input_flag, {{WRAPPER}} .tww_filter_form .twwf_color_checkbox_label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'default' => array(
                    'unit' => '%',
                    'top' => '0',
                    'right' => '0',
                    'bottom' => '0',
                    'left' => '0',
                ),
            )
        );
        $this->end_controls_section();

        /*Box filter*/
        $this->start_controls_section(
            'section_style_box_filter',
            array(
                'label' => 'Box',
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_box_typography',
                'label' => 'Typography',
                'selector' => '{{WRAPPER}} .tww_filter_form .tww_box_checkbox_label',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_p4',
                ),
            )
        );

        $this->start_controls_tabs('filter_box_text_colors');
        $this->start_controls_tab('filter_box_text_color_normal', array( 'label' => 'Normal' ));
        $this->add_control(
            'filter_box_text_color',
            array(
                'label' => 'Text color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_box_checkbox_label' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_TEXT,
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('filter_box_text_color_hover', array( 'label' => 'Hover' ));
        $this->add_control(
            'filter_box_text_hover_color',
            array(
                'label' => 'Text hover color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_box_checkbox_label:hover' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('filter_box_text_color_active', array( 'label' => 'Active' ));
        $this->add_control(
            'filter_box_text_active_color',
            array(
                'label' => 'Text active color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_box_checkbox:checked + .tww_box_checkbox_label' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_primary_inv',
                ),
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->start_controls_tabs('filter_box_background_colors');
        $this->start_controls_tab('filter_box_background_color_normal', array( 'label' => 'Normal' ));
        $this->add_control(
            'filter_box_background_color',
            array(
                'label' => 'Background color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_box_checkbox_label' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_primary',
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('filter_box_background_color_hover', array( 'label' => 'Hover' ));
        $this->add_control(
            'filter_box_background_hover_color',
            array(
                'label' => 'Background hover color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_box_checkbox_label:hover' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_3',
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('filter_box_background_active_color_active', array( 'label' => 'Active' ));
        $this->add_control(
            'filter_box_background_active_color',
            array(
                'label' => 'Background active color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_box_checkbox:checked + .tww_box_checkbox_label' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ),
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control(
            'filter_box_border_radius',
            array(
                'label' => 'Border radius',
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .tww_box_checkbox_label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'default' => array(
                    'unit' => 'px',
                    'top' => '0',
                    'right' => '0',
                    'bottom' => '0',
                    'left' => '0',
                ),
            )
        );
        $this->end_controls_section();

        /*Pillbox & dropdown*/
        $this->start_controls_section(
            'section_style_pillbox_dropdown_filter',
            array(
                'label' => 'Pillbox & dropdown',
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'pillbox_dropdown_typography',
                'label' => 'Typography',
                'selector' => '{{WRAPPER}} .tww_filter_form .select2-container--default .select2-selection--multiple .select2-selection__choice, {{WRAPPER}} .tww_filter_form .select2-container .select2-selection--single .select2-selection__rendered, .select2-results__options .select2-results__option, {{WRAPPER}} .tww_filter_form .select2-container--default .select2-selection--multiple .select2-search__field::placeholder',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_p5',
                ),
            )
        );

        $this->start_controls_tabs('pillbox_dropdown_option_text_colors');
        $this->start_controls_tab('pillbox_dropdown_option_text_color_normal', array( 'label' => 'Normal' ));
        $this->add_control(
            'pillbox_dropdown_option_text_color',
            array(
                'label' => 'Option text color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '.select2-container--default .select2-dropdown .select2-results ul li, {{WRAPPER}} .tww_filter_form .select2-container--default .select2-selection--single .select2-selection__rendered' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('pillbox_dropdown_option_text_color_hover', array( 'label' => 'Hover' ));
        $this->add_control(
            'pillbox_dropdown_option_text_hover_color',
            array(
                'label' => 'Option text hover color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '.select2-container--default .select2-dropdown .select2-results__option--highlighted[aria-selected]' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control(
            'pillbox_dropdown_option_hover_background_color',
            array(
                'label' => 'Option hover background color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '.select2-container--default .select2-dropdown .select2-results__option--highlighted[aria-selected]' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_3',
                ),
            )
        );
        $this->add_control(
            'pillbox_dropdown_field_background_color',
            array(
                'label' => 'Field background color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .select2-selection, body .select2-container .select2-dropdown' => '  background: {{VALUE}} 0% 0% no-repeat padding-box;',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_primary',
                ),
            )
        );

        $this->start_controls_tabs('pillbox_dropdown_field_border_colors');
        $this->start_controls_tab('pillbox_dropdown_field_border_color_normal', array( 'label' => 'Normal' ));
        $this->add_control(
            'pillbox_dropdown_field_border_color',
            array(
                'label' => 'Field border color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .select2-selection--multiple, {{WRAPPER}} .select2-container--default .select2-selection--single, body .select2-container .select2-dropdown' => 'border-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_3',
                ),
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab('pillbox_dropdown_field_active_border_color_active', array( 'label' => 'Active' ));
        $this->add_control(
            'pillbox_dropdown_field_active_border_color',
            array(
                'label' => 'Field active border color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .select2-container--default.select2-container--focus .select2-selection--multiple, {{WRAPPER}} .tww_filter_form .select2-container--default.select2-container--open.select2-container--below .select2-selection--single' => ' border-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_3',
                ),
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control(
            'pillbox_dropdown_field_border_radius',
            array(
                'label' => 'Field border radius',
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .select2-selection--multiple, {{WRAPPER}} .tww_filter_form .select2-container--default .select2-selection--single, body .select2-container .select2-dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'default' => array(
                    'unit' => 'px',
                    'top' => '0',
                    'right' => '0',
                    'bottom' => '0',
                    'left' => '0',
                ),
            )
        );

        $this->add_control(
            'pillbox_tag_background_color',
            array(
                'label' => 'Pillbox tag background color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .select2-container--default .select2-selection--multiple .select2-selection__choice' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_3',
                ),
            )
        );
        $this->add_control(
            'pillbox_tag_icon_color',
            array(
                'label' => 'Pillbox tag icon color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .select2-selection__choice__remove' => ' color: {{VALUE}} !important',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ),
            )
        );
        $this->add_control(
            'pillbox_tag_text_color',
            array(
                'label' => 'Pillbox tag text color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .select2-container--default .select2-selection--multiple .select2-selection__choice' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
            )
        );

        $this->end_controls_section();

        /*Button*/
        $this->start_controls_section(
            'section_style_button',
            array(
                'label' => 'Button',
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => array('tww_control_ajax_filtering' => ''),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_button_typography',
                'label' => 'Typography',
                'selector' => '{{WRAPPER}} .tww_filter_form .twwf_submit',
                'global' => array(
                    'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                ),
            )
        );

        $this->add_control(
            'filter_buttons_space',
            array(
                'label' => esc_html__('Space Between', 'elementor-pro'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_submit' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ),
                'default' => array(
                    'size' => 15,
                    'unit' => 'px',
                ),
            )
        );

        $this->add_control('filter_button_style', array(
            'label' => 'Filter button',
            'type' => Controls_Manager::HEADING,
            'separator' => 'before',
        ));
        $this->add_control(
            'filter_button_text_color',
            array(
                'label' => 'Text color ',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_submit' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_button',
                ),
            )
        );
        $this->add_control(
            'filter_button_background_color',
            array(
                'label' => 'Background color ',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_submit' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_primary',
                ),
            )
        );
        $this->add_responsive_control(
            'filter_button_border_type',
            array(
                'label' => 'Border Type',
                'type' => Controls_Manager::SELECT,
                'options' => array(
                    'none' => 'None',
                    'solid' => 'Solid',
                    'double' => 'Double',
                    'dotted' => 'Dotted',
                    'dashed' => 'Dashed',
                    'groove' => 'Groove',
                ),
                'default' => 'solid',
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_submit' => 'border-style:{{VALUE}}',
                ),
            )
        );
        $this->add_responsive_control(
            'filter_button_border_width',
            array(
                'label' => 'Border width',
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_submit' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition' => array(
                    'filter_button_border_type!' => 'none',
                ),
                'default' => array(
                    'unit' => 'px',
                    'top' => '1',
                    'right' => '1',
                    'bottom' => '1',
                    'left' => '1',
                ),
            )
        );
        $this->add_control(
            'filter_button_border_color',
            array(
                'label' => 'Border color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_submit' => 'border-color: {{VALUE}}',
                ),
                'condition' => array(
                    'filter_button_border_type!' => 'none',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ),
            )
        );
        $this->add_control(
            'filter_button_border_radius',
            array(
                'label' => 'Border radius',
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'default' => array(
                    'unit' => 'px',
                    'top' => '0',
                    'right' => '0',
                    'bottom' => '0',
                    'left' => '0',
                ),
                'condition' => array(
                    'filter_button_border_type!' => 'none',
                ),
            )
        );

        $this->add_control('reset_button_style', array(
            'label' => 'Reset button',
            'type' => Controls_Manager::HEADING,
            'separator' => 'before',
        ));
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_reset_button_typography',
                'label' => 'Typography',
                'selector' => '{{WRAPPER}} .tww_filter_form .twwf_reset_filter',
                'global' => array(
                    'default' => 'globals/typography?id=twbb_p3',
                ),
            )
        );
        $this->add_control(
            'filter_reset_button_text_color',
            array(
                'label' => 'Filter reset button text color ',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_reset_filter' => ' color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_button',
                ),
            )
        );
        $this->add_control(
            'filter_reset_button_background_color',
            array(
                'label' => 'Filter reset button background color ',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_reset_filter' => ' background-color: {{VALUE}}',
                ),
                'global' => array(
                    'default' => 'globals/colors?id=twbb_bg_primary',
                ),
            )
        );
        $this->add_responsive_control(
            'filter_reset_button_border_type',
            array(
                'label' => 'Border Type',
                'type' => Controls_Manager::SELECT,
                'options' => array(
                    'none' => 'None',
                    'solid' => 'Solid',
                    'double' => 'Double',
                    'dotted' => 'Dotted',
                    'dashed' => 'Dashed',
                    'groove' => 'Groove',
                ),
                'default' => 'none',
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_reset_filter' => 'border-style:{{VALUE}}',
                ),
            )
        );
        $this->add_responsive_control(
            'filter_reset_button_border_width',
            array(
                'label' => 'Border width',
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_reset_filter' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition' => array(
                    'filter_reset_button_border_type!' => 'none',
                ),
                'default' => array(
                    'unit' => 'px',
                    'top' => '1',
                    'right' => '1',
                    'bottom' => '1',
                    'left' => '1',
                ),
            )
        );
        $this->add_control(
            'filter_reset_button_border_color',
            array(
                'label' => 'Border color',
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_reset_filter' => 'border-color: {{VALUE}}',
                ),
                'condition' => array(
                    'filter_reset_button_border_type!' => 'none',
                ),
            )
        );
        $this->add_control(
            'filter_reset_button_border_radius',
            array(
                'label' => 'Border radius',
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
                'selectors' => array(
                    '{{WRAPPER}} .tww_filter_form .twwf_reset_filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'default' => array(
                    'unit' => 'px',
                    'top' => '6',
                    'right' => '6',
                    'bottom' => '6',
                    'left' => '6',
                ),
                'condition' => array(
                    'filter_reset_button_border_type!' => 'none',
                ),
            )
        );

        $this->end_controls_section();
    }

    public function get_script_depends() {
        return array( 'jquery_ui-script', 'tww_select2-script', 'tww_filter-script', 'tww_filter-ajax' );
    }

    public function get_style_depends() {
        return array( 'tww_select2-style', 'tww_filter-style', 'tww_filter-icons', 'jquery_ui-style' );
    }

    public function render() {
        $settings = $this->get_settings_for_display();

        if (isset ($settings['tww_expand_filter']) && $settings['tww_expand_filter'] === 'yes') {
            $settings['field_state'] = 'expanded';
        } else {
            $settings['field_state'] = 'collapsed';
        }

        if (!empty($settings['tww_control_filter'])) {
            wp_print_styles('tww_select2-style');
            wp_print_styles('tww_filter-style');
            wp_print_styles('tww_filter-icons');
            wp_print_styles('jquery_ui-style');
            $filter_id = $settings['tww_control_filter'];
            $filterBuilder = new FilterBuilder($filter_id);
            $this->filter = $filterBuilder->getFilter();
            $filterBuilder->renderFilter($settings);
            add_filter('woocommerce_shortcode_products_query', array($this, 'shortcodeProductsQuery'), 10, 3);
            add_filter('twb_shortcode_products_query', array($this, 'shortcodeProductsQuery'), 10, 3);
        }
    }

    public function shortcodeProductsQuery($query_args, $atts, $loop_name) {
        $queryBuilder = new QueryBuilder($this->filter, $query_args);

        return $queryBuilder->get();
    }
}
