<?php
namespace Tenweb_Builder\Widgets\AI_Testimonials\Skins;

if (!defined('ABSPATH')) {
    exit;
}

class Skin_10 extends Skin_Base {
    public function get_id() {
        return 'skin_10';
    }

    public function get_title() {
        return esc_html__('Skin 10', 'tenweb-builder');
    }

    protected function _register_controls_actions() {
        parent::_register_controls_actions();
        add_action('elementor/element/twbb_ai_testimonials/skin_10_section_navigation/after_section_end', [$this, 'all_update_controls']);
    }

    public function all_update_controls() {
        $this->update_view_type_controls();
        $this->update_content_controls();
        $this->update_gen_style_sections();
        $this->update_slider_controls();
    }

    public function update_view_type_controls() {
        $this->update_responsive_control('content_alignment', [
            'default' => 'left',
            'mobile_default' => 'left' // Set default alignment
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
                'size' => 32,
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
                'size' => 32,
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('stars_size', [
            'default' => [
                'unit' => 'px',
                'size' => 21,
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 21,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('author_info_space_below', [
            'default' => [
                'unit' => 'px',
                'size' => 0,
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

        $this->update_responsive_control('divider_spacing', [
            'default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('stars_spacing_bottom', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 32,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('quote_spacing_bottom', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 32,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('author_info_space_below', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 24,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('image_space_below', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 16,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('divider_spacing', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('company_logo_spacing_bottom', [
            'default' => [
                'unit' => 'px',
                'size' => 0,
                'sizes' => []
            ]
        ]);

        $this->update_control('show_company_logo', [
            'default' => ''
        ]);

        $this->update_responsive_control('card_padding', [
            'mobile_default' => [
                'unit' => 'px',
                'top' => '24',
                'right' => '24',
                'bottom' => '24',
                'left' => '24',
                'isLinked' => true
            ]
        ]);

        $this->update_control('card_border_border', [
            'default' => 'solid'
        ]);

        $this->update_responsive_control('card_border_width', [
            'default' => [
                'unit' => 'px',
                'top' => '1',
                'right' => '1',
                'bottom' => '1',
                'left' => '1',
                'isLinked' => true
            ],
            'mobile_default' => [
                'unit' => 'px',
                'top' => '1',
                'right' => '1',
                'bottom' => '1',
                'left' => '1',
                'isLinked' => true
            ]
        ]);

        $this->update_responsive_control('image_size', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 56,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('author_text_alignment', [
            'mobile_default' => 'left'
        ]);

        $this->update_responsive_control('carousel_full_width', [
            'mobile_default' => 'yes'
        ]);
    }

    public function update_slider_controls() {
        $this->update_control('slider_view', [
            'default' => 'yes'
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

        $this->update_responsive_control('arrows_size', [
            'default' => [
                'unit' => 'px',
                'size' => 40,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('arrows_border_radius', [
            'default' => [
                'unit' => 'px',
                'top' => '20',
                'right' => '20',
                'bottom' => '20',
                'left' => '20',
                'isLinked' => true
            ]
        ]);

        $this->update_responsive_control('show_arrows', [
            'mobile_default' => 'yes'
        ]);

        $this->update_responsive_control('pagination_alignment', [
            'mobile_default' => 'left'
        ]);

        $this->update_control('loop', [
            'default' => ''
        ]);

        $this->update_responsive_control('carousel_full_width', [
            'mobile_default' => ''
        ]);

        $this->update_responsive_control('show_arrows', [
            'mobile_default' => 'yes'
        ]);

        $this->update_responsive_control('pagination_alignment', [
            'mobile_default' => 'left'
        ]);

        $this->update_control('loop', [
            'default' => ''
        ]);

        $this->update_responsive_control('arrows_size', [
            'default' => [
                'unit' => 'px',
                'size' => 40,
                'sizes' => []
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

        $this->update_responsive_control('arrows_border_radius_tablet', [
            'default' => [
                'unit' => '%',
                'top' => 20,
                'right' => 20,
                'bottom' => 20,
                'left' => 20,
                'isLinked' => true
            ]
        ]);

        $this->update_responsive_control('arrows_border_radius', [
            'mobile_default' => [
                'unit' => '%',
                'top' => '50',
                'right' => '50',
                'bottom' => '50',
                'left' => '50',
                'isLinked' => true
            ]
        ]);
    }
}
