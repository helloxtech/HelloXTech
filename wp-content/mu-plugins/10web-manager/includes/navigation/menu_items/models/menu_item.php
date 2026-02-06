<?php

namespace Tenweb_Manager\Navigation;

class MenuItem {
  public $id;
  public $type;
  public $order;
  public $parent_id;
  public $title;
  public $url;
  public $children;

  public function __construct($id, $title, $url, $type, $order, $parent_id = 0, $children = []){
    $this->id = $id;
    $this->title = $title;
    $this->url = $url;
    $this->type = $type;
    $this->order = $order;
    $this->parent_id = $parent_id;
    $this->children = $children;
  }

  public function to_wp_format(){
    return [
      'menu-item-db-id' => (int)$this->id,
      'menu-item-type' => sanitize_text_field($this->type),
      'menu-item-position' => (int)$this->order,
      'menu-item-parent-id' => (int)$this->parent_id,
      'menu-item-title' => sanitize_text_field($this->title),
      'menu-item-status' => 'publish'
    ];
  }
}