<?php

namespace Tenweb_Manager\Navigation;

class Menu {
  public $id;
  public $name;
  public $slug;
  public $items;

  public function __construct($id = null, $name = null, $slug = null, $items = null){
    $this->id = $id;
    $this->name = $name;
    $this->slug = $slug;
    $this->items = $items;
  }

}