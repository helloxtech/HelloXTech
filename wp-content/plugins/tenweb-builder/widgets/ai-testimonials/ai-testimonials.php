<?php
namespace Tenweb_Builder;

include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-base.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-1.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-2.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-3.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-4.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-5.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-6.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-7.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-8.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-9.php');
include_once(TWBB_DIR . '/widgets/ai-testimonials/skins/skin-10.php');

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) exit;

class AI_Testimonials extends Widget_Base {

    protected $_has_template_content = false;

    public function get_name() {
        return Builder::$prefix . '_ai_testimonials';
    }

    public function get_title() {
        return __('Testimonials', 'tenweb-builder');
    }

    public function get_icon() {
        return 'twbb-testimonial-carousel twbb-widget-icon';
    }

    public function get_categories() {
        return ['tenweb-widgets'];
    }

	public function get_script_depends() {
		return [ 'imagesloaded', 'swiper' ];
	}

	public function get_style_depends() {
		if( \Tenweb_Builder\Modules\Utils::is_swiper_latest() ) {
			return ['swiper'];
		} else {
			return ['e-swiper'];
		}
	}

    protected function register_skins() {
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_1( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_2( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_3( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_4( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_5( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_6( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_7( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_8( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_9( $this ));
        $this->add_skin(new \Tenweb_Builder\Widgets\AI_Testimonials\Skins\Skin_10( $this ));
    }

    protected function register_controls() {
        // First section - View Type
        $this->start_controls_section(
            'section_view_type',
            [
                'label' => __('Testimonial View Type', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->end_controls_section();
        // Content Section
        $this->start_controls_section(
            'testimonials_section',
            [
                'label' => __('Testimonials', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();
        $lorem_ipsum = '"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat."';

        // Basic Content Controls
        $repeater->add_control(
            'quote_text',
            [
                'label' => __('Content', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __($lorem_ipsum, 'tenweb-builder'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'author_name',
            [
                'label' => __('Author Name', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('John Doe', 'tenweb-builder'),
            ]
        );

        $repeater->add_control(
            'author_position',
            [
                'label' => __('Author Position', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('CEO', 'tenweb-builder'),
            ]
        );

        $repeater->add_control(
            'company_name',
            [
                'label' => __('Company Name', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Company Name', 'tenweb-builder'),
            ]
        );

        $repeater->add_control(
            'author_image',
            [
                'label' => __('Author Image', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'company_logo',
            [
                'label' => __('Company Logo', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_types' => ['image', 'svg'],
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        // Add the new Graphic Element control
        // Replace the existing graphic_element control with these new controls
        $repeater->add_control(
            'graphic_element_type',
            [
                'label' => __('Graphic Element Type', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'image' => __('Image', 'tenweb-builder'),
                    'video' => __('Video', 'tenweb-builder'),
                ],
            ]
        );

        $repeater->add_control(
            'graphic_element_image',
            [
                'label' => __('Choose Image', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'graphic_element_type' => 'image',
                ],
            ]
        );

        $repeater->add_control(
            'graphic_element_video',
            [
                'label' => __('Choose Video', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'video',
                'default' => [
                    'url' => '',
                ],
                'condition' => [
                    'graphic_element_type' => 'video',
                ],
            ]
        );

        $repeater->add_control(
            'number_of_stars',
            [
                'label' => __('Rating', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 5,
                'step' => 1,
                'default' => 5,
            ]
        );

        $this->add_control(
            'testimonial_items',
            [
                'label' => __('Testimonials', 'tenweb-builder'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'quote_text' => __($lorem_ipsum, 'tenweb-builder'),
                        'author_name' => __('John Doe', 'tenweb-builder'),
                        'author_position' => __('CEO', 'tenweb-builder'),
                        'company_name' => __('Company Name', 'tenweb-builder'),
                        'number_of_stars' => 5,
                    ],
                    [
                        'quote_text' => __($lorem_ipsum, 'tenweb-builder'),
                        'author_name' => __('John Doe', 'tenweb-builder'),
                        'author_position' => __('CEO', 'tenweb-builder'),
                        'company_name' => __('Company Name', 'tenweb-builder'),
                        'number_of_stars' => 5,
                    ],
                    [
                        'quote_text' => __($lorem_ipsum, 'tenweb-builder'),
                        'author_name' => __('John Doe', 'tenweb-builder'),
                        'author_position' => __('CEO', 'tenweb-builder'),
                        'company_name' => __('Company Name', 'tenweb-builder'),
                        'number_of_stars' => 5,
                    ],
                    [
                        'quote_text' => __($lorem_ipsum, 'tenweb-builder'),
                        'author_name' => __('John Doe', 'tenweb-builder'),
                        'author_position' => __('CEO', 'tenweb-builder'),
                        'company_name' => __('Company Name', 'tenweb-builder'),
                        'number_of_stars' => 5,
                    ],
                    [
                        'quote_text' => __($lorem_ipsum, 'tenweb-builder'),
                        'author_name' => __('John Doe', 'tenweb-builder'),
                        'author_position' => __('CEO', 'tenweb-builder'),
                        'company_name' => __('Company Name', 'tenweb-builder'),
                        'number_of_stars' => 5,
                    ],
                    [
                        'quote_text' => __($lorem_ipsum, 'tenweb-builder'),
                        'author_name' => __('John Doe', 'tenweb-builder'),
                        'author_position' => __('CEO', 'tenweb-builder'),
                        'company_name' => __('Company Name', 'tenweb-builder'),
                        'number_of_stars' => 5,
                    ],
                ],
                'title_field' => '{{{ author_name }}}', //phpcs:ignore WordPressVIPMinimum.Security.Mustache.OutputNotation
            ]
        );

        $this->end_controls_section();
        $this->start_controls_section(
            'section_general_style',
            [
                'label' => __('General', 'tenweb-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->end_controls_section();
    }

}

\Elementor\Plugin::instance()->widgets_manager->register( new AI_Testimonials() );
