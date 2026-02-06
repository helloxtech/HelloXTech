<?php
namespace Tenweb_Builder\widgets\traits\menuCart;

require_once TWBB_DIR . '/widgets/traits/button_trait.php';

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use Tenweb_Builder\Widgets\Traits\Button_Trait;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

trait menuCart_Trait {
    use Button_Trait;

    public $widgetInstanse;

    protected function register_menuCart_content_menuIcon_controls() {
        $this->add_control(
            'menu_cart_icon',
            [
                'label' => esc_html__( 'Icon', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'cart-light' => esc_html__( 'Cart', 'tenweb-builder' ) . ' ' . esc_html__( 'Light', 'tenweb-builder' ),
                    'cart-medium' => esc_html__( 'Cart', 'tenweb-builder' ) . ' ' . esc_html__( 'Medium', 'tenweb-builder' ),
                    'cart-solid' => esc_html__( 'Cart', 'tenweb-builder' ) . ' ' . esc_html__( 'Solid', 'tenweb-builder' ),
                    'basket-light' => esc_html__( 'Basket', 'tenweb-builder' ) . ' ' . esc_html__( 'Light', 'tenweb-builder' ),
                    'basket-medium' => esc_html__( 'Basket', 'tenweb-builder' ) . ' ' .esc_html__( 'Medium', 'tenweb-builder' ),
                    'basket-solid' => esc_html__( 'Basket', 'tenweb-builder' ) . ' ' . esc_html__( 'Solid', 'tenweb-builder' ),
                    'bag-light' => esc_html__( 'Bag', 'tenweb-builder' ) . ' ' . esc_html__( 'Light', 'tenweb-builder' ),
                    'bag-medium' => esc_html__( 'Bag', 'tenweb-builder' ) . ' ' . esc_html__( 'Medium', 'tenweb-builder' ),
                    'bag-solid' => esc_html__( 'Bag', 'tenweb-builder' ) . ' ' . esc_html__( 'Solid', 'tenweb-builder' ),
                    'custom' => esc_html__( 'Custom', 'tenweb-builder' ),
                ],
                'default' => 'cart-medium',
                'prefix_class' => 'toggle-icon--', // Prefix class not used anymore, but kept for BC reasons.
                'render_type' => 'template',
            ]
        );

        $this->add_control(
            'menu_cart_icon_svg',
            [
                'label' => esc_html__( 'Custom Icon', 'tenweb-builder' ),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon_active',
                'default' => [
                    'value' => 'fas fa-shopping-cart',
                    'library' => 'fa-solid',
                ],
                'skin_settings' => [
                    'inline' => [
                        'none' => [
                            'label' => 'None',
                        ],
                    ],
                ],
                'recommended' => [
                    'fa-solid' => [
                        'shopping-bag',
                        'shopping-basket',
                        'shopping-cart',
                        'cart-arrow-down',
                        'cart-plus',
                    ],
                ],
                'skin' => 'inline',
                'label_block' => false,
                'condition' => [
                    'menu_cart_icon' => 'custom',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_items_indicator',
            [
                'label' => esc_html__( 'Items Indicator', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'none' => esc_html__( 'None', 'tenweb-builder' ),
                    'bubble' => esc_html__( 'Bubble', 'tenweb-builder' ),
                    'plain' => esc_html__( 'Plain', 'tenweb-builder' ),
                ],
                'prefix_class' => 'twbb_menu-cart--items-indicator-',
                'default' => 'bubble',
            ]
        );

        $this->add_control(
            'menu_cart_hide_empty_indicator',
            [
                'label' => esc_html__( 'Hide Empty', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'tenweb-builder' ),
                'label_off' => esc_html__( 'No', 'tenweb-builder' ),
                'return_value' => 'hide',
                'prefix_class' => 'twbb_menu-cart--empty-indicator-',
                'condition' => [
                    'menu_cart_items_indicator!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_show_subtotal',
            [
                'label' => esc_html__( 'Subtotal', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'tenweb-builder' ),
                'label_off' => esc_html__( 'Hide', 'tenweb-builder' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'prefix_class' => 'twbb_menu-cart--show-subtotal-',
            ]
        );

    }

    protected function register_menuCart_content_cart_controls() {
        $this->add_control(
            'menu_cart_cart_type',
            [
                'label' => esc_html__( 'Cart Type', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'side-cart' => esc_html__( 'Side Cart', 'tenweb-builder' ),
                    'mini-cart' => esc_html__( 'Mini Cart', 'tenweb-builder' ),
                ],
                'default' => 'side-cart',
                'prefix_class' => 'twbb_menu-cart--cart-type-',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'menu_cart_open_cart',
            [
                'label' => esc_html__( 'Open Cart', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'click' => esc_html__( 'On Click', 'tenweb-builder' ),
                    'mouseover' => esc_html__( 'On Hover', 'tenweb-builder' ),
                ],
                'default' => 'click',
                'frontend_available' => true,
                'render_type' => 'template',
            ]
        );

        $this->add_responsive_control(
            'menu_cart_side_cart_alignment',
            [
                'label' => esc_html__( 'Cart Position', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__( 'Left', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'end' => [
                        'title' => esc_html__( 'Right', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'condition' => [
                    'menu_cart_cart_type' => 'side-cart',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'start' => '--side-cart-alignment-transform: translateX(-100%); --side-cart-alignment-right: auto; --side-cart-alignment-left: 0;',
                    'end' => '--side-cart-alignment-transform: translateX(100%); --side-cart-alignment-left: auto; --side-cart-alignment-right: 0;',
                ],
            ]
        );

        $this->add_responsive_control(
            'menu_cart_mini_cart_alignment',
            [
                'label' => esc_html__( 'Cart Position', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__( 'Left', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__( 'Right', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'condition' => [
                    'menu_cart_cart_type' => 'mini-cart',
                ],
                'selectors' => [
                    '{{WRAPPER}}.twbb_menu-cart--cart-type-mini-cart .twbb_menu-cart_10web__container' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'start' => 'left: 0; right: auto; transform: none;',
                    'center' => 'left: 50%; right: auto; transform: translateX(-50%);',
                    'end' => 'right: 0; left: auto; transform: none;',
                ],
            ]
        );

        $this->add_responsive_control(
            'menu_cart_mini_cart_spacing',
            [
                'label' => esc_html__( 'Cart Distance', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => -300,
                        'max' => 300,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'condition' => [
                    'menu_cart_cart_type' => 'mini-cart',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--mini-cart-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_heading_close_cart_button',
            [
                'label' => esc_html__( 'Close Cart', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'menu_cart_close_cart_button_show',
            [
                'label' => esc_html__( 'Close Icon', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'tenweb-builder' ),
                'label_off' => esc_html__( 'Hide', 'tenweb-builder' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}} .twbb_menu-cart__close-button, {{WRAPPER}} .twbb_menu-cart__close-button-custom' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    '' => 'display: none;',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_close_cart_icon_svg',
            [
                'label' => esc_html__( 'Custom Icon', 'tenweb-builder' ),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon_active',
                'skin_settings' => [
                    'inline' => [
                        'none' => [
                            'label' => 'Default',
                            'icon' => 'fas fa-times',
                        ],
                        'icon' => [
                            'icon' => 'eicon-star',
                        ],
                    ],
                ],
                'recommended' => [
                    'fa-regular' => [
                        'times-circle',
                    ],
                    'fa-solid' => [
                        'times',
                        'times-circle',
                    ],
                ],
                'skin' => 'inline',
                'label_block' => false,
                'condition' => [
                    'menu_cart_close_cart_button_show!' => '',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_close_cart_button_alignment',
            [
                'label' => esc_html__( 'Icon Position', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__( 'Left', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'end' => [
                        'title' => esc_html__( 'Right', 'tenweb-builder' ),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'condition' => [
                   'menu_cart_close_cart_button_show!' => '',
                ],
                'selectors_dictionary' => [
                    'start' => 'margin-right: auto',
                    'end' => 'margin-left: auto',
                ],
                'selectors' => [
                    '{{WRAPPER}} .twbb_menu-cart__close-button, {{WRAPPER}} .twbb_menu-cart__close-button-custom' => '{{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_heading_remove_item_button',
            [
                'label' => esc_html__( 'Remove Item', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'menu_cart_show_remove_icon',
            [
                'label' => esc_html__( 'Remove Item Icon', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'tenweb-builder' ),
                'label_off' => esc_html__( 'Hide', 'tenweb-builder' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'prefix_class' => 'twbb_menu-cart--show-remove-button-',
            ]
        );

        $this->add_control(
            'menu_cart_remove_item_button_position',
            [
                'label' => esc_html__( 'Icon Position', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'top' => [
                        'title' => esc_html__( 'Top', 'tenweb-builder' ),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'middle' => [
                        'title' => esc_html__( 'Middle', 'tenweb-builder' ),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'bottom' => [
                        'title' => esc_html__( 'Bottom', 'tenweb-builder' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => '',
                'prefix_class' => 'remove-item-position--',
                'condition' => [
                    'menu_cart_show_remove_icon!' => '',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_heading_price_quantity',
            [
                'label' => esc_html__( 'Price and Quantity', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'menu_cart_price_quantity_position',
            [
                'label' => esc_html__( 'Position', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'top' => [
                        'title' => esc_html__( 'Top', 'tenweb-builder' ),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'bottom' => [
                        'title' => esc_html__( 'Bottom', 'tenweb-builder' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'top' => '--price-quantity-position--grid-template-rows: auto 75%; --price-quantity-position--align-self: start;',
                    'bottom' => '',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_show_divider',
            [
                'label' => esc_html__( 'Cart Dividers', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'separator' => 'before',
                'label_on' => esc_html__( 'Show', 'tenweb-builder' ),
                'label_off' => esc_html__( 'Hide', 'tenweb-builder' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}}' => '--divider-style: {{VALUE}}; --subtotal-divider-style: {{VALUE}};',
                ],
                'selectors_dictionary' => [
                    '' => 'none',
                    'yes' => 'solid',
                ],
            ]
        );


        /* Buttons */
        $this->add_control(
            'menu_cart_heading_buttons',
            [
                'label' => esc_html__( 'Buttons', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'menu_cart_view_cart_button_show',
            [
                'label' => esc_html__( 'View Cart', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'tenweb-builder' ),
                'label_off' => esc_html__( 'Hide', 'tenweb-builder' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    '' => '--view-cart-button-display: none; --cart-footer-layout: 1fr;',
                ],
                'render_type' => 'template',
            ]
        );

        $this->register_button_content_controls([
            'section_condition' => ['menu_cart_view_cart_button_show' => 'yes'],
            'button_default_text' => __('View Cart', 'tenweb-builder'),
            'text_control_label' => __('View Cart', 'tenweb-builder'),
            'prefix' => 'menu_cart_view_',
        ]);

        // This control sets the default cart link, but it is intentionally hidden from the Elementor UI
        // using a fake condition that will never match.
        // Purpose: To programmatically use this value in rendering logic without exposing it to the user.
        $this->update_control(
            'menu_cart_view_link',
            [
                'default' => [
                    'url' => function_exists('wc_get_cart_url') ? esc_url(wc_get_cart_url()) : '',
                ],
                'condition' => [
                    '_never_match_key' => 'never_value',
                ],
            ]
        );

        $this->add_control(
            'menu_cart_checkout_button_show',
            [
                'label' => esc_html__( 'Checkout', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'tenweb-builder' ),
                'label_off' => esc_html__( 'Hide', 'tenweb-builder' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    '' => '--checkout-button-display: none; --cart-footer-layout: 1fr;',
                ],
                'render_type' => 'template',
            ]
        );

        $this->register_button_content_controls([
            'section_condition' => ['menu_cart_checkout_button_show' => 'yes'],
            'button_default_text' => __('Checkout', 'tenweb-builder'),
            'text_control_label' => __('Checkout', 'tenweb-builder'),
            'prefix' => 'menu_cart_checkout_view_',
        ]);

        // This control sets the default checkout link, but it is intentionally hidden from the Elementor UI
        // using a fake condition that will never match.
        // Purpose: To programmatically use this value in rendering logic without exposing it to the user.
        $this->update_control(
            'menu_cart_checkout_link',
            [
                'default' => [
                    'url' => function_exists('wc_get_checkout_url') ? esc_url(wc_get_checkout_url()) : '',
                ],
                'condition' => [
                    '_never_match_key' => 'never_value',
                ],
            ]
        );


        $this->add_control(
            'buttons_position',
            [
                'label' => esc_html__( 'Position', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'top' => [
                        'title' => esc_html__( 'Top', 'tenweb-builder' ),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'bottom' => [
                        'title' => esc_html__( 'Bottom', 'tenweb-builder' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => '',
                'condition' => [
                    'menu_cart_cart_type' => 'side-cart',
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'menu_cart_view_cart_button_show',
                            'operator' => '!==',
                            'value' => '',
                        ],
                        [
                            'name' => 'menu_cart_checkout_button_show',
                            'operator' => '!==',
                            'value' => '',
                        ],
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'bottom' => '--cart-buttons-position-margin: auto;',
                ],
            ]
        );
    }

    protected function register_menuCart_content_additionalOption_controls() {
        $this->add_control(
            'menu_cart_heading_additional_options',
            [
                'label' => esc_html__( 'Cart', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'menu_cart_automatically_open_cart',
            [
                'label' => esc_html__( 'Automatically Open Cart', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'description' => esc_html__( 'Open the cart every time an item is added.', 'tenweb-builder' ),
                'label_on' => esc_html__( 'On', 'tenweb-builder' ),
                'label_off' => esc_html__( 'Off', 'tenweb-builder' ),
                'return_value' => 'yes',
                'default' => 'no',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'menu_cart_automatically_update_cart',
            [
                'label' => esc_html__( 'Automatically Update Cart', 'tenweb-builder' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'On', 'tenweb-builder' ),
                'label_off' => esc_html__( 'Off', 'tenweb-builder' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__( 'Updates to the cart (e.g., a removed item) via Ajax. The cart will update without refreshing the whole page.', 'tenweb-builder' ),
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'yes' => '--elementor-remove-from-cart-button: none; --remove-from-cart-button: block;',
                    ''    => '--elementor-remove-from-cart-button: block; --remove-from-cart-button: none;',
                ],
            ]
        );
    }

    protected function register_menuCart_style_menuIcon_controls($prefix = '', $selector = '{{WRAPPER}}') {
        $this->start_controls_tabs( $prefix . 'menu_cart_toggle_button_colors' );

        $this->start_controls_tab( $prefix . 'menu_cart_toggle_button_normal_colors', [ 'label' => esc_html__( 'Normal', 'tenweb-builder' ) ] );

        $this->add_control(
            $prefix . 'menu_cart_toggle_button_text_color',
            [
                'label' => esc_html__( 'Price Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--toggle-button-text-color: {{VALUE}};',
                ],
                'condition' => [
                    'menu_cart_show_subtotal!' => '',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_toggle_button_icon_color',
            [
                'label' => esc_html__( 'Icon Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--toggle-button-icon-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_toggle_button_background_color',
            [
                'label' => esc_html__( 'Background Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--toggle-button-background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => $prefix . 'menu_cart_toggle_button_normal_box_shadow',
                'selector' => $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button.elementor-button',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => $prefix . 'menu_cart_toggle_button_normal_border',
                'selector' => $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button.elementor-button',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_toggle_button_normal_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button.elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_cart_toggle_button_normal_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button.elementor-button',
                'separator' => 'before',
            ]
        );



        $this->end_controls_tab();

        $this->start_controls_tab( $prefix . 'menu_cart_toggle_button_hover_colors', [ 'label' => __( 'Hover', 'tenweb-builder' ) ] );

        $this->add_control(
            $prefix . 'menu_cart_toggle_button_hover_text_color',
            [
                'label' => esc_html__( 'Price Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--toggle-button-hover-text-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_toggle_button_hover_icon_color',
            [
                'label' => esc_html__( 'Icon Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--toggle-button-icon-hover-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_toggle_button_hover_background_color',
            [
                'label' => esc_html__( 'Background Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--toggle-button-hover-background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => $prefix . 'menu_cart_toggle_button_hover_box_shadow',
                'selector' => $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button.elementor-button:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => $prefix . 'menu_cart_toggle_button_hover_border',
                'selector' => $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button.elementor-button:hover',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_toggle_button_hover_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button.elementor-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_cart_toggle_button_hover_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button.elementor-button:hover',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->add_control(
            $prefix . 'menu_cart_heading_icon_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Icon', 'tenweb-builder' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_toggle_icon_size',
            [
                'label' => esc_html__( 'Size', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    $selector => '--toggle-icon-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_toggle_icon_spacing',
            [
                'label' => esc_html__( 'Spacing', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    'body:not(.rtl) ' . $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button .elementor-button-text' => 'margin-right: {{SIZE}}{{UNIT}}',
                    'body.rtl ' . $selector . ' .twbb_menu-cart_10web__toggle .twbb_menu-cart__toggle_button .elementor-button-text' => 'margin-left: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'show_subtotal!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_toggle_button_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    $selector => '--toggle-icon-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_items_indicator_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Items Indicator', 'tenweb-builder' ),
                'separator' => 'before',
                'condition' => [
                    'menu_cart_items_indicator!' => 'none',
                ],
            ]
        );
        $this->add_control(
            $prefix . 'menu_cart_items_indicator_text_color',
            [
                'label' => esc_html__( 'Text Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--items-indicator-text-color: {{VALUE}};',
                ],
                'condition' => [
                    'menu_cart_items_indicator!' => 'none',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_items_indicator_background_color',
            [
                'label' => esc_html__( 'Background Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--items-indicator-background-color: {{VALUE}};',
                ],
                'condition' => [
                    'menu_cart_items_indicator' => 'bubble',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_items_indicator_distance',
            [
                'label' => esc_html__( 'Distance', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 4,
                        'step' => 0.1,
                    ],
                ],
                //10Web minor customization
                'selectors' => [
                    'body:not(.rtl) ' . $selector . ' .twbb_menu-cart_10web__toggle .elementor-button-icon .elementor-button-icon-qty[data-counter]' => 'right: calc(-15px - {{SIZE}}{{UNIT}}); top: calc( -15px - {{SIZE}}{{UNIT}});',
                    'body.rtl ' . $selector . ' .twbb_menu-cart_10web__toggle .elementor-button-icon .elementor-button-icon-qty[data-counter]' => 'right: calc(15px - {{SIZE}}{{UNIT}}); top: calc( 15px - {{SIZE}}{{UNIT}}); left: auto;',
                ],
                'condition' => [
                    'menu_cart_items_indicator' => 'bubble',
                ],
            ]
        );
    }

    protected function register_menuCart_style_cart_controls($prefix = '', $selector = '{{WRAPPER}}') {
        $this->add_control(
            $prefix . 'menu_cart_background_color',
            [
                'label' => esc_html__( 'Background Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--cart-background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_border_type',
            [
                'label' => esc_html__( 'Border Type', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'none' => esc_html__( 'None', 'tenweb-builder' ),
                    'solid' => esc_html__( 'Solid', 'tenweb-builder' ),
                    'double' => esc_html__( 'Double', 'tenweb-builder' ),
                    'dotted' => esc_html__( 'Dotted', 'tenweb-builder' ),
                    'dashed' => esc_html__( 'Dashed', 'tenweb-builder' ),
                    'groove' => esc_html__( 'Groove', 'tenweb-builder' ),
                ],
                'selectors' => [
                    $selector => '--cart-border-style: {{VALUE}};',
                ],
                'default' => 'none',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_border_width',
            [
                'label' => esc_html__( 'Width', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    $selector . ' .twbb_menu-cart_10web__main' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    $prefix . 'menu_cart_border_type!' => 'none',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_border_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--cart-border-color: {{VALUE}};',
                ],
                'condition' => [
                    $prefix . 'menu_cart_border_type!' => 'none',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    $selector => '--cart-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => $prefix . 'menu_cart_cart_box_shadow',
                'selector' => $selector . ' .twbb_menu-cart_10web__main',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_padding',
            [
                'label' => esc_html__( 'Padding', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    $selector => '--cart-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_heading_close',
            [
                'label' => esc_html__( 'Close Cart', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'condition' => [
                    'menu_cart_close_cart_button_show!' => '',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_close_cart_icon_size',
            [
                'label' => esc_html__( 'Icon Size', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    $selector => '--cart-close-icon-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'menu_cart_close_cart_button_show!' => '',
                ],
            ]
        );

        $this->start_controls_tabs( $prefix . 'cart_icon_style' );

        $this->start_controls_tab(
            $prefix . 'menu_cart_icon_normal',
            [
                'label' => esc_html__( 'Normal', 'tenweb-builder' ),
                'condition' => [
                    'menu_cart_close_cart_button_show!' => '',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_close_cart_icon_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--cart-close-button-color: {{VALUE}};',
                ],
                'condition' => [
                    'menu_cart_close_cart_button_show!' => '',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            $prefix . 'menu_cart_icon_hover',
            [
                'label' => esc_html__( 'Hover', 'tenweb-builder' ),
                'condition' => [
                    'menu_cart_close_cart_button_show!' => '',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_close_cart_icon_hover_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--cart-close-button-hover-color: {{VALUE}};',
                ],
                'condition' => [
                    'menu_cart_close_cart_button_show!' => '',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            $prefix . 'menu_cart_heading_remove_item_button_style',
            [
                'label' => esc_html__( 'Remove Item', 'tenweb-builder' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'menu_cart_show_remove_icon!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_remove_item_button_size',
            [
                'label' => esc_html__( 'Icon Size', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'selectors' => [
                    $selector => '--remove-item-button-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'menu_cart_show_remove_icon!' => '',
                ],
            ]
        );

        $this->start_controls_tabs(
            $prefix . 'menu_cart_cart_remove_item_button_style',
            [
                'condition' => [
                    'menu_cart_show_remove_icon!' => '',
                ],
            ]
        );

        $this->start_controls_tab(
            $prefix . 'menu_cart_remove_item_button_normal',
            [
                'label' => esc_html__( 'Normal', 'tenweb-builder' ),
                'condition' => [
                    'menu_cart_show_remove_icon!' => '',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_remove_item_button_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--remove-item-button-color: {{VALUE}}',
                ],
                'condition' => [
                    'menu_cart_show_remove_icon!' => '',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            $prefix . 'menu_cart_remove_item_button_hover',
            [
                'label' => esc_html__( 'Hover', 'tenweb-builder' ),
                'condition' => [
                    'menu_cart_show_remove_icon!' => '',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_remove_item_button_hover_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--remove-item-button-hover-color: {{VALUE}};',
                ],
                'condition' => [
                    'menu_cart_show_remove_icon!' => '',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            $prefix . 'menu_cart_heading_subtotal_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Subtotal', 'tenweb-builder' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_subtotal_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--menu-cart-subtotal-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_cart_subtotal_typography',
                'selector' => $selector . ' .twbb_menu-cart__subtotal',
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_subtotal_alignment',
            [
                'label' => esc_html__( 'Alignment', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'tenweb-builder' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'tenweb-builder' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'tenweb-builder' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    $selector => '--menu-cart-subtotal-text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_subtotal_divider_style',
            [
                'label' => esc_html__( 'Divider Style', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'None', 'tenweb-builder' ),
                    'solid' => esc_html__( 'Solid', 'tenweb-builder' ),
                    'double' => esc_html__( 'Double', 'tenweb-builder' ),
                    'dotted' => esc_html__( 'Dotted', 'tenweb-builder' ),
                    'dashed' => esc_html__( 'Dashed', 'tenweb-builder' ),
                    'groove' => esc_html__( 'Groove', 'tenweb-builder' ),
                ],
                'selectors' => [
                    $selector . ' .widget_shopping_cart_content' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    '' => '--subtotal-divider-left-width: 0; --subtotal-divider-right-width: 0;',
                    'solid' => '--subtotal-divider-style: solid;',
                    'double' => '--subtotal-divider-style: double;',
                    'dotted' => '--subtotal-divider-style: dotted;',
                    'dashed' => '--subtotal-divider-style: dashed;',
                    'groove' => '--subtotal-divider-style: groove;',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_subtotal_divider_width',
            [
                'label' => esc_html__( 'Width', 'tenweb-builder' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'selectors' => [
                    $selector . ' .widget_shopping_cart_content' => '--subtotal-divider-top-width: {{TOP}}{{UNIT}}; --subtotal-divider-right-width: {{RIGHT}}{{UNIT}}; --subtotal-divider-bottom-width: {{BOTTOM}}{{UNIT}}; --subtotal-divider-left-width: {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_subtotal_divider_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .widget_shopping_cart_content' => '--subtotal-divider-color: {{VALUE}}',
                ],
            ]
        );

    }

    protected function register_menuCart_style_products_controls($prefix = '', $selector = '{{WRAPPER}}') {
        $this->add_control(
            $prefix . 'menu_cart_heading_product_title_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Product Title', 'tenweb-builder' ),
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_cart_product_title_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => $selector . ' .twbb_menu-cart__product-name a',
            ]
        );

        $this->start_controls_tabs( $prefix . 'menu_cart_product_title_colors' );

        $this->start_controls_tab( $prefix . 'menu_cart_product_title_normal_colors', [ 'label' => esc_html__( 'Normal', 'tenweb-builder' ) ] );

        $this->add_control(
            $prefix . 'menu_cart_product_title_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb_menu-cart__product-name, ' . $selector . ' .twbb_menu-cart__product-name a' => 'color: {{VALUE}}',

                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( $prefix . 'menu_cart_product_title_hover_colors', [ 'label' => esc_html__( 'Hover', 'tenweb-builder' ) ] );

        $this->add_control(
            $prefix . 'menu_cart_product_title_hover_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb_menu-cart__product-name a:hover' => 'color: {{VALUE}};',

                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            $prefix . 'menu_cart_heading_product_variations_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Variations', 'tenweb-builder' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_product_variations_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--product-variations-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_cart_product_variations_typography',
                'selector' => $selector . ' .twbb_menu-cart__product .variation',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_heading_product_price_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Product Price', 'tenweb-builder' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_product_price_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--product-price-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_cart_product_price_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => $selector . ' .twbb_menu-cart__product-price .woocommerce-Price-amount.amount',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_heading_quantity_title_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Quantity', 'tenweb-builder' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_product_quantity_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector . ' .twbb_menu-cart__product-price .product-quantity' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_cart_product_quantity_typography',
                'selector' => $selector . ' .twbb_menu-cart__product-price .product-quantity',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_heading_product_divider_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Divider', 'tenweb-builder' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_divider_style',
            [
                'label' => esc_html__( 'Style', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'None', 'tenweb-builder' ),
                    'solid' => esc_html__( 'Solid', 'tenweb-builder' ),
                    'double' => esc_html__( 'Double', 'tenweb-builder' ),
                    'dotted' => esc_html__( 'Dotted', 'tenweb-builder' ),
                    'dashed' => esc_html__( 'Dashed', 'tenweb-builder' ),
                    'groove' => esc_html__( 'Groove', 'tenweb-builder' ),
                ],
                'selectors' => [
                    $selector => '--divider-style: {{VALUE}}; --subtotal-divider-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_divider_color',
            [
                'label' => esc_html__( 'Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--divider-color: {{VALUE}}; --subtotal-divider-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_divider_width',
            [
                'label' => esc_html__( 'Weight', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    $selector => '--divider-width: {{SIZE}}{{UNIT}}; --subtotal-divider-top-width: {{SIZE}}{{UNIT}}; --subtotal-divider-right-width: {{SIZE}}{{UNIT}}; --subtotal-divider-bottom-width: {{SIZE}}{{UNIT}}; --subtotal-divider-left-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_divider_gap',
            [
                'label' => esc_html__( 'Space Between', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    $selector => '--product-divider-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

    }

    protected function register_menuCart_style_buttons_controls($prefix = '', $selector = '{{WRAPPER}}') {
        $this->add_responsive_control(
            $prefix . 'menu_cart_buttons_layout',
            [
                'label' => esc_html__( 'Layout', 'tenweb-builder' ),
                'type' => Controls_Manager::SELECT2,
                'options' => [
                    'inline' => esc_html__( 'Inline', 'tenweb-builder' ),
                    'stacked' => esc_html__( 'Stacked', 'tenweb-builder' ),
                ],
                'default' => 'inline',
                'devices' => [ 'desktop', 'tablet', 'mobile' ],
                'condition' => [
                    'menu_cart_view_cart_button_show!' => '',
                    'menu_cart_checkout_button_show!' => '',
                ],
                'selectors' => [
                    $selector => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'inline' => '--cart-footer-layout: 1fr 1fr; --products-max-height-sidecart: calc(100vh - 240px); --products-max-height-minicart: calc(100vh - 385px)',
                    'stacked' => '--cart-footer-layout: 1fr; --products-max-height-sidecart: calc(100vh - 300px); --products-max-height-minicart: calc(100vh - 450px)',
                ],
            ]
        );
        $this->add_responsive_control(
            $prefix . 'menu_cart_space_between_buttons',
            [
                'label' => esc_html__( 'Space Between', 'tenweb-builder' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    $selector => '--space-between-buttons: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'menu_cart_view_cart_button_show!' => '',
                    'menu_cart_checkout_button_show!' => '',
                ],
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_heading_view_cart_button_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'View Cart', 'tenweb-builder' ),
                'condition' => [
                    'menu_cart_view_cart_button_show!' => '',
                ],
            ]
        );


        $this->register_button_style_controls([
            'section_condition' => [
                    'menu_cart_view_cart_button_show' => 'yes'
            ],
            'prefix' => 'menu_cart_view_',
        ]);

        $this->add_control(
            $prefix . 'menu_cart_heading_checkout_cart_button_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Checkout', 'tenweb-builder' ),
                'condition' => [
                    'menu_cart_checkout_button_show!' => '',
                ],
            ]
        );


        $this->register_button_style_controls([
            'section_condition' => [
                    'menu_cart_checkout_button_show' => 'yes'
            ],
            'prefix' => 'menu_cart_checkout_view_',
        ]);

    }

    protected function register_menuCart_style_messages_controls($prefix = '', $selector = '{{WRAPPER}}') {
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $prefix . 'menu_cart_empty_message_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => $selector . ' .woocommerce-mini-cart__empty-message',
            ]
        );

        $this->add_control(
            $prefix . 'menu_cart_empty_message_color',
            [
                'label' => esc_html__( 'Empty Cart Message Color', 'tenweb-builder' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--empty-message-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . 'menu_cart_empty_message_alignment',
            [
                'label' => esc_html__( 'Border Radius', 'tenweb-builder' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'tenweb-builder' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'tenweb-builder' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'tenweb-builder' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__( 'Justified', 'tenweb-builder' ),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    $selector => '--empty-message-alignment: {{VALUE}};',
                ],
            ]
        );
    }


    /**
     * Renders the main container for the menu cart widget.
     *
     * Initializes the cart widget with settings and outputs the container
     * which wraps the cart toggle button and mini cart popup content.
     *
     * @param Widget_Base|null $instance The Elementor widget instance.
    */
    protected function render_menuCart( Widget_Base $instance = null, $prefix = '' ) {
        $this->widgetInstanse = $instance;
        $settings = $instance->get_settings_for_display();

        echo '<div class="twbb-menu-cart-widget">';
        $this->init_menu_cart($settings, $prefix);
        echo '</div>';
    }

    /**
     * Initializes the markup for the mini cart.
     *
     * Outputs the structure containing the cart toggle button and the
     * hidden cart popup with close button and cart contents.
     *
     * @param array $settings The widget settings from Elementor.
    */
    public function init_menu_cart( $settings, $prefix = '' ) {
        if ( ! function_exists( 'WC' ) || null === WC()->cart ) {
            return;
        }
        ?>
        <div class="twbb_menu-cart_10web__wrapper">
            <div class="twbb_menu-cart_10web__toggle_wrapper">
                <div class="twbb_menu-cart_10web__container elementor-lightbox" aria-hidden="true">
                    <div class="twbb_menu-cart_10web__main" aria-hidden="true">

                        <?php $this->render_menu_cart_close_button( $settings, $prefix ); ?>

                        <div class="widget_shopping_cart_content">
                            <?php $this->mini_cart_template(); ?>
                        </div>
                        <div class="twbb_menu-cart_footer-buttons">
                            <?php
                            if( isset($settings['menu_cart_view_cart_button_show']) && $settings['menu_cart_view_cart_button_show'] === 'yes' ) {
                                $this->render_button($this->widgetInstanse, 'menu_cart_view_');
                            }
                            if( isset($settings['menu_cart_checkout_button_show']) && $settings['menu_cart_checkout_button_show'] === 'yes') {
                                $this->render_button($this->widgetInstanse, 'menu_cart_checkout_view_');
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php $this->render_menu_cart_toggle_button( $settings, $prefix ); ?>
            </div>
        </div> <!-- close twbb_menu-cart_10web__wrapper -->
        <?php
    }

    /**
     * Renders a single item in the mini cart.
     *
     * Outputs product image, name, quantity, price, and a remove button
     * for each cart item, applying standard WooCommerce filters and classes.
     *
     * @param string $cart_item_key The cart item key.
     * @param array $cart_item      The cart item array.
    */
    public function twbb_render_cart_item( $cart_item_key, $cart_item ) {
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $is_product_visible = ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_widget_cart_item_visible', TRUE, $cart_item, $cart_item_key));
        if ( !$is_product_visible ) {
            return;
        }
        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
        $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
        ?>
        <div class="twbb_menu-cart__product woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
            <div class="twbb_menu-cart__product-image product-thumbnail">
                <?php
                $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                if ( !$product_permalink ) :
                    echo wp_kses_post($thumbnail);
                else :
                    printf('<a href="%s">%s</a>', esc_url($product_permalink), wp_kses_post($thumbnail));
                endif;
                ?>
            </div>
            <div class="twbb_menu-cart__product-name product-name" data-title="<?php esc_attr_e('Product', 'tenweb-builder'); ?>">
                <?php
                if ( !$product_permalink ) :
                    echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;');
                else :
                    echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
                endif;
                do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);
                // Meta data.
                echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>
            <div class="twbb_menu-cart__product-price product-price" data-title="<?php esc_attr_e('Price', 'tenweb-builder'); ?>">
                <?php echo apply_filters('woocommerce_widget_cart_item_quantity', '<span class="product-quantity">' . sprintf('%s &times; %s', $cart_item['quantity'], $product_price) . '</span>', $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="twbb_menu-cart__product-remove product-remove">
                <?php foreach ( [ 'twbb_remove_from_cart_button', 'remove_from_cart_button' ] as $class ) {
                    echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        '<a href="%s" class="%s" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"></a>',
                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                        $class,
                        __( 'Remove this item', 'tenweb-builder' ),
                        esc_attr( $product_id ),
                        esc_attr( $cart_item_key ),
                        esc_attr( $_product->get_sku() )
                    ), $cart_item_key );
                } ?>
            </div>
        </div>
        <?php
    }

    /**
     * Outputs the mini cart content area.
     *
     * If the cart is empty, displays an empty cart message. Otherwise,
     * it renders all products, subtotal, and footer action buttons
     * (View Cart / Checkout) based on the widget settings.
     *
    */
    public function mini_cart_template() {
        $cart_items = WC()->cart->get_cart();

        if ( empty( $cart_items ) ) { ?>
            <div class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'tenweb-builder' ); ?></div>
        <?php } else { ?>
            <div class="twbb_menu-cart__products woocommerce-mini-cart cart woocommerce-cart-form__contents">
                <?php
                foreach ( $cart_items as $cart_item_key => $cart_item ) {
                    $this->twbb_render_cart_item( $cart_item_key, $cart_item );
                }
                ?>
            </div>
            <div class="twbb_menu-cart__subtotal">
                <strong><?php echo esc_html__( 'Subtotal', 'tenweb-builder' ); // phpcs:ignore WordPress.WP.I18n ?>:</strong>
                <?php echo WC()->cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

            <?php
        }
    }

    /**
     * Renders the cart toggle button.
     *
     * Displays subtotal and item count. Includes a default or custom icon
     * representing the cart. The button is used to show/hide the mini cart popup.
     *
     * @param array $settings The widget settings from Elementor.
    */
    public function render_menu_cart_toggle_button( $settings, $prefix = '' ) {
        if ( null === WC()->cart ) {
            return;
        }
        $product_count = WC()->cart->get_cart_contents_count();
        $sub_total = WC()->cart->get_cart_subtotal();
        $icon = ! empty( $settings[$prefix . 'menu_cart_icon'] ) ? $settings[$prefix . 'menu_cart_icon'] : 'cart-medium';
        ?>
        <div class="twbb_menu-cart_10web__toggle elementor-button-wrapper">
            <a id="twbb_menu-cart__toggle_button" href="#" class="twbb_menu-cart__toggle_button elementor-button elementor-size-sm" aria-expanded="false">
                <span class="elementor-button-text"><?php echo $sub_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                <span class="elementor-button-icon">
					<span class="elementor-button-icon-qty" data-counter="<?php echo esc_attr( $product_count ); ?>"><?php echo $product_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php
                    self::render_menu_icon( $settings, $icon, $prefix );
                    ?>
					<span class="elementor-screen-only"><?php esc_html_e( 'Cart', 'tenweb-builder' ); ?></span>
				</span>
            </a>
        </div>
        <?php
    }

    /**
     * Renders the close button for the mini cart popup.
     *
     * Supports both default and custom SVG icons based on the widget settings.
     * This button is visible inside the popup and allows users to close it.
     *
     * @param array $settings The widget settings from Elementor.
    */
    public function render_menu_cart_close_button( $settings, $prefix = '' ) {
        $has_custom_icon = ! empty( $settings['menu_cart_close_cart_icon_svg']['value'] ) && 'yes' === $settings['menu_cart_close_cart_button_show'];
        $toggle_button_class = 'twbb_menu-cart__close-button';
        if ( $has_custom_icon ) {
            $toggle_button_class .= '-custom';
        }
        ?>
        <div class="<?php echo sanitize_html_class( $toggle_button_class ); ?>">
            <?php
            if ( $has_custom_icon ) {
                Icons_Manager::render_icon( $settings[$prefix . 'menu_cart_close_cart_icon_svg'], [
                    'class' => 'e-close-cart-custom-icon',
                    'aria-hidden' => 'true',
                ] );
            }
            ?>
        </div>
        <?php
    }

    /**
     * Renders the cart icon inside the toggle button.
     *
     * Uses either a built-in icon from the eicons library or a custom SVG icon
     * depending on the selected icon type in the widget settings.
     *
     * @param array  $settings The widget settings.
     * @param string $icon     The default icon name.
    */
    public function render_menu_icon( $settings, string $icon, $prefix = '' ) {
        if ( ! empty( $settings[$prefix . 'menu_cart_icon'] ) && 'custom' === $settings[$prefix . 'menu_cart_icon'] ) {
            $this->render_custom_menu_icon( $settings, $prefix );
        } else {
            Icons_Manager::render_icon( [
                'library' => 'eicons',
                'value' => 'eicon-' . $icon,
            ] );
        }
    }

    /**
     * Renders a custom SVG icon for the toggle button.
     *
     * If a valid custom SVG icon is defined in settings, it renders it.
     * Otherwise, falls back to a default Font Awesome shopping cart icon.
     *
     * @param array $settings The widget settings from Elementor.
    */
    private function render_custom_menu_icon( $settings, $prefix = '' ) {
        if ( empty( $settings[$prefix . 'menu_cart_icon_svg'] ) ) {
            echo '<i class="fas fa-shopping-cart"></i>'; // Default Custom icon.
        } else {
            Icons_Manager::render_icon( $settings[$prefix . 'menu_cart_icon_svg'], [
                'class' => 'e-toggle-cart-custom-icon',
                'aria-hidden' => 'true',
            ] );
        }
    }
}
