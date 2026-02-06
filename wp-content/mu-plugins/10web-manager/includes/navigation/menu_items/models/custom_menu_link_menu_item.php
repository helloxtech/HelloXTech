<?php

namespace Tenweb_Manager\Navigation;

class CustomLinkMenuItem extends MenuItem {
  public $object_type = "custom";

  public function __construct($id, $title, $url, $order, $parent_id){
    parent::__construct($id, $title, $url, 'custom', $order, $parent_id);
  }

  public function to_wp_format(){

    $wp_format = parent::to_wp_format();
    $wp_format['menu-item-object-id'] = sanitize_text_field($this->id);
    $wp_format['menu-item-object'] = "custom";
    $wp_format['menu-item-url'] = sanitize_url($this->url);

    return $wp_format;
  }

}