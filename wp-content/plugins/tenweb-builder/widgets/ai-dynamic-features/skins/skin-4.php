<?php
namespace Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins;

if (!defined('ABSPATH')) {
	exit;
}

class Skin_4 extends Skin_Base {
	public function get_id() {
		return 'skin_4';
	}

	public function get_title() {
		return esc_html__( 'Skin 4', 'tenweb-builder');
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
		$this->update_control('feature_list_style_choice', ['default' => 'style_4']);
		$this->update_control('media_position', ['default' => 'image-left']);
		$this->update_responsive_control('justify_content', ['default' => 'space-between']);
		$this->update_control('buttons_position', ['default' => 'under_features']);
	}

	public function update_controls_content_before() {
		$this->update_control('show_title', ['default' => '']);
		$this->update_control('show_description', ['default' => '']);
	}

	public function update_controls_buttons_after() {
		$this->update_control('show_button_1', ['default' => 'yes']);
	}

	public function update_controls_content() {
		$this->update_responsive_control('content_width', ['default' => ['unit' => '%', 'size' => 42]]);
		// Don't even ask me what the hell is going on here, just change the 'default to '' to get the global value working.
		$this->update_control('title_field_typography_typography', ['global' => ['default' => 'globals/typography?id=primary'], 'default' => ''], ['recursive' => true]);
		$this->update_control('title_field_color', ['global' => ['default' => 'globals/colors?id=primary']]);
		$this->update_responsive_control('title_space_below', ['default' => ['size' => 0], 'mobile_default' => ['size' => 32]]);
		$this->update_control('media_width', ['default' => ['size' => 720, 'unit' => 'px']]);
		$this->update_control('media_height', ['default' => ['size' => 100, 'unit' => '%'], 'mobile_default' => ['size' => 228, 'unit' => 'px']]);
		$this->update_control('media_alignment', ['default' => 'flex-start', 'mobile_default' => 'center']);
		$this->update_control('buttons_group_space_below', ['default' => ['size' => 0], 'mobile_default' => ['size' => 32]]);
	}
}
