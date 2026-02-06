<?php
namespace Tenweb_Builder\Widgets\AI_Testimonials\Skins;

if (!defined('ABSPATH')) {
    exit;
}

class Skin_4 extends Skin_Base {
    public function get_id() {
        return 'skin_4';
    }

    public function get_title() {
        return esc_html__('Skin 4', 'tenweb-builder');
    }

    protected function _register_controls_actions() {
        parent::_register_controls_actions();
        add_action('elementor/element/twbb_ai_testimonials/skin_4_section_navigation/after_section_end', [$this, 'all_update_controls']);
    }

    public function all_update_controls() {
        $this->update_view_type_controls();
        $this->update_content_controls();
        $this->update_gen_style_sections();
        $this->update_other_style_sections();
        $this->update_slider_options();
    }

    public function update_view_type_controls() {
        $this->update_responsive_control('columns', [
            'default' => '2'
        ]);
        
        $this->update_responsive_control('content_alignment', [
            'default' => 'center'
        ]);

        $this->update_control('show_stars', [
            'default' => ''
        ]);
    }

    public function update_content_controls() {
        $this->update_responsive_control('author_image_position', [
            'default' => 'top'
        ]);

        $this->update_responsive_control('author_text_alignment', [
            'default' => 'center'
        ]);

        $this->update_control('company_logo_location', [
            'default' => 'above_quote'
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

        $this->update_control('divider_color', [
            'default' => '#FFFFFF'
        ]);

        $this->update_responsive_control('company_logo_spacing_bottom', [
            'default' => [
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
                'size' => 48,
                'sizes' => []
            ]
        ]);

        $this->update_control('quote_color', [
            'default' => '#000000ff'
        ]);

        $this->update_control('author_name_color', [
            'default' => '#000000ff'
        ]);

        $this->update_control('position_company_color', [
            'default' => '#000000ff'
        ]);
    }

    public function update_other_style_sections() {
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

        $this->update_responsive_control('quote_icon_spacing', [
            'default' => [
                'unit' => 'px',
                'size' => 0,
                'sizes' => []
            ]
        ]);

        // Mobile-specific spacing controls
        $mobile_spacing_controls = [
            'quote_spacing_bottom' => 24,
            'author_info_space_below' => 24,
            'image_size' => 56,
            'image_space_below' => 16,
            'company_logo_width' => 140,
            'company_logo_height' => 56,
            'company_logo_spacing_bottom' => 24
        ];

        foreach ($mobile_spacing_controls as $control => $size) {
            $this->update_responsive_control($control, [
                'mobile_default' => [
                    'unit' => 'px',
                    'size' => $size,
                    'sizes' => []
                ]
            ]);
        }

        $this->update_responsive_control('image_size', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 56,
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
        
        $this->update_responsive_control('slider_full_width_layout', [
            'default' => 'cut-from-right'
        ]);
        
        $this->update_responsive_control('navigation_position', [
            'default' => 'inside'
        ]);
        
        $this->update_responsive_control('pagination_alignment', [
            'default' => 'center'
        ]);
        
        $this->update_responsive_control('navigation_gap', [
            'default' => [
                'unit' => 'px',
                'size' => '',
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 0,
                'sizes' => []
            ]
        ]);
        
        $this->update_responsive_control('arrows_size', [
            'default' => [
                'unit' => 'px',
                'size' => 50,
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
                'unit' => 'px',
                'top' => '25',
                'right' => '25',
                'bottom' => '25',
                'left' => '25',
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
