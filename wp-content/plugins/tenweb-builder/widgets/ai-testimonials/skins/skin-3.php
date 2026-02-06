<?php
namespace Tenweb_Builder\Widgets\AI_Testimonials\Skins;

if (!defined('ABSPATH')) {
    exit;
}

class Skin_3 extends Skin_Base {
    public function get_id() {
        return 'skin_3';
    }

    public function get_title() {
        return esc_html__('Skin 3', 'tenweb-builder');
    }

    protected function _register_controls_actions() {
        parent::_register_controls_actions();
        add_action('elementor/element/twbb_ai_testimonials/skin_3_section_navigation/after_section_end', [$this, 'all_update_controls']);
    }

    public function all_update_controls() {
        $this->update_view_type_controls();
        $this->update_content_controls();
        $this->update_gen_style_sections();
        $this->update_other_style_sections();
    }

    public function update_view_type_controls() {
        // No specific controls for view type in this skin
    }

    public function update_content_controls() {
        $this->update_responsive_control('author_image_position', [
            'default' => 'top'
        ]);

        $this->update_responsive_control('company_logo_position', [
            'default' => 'bottom'
        ]);

        $this->update_responsive_control('card_padding', [
            'default' => [
                'unit' => 'px',
                'top' => '0',
                'right' => '0',
                'bottom' => '0',
                'left' => '0',
                'isLinked' => true
            ],
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
                'size' => 32,
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
                'size' => 32,
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

        $this->update_responsive_control('divider_spacing', [
            'default' => [
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
    }

    public function update_other_style_sections() {
        $this->update_responsive_control('stars_spacing_bottom', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 24,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('quote_spacing_bottom', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 24,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('author_info_space_below', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 16,
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

        $this->update_responsive_control('company_logo_width', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 140,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('company_logo_height', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 56,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('company_logo_spacing_bottom', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 0,
                'sizes' => []
            ]
        ]);

        $this->update_responsive_control('author_text_alignment', [
            'mobile_default' => 'left'
        ]);

        $this->update_responsive_control('content_alignment', [
            'mobile_default' => 'left'
        ]);
    }
}
