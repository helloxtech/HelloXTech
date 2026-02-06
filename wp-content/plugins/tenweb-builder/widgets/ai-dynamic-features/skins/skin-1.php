<?php
namespace Tenweb_Builder\Widgets\AI_Dynamic_Features\Skins;

if (!defined('ABSPATH')) {
	exit;
}

class Skin_1 extends Skin_Base {
	public function get_id() {
		return 'skin_1';
	}

	public function get_title() {
		return esc_html__( 'Skin 1', 'tenweb-builder');
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();
	}
}
