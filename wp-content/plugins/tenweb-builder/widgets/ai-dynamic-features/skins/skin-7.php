<?php
namespace Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins;

if (!defined('ABSPATH')) {
	exit;
}

class Skin_7 extends Skin_Base {
	public function get_id() {
		return 'skin_7';
	}

	public function get_title() {
		return esc_html__( 'Skin 7', 'tenweb-builder');
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();
		//section_buttons is the last added section, so we need to make any modifications after it to make sure all controls are registered.
		add_action('elementor/element/twbb_dynamic_features/section_buttons/after_section_end', [$this, 'all_update_controls']);
	}

	public function all_update_controls() {
		$this->update_controls_layout();
		$this->update_controls_content_before();
		$this->update_controls_buttons_after();
		$this->update_controls_content();
	}

	public function update_controls_layout() {
		$this->update_control('feature_list_style_choice', ['default' => 'style_7']);
		$this->update_control('media_position', ['default' => 'image-right']);
		$this->update_responsive_control('justify_content', ['default' => 'space-between']);
	}

	public function update_controls_content_before() {
		$this->update_control('show_title', ['default' => 'yes']);
		$this->update_control('show_description', ['default' => '']);
	}

	public function update_controls_buttons_after() {
		$this->update_control('show_button_1', ['default' => '']);
	}

	public function update_controls_content() {
		$this->update_responsive_control('content_media_gap', ['default' => ['unit' => 'px', 'size' => 72]]);
		$this->update_control('box_background_color_color', ['global' => ['default' => 'globals/colors?id=twbb_bg_3']], ['recursive' => true]);
		$this->update_responsive_control('box_padding', ['default' => ['top' => '64', 'right' => '64', 'bottom' => '64', 'left' => '64', 'unit' => 'px'], 'mobile_default' => ['top' => '32', 'right' => '24', 'bottom' => '0', 'left' => '24', 'unit' => 'px', 'isLinked' => false]]);
		$this->update_responsive_control('box_border_radius', ['default' => ['top' => '32', 'right' => '32', 'bottom' => '32', 'left' => '32', 'unit' => 'px']]);
		$this->update_responsive_control('box_border_radius_hover', ['default' => ['top' => '32', 'right' => '32', 'bottom' => '32', 'left' => '32', 'unit' => 'px']]);
		$this->update_responsive_control('content_width', ['default' => ['unit' => '%', 'size' => 50]]);
		$this->update_responsive_control('title_space_below', ['default' => ['size' => 48], 'mobile_default' => ['size' => 12]]);
		$this->update_control('media_width', ['default' => ['size' => 518, 'unit' => 'px']]);
		$this->update_control('media_height', ['default' => ['size' => 100, 'unit' => '%'], 'mobile_default' => ['size' => 196, 'unit' => 'px']]);
		$this->update_responsive_control('media_border_radius', ['default' => ['top' => '16', 'right' => '0', 'bottom' => '0', 'left' => '16', 'unit' => 'px'], 'mobile_default' => ['top' => '16', 'right' => '16', 'bottom' => '0', 'left' => '0', 'unit' => 'px']]);
		$this->update_control('buttons_group_space_below', ['default' => ['size' => 0], 'mobile_default' => ['size' => 32]]);
	}
}
