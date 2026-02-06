<?php

namespace Tenweb_Manager\Navigation;
class TermMenuItem extends MenuItem {

  public $object_id; // term id
  public $object_type; // taxonomy

  public function __construct($id, $title, $url, $order, $parent_id, $object_id, $object_type){
    parent::__construct($id, $title, $url, 'taxonomy', $order, $parent_id);
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
