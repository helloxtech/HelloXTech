<?php

namespace Tenweb_Manager\Navigation;
class Navigation {

  protected static $instance = null;

  private function __construct(){
    include_once TENWEB_INCLUDES_DIR . '/navigation/rest_api.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu/models/menu.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu/menu_controller.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu_items/models/menu_item.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu_items/models/post_menu_item.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu_items/models/term_menu_item.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu_items/models/custom_menu_link_menu_item.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu_items/menu_item_controller.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu_item_types/models/menu_item_type.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu_item_types/models/menu_item_type_item.php';
    include_once TENWEB_INCLUDES_DIR . '/navigation/menu_item_types/menu_item_types_controller.php';
    RestApi::get_instance();
  }

  public static function get_instance(){
    if(self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }
}