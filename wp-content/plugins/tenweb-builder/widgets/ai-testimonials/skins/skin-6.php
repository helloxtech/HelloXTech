<?php
namespace Tenweb_Builder\Widgets\AI_Testimonials\Skins;

if (!defined('ABSPATH')) {
    exit;
}

class Skin_6 extends Skin_Base {
    public function get_id() {
        return 'skin_6';
    }

    public function get_title() {
        return esc_html__('Skin 6', 'tenweb-builder');
    }

    protected function _register_controls_actions() {
        parent::_register_controls_actions();
        add_action('elementor/element/twbb_ai_testimonials/skin_6_section_navigation/after_section_end', [$this, 'all_update_controls']);
    }

    public function all_update_controls() {
        $this->update_view_type_controls();
        $this->update_content_controls();
        $this->update_gen_style_sections();
        $this->update_slider_options();
    }

    public function update_view_type_controls() {
        $this->update_responsive_control('columns', [
            'default' => '1'
        ]);

        $this->update_responsive_control('content_alignment', [
            'default' => 'left',
            'mobile_default' => 'left',
        ]);

        $this->update_responsive_control('author_text_alignment', [
            'mobile_default' => 'left'
        ]);
    }

    public function update_content_controls() {
        $this->update_responsive_control('author_image_position', [
            'default' => 'left',
            'mobile_default' => 'top',
        ]);
    }

    public function update_gen_style_sections() {
        $this->update_control('background_color', [
            'default' => ''
        ]);

        $this->update_responsive_control('column_gap', [
            'default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('row_gap', [
            'default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('divider_spacing', [
            'default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('stars_size', [
            'default' => [
                'unit' => 'px',
                'size' => 21,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('author_info_space_below', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => '16',
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('image_space_below', [
            'default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('image_border_radius', [
            'default' => [
                'unit' => '%',
                'top' => '50',
                'right' => '50',
                'bottom' => '50',
                'left' => '50',
                'isLinked' => true
            ]
        ]);

        $this->update_responsive_control('card_padding', [
            'default' => [
                'unit' => 'px',
                'top' => '0',
                'right' => '0',
                'bottom' => '0',
                'left' => '0',
                'isLinked' => true
            ]
        ]);
    }

    public function update_slider_options() {
        $this->update_control('slider_view', [
            'default' => 'yes'
        ]);
        $this->update_responsive_control('slides_per_view', [
            'default' => '2'
        ]);
        
        $this->update_responsive_control('slides_to_scroll', [
            'default' => '1'
        ]);
        
        $this->update_responsive_control('navigation_position', [
            'default' => 'outside'
        ]);
        
        $this->update_responsive_control('pagination_alignment', [
            'default' => 'left',
            'mobile_default' => 'left',
        ]);
        
        $this->update_responsive_control('navigation_gap', [
            'default' => [
                'unit' => 'px',
                'size' => '20',
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 0,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('show_arrows', [
            'mobile_default' => 'yes'
        ]);
        
        $this->update_responsive_control('arrows_size', [
            'default' => [
                'unit' => 'px',
                'size' => 40,
                'sizes' => []
            ]
        ]);
        
        $this->update_responsive_control('arrows_border_width', [
            'default' => [
                'unit' => 'px',
                'top' => '1',
                'right' => '1',
                'bottom' => '1',
                'left' => '1',
                'isLinked' => true
            ]
        ]);
        
        $this->update_responsive_control('arrows_border_radius', [
            'default' => [
                'unit' => '%',
                'top' => '50',
                'right' => '50',
                'bottom' => '50',
                'left' => '50',
                'isLinked' => true
            ]
        ]);
        
        $this->update_responsive_control('carousel_full_width', [
            'default' => ''
        ]);
        
        $this->update_control('loop', [
            'default' => 'yes'
        ]);
    }
}

