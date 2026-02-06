<?php
use Tenweb_Builder\widgets\traits\menuCart\menuCart_Trait;

if ( ! defined( 'ABSPATH' ) ) exit;
$trait_instance = new class {
    use menuCart_Trait;

    public function render_fragment() {
        $this->mini_cart_template();
    }
};

$trait_instance->render_fragment();
