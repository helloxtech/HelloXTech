<?php
namespace Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins;

if (!defined('ABSPATH')) {
	exit;
}

class Skin_5 extends Skin_Base {
	public function get_id() {
		return 'skin_5';
	}

	public function get_title() {
		return esc_html__( 'Skin 5', 'tenweb-builder');
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
		$this->update_control('feature_list_style_choice', ['default' => 'style_5']);
		$this->update_responsive_control('justify_content', ['default' => 'space-between']);
		$this->update_control('buttons_position', ['default' => 'under_heading']);
	}

	public function update_controls_content_before() {
		$this->update_control('show_title', ['default' => '']);
		$this->update_control('show_description', ['default' => 'yes']);
	}

	public function update_controls_buttons_after() {
		$this->update_control('show_button_1', ['default' => 'yes']);
	}

	public function update_controls_content() {
		$this->update_responsive_control('content_media_gap', ['default' => ['unit' => 'px', 'size' => 104]]);
		$this->update_responsive_control('content_width', ['default' => ['unit' => '%', 'size' => 50]]);
		$this->update_control('title_field_typography_typography', ['fields_options' => [
			'typography' => ['default' => 'yes'],
			'font_size' => [
				'default' => [
					'unit' => 'px',
					'size' => 48,
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 30,
				],
			],
		]], ['recursive' => true]);
		$this->update_control('title_field_color', ['global' => ['default' => 'globals/colors?id=primary']]);
		$this->update_responsive_control('title_space_below', ['default' => ['size' => 16], 'mobile_default' => ['size' => 32]]);
		$this->update_control('description_field_typography_typography', ['fields_options' => [
			'typography' => ['default' => 'yes'],
			'font_size' => [
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 16,
				],
			],
			'font_weight' => [
				'default' => '400',
			],
			'line_height' => [
				'default' => [
					'unit' => '%',
					'size' => 150,
				],
			],
		],], ['recursive' => true]);
		$this->update_control('description_space_below', ['default' => ['size' => 48], 'mobile_default' => ['size' => 32]]);
		$this->update_control('media_width', ['default' => ['size' => 50, 'unit' => 'vw']]);
		$this->update_control('media_height', ['default' => ['size' => 100, 'unit' => 'vh'], 'mobile_default' => ['size' => 343, 'unit' => 'px']]);
		$this->update_control('media_alignment', ['default' => 'flex-start', 'mobile_default' => 'center']);
		$this->update_control('buttons_group_space_below', ['default' => ['size' => 0], 'mobile_default' => ['size' => 32]]);
	}
}
