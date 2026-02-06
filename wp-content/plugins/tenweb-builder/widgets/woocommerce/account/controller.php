<?php
namespace Tenweb_Builder\Widgets\Woocommerce\Widgets;
include_once (TWBB_DIR . '/widgets/traits/account_trait.php');

use Elementor\Controls_Manager;
use Tenweb_Builder\Widgets\Traits\Account_Trait;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class  Account extends Base_Widget {
    use Account_Trait;

	public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);

		$this->register_account_styles();
	}


	public function get_name() {
        return 'twbb_woocommerce-account';
    }

    public function get_title() {
        return esc_html__( 'Account', 'tenweb-builder' );
    }

    public function get_icon() {
        return 'twbb-account twbb-widget-icon';
    }

    public function get_keywords() {
        return [ 'woocommerce', 'account' ];
    }

	public function get_style_depends() {
		return ['twbb-woocommerce-account-style'];
	}

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Content', 'tenweb-builder' ),
            ]
        );

	    $this->register_account_content_controls();

        $this->end_controls_section();

	    $this->start_controls_section(
		    'content_style',
		    [
			    'label' => esc_html__( 'Content', 'tenweb-builder' ),
			    'tab' => Controls_Manager::TAB_STYLE,
		    ]
	    );

	    $this->register_account_style_controls();

	    $this->end_controls_section();
    }

    public function render() {
		$this->render_account( $this, 'account_widget_');
    }

    public function get_group_name() {
        return 'woocommerce';
    }
}

\Elementor\Plugin::instance()->widgets_manager->register( new Account() );
