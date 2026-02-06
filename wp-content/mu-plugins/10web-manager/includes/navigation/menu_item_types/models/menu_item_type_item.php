<?php

namespace Tenweb_Manager\Navigation;

class MenuItemTypeItem {
  public $title;
  public $type;
  public $object_type;
  public $object_id;
  public $special_status;

  public function __construct($object_id, $title, $type, $object_type, $special_status = null){
    $this->object_id = $object_id;
    $this->title = $title;
    $this->type = $type;
    $this->object_type = $object_type;
    $this->special_status = $special_status;
  }
}