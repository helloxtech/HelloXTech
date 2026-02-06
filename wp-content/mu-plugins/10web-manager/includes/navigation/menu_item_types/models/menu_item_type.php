<?php

namespace Tenweb_Manager\Navigation;

class MenuItemType {
  public $title;
  public $type;
  public $object_type;

  public function __construct($title, $type, $object_type){
    $this->title = $title;
    $this->type = $type;
    $this->object_type = $object_type;
  }
}