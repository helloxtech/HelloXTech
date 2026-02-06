<?php
namespace Tenweb_Builder\Widgets\Woocommerce\Widgets;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Repeater;
use Elementor\Group_Control_Background;use Tenweb_Builder\Classes\Woocommerce\Woocommerce;

class Checkout_10Web extends Base_Widget {

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        add_action('elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_script' ]);
    }

    public function get_name() {
        return TWBB_PREFIX . '_10web_checkout';
    }

    public function get_title() {
        return esc_html__( 'Checkout', 'tenweb-builder');
    }

    public function get_icon() {
        return 'twbb-checkout twbb-widget-icon';
    }

    public function get_keywords() {
        return [ 'woocommerce', 'checkout' ];
    }

    public function get_script_depends() {
        return [
            'wc-checkout',
            'wc-password-strength-meter',
            'selectWoo',
        ];
    }

    public function enqueue_editor_script() {

        wp_enqueue_script(
            'twbb-checkout-10web-editor-script',
            TWBB_URL . '/widgets/woocommerce/checkout-10web/assets/admin-scripts.js',
            [ 'jquery', 'elementor-editor' ],
            TWBB_VERSION,
            true
        );
    }

    public function get_style_depends() {
        return [ 'select2' ];
    }

    public function get_categories() {
        return [ Woocommerce::WOOCOMMERCE_GROUP ];
    }

    /**
     * Is WooCommerce Feature Active.
     *
     * Checks whether a specific WooCommerce feature is active. These checks can sometimes look at multiple WooCommerce
     * settings at once so this simplifies and centralizes the checking.
     *
     * @since 3.5.0
     *
     * @param string $feature
     * @return bool
     */
    protected function is_wc_feature_active( $feature ) {
        switch ( $feature ) {
            case 'checkout_login_reminder':
                return 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );
            case 'shipping':
                if ( class_exists( 'WC_Shipping_Zones' ) ) {
                    $all_zones = \WC_Shipping_Zones::get_zones();
                    if ( count( $all_zones ) > 0 ) {
                        return true;
                    }
                }
                break;
            case 'coupons':
                return function_exists( 'wc_coupons_enabled' ) && wc_coupons_enabled();
            case 'signup_and_login_from_checkout':
                return 'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout' );
            case 'ship_to_billing_address_only':
                return wc_ship_to_billing_address_only();
        }

        return false;
    }


    /**
     * Init Gettext Modifications
     *
     * Sets the `$gettext_modifications` property used with the `filter_gettext()` in the extended Base_Widget.
     *
     * @since 3.5.0
     */
    protected function init_gettext_modifications() {
        $instance = $this->get_settings_for_display();
        $this->gettext_modifications = [
            'Billing details' => isset( $instance['billing_details_section_title'] ) ? $instance['billing_details_section_title'] : '',
            'Billing &amp; Shipping' => isset( $instance['billing_details_section_title'] ) ? $instance['billing_details_section_title'] : '',
            'Ship to a different address?' => isset( $instance['shipping_details_section_title'] ) ? $instance['shipping_details_section_title'] : '',
            'Additional information' => isset( $instance['additional_information_section_title'] ) ? $instance['additional_information_section_title'] : '',
            'Your order' => isset( $instance['order_summary_section_title'] ) ? $instance['order_summary_section_title'] : '',
            'Have a coupon?' => isset( $instance['coupon_section_title_text'] ) ? $instance['coupon_section_title_text'] : '',
            'Click here to enter your coupon code' => isset( $instance['coupon_section_title_link_text'] ) ? $instance['coupon_section_title_link_text'] : '',
            'Returning customer?' => isset( $instance['returning_customer_section_title'] ) ? $instance['returning_customer_section_title'] : '',
            'Click here to login' => isset( $instance['returning_customer_link_text'] ) ? $instance['returning_customer_link_text'] : '',
            'Create an account?' => isset( $instance['create_account_text'] ) ? $instance['create_account_text'] : '',
            'Coupon code' => isset( $instance['coupon_placeholder'] ) ? $instance['coupon_placeholder'] : '',
            'Apply coupon' => isset( $instance['coupon_apply_button_text'] ) ? $instance['coupon_apply_button_text'] : '',
        ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__( 'Columns', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'sticky_right_column',
            [
                'label' => esc_html__( 'Sticky Right Column', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'On', 'tenweb-builder'),
                'label_off' => esc_html__( 'Off', 'tenweb-builder'),
                'return_value' => 'on',
                'default' => 'on',
                'description' => esc_html__( 'The "Order summary" section will stay fixed while scrolling.', 'tenweb-builder'),
                'frontend_available' => true,
                'render_type' => 'template',
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_fields',
            [
                'label' => esc_html__( 'Fields', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'show_field_labels',
            [
                'label' => esc_html__( 'Field labels', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __( 'Show', 'tenweb-builder'),
                'label_off' => __( 'Hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => '',
                'prefix_class' => 'twbb-show-label-',
            ]
        );

        $this->add_control(
            'show_field_placeholders',
            [
                'label' => esc_html__( 'Field Placeholder', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __( 'Show', 'tenweb-builder'),
                'label_off' => __( 'Hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
                'prefix_class' => 'twbb-show-field-placeholder-',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_contact',
            [
                'label' => esc_html__( 'Contact info', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'contact_section_title',
            [
                'label' => __( 'Contact Section Title', 'tenweb-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Contact', 'tenweb-builder'),
                'placeholder' => __( 'Enter section title', 'tenweb-builder'),
                'label_block' => true,
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'login_link_text',
            [
                'label' => __( 'Log in link text', 'tenweb-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Log in', 'tenweb-builder'),
                'placeholder' => __( 'Enter Log in link text', 'tenweb-builder'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'section_billing_shipping',
            [
                'label' => esc_html__( 'Shipping & Billing Details', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'shipping_details_section_title_main',
            [
                'label' => esc_html__( 'Shipping section title', 'tenweb-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Shipping', 'tenweb-builder'),
                'placeholder' => __( 'Enter section title', 'tenweb-builder'),
                'dynamic' => [
                   'active' => true,
                ],
            ]
        );

        $this->add_control(
            'billing_details_section_title',
            [
                'label' => esc_html__( 'Billing section title', 'tenweb-builder'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter section title', 'tenweb-builder'),
                'default' => esc_html__( 'Billing', 'tenweb-builder'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // Company Name Switcher
        $this->add_control(
            'enable_company_name',
            [
                'label' => __('Company name field', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'company_name_required',
            [
                'label' => __('Company name required', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'tenweb-builder'),
                'label_off' => __('No', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => '',
                'condition' => [
                    'enable_company_name' => 'yes',
                ],
            ]
        );

        // Address Line 2 Switcher
        $this->add_control(
            'enable_address_line2',
            [
                'label' => __('Address line 2 field', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'address_line2_required',
            [
                'label' => __('Address line 2 required', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'tenweb-builder'),
                'label_off' => __('No', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => '',
                'condition' => [
                    'enable_address_line2' => 'yes',
                ],
            ]
        );

        // Phone Switcher
        $this->add_control(
            'enable_phone',
            [
                'label' => __('Phone field', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'phone_required',
            [
                'label' => __('Phone required', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'tenweb-builder'),
                'label_off' => __('No', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => '',
                'condition' => [
                    'enable_phone' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'additional_information_active',
            [
                'label' => esc_html__( 'Order notes field', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => '',
                'render_type' => 'template',
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}}' => '--additional-information-display: block;',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_payment_methods',
            [
                'label' => esc_html__( 'Payment methods', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'payment_section_title',
            [
                'label' => esc_html__( 'Payment section title', 'tenweb-builder'),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Payment', 'tenweb-builder'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'payments_descriptive_text',
            [
                'label' => __('Payments descriptive text', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
                'render_type' => 'template',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'checkout_button_section',
            [
                'label' => esc_html__( 'Checkout button', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'terms_conditions_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Terms & Privacy Policy Agreement', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'terms_conditions_checkbox_active',
            [
                'label' => __('Checkbox checked by default', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'tenweb-builder'),
                'label_off' => __('No', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'purchase_button_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Checkout button', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'purchase_button_text',
            [
                'label' => __( 'Purchase button text', 'plugin-domain' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Pay Now', 'tenweb-builder'),
            ]
        );

        $this->end_controls_section();



        $this->start_controls_section(
            'order_summary_section',
            [
                'label' => esc_html__( 'Order summary & coupon codes', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'order_summary_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Order summary', 'tenweb-builder'),
            ]
        );


        $this->add_control(
            'order_summary_section_title',
            [
                'label' => esc_html__( 'Order summary section title', 'tenweb-builder'),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Order Summary', 'tenweb-builder'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'product_image',
            [
                'label' => __('Product images', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
                'prefix_class' => 'twbb-show-product-image-',
                'render_type' => 'template',
            ]
        );

        $this->add_control(
            'coupon_codes_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Coupon codes', 'tenweb-builder'),
            ]
        );

        $this->add_control(
            'coupon_active',
            [
                'label' => __('Coupon code field', 'tenweb-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'tenweb-builder'),
                'label_off' => __('Hide', 'tenweb-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
                'prefix_class' => 'twbb-coupon-active-',
                'render_type' => 'template',
            ]
        );

        $this->add_control(
            'coupon_placeholder',
            [
                'label' => esc_html__( 'Coupon code Placeholder', 'tenweb-builder'),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'default' => 'Gift card or discount code',
                'condition' => [
                    'coupon_active' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'coupon_apply_button_text',
            [
                'label' => esc_html__( 'Coupon code action button text', 'tenweb-builder'),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'default' => 'Apply',
                'condition' => [
                    'coupon_active' => 'yes',
                ],
            ]
        );


        $this->end_controls_section();

        $this->register_style_tab_controls();
    }

    public function register_style_tab_controls() {

        $this->start_controls_section(
            'columns_checkout_tabs_style',
            [
                'label' => esc_html__( 'Columns', 'tenweb-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'col1_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Left Column', 'tenweb-builder'),
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'col1_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-col1, {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-col1 .twbb-checkout-section',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_primary',
                        ],
                    ],
                ]
            ]
        );

        $this->add_control(
            'col1_background_color_width',
            [
                'label' => esc_html__( 'Background color width', 'tenweb-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'full' => esc_html__( 'Full width', 'tenweb-builder'),
                    'boxed' => esc_html__( 'Boxed', 'tenweb-builder'),
                ],
                'default' => 'boxed',
                'prefix_class' => 'twbb-col1-',
                'render_type' => 'template',
            ]
        );


        $this->add_control(
            'col1_border_type',
            [
                'label' => esc_html__( 'Border Type', 'tenweb-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_custom_border_type_options(),
                'default' => 'none',
                'selectors' => [
                    '{{WRAPPER}}' => '--col1-border-type: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'col1_border_width',
            [
                'label' => esc_html__( 'Width', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-col1' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'col1_border_type!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'col1_border_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--col1-border-color: {{VALUE}};',
                ],
                'condition' => [
                    'col1_border_type!' => 'none',
                ],
            ]
        );

        $this->add_responsive_control(
            'col1_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--col1-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'col1_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'default' => [
                    'top' => 40,
                    'right' => 40,
                    'bottom' => 0,
                    'left' => 40,
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'top' => 40,
                    'right' => 40,
                    'bottom' => 0,
                    'left' => 40,
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--col1-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --col1-padding-left: {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'col1_margin',
            [
                'label' => esc_html__( 'Margin', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--col1-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'col1_background_color_width!' => 'full',
                ],
            ]
        );

        $this->add_responsive_control(
            'col2_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Right Column', 'tenweb-builder'),
                'separator' => 'before',
            ]
        );


        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'col2_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-col2, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-col2 .twbb-checkout-section,
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-mobile-order-summery .twbb-order-review-heading,
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-mobile-order-summery .twbb-order-review-content',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                         'default' => '#F5F5F5',
                    ],
                ]
            ]
        );

        $this->add_control(
            'col2_background_color_width',
            [
                'label' => esc_html__( 'Background color width', 'tenweb-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'full' => esc_html__( 'Full width', 'tenweb-builder'),
                    'boxed' => esc_html__( 'Boxed', 'tenweb-builder'),
                ],
                'default' => 'full',
                'prefix_class' => 'twbb-col2-',
                'render_type' => 'template',
            ]
        );


        $this->add_control(
            'col2_border_type',
            [
                'label' => esc_html__( 'Border Type', 'tenweb-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_custom_border_type_options(),
                'default' => 'none',
                'selectors' => [
                    '{{WRAPPER}}' => '--col2-border-type: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'col2_border_width',
            [
                'label' => esc_html__( 'Width', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-col2' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'col2_border_type!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'col2_border_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--col2-border-color: {{VALUE}};',
                ],
                'condition' => [
                    'col2_border_type!' => 'none',
                ],
            ]
        );

        $this->add_responsive_control(
            'col2_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--col2-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'col2_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'default' => [
                    'top' => 40,
                    'right' => 40,
                    'bottom' => 0,
                    'left' => 40,
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'top' => 40,
                    'right' => 40,
                    'bottom' => 0,
                    'left' => 40,
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--col2-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};
                                      --col2-padding-top: {{TOP}}{{UNIT}};
                                      --col2-padding-right: {{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'col2_margin',
            [
                'label' => esc_html__( 'Margin', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--col2-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
               'condition' => [
                    'col2_background_color_width!' => 'full',
                ],

            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'checkout_layout_style',
            [
                'label' => esc_html__( 'Sections', 'tenweb-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'checkout_layout_title_style_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Section titles', 'tenweb-builder'),
            ]
        );

        $this->add_responsive_control(
        'sections_titles_alignment',
            [
                'label' => esc_html__( 'Alignment', 'tenweb-builder'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__( 'Left', 'tenweb-builder'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'tenweb-builder'),
                        'icon' => 'eicon-text-align-center',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--sections_titles_alignment: {{VALUE}};',
                ],
                'default' => 'start',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sections_titles_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout h3.twbb-section-title, {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section.billing-details h3',
                'global' => [
					'default' => 'globals/typography?id=twbb_h6',
				],
            ]
        );

        $this->add_control(
            'sections_title_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-section-title,
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section.billing-details h3' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sections_title_spacing',
            [
                'label' => esc_html__( 'Spacing below section titles', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 14,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--sections-title-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sections_spacing',
            [
                'label' => esc_html__( 'Spacing between sections', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section:not(.twbb-checkout-button-section):not(.shipping-details)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->style_tab_form_controls();

        $this->shipping_and_billing_style_controls();

        $this->add_shipping_methods_controls();

        $this->add_payment_methods_controls();

        $this->style_tab_purchase_button_controls();

        $this->style_tab_order_summary_controls();
    }

    private function style_tab_purchase_button_controls() {
        $this->start_controls_section(
            'section_checkout_tabs_purchase_button',
            [
                'label' => esc_html__( 'Checkout button', 'tenweb-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'terms_and_privacy_policy',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Terms and Privacy Policy', 'tenweb-builder'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'terms_and_privacy_policy_text_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .woocommerce-privacy-policy-text .woocommerce-form__label-for-checkbox',
                'global' => [
					'default' => 'globals/typography?id=twbb_p5',
				],
            ]
        );

        $this->add_control(
            'terms_and_privacy_policy_text_color',
            [
                'label' => esc_html__( 'Text Color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .woocommerce-privacy-policy-text .woocommerce-form__label-for-checkbox' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
            ]
        );

        $this->add_control(
            'terms_and_privacy_policy_links_color',
            [
                'label' => esc_html__( 'Links color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .woocommerce-privacy-policy-text .woocommerce-form__label-for-checkbox a' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
            ]
        );

        $this->add_responsive_control(
            'terms_and_privacy_policy_checkbox_size',
            [
                'label' => esc_html__( 'Checkbox size', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}}' => '--terms-checkbox-size: {{SIZE}}{{UNIT}};',
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 16,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_responsive_control(
            'terms_and_privacy_policy_checkbox_spacing',
            [
                'label' => esc_html__( 'Spacing below agreement', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--terms-below-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
            'purchase_button_style_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Checkout button', 'tenweb-builder'),
            ]
        );

        $this->add_responsive_control(
            'purchase_button_spacing_below',
            [
                'label' => esc_html__( 'Space below button', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--button-below-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'purchase_button_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--purchase-button-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --purchase-button-width: fit-content;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'purchase_button_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #place_order',
                'global' => [
					'default' => 'globals/typography?id=accent',
				],
            ]
        );


        $this->start_controls_tabs( 'purchase_button_styles' );

        $this->start_controls_tab( 'purchase_button_normal_styles', [
            'label' => esc_html__( 'Normal', 'tenweb-builder'),
        ] );

        $this->add_control(
            'purchase_button_normal_text_color',
            [
                'label' => esc_html__( 'Text Color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--purchase-button-normal-text-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=twbb_button_inv',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'purchase_button_normal_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #place_order',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=accent',
                        ],
                    ],
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'purchase_button_normal_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #place_order',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'purchase_button_normal_border',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #place_order',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'purchase_button_border_normal_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--purchase-button-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab( 'purchase_button_hover_styles', [
            'label' => esc_html__( 'Hover', 'tenweb-builder'),
        ] );

        $this->add_control(
            'purchase_button_hover_text_color',
            [
                'label' => esc_html__( 'Text Color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--purchase-button-hover-text-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=twbb_button_inv',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'purchase_button_hover_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #place_order:hover',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_accent_hover',
                        ],
                    ],
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'purchase_button_hover_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #place_order:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'purchase_button_hover_border',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #place_order:hover',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'purchase_button_border_hover_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #place_order:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();



        $this->end_controls_section();

    }

    private function style_tab_order_summary_controls() {
        $this->start_controls_section(
            'section_checkout_tabs_order_summary',
            [
                'label' => esc_html__( 'Order summary & coupon codes', 'tenweb-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        /*-----------Product title ----------*/
        $this->add_control(
            'order_summary_product_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Product Title', 'tenweb-builder'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'order_summary_product_typography',
                'label' => esc_html__( 'Product title typography', 'tenweb-builder'),
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summery-image-name',
                'global' => [
					'default' => 'globals/typography?id=twbb_p4',
				],
            ]
        );

        $this->add_control(
            'order_summary_product_color',
            [
                'label' => esc_html__( 'Product title color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--order-summary-product-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],
            ]
        );

        /*-----------Product price ----------*/

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'order_summary_product_price_typography',
                'label' => esc_html__( 'Product price typography', 'tenweb-builder'),
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summery-product-item .woocommerce-Price-amount bdi',
                'global' => [
					'default' => 'globals/typography?id=twbb_p4',
				],
            ]
        );

        $this->add_control(
            'order_summary_product_price_color',
            [
                'label' => esc_html__( 'Product price color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--order-summary-product-price-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],
            ]
        );

        $this->add_responsive_control(
            'order_summary_product_image_border_radius',
            [
                'label' => esc_html__( 'Product image border radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summery-image-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summery-image-container img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_responsive_control(
            'order_summary_product_price_spacing_between',
            [
                'label' => esc_html__( 'Spacing between products', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summary-row.twbb-order-summary-product-row' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summary-row.twbb-order-summary-product-row.twbb-order-summary-product-row-last' => '0',
                ],
            ]
        );

        $this->add_responsive_control(
            'order_summary_product_price_spacing_below',
            [
                'label' => esc_html__( 'Spacing below products', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 15,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summary-row.twbb-order-summary-product-row.twbb-order-summary-product-row-last' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
            'coupon_button_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Coupon code action button', 'tenweb-builder'),
            ]
        );


        $this->add_control(
            'coupon_spacing_below',
            [
                'label' => esc_html__( 'Spacing below coupon field', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'coupon_field_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 10,
                    'right' => 20,
                    'bottom' => 10,
                    'left' => 20,
                    'unit' => 'px',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'coupon_button_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button',
                'global' => [
					'default' => 'globals/typography?id=accent',
				],

            ]
        );

        $this->start_controls_tabs( 'coupon_button_styles_tabs' );

        $this->start_controls_tab( 'coupon_button_inactive_styles', [
            'label' => esc_html__( 'Inactive', 'tenweb-builder'),
        ] );

        $this->add_control(
            'coupon_button_inactive_text_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button.twbb-coupon-button-inactive' => 'color: {{VALUE}};',
                ],
                'default' => '#949494',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'coupon_button_inactive_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button.twbb-coupon-button-inactive',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_3',
                        ],
                    ],
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'coupon_button_inactive_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button.twbb-coupon-button-inactive',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'coupon_button_inactive_border',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button.twbb-coupon-button-inactive',
                'separator' => 'before',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                       'default' => '#DCDCDC',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'coupon_button_inactive_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button.twbb-coupon-button-inactive' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
            ]
        );

        $this->end_controls_tab();


        $this->start_controls_tab( 'coupon_button_normal_styles', [
            'label' => esc_html__( 'Normal', 'tenweb-builder'),
        ] );

        $this->add_control(
            'coupon_button_normal_text_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button' => 'color: {{VALUE}}; --coupon-button-text-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=twbb_button_inv',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'coupon_button_normal_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=accent',
                        ],
                    ],
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'coupon_button_normal_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'coupon_button_normal_border',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button',
                'separator' => 'before',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'coupon_button_normal_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'coupon_button_hover_styles', [
            'label' => esc_html__( 'Hover   ', 'tenweb-builder'),
        ] );

        $this->add_control(
            'coupon_button_hover_text_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button:hover' => 'color: {{VALUE}};',
                ],
                'default' => '#949494',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'coupon_button_hover_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button:hover',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_3',
                        ],
                    ],
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'coupon_button_hover_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'coupon_button_hover_border',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button:hover',
                'separator' => 'before',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                ],

            ]
        );

        $this->add_responsive_control(
            'coupon_button_hover_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-order-summary-row-coupon-form .twbb-coupon-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        /*----------Subtotals---------*/
        $this->add_control(
            'sections_secondary_typography',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Subtotals', 'tenweb-builder'),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'order_summary_rows_gap',
            [
                'label' => esc_html__( 'Spacing between subtotals', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--order-summary-rows-gap-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sections_secondary_titles_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-secondary-title, {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-secondary-title *',
                'global' => [
					'default' => 'globals/typography?id=twbb_p4',
				],
            ]
        );


        $this->add_control(
            'sections_secondary_title_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--sections-secondary-title-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],

            ]
        );


        $this->add_responsive_control(
            'order_summary_subtotals_space_below',
            [
                'label' => esc_html__( 'Spacing below subtotals', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summary-row-total' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        /*---------- Total (Name) ----------*/
        $this->add_control(
            'order_summary_total_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Total', 'tenweb-builder'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => esc_html__( 'Total title typography', 'tenweb-builder'),
                'name' => 'order_summary_total_title_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summary-row.twbb-order-summary-row-total .twbb-order-summary-total-title,
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-mobile-order-summery .twbb-order-review-heading-title',
            ]
        );

        $this->add_control(
            'order_summary_total_title_color',
            [
                'label' => esc_html__( 'Total title color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--order-summary-total-title-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],
            ]
        );


        /*---------- Total (price) ----------*/

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => esc_html__( 'Total price typography', 'tenweb-builder'),
                'name' => 'order_summary_total_price_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-order-summary-row.twbb-order-summary-row-total .twbb-order-summary-total-price,
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-mobile-order-summery .twbb-order-review-heading-total',
            ]
        );

        $this->add_control(
            'order_summary_total_price_color',
            [
                'label' => esc_html__( 'Total price color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--order-summary-total-price-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],

            ]
        );

        $this->end_controls_section();

    }

    private function style_tab_form_controls() {
        $this->start_controls_section(
            'section_checkout_tabs_forms',
            [
                'label' => esc_html__( 'Fields', 'tenweb-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_control(
            'forms_label_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Labels', 'tenweb-builder'),
                'condition' => [
                  'show_field_labels' => 'yes'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'forms_label_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row label, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-woocommerce-login-anchor .form-row label, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-coupon-anchor-description',
                'global' => [
					'default' => 'globals/typography?id=twbb_p4',
				],
                'condition' => [
                  'show_field_labels' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'forms_label_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-labels-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=text',
                ],
                'condition' => [
                  'show_field_labels' => 'yes'
                ]
            ]
        );

        $this->add_responsive_control(
            'forms_label_spacing',
            [
                'label' => esc_html__( 'Spacing below labels', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-label-spacing: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                  'show_field_labels' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'forms_field_placeholder_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Placeholders', 'tenweb-builder'),
                'condition' => [
                  'show_field_placeholders' => 'yes'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'forms_field_placeholder_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout input::placeholder, {{WRAPPER}}.elementor-widget-twbb_10web_checkout textarea::placeholder',
                'global' => [
					'default' => 'globals/typography?id=twbb_p4',
				],
                'condition' => [
                  'show_field_placeholders' => 'yes'
                ]
            ]
        );

        $this->start_controls_tabs( 'forms_fields_placeholder_tabs' );

        $this->start_controls_tab( 'forms_fields_normal_placeholder', [
            'label' => esc_html__( 'Normal', 'tenweb-builder'),
        ] );
        $this->add_control(
            'forms_fields_normal_placeholder_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                        '{{WRAPPER}}' => '--forms-field-normal-placeholder-color: {{VALUE}};',
                        '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text::placeholder, 
                        {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row textarea::placeholder, 
                        {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-woocommerce-login-anchor .input-text::placeholder' => 'color: {{VALUE}};',
                ],
                'default' => '#6B6B6B',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'forms_fields_hover_placeholder', [
            'label' => esc_html__( 'Hover', 'tenweb-builder'),
        ] );
        $this->add_control(
            'forms_fields_hover_placeholder_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text:hover::placeholder, 
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row textarea:hover::placeholder, 
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-woocommerce-login-anchor .input-text:hover::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'forms_fields_active_placeholder', [
            'label' => esc_html__( 'Active', 'tenweb-builder'),
        ] );
        $this->add_control(
            'forms_fields_active_placeholder_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text:focus::placeholder, 
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row textarea:focus::placeholder, 
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-woocommerce-login-anchor .input-text:focus::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->add_control(
            'forms_field_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Fields', 'tenweb-builder'),
            ]
        );

        $this->add_responsive_control(
            'forms_rows_gap',
            [
                'label' => esc_html__( 'Field Gap', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'default' => [
                    'size' => 15,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-rows-gap: {{SIZE}}{{UNIT}}; --forms-columns-gap: calc( {{SIZE}}{{UNIT}}/2 );',
                ],
            ]
        );

        $this->add_responsive_control(
            'forms_fields_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-fields-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section' => '--forms-fields-padding-right: {{RIGHT}}{{UNIT}};',
                    // style select2
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single' => 'height: auto;',
                ],
                'default' => [
                    'top' => 14,
                    'right' => 14,
                    'bottom' => 14,
                    'left' => 14,
                    'unit' => 'px',
                ],
                'separator' => 'after',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'forms_field_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row textarea, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row select, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-woocommerce-login-anchor .input-text, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .checkout-inline-error-message
                .select2-results__option',
                'global' => [
					'default' => 'globals/typography?id=twbb_p4',
				],
            ]
        );

        $this->start_controls_tabs( 'forms_fields_styles' );

        $this->start_controls_tab( 'forms_fields_normal_styles', [
            'label' => esc_html__( 'Normal', 'tenweb-builder'),
        ] );

        $this->add_control(
            'forms_fields_normal_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-fields-normal-color: {{VALUE}};',
                    '.e-woo-select2-wrapper .select2-results__option' => 'color: {{VALUE}};',
                    // style select2 arrow
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single .select2-selection__arrow b' => 'border-color: {{VALUE}} transparent transparent transparent;',
                ],
                'global' => [
                    'default' => 'globals/colors?id=text',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'forms_fields_normal_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout  .twbb-checkout-section .form-row textarea, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .e-woocommerce-login-anchor .form-row .input-text, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single .select2-selection__rendered, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #payment .payment_methods .payment_box',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_primary',
                        ],
                    ],
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'forms_fields_normal_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .input-text, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout  .twbb-checkout-section .form-row textarea, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-woocommerce-login-anchor .input-text, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single .select2-selection__rendered',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'forms_fields_normal_border',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout  .twbb-checkout-section .form-row textarea, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .e-woocommerce-login-anchor .form-row .input-text, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single',
                'separator' => 'before',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                        'default' => '#DCDCDC',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'forms_fields_normal_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'render_type' => 'template',
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-fields-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'forms_fields_hover_styles', [
            'label' => esc_html__( 'Hover', 'tenweb-builder'),
        ] );

        $this->add_control(
            'forms_fields_hover_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-fields-hover-color: {{VALUE}};',
                ],
                'default' => '#6B6B6B',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'forms_fields_hover_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text:hover, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout  .twbb-checkout-section .form-row textarea:hover, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select:hover, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .e-woocommerce-login-anchor .form-row .input-text:hover, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #payment .payment_methods .payment_box:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'forms_fields_hover_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .input-text:hover, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout  .twbb-checkout-section .form-row textarea:hover, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select:hover, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-woocommerce-login-anchor .input-text:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'forms_fields_hover_border',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text:hover, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout  .twbb-checkout-section .form-row textarea:hover, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select:hover, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .e-woocommerce-login-anchor .form-row .input-text:hover',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'forms_fields_hover_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],

                'selectors' => [
                    '{{WRAPPER}}' => '--forms-fields-hover-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_tab();


        $this->start_controls_tab( 'forms_fields_focus_styles', [
            'label' => esc_html__( 'Active', 'tenweb-builder'),
        ] );

        $this->add_control(
            'forms_fields_focus_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-fields-focus-color: {{VALUE}}',
                    '.e-woo-select2-wrapper .select2-results__option:focus' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=text',
                ],

            ]
        );


        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'forms_fields_focus_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text:focus, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout  .twbb-checkout-section .form-row textarea:focus, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select:focus, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .e-woocommerce-login-anchor .form-row .input-text:focus, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single:focus,
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single .select2-selection__rendered:focus',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_primary',
                        ],
                    ],
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'forms_fields_focus_box_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .input-text:focus, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section textarea:focus, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select:focus, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .e-woocommerce-login-anchor .input-text:focus, 
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single:focus,
                               {{WRAPPER}}.elementor-widget-twbb_10web_checkout .select2-container--default .select2-selection--single .select2-selection__rendered:focus',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'forms_fields_focus_border',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .input-text:focus, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout  .twbb-checkout-section .form-row textarea:focus, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section select:focus, 
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .e-woocommerce-login-anchor .form-row .input-text:focus',
                'separator' => 'before',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_inv',
                        ],
                    ],
                ],

            ]
        );

        $this->add_responsive_control(
            'forms_fields_focus_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--forms-fields-focus-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();



        $this->add_control(
            'error_message_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Error messages', 'tenweb-builder'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'error_message_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .form-row .checkout-inline-error-message',
                'global' => [
					'default' => 'globals/typography?id=twbb_p6',
				],
            ]
        );

        $this->add_control(
            'error_message_color',
            [
                'label' => esc_html__( 'Error text & border color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--error-message-color: {{VALUE}};',
                ],
                'default' => '#D41125',
            ]
        );


        $this->end_controls_section();
    }

    private function shipping_and_billing_style_controls() {
        $this->start_controls_section(
            'section_shipping_and_billing_section',
            [
                'label' => esc_html__( 'Shipping & billing details', 'tenweb-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'shipping_and_billing_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Billing Address Auto-Fill', 'tenweb-builder'),
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'billing_address_autofill',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section.twbb_use_shipping_as_billing_container .twbb_use_shipping_as_billing_label,
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout a.twbb-myaccount-link',
                'global' => [
					'default' => 'globals/typography?id=twbb_p5',
				],

            ]
        );

        $this->start_controls_tabs( 'billing_address_autofill_tabs');
        $this->start_controls_tab( 'billing_address_autofill_normal_tab', [
			'label' => esc_html__( 'Normal', 'tenweb-builder'),
		] );
        $this->add_control(
            'billing_address_autofill_normal_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section.twbb_use_shipping_as_billing_container .twbb_use_shipping_as_billing_label' => 'color: {{VALUE}}',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout a.twbb-myaccount-link' => 'color: {{VALUE}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout a.twbb-myaccount-link:hover' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=text',
                ],

            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab( 'billing_address_autofill_active_tab', [
			'label' => esc_html__( 'Active', 'tenweb-builder'),
		] );
        $this->add_control(
            'billing_address_autofill_active_color',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section.twbb_use_shipping_as_billing_container div.twbb-use-shipping-as-billing-active .twbb_use_shipping_as_billing_label' => 'color: {{VALUE}}'
                ],
                'global' => [
                    'default' => 'globals/colors?id=text',
                ],

            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control(
            'billing_address_autofill_checkbox_size',
            [
                'label' => esc_html__( 'Checkbox size', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}}' => '--billing-address-autofill-checkbox-size: {{SIZE}}{{UNIT}};',
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 16,
                    'unit' => 'px',
                ],
            ]
        );

        $this->end_controls_section();

    }

    private function add_shipping_methods_controls() {
        $this->start_controls_section(
            'section_checkout_tabs_shipping_mothods',
            [
                'label' => esc_html__( 'Shipping Methods', 'tenweb-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'shipping_section_title_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li label,
                 {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li.twbb-noshipping-methods,
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li label .woocommerce-Price-amount amount bdi',
            ]
        );

        $this->add_control(
            'shipping_section_radio_color',
            [
                'label' => __('Radio button color', 'plugin-domain'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-active-shipping-method input[type="radio"]' => 'accent-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ],
            ]
        );

        $this->add_responsive_control(
            'shipping_section_radio_size',
            [
                'label' => esc_html__( 'Radio button size', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout #shipping_method input[type="radio"]' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( 'shipping_section_border_tabs');
        $this->start_controls_tab( 'shipping_section_border_normal_tab', [
			'label' => esc_html__( 'Normal', 'tenweb-builder'),
		] );

         $this->add_control(
            'shipping_section_title_color_normal',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li label,
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li.twbb-noshipping-methods,
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li label .woocommerce-Price-amount amount bdi' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'shipping_section_title_background_normal',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_primary',
                        ],
                    ],
                ]
            ],
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'shipping_section_title_shadow_normal',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'shipping_section_border_normal_type',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_3',
                        ],
                    ],
                ],
            ]
        );

		$this->end_controls_tab();

		$this->start_controls_tab( 'shipping_section_border_active_tab', [
			'label' => esc_html__( 'Active', 'tenweb-builder'),
		] );

        $this->add_control(
            'shipping_section_title_color_active',
            [
                'label' => esc_html__( 'Text color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li.twbb-active-shipping-method label, 
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li.twbb-active-shipping-method label .woocommerce-Price-amount amount bdi' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'shipping_section_title_background_active',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li.twbb-active-shipping-method',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => '#F4F4F4',
                    ],
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'shipping_section_title_shadow_active',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li.twbb-active-shipping-method',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'shipping_section_border_active_type',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li.twbb-active-shipping-method',
                 'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_inv',
                        ],
                    ],
                ],
            ]
        );

        $this->end_controls_tab();

		$this->end_controls_tabs();

        $this->add_responsive_control(
            'shipping_section_border_normal_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li:first-child' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} 0 0;',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li:last-child' => 'border-radius: 0 0 {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li:only-child' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],

            ]
        );

        $this->add_responsive_control(
            'shipping_section_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'default' => [
                    'top' => 15,
                    'right' => 15,
                    'bottom' => 15,
                    'left' => 15,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'shipping_section_margin',
            [
                'label' => esc_html__( 'Margin', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section #shipping_method.woocommerce-shipping-methods li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function add_payment_methods_controls() {
        $this->start_controls_section(
            'section_checkout_tabs_payment_mothods',
            [
                'label' => esc_html__( 'Payment Methods', 'tenweb-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'payment_section_normal_title_typography',
                'label' => 'Title typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-title label,
                {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-nopayment-item',
                'fields_options' => [
                    'typography' => [
                        'default' => 'custom',
                    ],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 14,
                        ],
                    ],
                    'font_weight' => [
                        'default' => '500',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'payment_description_typography',
                'label' => 'Description typography',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-description',
                'global' => [
					'default' => 'globals/typography?id=twbb_p6',
				],
            ]
        );

        $this->add_control(
            'payment_section_radio_color',
            [
                'label' => __('Radio button color', 'plugin-domain'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item .twbb-payment-title input[type="radio"]' => 'accent-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=twbb_bg_inv',
                ],
            ]
        );

       $this->add_responsive_control(
            'payment_section_radio_size',
            [
                'label' => esc_html__( 'Radio button size', 'tenweb-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item .twbb-payment-title input[type="radio"]' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item:only-child .twbb-payment-title input[type="radio"]' => 'display:none',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item .twbb-payment-description' => 'padding-left: calc({{SIZE}}{{UNIT}} + 10px);',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item:only-child .twbb-payment-description' => 'padding-left: 0',
                ],
            ]
        );

        $this->start_controls_tabs( 'payment_method_titles' );

        $this->start_controls_tab( 'payment_method_normal_titles', [
            'label' => esc_html__( 'Normal', 'tenweb-builder'),
        ] );

        $this->add_control(
            'payment_section_normal_title_color',
            [
                'label' => esc_html__( 'Title color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-title label' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'payment_section_normal_title_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item:not(.twbb-active-payment)',
                'fields_options' => [
                    'background' => [
                    'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_primary',
                        ],
                    ],
                ]
            ],
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'payment_section_normal_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item:not(.twbb-active-payment)',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'payment_section_border_type_normal',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item:not(.twbb-active-payment)',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_3',
                        ],
                    ],
                ],

            ]
        );

        $this->end_controls_tab();
        $this->start_controls_tab( 'payment_method_active_titles', [
            'label' => esc_html__( 'Active', 'tenweb-builder'),
        ] );

       $this->add_control(
            'payment_section_active_title_color',
            [
                'label' => esc_html__( 'Title color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-active-payment.twbb-payment-item .twbb-payment-title label,
                    {{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-active-payment.twbb-payment-item .twbb-nopayment-item' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => 'globals/colors?id=primary',
                ],
            ]
        );

        $this->add_control(
            'payment_description_color_active',
            [
                'label' => esc_html__( 'Description color', 'tenweb-builder'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-active-payment.twbb-payment-item .twbb-payment-description' => '--sections-descriptions-color: {{VALUE}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'payment_section_active_title_background',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-active-payment.twbb-payment-item',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => '#F4F4F4',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'payment_section_active_shadow',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-active-payment.twbb-payment-item',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'payment_section_border_type_active',
                'selector' => '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-active-payment.twbb-payment-item',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => 1,
                            'right' => 1,
                            'bottom' => 1,
                            'left' => 1,
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                        'global' => [
                            'default' => 'globals/colors?id=twbb_bg_inv',
                        ],
                    ],
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control(
            'payment_section_border_radius_normal',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item:first-child' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} 0 0',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item:last-child' => 'border-radius: 0 0 {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item:only-child' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],

            ]
        );

        $this->add_responsive_control(
            'payment_section_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'default' => [
                    'top' => 15,
                    'right' => 15,
                    'bottom' => 15,
                    'left' => 15,
                    'unit' => 'px',
                ],

                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'payment_section_margin',
            [
                'label' => esc_html__( 'Margin', 'tenweb-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],

                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-twbb_10web_checkout .twbb-checkout-section .twbb-payment-container .twbb-payment-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'after',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get Billing Field Defaults
     *
     * Get defaults used for the billing details repeater control.
     *
     * @since 3.5.0
     *
     * @return array
     */
     private function get_billing_field_defaults() {
        $fields = [
            'billing_first_name' => [
                'label' => esc_html__( 'First Name', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_last_name' => [
                'label' => esc_html__( 'Last Name', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_company' => [
                'label' => esc_html__( 'Company Name', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_country' => [
                'label' => esc_html__( 'Country / Region', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_address_1' => [
                'label' => esc_html__( 'Street Address', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_postcode' => [
                'label' => esc_html__( 'Post Code', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_city' => [
                'label' => esc_html__( 'Town / City', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_state' => [
                'label' => esc_html__( 'State', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_phone' => [
                'label' => esc_html__( 'Phone', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'billing_email' => [
                'label' => esc_html__( 'Email Address', 'tenweb-builder'),
                'repeater_state' => '',
            ],
        ];

        return $fields;
    }


    /**
     * Get Shipping Field Defaults
     *
     * Get defaults used for the shipping details repeater control.
     *
     * @since 3.5.0
     *
     * @return array
    */
    private function get_shipping_field_defaults() {
        $fields = [
            'shipping_first_name' => [
                'label' => esc_html__( 'First Name', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'shipping_last_name' => [
                'label' => esc_html__( 'Last Name', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'shipping_company' => [
                'label' => esc_html__( 'Company Name', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'shipping_country' => [
                'label' => esc_html__( 'Country / Region', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'shipping_address_1' => [
                'label' => esc_html__( 'Street Address', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'shipping_postcode' => [
                'label' => esc_html__( 'Post Code', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'shipping_phone' => [
                'label' => esc_html__( 'Phone', 'tenweb-builder'),
                'repeater_state' => '',
            ],

            'shipping_city' => [
                'label' => esc_html__( 'Town / City', 'tenweb-builder'),
                'repeater_state' => '',
            ],
            'shipping_state' => [
                'label' => esc_html__( 'State', 'tenweb-builder'),
                'repeater_state' => '',
            ],
        ];

        return $fields;
    }

	/**
     * Modify Form Field.
     *
     * WooCommerce filter is used to apply widget settings to the Checkout forms address fields.
     * Billing & Shipping fields use default values, Additional fields use settings values.
     *
     * @since 3.5.0
     *
     * @param array $args Form field arguments.
     * @param string $key Field key.
     * @param string $value Field value.
     * @return array
    */
    public function modify_form_field( $args, $key, $value ) {

        // Get default billing & shipping fields
        $defaults = array_merge(
            $this->get_shipping_field_defaults(),
            $this->get_billing_field_defaults()
        );


        // Apply default values for billing & shipping fields
       if ( isset( $defaults[ $key ] ) ) {
            $args['label'] = !empty($args['label']) ? $args['label'] : $defaults[ $key ]['label'];
            $args['placeholder'] = !empty($args['placeholder']) ?  $args['placeholder'] : $args['label']; // Placeholder falls back to label
        }
        return $args;
    }


    /**
    * Function is called from filter woocommerce_update_order_review_fragments
    * and update teplate default fragment which is comming from Woo template to new custom fragment
    */
    public static function update_custom_order_review_fragments($fragments) {
        ob_start();
        Checkout_10Web::order_review_fragment();
        $fragments['.woocommerce-checkout-review-order-table'] = ob_get_clean();

        ob_start();
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo Checkout_10Web::shipping_methods_html();
        $fragments['#shipping_method'] = ob_get_clean();

        return $fragments;
    }

    public static function order_review_fragment($settings = []) {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }
        ?>
        <div id="order_review" class="twbb-order-summary-container shop_table woocommerce-checkout-review-order-table">
            <div class="twbb-order-review-heading">
                <div class="twbb-order-review-heading-title">
                    <span class="twbb-order-review-heading-title-text">
                        <?php if( !empty($settings['order_summary_section_title']) ) {
                            echo esc_html($settings['order_summary_section_title']);
                        } else {
                             echo esc_html__('Order Summary', 'tenweb-builder');
                        } ?>
                    </span>
                </div>
                <div class="twbb-order-review-heading-total"><?php echo wp_kses(WC()->cart->get_total(), array( 'span' => array('class' => array()), 'bdi' => array() )); ?></div>
            </div>
            <div class="twbb-order-review-content">
                <?php
                $cart = WC()->cart->get_cart();
                $count = count($cart);
                $i = 1;
                foreach ($cart as $cart_item_key => $cart_item) {
                    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product = $cart_item['data'];
                    // Get the product thumbnail (small size)
                    $thumbnail = $product->get_image('thumbnail');
                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                        $lastChildClass = ($count === $i) ? " twbb-order-summary-product-row-last" : "";
                        $i++;
                    ?>
                    <div class="twbb-order-summary-row twbb-order-summary-product-row<?php echo esc_attr($lastChildClass); ?>">
                        <div class="twbb-order-summary-col1">
                            <div class="twbb-order-summery-product-item">
                                <div class="twbb-order-summery-image-container">
                                    <?php echo wp_kses($thumbnail,array(
                                            'img' => array(
                                                    'width' => array(),
                                                    'height' => array(),
                                                    'src' => array(),
                                                    'class' => array(),
                                                    'decoding' => array(),
                                                    'srcset' => array(),
                                                    'sizes' => array(),
                                                    'alt' => array(),
                                                    ))
                                                    ); ?>
                                    <span class="twbb-order-summery-product-count">
                                        <?php echo esc_html($cart_item['quantity']); ?>
                                    </span>
                                </div>
                                <div class="twbb-order-summery-image-name">
                                    <?php echo esc_html($product->get_name()); ?>
                                    <div class="twbb-order-summery-product-noimage-count">x<?php echo esc_html($cart_item['quantity']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="twbb-order-summary-col2">
                            <div class="twbb-order-summery-product-item">
                            <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    }
                }

                if ( wc_coupons_enabled() ) {
                    $placeholder = isset($settings['coupon_placeholder']) ? $settings['coupon_placeholder'] : esc_attr__( 'Coupon code', 'tenweb-builder');
                    ?>
                    <div class="twbb-order-summary-row twbb-order-summary-group twbb-order-summary-row-coupon-form e-coupon-box">
                        <form class="checkout_coupon woocommerce-form-coupon coupon-input-container" method="post">
                            <div class="form-row twwbb-coupon-input-row">
                                <input type="text" name="coupon_code" class="input-text" placeholder="<?php echo esc_attr($placeholder); ?>" id="coupon_code" value="" />

                            </div>
                            <p class="form-row">
                                <button type="submit" class="button woocommerce-button e-apply-coupon twbb-coupon-button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'tenweb-builder'); ?>">
                                    <?php esc_html_e( 'Apply coupon', 'tenweb-builder'); ?>
                                </button>
                            </p>
                        </form>
                        <p class="twbb-coupon-error-message checkout-inline-error-message" style="display: none;"></p>
                    </div>
                <?php

                }
                ?>

                <div class="twbb-order-summary-subtotals-container">
                    <div class="twbb-order-summary-row twbb-order-summary-group twbb-order-summary-row-subtotal twbb-order-summary-group-subtotal">
                        <div class="twbb-order-summary-col1">
                            <div class="twbb-secondary-title"><?php esc_html_e('Subtotal', 'tenweb-builder'); ?></div>
                        </div>
                        <div class="twbb-order-summary-col2">
                            <div class="twbb-secondary-title"><?php echo WC()->cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                        </div>
                    </div>

                    <?php
                    WC()->cart->calculate_totals();
                    $packages = WC()->shipping->get_packages();
                    if( Checkout_10Web::isShippingActiveMethodsExists($packages) ) {
                    ?>
                    <div class="twbb-order-summary-row twbb-order-summary-group twbb-order-summary-row-shipping twbb-order-summary-group-subtotal">
                        <div class="twbb-order-summary-col1">
                            <div class="twbb-secondary-title"><?php esc_html_e('Shipping', 'tenweb-builder'); ?></div>
                        </div>
                        <div class="twbb-order-summary-col2">
                            <div class="twbb-secondary-title">
                                <?php
                                $shipping_total = WC()->cart->get_shipping_total();
                                $formatted_shipping_total = wc_price($shipping_total);
                                echo wp_kses($formatted_shipping_total, array( 'span' => array('class' => array()), 'bdi' => array() ));
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="twbb-order-summary-row twbb-order-summary-group twbb-order-summary-row-tax twbb-order-summary-group-subtotal">
                        <div class="twbb-order-summary-col1">
                            <div class="twbb-secondary-title"><?php esc_html_e('Tax', 'tenweb-builder'); ?></div>
                        </div>
                        <div class="twbb-order-summary-col2">
                            <div class="twbb-secondary-title">
                                <?php
                                $tax_total = WC()->cart->get_total_tax();
                                $formatted_tax_total = wc_price($tax_total);
                                echo wp_kses($formatted_tax_total, array( 'span' => array('class' => array()), 'bdi' => array() ));
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    $coupons = WC()->cart->get_coupons();
                    if( count($coupons) ) {
                            foreach ( $coupons as $code => $coupon ) : ?>
                            <div class="twbb-order-summary-row twbb-order-summary-group twbb-order-summary-row-coupon twbb-order-summary-group-subtotal">
                            <div class="twbb-order-summary-col1 twbb-secondary-title">
                                <div class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                                    <?php
                                    if ( is_string( $coupon ) ) {
                                        $coupon = new WC_Coupon( $coupon );
                                    }

                                    $label = apply_filters( 'woocommerce_cart_totals_coupon_label', esc_html__( 'Coupon ', 'tenweb-builder'). "<span class='twbb-coupon-code-text'>\"".$coupon->get_code()."\"</span>", $coupon );
                                    echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                                    ?>

                                </div>
                            </div>
                            <div class="twbb-order-summary-col2 twbb-secondary-title">
                                <div class="twbb-order-summary-coupon-row">
                                <?php
                                    if ( is_string( $coupon ) ) {
                                        $coupon = new WC_Coupon( $coupon );
                                    }

                                    $amount  = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );
                                    $discount_amount_html = '-' . wc_price( $amount );

                                    if ( $coupon->get_free_shipping() && empty( $amount ) ) {
                                        $discount_amount_html = __( 'Free shipping coupon', 'tenweb-builder');
                                    }

                                    $discount_amount_html = apply_filters( 'woocommerce_coupon_discount_amount_html', $discount_amount_html, $coupon );
                                    $coupon_html = $discount_amount_html . ' <a href="' . esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), wc_get_checkout_url() ) ) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . '<span class="twbb-remove-coupon" title="'.esc_attr__( 'Remove', 'tenweb-builder').'">['.esc_html__('Remove', 'tenweb-builder').']</span>' . '</a>';

                                    echo wp_kses( apply_filters( 'woocommerce_cart_totals_coupon_html', $coupon_html, $coupon, $discount_amount_html ), array_replace_recursive( wp_kses_allowed_html( 'post' ), array( 'a' => array( 'data-coupon' => true ) ) ) ); // phpcs:ignore PHPCompatibility.PHP.NewFunctions.array_replace_recursiveFound
                                ?>
                                </div>
                            </div>
                            </div>
                        <?php endforeach;
                    } ?>
                </div>
                <div class="twbb-order-summary-row twbb-order-summary-group twbb-order-summary-row-total">
                    <div class="twbb-order-summary-col1">
                        <div class="twbb-order-summary-total-title"><?php esc_html_e('Total', 'tenweb-builder'); ?></div>
                    </div>
                    <div class="twbb-order-summary-col2">
                        <div class="twbb-order-summary-total-price"><?php echo wp_kses(WC()->cart->get_total(), array( 'span' => array('class' => array()), 'bdi' => array() )); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function register_hooks() {
        if ( class_exists( 'WooCommerce' ) ) {
            add_filter( 'woocommerce_update_order_review_fragments', [ '\Tenweb_Builder\Widgets\Woocommerce\Widgets\Checkout_10Web', 'update_custom_order_review_fragments' ] );
        }
    }

    public static function shipping_methods_html() {
        ob_start();
        $packages = WC()->shipping->get_packages();
        if (!empty($packages)) { ?>
            <ul id="shipping_method" class="woocommerce-shipping-methods">
             <?php
            foreach ($packages as $i => $package) {
                $available_methods = $package['rates'];
                if (!empty($available_methods)) {
                    foreach ($available_methods as $method_id => $method) {
                        $chosen_method = WC()->session->get('chosen_shipping_methods')[$i];
                        $checked = checked( $method->id, $chosen_method, false );
                        $active_class = $checked === '' ? '' : 'twbb-active-shipping-method'
                        ?>
                        <li class="<?php echo esc_attr($active_class); ?>">
                            <?php
                            if( count($available_methods) > 1 ) {
                            printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', intval($i), esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), $checked ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                            ?>
                            <label><?php echo esc_html($method->get_label()) . wc_price($method->get_cost()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
                        </li>
                        <?php
                    }
                } else {
                    echo '<li class="twbb-noshipping-methods">' . esc_html__('No shipping methods available', 'tenweb-builder') . '</li>';
                }
            }
            echo '</ul>';
        } else {
            echo '<p class="twbb-noshipping-methods">' . esc_html__('No shipping methods available', 'tenweb-builder') . '</p>';
        }
        return ob_get_clean();
    }

    /* Function show/hide form fields and change require param according to widget settings */
    public function twbb_modify_form_fields($fields) {

        $settings = $this->get_settings_for_display();

        // Modify fields dynamically
        if ($settings['enable_company_name'] !== 'yes') {
            unset($fields['billing']['billing_company']);
            unset($fields['shipping']['shipping_company']);
        } else {
            $fields['billing']['billing_company']['required'] = $settings['company_name_required'] === 'yes';
            $fields['shipping']['shipping_company']['required'] = $settings['company_name_required'] === 'yes';
        }

        if ($settings['enable_address_line2'] !== 'yes') {
            unset($fields['billing']['billing_address_2']);
            unset($fields['shipping']['shipping_address_2']);
        } else {
            $fields['billing']['billing_address_2']['required'] = $settings['address_line2_required'] === 'yes';
            $fields['shipping']['shipping_address_2']['required'] = $settings['address_line2_required'] === 'yes';
        }

        if ($settings['enable_phone'] !== 'yes') {
            unset($fields['billing']['billing_phone']);
            unset($fields['shipping']['shipping_phone']);
        } else {
            $fields['billing']['billing_phone']['required'] = $settings['phone_required'] === 'yes';
            $fields['shipping']['shipping_phone']['required'] = $settings['phone_required'] === 'yes';
        }

        if (isset($fields['billing']['billing_email'])) {
            $fields['custom_section']['billing_email'] = $fields['billing']['billing_email'];
            unset($fields['billing']['billing_email']);
        }

        if ($settings['additional_information_active'] !== 'yes') {
            unset( $fields['order']['order_comments'] ); // Remove order comments field
        }

        if (isset($fields['shipping']['shipping_phone'])) {
            $fields['shipping']['shipping_phone']['type'] = 'tel'; // instead of 'text'
        }
        if (isset($fields['billing']['billing_phone'])) {
            $fields['billing']['billing_phone']['type'] = 'tel'; // instead of 'text'
        }

        return $fields;

    }

    public function twbb_check_thankyou_page() {
        global $wp;
        if (is_checkout() && !empty($wp->query_vars['order-received'])) {
            $order_id = absint($wp->query_vars['order-received']);
            $order = wc_get_order($order_id);

            if ($order) {
                // Display the WooCommerce thank-you page
                wc_get_template('checkout/thankyou.php', array('order' => $order));
                return true; // Stop further execution to prevent checkout form from loading
            }
        }
        return false;
    }

    public function addShippingSectionTitle() {
        $settings = $this->get_settings_for_display();
            if(!empty($settings['shipping_details_section_title_main'])) { ?>
                <h3 class="twbb-section-title"><?php echo esc_html($settings['shipping_details_section_title_main']); ?></h3>
            <?php }
    }

    public static function isShippingActiveMethodsExists($packages) {
        $has_enabled_shipping = false;
        foreach ( $packages as $package ) {
            if ( ! empty( $package['rates'] ) ) {
                $has_enabled_shipping = true;
                break;
            }
        }
        return $has_enabled_shipping;
    }

    public function render() {

        if( $this->twbb_check_thankyou_page() ) return;

        // There are several hooks called in woocommerce.php
        add_filter( 'woocommerce_form_field_args', [ $this, 'modify_form_field' ], 70, 3 );
        add_filter( 'gettext', [ $this, 'filter_gettext' ], 20, 3 );
        add_filter('woocommerce_checkout_fields', [$this, 'twbb_modify_form_fields' ]);

        /* manually force it to return true when rendering inside the editor as in Editor it is hidden */
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            add_filter('woocommerce_cart_needs_shipping_address', '__return_true');
        }

        $settings = $this->get_settings_for_display();
        if( empty($settings['coupon_active']) ) {
            add_filter('woocommerce_coupons_enabled', '__return_false');
        }

        add_action('woocommerce_before_checkout_shipping_form', [$this, 'addShippingSectionTitle']);

        $checkout = WC()->checkout;
        // Ensure WooCommerce is loaded
        if (!function_exists('WC')) {
            echo '<p>'. esc_html__("WooCommerce is not active. Please activate WooCommerce to use this page.", 'tenweb-builder').'</p>';
            return;
        }

        // Load WooCommerce checkout hooks and actions
        wc_get_template_part('checkout/form-checkout', '', [
            'checkout' => WC()->checkout(),
        ]);

        $radius = $this->get_settings_for_display('forms_fields_normal_border_radius');
        $is_all_zero = (
            isset($radius['top'], $radius['right'], $radius['bottom'], $radius['left']) &&
            (int)$radius['top'] === 0 &&
            (int)$radius['right'] === 0 &&
            (int)$radius['bottom'] === 0 &&
            (int)$radius['left'] === 0
        );
        $class = $is_all_zero ? ' twbb-checkbox-no-radius' : '';
        ?>
        <div class="twbb-checkout-page">
            <form name="checkout" method="post" class="twbb-checkout-form checkout woocommerce-checkout<?php echo esc_attr($class); ?>" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="Checkout" novalidate="novalidate">
                <input type="hidden" name="twbb_checkout_10web_submited" value="1">
                <div class="twbb-checkout-container">
                    <div class="twbb-mobile-order-summery">
                            <?php Checkout_10Web::order_review_fragment(); ?>
                    </div>
                    <div class="twbb-checkout-col1">
                        <div class="twbb-checkout-section contact-details">
                            <div class="twbb-contact-title-container">
                            <h3 class="twbb-section-title"><?php echo esc_html($settings['contact_section_title']); ?></h3>
                            <?php
                            if ( !is_user_logged_in() && $this->is_wc_feature_active('checkout_login_reminder' ) ) {
                                $login_url = wc_get_page_permalink( 'myaccount' );
                                ?>
                                <a class="twbb-myaccount-link" href="<?php echo esc_url( $login_url ); ?>"><?php echo esc_html($settings['login_link_text']); ?></a>
                                <?php
                            }
                            ?>
                            </div>
                            <div class="twbb-contact-section-container">
                                <?php
                                if (!is_user_logged_in() && $this->is_wc_feature_active('checkout_login_reminder' ) ) {
                                    $login_url = wc_get_page_permalink( 'myaccount' );
                                    ?>
                                    <a class="twbb-myaccount-link twbb-myaccount-link-labelHidden" href="<?php echo esc_url( $login_url ); ?>"><?php echo esc_html($settings['login_link_text']); ?></a>
                                    <?php
                                }
                                woocommerce_form_field('billing_email', WC()->checkout->get_checkout_fields()['custom_section']['billing_email'], WC()->checkout->get_value('billing_email')); ?>
                            </div>
                        </div>
                        <?php
                        WC()->cart->calculate_totals();
                        $packages = WC()->shipping->get_packages();

                        if ( $checkout->get_checkout_fields() ) {
                            if( Checkout_10Web::isShippingActiveMethodsExists($packages) ) {
                            ?>
                            <!-- Shipping Details (Always Visible) -->
                            <div class="twbb-checkout-section shipping-details">
                                <?php do_action('woocommerce_checkout_shipping'); ?>
                            </div>
                            <!-- New Checkbox: Use Shipping Address as Billing -->
                            <div class="twbb-checkout-section twbb_use_shipping_as_billing_container">
                                <div for="twbb_use_shipping_as_billing" class="twbb_use_shipping_as_billing_content twbb-use-shipping-as-billing-active">
                                    <input type="hidden" name="twbb_use_shipping_as_billing" value="">
                                    <input type="checkbox" id="twbb_use_shipping_as_billing" name="twbb_use_shipping_as_billing"  value="1" checked>
                                    <span class="twbb_use_shipping_as_billing_label"><?php esc_html_e('Use shipping address as billing address', 'tenweb-builder'); ?></span>
                                </div>
                            </div>
                            <?php
                            }
                            $class = empty($packages) ? '' : ' twbb-billing-hidden';
                             ?>

                            <!-- Billing Details (Hidden by Default) -->
                            <div id="billing-details" class="twbb-checkout-section billing-details<?php echo esc_attr($class); ?>">
                                <?php do_action('woocommerce_checkout_billing'); ?>
                            </div>
                        <?php
                        }

                        if ( !empty($packages) ) { ?>
                        <!-- Shipping Methods -->
                        <div class="twbb-checkout-section">
                            <h3 class="twbb-section-title"><?php echo esc_html_e('Shipping methods', 'tenweb-builder'); ?></h3>
                            <?php
                            if ( WC()->cart->needs_shipping() ) { ?>
                                <?php
                                echo wp_kses( Checkout_10Web::shipping_methods_html(), array(
                                    'ul'    => array('id' => true, 'class' => true),
                                    'li'    => array('class' => true),
                                    'input' => array(
                                        'type'      => true,
                                        'name'      => true,
                                        'data-index'=> true,
                                        'id'        => true,
                                        'value'     => true,
                                        'class'     => true,
                                        'checked'   => true
                                    ),
                                    'label' => array(),
                                    'p'     => array(),
                                    'span'  => array('class' => true),
                                    'bdi'  => array('class' => true),
                                ));
                           } ?>
                        </div>
                        <?php } ?>

                        <!-- Payment Section -->
                        <div class="twbb-checkout-section payment-methods">
                            <?php if(!empty($settings['payment_section_title'])) { ?>
                                <h3 class="twbb-section-title"><?php echo esc_html($settings['payment_section_title']); ?></h3>
                            <?php }
                            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                            if (!empty($available_gateways)) { ?>
                                <div class="twbb-payment-container" id="payment">
                                    <?php
                                    $i = 0;
                                    foreach ($available_gateways as $gateway) {
                                        $active_class = $i ? '' : ' twbb-active-payment';
                                        $none = $i ? 'none' : 'block';
                                        ?>
                                        <div class="twbb-payment-item<?php echo esc_attr($active_class); ?>">
                                            <div class="twbb-payment-title">
                                                <input type="radio" name="payment_method" id="payment_method_<?php echo esc_attr($gateway->id); ?>" value="<?php echo esc_attr($gateway->id); ?>" required />
                                                <label for="payment_method_<?php echo esc_attr($gateway->id); ?>">
                                                    <?php echo esc_html($gateway->get_title()); ?>
                                                </label>
                                            </div>
                                            <?php if( $settings['payments_descriptive_text'] === 'yes' || $gateway->has_fields() ) { ?>
                                            <div class="twbb-payment-description" style="display: <?php echo esc_attr($none); ?>">
                                                <?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
                                                    <?php $gateway->payment_fields(); ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <?php
                                        $i++;
                                    }
                                    ?>
                                </div>
                                <?php
                            } else { ?>
                                <div class="twbb-payment-container" id="payment">
                                    <div class="twbb-payment-item">
                                        <p class="twbb-nopayment-item"><?php echo esc_html__('No payment methods are available.', 'tenweb-builder'); ?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>

                        <!-- Submit Button -->
                        <div class="twbb-checkout-section twbb-checkout-button-section">
                            <div class="form-row place-order">
                                <div class="woocommerce-terms-and-conditions-wrapper">
                                    <div class="woocommerce-privacy-policy-text">
                                    <?php
                                    if ( apply_filters( 'woocommerce_checkout_show_terms', true ) && function_exists( 'wc_terms_and_conditions_checkbox_enabled' ) ) {
                                        ?>
                                        <div class="woocommerce-terms-and-conditions-wrapper">
                                            <p class="form-row validate-required woocommerce-invalid woocommerce-invalid-required-field">
                                                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox twbb-terms-checkbox">
                                                    <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php if($settings['terms_conditions_checkbox_active'] === 'yes') { echo 'checked'; }?> name="terms" id="terms">
                                                    <?php
                                                    echo wp_kses(
                                                        $this->woocommerce_terms_and_conditions_checkbox_text(),
                                                        array(
                                                            'a' => array(
                                                                'href' => array(),  // Allow href attribute
                                                                'class' => array(), // Allow class attribute
                                                                'target' => array() // Allow target attribute
                                                            ),
                                                            'span' => array(
                                                                'class' => array(), // Allow class attribute
                                                            )
                                                        )
                                                    );
                                                    ?>
                                                </label>
                                                <input type="hidden" name="terms-field" value="1">
                                            </p>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    </div>
                                </div>
                                <button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="<?php echo esc_attr($settings['purchase_button_text']); ?>" data-value="<?php echo esc_attr($settings['purchase_button_text']); ?>"><?php echo esc_html($settings['purchase_button_text']); ?></button>
                                <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="twbb-checkout-col2">
                        <div class="twbb-checkout-section<?php echo esc_attr($settings['sticky_right_column']) !== '' ? ' e-sticky-right-column--active' : '';  ?>">
                            <!-- Order Review -->
                            <div class="twbb-checkout order-review woocommerce-checkout-review-order">
                                <div class="order-summary">
                                    <?php if( !empty($settings['order_summary_section_title']) ) { ?>
                                    <h3 class="twbb-section-title"><?php echo esc_html($settings['order_summary_section_title']); ?></h3>
                                    <?php
                                    }
                                    echo Checkout_10Web::order_review_fragment($settings); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    ?>
                                </div>
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="twbb-checkout-section twbb-checkout-button-section twbb-checkout-button-section-mobile">
                            <div class="form-row place-order">
                                <div class="woocommerce-terms-and-conditions-wrapper">
                                    <div class="woocommerce-privacy-policy-text">
                                    <?php
                                    if ( apply_filters( 'woocommerce_checkout_show_terms', true ) && function_exists( 'wc_terms_and_conditions_checkbox_enabled' ) ) {
                                        ?>
                                        <div class="woocommerce-terms-and-conditions-wrapper">
                                            <p class="form-row validate-required woocommerce-invalid woocommerce-invalid-required-field">
                                                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox twbb-terms-checkbox">
                                                    <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php if($settings['terms_conditions_checkbox_active'] === 'yes') { echo 'checked'; }?> name="terms" id="terms">
                                                    <?php
                                                    echo wp_kses(
                                                        $this->woocommerce_terms_and_conditions_checkbox_text(),
                                                        array(
                                                            'a' => array(
                                                                'href' => array(),  // Allow href attribute
                                                                'class' => array(), // Allow class attribute
                                                                'target' => array() // Allow target attribute
                                                            ),
                                                            'span' => array(
                                                                'class' => array(), // Allow class attribute
                                                            )

                                                        )
                                                    );
                                                    ?>
                                                </label>
                                                <input type="hidden" name="terms-field" value="1">
                                            </p>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    </div>
                                </div>
                                <button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="<?php echo esc_attr($settings['purchase_button_text']); ?>" data-value="<?php echo esc_attr($settings['purchase_button_text']); ?>"><?php echo esc_html($settings['purchase_button_text']); ?></button>
                                <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
                            </div>
                        </div>

                    </div>
                </div>

            </form>
        </div>
        <?php
        remove_filter( 'woocommerce_form_field_args', [ $this, 'modify_form_field' ], 70 );
        remove_filter( 'gettext', [ $this, 'filter_gettext' ], 20 );
        remove_filter('woocommerce_checkout_fields', [$this, 'twbb_modify_form_fields' ]);
        remove_action('woocommerce_before_checkout_shipping_form', [$this, 'addShippingSectionTitle']);
    }


    /**
     * WooCommerce Terms and Conditions Checkbox Text.
     *
     * WooCommerce filter is used to apply widget settings to Checkout Terms & Conditions text and link text.
     *
     * @since 3.5.0
     *
     * @param string $text
     * @return string
     */
    public function woocommerce_terms_and_conditions_checkbox_text() {
        $terms_page_id = wc_terms_and_conditions_page_id();
        $privacy_page_id = wc_privacy_policy_page_id(); // Get the WooCommerce Privacy Policy page

        $terms_link = $terms_page_id ? '<a href="' . esc_url( get_permalink( $terms_page_id ) ) . '" target="_blank">' . esc_html__('Terms', 'tenweb-builder') . '</a>' : '';
        $privacy_link = $privacy_page_id ? '<a href="' . esc_url( get_permalink( $privacy_page_id ) ) . '" target="_blank">' . esc_html__('Privacy Policy', 'tenweb-builder') . '</a>' : '';
        if ($terms_link && $privacy_link) {
            $message = sprintf(
                esc_html__('By placing this order, I agree to the %s & %s', 'tenweb-builder'),
                $terms_link,
                $privacy_link
            );
        } elseif ($terms_link) {
            $message = sprintf(
                esc_html__('By placing this order, I agree to the %s & Privacy Policy', 'tenweb-builder'),
                $terms_link
            );
        } elseif ($privacy_link) {
            $message = sprintf(
                esc_html__('By placing this order, I agree to the Terms & %s', 'tenweb-builder'),
                $privacy_link
            );
        } else {
            $message = esc_html__('By placing this order, I agree to the Terms & Privacy Policy', 'tenweb-builder');
        }

        return "<span>".$message."</span>";
    }
}

\Elementor\Plugin::instance()->widgets_manager->register( new Checkout_10Web() );
