<?php
namespace Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins;

if (!defined('ABSPATH')) {
	exit;
}

class Skin_2 extends Skin_Base {
	public function get_id() {
		return 'skin_2';
	}

	public function get_title() {
		return esc_html__( 'Skin 2', 'tenweb-builder');
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();
		add_action('elementor/element/twbb_dynamic_features/section_buttons/after_section_end', [$this, 'all_update_controls']);
	}

	public function all_update_controls() {
		$this->update_controls_layout();
		$this->update_controls_content_before();
		$this->update_controls_buttons_after();
		$this->update_controls_content();
	}

	public function update_controls_layout() {
		$this->update_control('feature_list_style_choice', ['default' => 'style_2']);
		$this->update_control('media_position', ['default' => 'image-left']);
		$this->update_responsive_control('justify_content', ['default' => 'space-between']);
		$this->update_control('buttons_position', ['default' => 'under_features']);
	}

	public function update_controls_content_before() {
		$this->update_control('show_title', ['default' => 'yes']);
		$this->update_control('show_description', ['default' => '']);
	}

	public function update_controls_buttons_after() {
		$this->update_control('show_button_1', ['default' => 'yes']);
	}

	public function update_controls_content() {
		$this->update_control('image_animation', ['default' => 'vertical_slider']);
		$this->update_control('image_animation_duration', ['default' => ['size' => 500]]);
		$this->update_responsive_control('content_media_gap', ['default' => ['unit' => 'px', 'size' => 156]]);
		$this->update_responsive_control('box_padding', ['default' => ['top' => '50', 'right' => '50', 'bottom' => '50', 'left' => '50', 'unit' => 'px']]);
		$this->update_responsive_control('box_border_radius', ['default' => ['top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px']]);
		$this->update_responsive_control('content_width', ['default' => ['unit' => '%', 'size' => 42]]);
		// Don't even ask me what the hell is going on here, just change the 'default to '' to get the global value working.
		$this->update_control('title_field_typography_typography', ['global' => ['default' => 'globals/typography?id=primary'], 'default' => ''], ['recursive' => true]);
		$this->update_control('title_field_color', ['global' => ['default' => 'globals/colors?id=primary']]);
		$this->update_responsive_control('title_space_below', ['default' => ['size' => 32], 'mobile_default' => ['size' => 32]]);
		$this->update_control('description_field_typography_typography', ['global' => ['default' => 'globals/typography?id=twbb_p1'], 'default' => ''], ['recursive' => true]);
		$this->update_control('description_space_below', ['default' => ['size' => 20], 'mobile_default' => ['size' => 20]]);
		$this->update_control('media_width', ['default' => ['size' => 50, 'unit' => '%']]);
		$this->update_control('media_height', ['default' => ['size' => 624, 'unit' => 'px'], 'mobile_default' => ['size' => 300, 'unit' => 'px']]);
		$this->update_control('media_alignment', ['default' => 'flex-start', 'mobile_default' => 'center']);
		$this->update_responsive_control('media_border_radius', ['default' => ['top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px']]);
		$this->update_control('buttons_group_space_below', ['default' => ['size' => 0], 'mobile_default' => ['size' => 32]]);
	}
}
