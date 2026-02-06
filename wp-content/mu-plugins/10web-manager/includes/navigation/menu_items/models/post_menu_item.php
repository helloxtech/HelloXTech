<?php

namespace Tenweb_Manager\Navigation;

class PostMenuItem extends MenuItem {
  public $object_id; // post id
  public $object_type; // post type

  public function __construct($id, $title, $url, $order, $parent_id, $object_id, $object_type){
    parent::__construct($id, $title, $url, 'post_type', $order, $parent_id);
    $this->object_id = $object_id;
    $this->object_type = $object_type;
  }

  public function to_wp_format(){
    $wp_format = parent::to_wp_format();
    $wp_format['menu-item-object-id'] = (int)$this->object_id;
    $wp_format['menu-item-object'] = sanitize_text_field($this->object_type);
    return $wp_format;
  }
}