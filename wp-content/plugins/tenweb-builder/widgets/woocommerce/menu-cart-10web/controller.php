<?php
namespace Tenweb_Builder\Widgets\Woocommerce\Products;

require_once TWBB_DIR . '/widgets/traits/menuCart/menu_cart_trait.php';

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Tenweb_Builder\Classes\Woocommerce\Woocommerce;
use Tenweb_Builder\widgets\traits\menuCart\menuCart_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Menu_Cart_10Web extends Widget_Base {

    use menuCart_Trait;

    public function get_script_depends() {
        return ['twbb-menu-cart-10web-script', 'twbb-menu-cart-trait-script'];
    }

    public function get_style_depends() {
        return ['twbb-menu-cart-style'];
    }

	public function get_name() {
		return 'twbb_woocommerce-menu-cart-10web';
	}

	public function get_title() {
		return esc_html__( 'Menu Cart 10WEB', 'tenweb-builder' );
	}

	public function get_icon() {
		return 'twbb-menu_cart twbb-widget-icon';
	}

	public function get_categories() {
		return [ Woocommerce::WOOCOMMERCE_GROUP ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'product', 'menu', 'cart' ];
	}

	protected function register_controls() {
        $this->register_menuCart_content_controls();
        $this->register_menuCart_style_controls();
	}

    protected function register_menuCart_content_controls() {
        $this->start_controls_section(
            'section_menu_icon_content',
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

    protected function register_menuCart_style_controls() {
        $this->start_controls_section(
            'section_toggle_style',
            [
                'label' => esc_html__( 'Menu Icon', 'tenweb-builder' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->register_menuCart_style_menuIcon_controls();
        $this->end_controls_section();

        $this->start_controls_section(
            'section_cart_style',
            [
                'label' => esc_html__( 'Cart', 'tenweb-builder' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->register_menuCart_style_cart_controls();
        $this->end_controls_section();

        $this->start_controls_section(
            'section_product_tabs_style',
            [
                'label' => esc_html__( 'Products', 'tenweb-builder' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->register_menuCart_style_products_controls();
        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_buttons',
            [
                'label' => esc_html__( 'Buttons', 'tenweb-builder' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'menu_cart_view_cart_button_show',
                            'operator' => '!=',
                            'value' => '',
                        ],
                        [
                            'name' => 'menu_cart_checkout_button_show',
                            'operator' => '!=',
                            'value' => '',
                        ],
                    ],
                ],
            ]
        );

        $this->register_menuCart_style_buttons_controls();
        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_messages',
            [
                'label' => esc_html__( 'Messages', 'tenweb-builder' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->register_menuCart_style_messages_controls();

        $this->end_controls_section();
    }

	protected function render() {
		$this->render_menuCart($this);
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new Menu_Cart_10Web() );
