<?php
namespace Tenweb_Builder\Widgets\AI_Testimonials\Skins;

if (!defined('ABSPATH')) {
    exit;
}

class Skin_8 extends Skin_Base {
    public function get_id() {
        return 'skin_8';
    }

    public function get_title() {
        return esc_html__('Skin 8', 'tenweb-builder');
    }

    protected function _register_controls_actions() {
        parent::_register_controls_actions();
        add_action('elementor/element/twbb_ai_testimonials/skin_8_section_navigation/after_section_end', [$this, 'all_update_controls']);
    }

    public function all_update_controls() {
        $this->update_style_defaults();
        $this->update_slider_controls();
    }

    public function update_style_defaults() {
        // Update or add columns
        $this->update_responsive_control('columns', [
            'default' => '1'
        ]);
        
        // Update or add background_color
        $this->update_control('background_color', [
            'default' => ''
        ]);
        
        // Update or add column_gap
        $this->update_responsive_control('column_gap', [
            'default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ]
        ]);
        
        // Update or add row_gap
        $this->update_responsive_control('row_gap', [
            'default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ]
        ]);
        
        // Update or add stars_size
        $this->update_responsive_control('stars_size', [
            'default' => [
                'unit' => 'px',
                'size' => 21,
                'sizes' => []
            ]
        ]);
        
        // Update or add author_info_space_below
        $this->update_responsive_control('author_info_space_below', [
            'default' => [
                'unit' => 'px',
                'size' => 0,
                'sizes' => []
            ]
        ]);
        
        // Update or add image_space_below
        $this->update_responsive_control('image_space_below', [
            'default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ]
        ]);
        
        // Update or add image_border_radius
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
        
        // Update or add divider_spacing
        $this->update_responsive_control('divider_spacing', [
            'default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ]
        ]);
        
        // Update or add content_alignment
        $this->update_responsive_control('content_alignment', [
            'mobile_default' => 'left'
        ]);
        
        // Update or add show_author_image
        $this->update_control('show_author_image', [
            'default' => ''
        ]);
        
        // Update or add author_image_position
        $this->update_responsive_control('author_image_position', [
            'default' => 'top'
        ]);
        
        // Update or add column_gap
        $this->update_responsive_control('column_gap', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ]
        ]);
        
        // Update or add row_gap
        $this->update_responsive_control('row_gap', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ]
        ]);
        
        // Update or add card_padding
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
        
        // Update or add stars_size
        $this->update_responsive_control('stars_size', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 21,
                'sizes' => []
            ]
        ]);
        
        // Update or add stars_spacing_bottom
        $this->update_responsive_control('stars_spacing_bottom', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 24,
                'sizes' => []
            ]
        ]);
        
        // Update or add quote_spacing_bottom
        $this->update_responsive_control('quote_spacing_bottom', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 24,
                'sizes' => []
            ]
        ]);
        
        // Update or add image_space_below
        $this->update_responsive_control('image_space_below', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ]
        ]);
        
        // Update or add divider_spacing
        $this->update_responsive_control('divider_spacing', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ]
        ]);
        
        // Update or add company_logo_spacing_bottom
        $this->update_responsive_control('company_logo_spacing_bottom', [
            'default' => [
                'unit' => 'px',
                'size' => 0,
                'sizes' => []
            ]
        ]);
        
        // Update or add graphic_element_width
        $this->update_responsive_control('graphic_element_width', [
            'default' => [
                'unit' => 'px',
                'size' => 616,
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 300,
                'sizes' => []
            ]
        ]);
        
        // Update or add graphic_element_height
        $this->update_responsive_control('graphic_element_height', [
            'default' => [
                'unit' => 'px',
                'size' => 640,
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 340,
                'sizes' => []
            ]
        ]);
        
        // Update or add graphic_element_gap
        $this->update_responsive_control('graphic_element_gap', [
            'default' => [
                'unit' => 'px',
                'size' => 80,
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 48,
                'sizes' => []
            ]
        ]);
        
        // Update or add show_graphic_element
        $this->update_responsive_control('show_graphic_element', [
            'default' => 'yes',
            'mobile_default' => 'yes'
        ]);
        
        // Update or add graphic_element_position
        $this->update_responsive_control('graphic_element_position', [
            'mobile_default' => 'bottom'
        ]);
        
        // Update or add company_logo_position
        $this->update_responsive_control('company_logo_position', [
            'mobile_default' => 'right'
        ]);
        
        // Update or add company_logo_width
        $this->update_responsive_control('company_logo_width', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 140,
                'sizes' => []
            ]
        ]);
        
        // Update or add company_logo_height
        $this->update_responsive_control('company_logo_height', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 56,
                'sizes' => []
            ]
        ]);
        
        // Update or add company_logo_spacing_bottom
        $this->update_responsive_control('company_logo_spacing_bottom', [
            'mobile_default' => [
                'unit' => 'px',
                'size' => 0,
                'sizes' => []
            ]
        ]);
        
        // Update or add graphic_element_width
        $this->update_responsive_control('graphic_element_width', [
            'mobile_default' => [
                'unit' => '%',
                'size' => 100,
                'sizes' => []
            ]
        ]);
        
        // Update or add author_text_alignment
        $this->update_responsive_control('author_text_alignment', [
            'mobile_default' => 'left'
        ]);
    }
    public function update_slider_controls() {
        $this->update_control('slider_view', [
            'default' => 'yes'
        ]);

        $this->update_responsive_control('slides_per_view', [
            'default' => '1',
            'mobile_default' => '1'
        ]);

        $this->update_responsive_control('slides_to_scroll', [
            'default' => '1'
        ]);

        $this->update_responsive_control('carousel_full_width', [
            'default' => ''
        ]);

        $this->update_responsive_control('slider_full_width_layout', [
            'default' => 'cut-from-right'
        ]);

        $this->update_responsive_control('navigation_gap', [
            'default' => [
                'unit' => 'px',
                'size' => 20,
                'sizes' => []
            ],
            'mobile_default' => [
                'unit' => 'px',
                'size' => 20,
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

        $this->update_responsive_control('show_arrows', [
            'mobile_default' => 'yes'
        ]);

        $this->update_responsive_control('pagination_alignment', [
            'mobile_default' => 'left'
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
            ],
            'tablet_default' => [
                'unit' => '%',
                'top' => 20,
                'right' => 20,
                'bottom' => 20,
                'left' => 20,
                'isLinked' => true
            ],
            'mobile_default' => [
                'unit' => '%',
                'top' => '50',
                'right' => '50',
                'bottom' => '50',
                'left' => '50',
                'isLinked' => true
            ]
        ]);

        $this->update_control('loop', [
            'default' => 'yes'
        ]);
    }
}
