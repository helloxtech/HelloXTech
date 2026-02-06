<?php

namespace Tenweb_Manager\Navigation;
class MenuItemController {

  public static function add_item($params){
    $menu_id = $params["menu_id"];
    $item = self::get_menu_item_from_params($params);
    $item_id = wp_update_nav_menu_item($menu_id, 0, $item->to_wp_format());

    if(is_wp_error($item_id)) {
      return $item_id;
    }

    $menu_item = wp_setup_nav_menu_item(get_post($item_id));
    if(isset($menu_item->_invalid) && $menu_item->_invalid) {
      self::delete_item($item_id);
      return new \WP_Error("invalid_menu_item");
    }

    return $item_id;
  }

  public static function update_item($item_id, $params, $menu_id=0){
    /**
     * Before item update we should merge params with DB data. e.g. If we update only title, another params (e.g. url)
     * will be updated with empty values.
     * */
    $menu_item = self::get_merged_menu_item($item_id, $params);

    if(is_wp_error($menu_item)) {
      return $menu_item;
    }

    return wp_update_nav_menu_item($menu_id, $item_id, $menu_item->to_wp_format());
  }

  public static function delete_item($item_id){
    $response = wp_delete_post($item_id);
    if(!$response) {
      return new \WP_Error("failed_to_delete_menu_item");
    }

    return true;
  }

  public static function get_menu_item_from_wp_data($wp_data){
    if($wp_data->type === "post_type") {
      return new PostMenuItem($wp_data->ID, $wp_data->title, $wp_data->url, $wp_data->menu_order, $wp_data->menu_item_parent, $wp_data->object_id, $wp_data->object);
    }

    if($wp_data->type === "taxonomy") {
      return new TermMenuItem($wp_data->ID, $wp_data->title, $wp_data->url, $wp_data->menu_order, $wp_data->menu_item_parent, $wp_data->object_id, $wp_data->object);
    }

    if($wp_data->type === "custom") {
      return new CustomLinkMenuItem($wp_data->ID, $wp_data->title, $wp_data->url, $wp_data->menu_order, $wp_data->menu_item_parent);
    }

    return null; // todo
  }

  public static function get_menu_item_from_params($params){
    $type = trim($params['type']);
    $order = $params['order'];
    $parent_id = $params['parent_id'];
    $title = (isset($params['title'])) ? trim($params['title']) : null;
    $url = (isset($params['url'])) ? trim($params['url']) : null;

    if($type === 'post_type') {
      $item = new PostMenuItem(0, $title, $url, $order, $parent_id, $params["object_id"], trim($params["object_type"]));
    } else if($type === 'taxonomy') {
      $item = new TermMenuItem(0, $title, $url, $order, $parent_id, $params["object_id"], trim($params["object_type"]));
    } else {
      $item = new CustomLinkMenuItem(0, $title, $url, $order, $parent_id);
    }

    return $item;
  }

  protected static function get_merged_menu_item($item_id, $params){
    /**
     * Function merges user input and menu item in DB. Takes menu data from DB and updates properties with user inputs
     * */

    $post = get_post($item_id);
    if($post === null) {
      return new \WP_Error("no_menu_item_with_id");
    }

    $wp_data = wp_setup_nav_menu_item($post);
    $menu_item = self::get_menu_item_from_wp_data($wp_data);

    foreach($menu_item as $prop_name => $val) {

      if($prop_name === 'id') {
        continue;
      }

      if(!isset($params[$prop_name])) {
        continue;
      }

      $new_value = $params[$prop_name];

      if(is_string($new_value)) {
        $new_value = trim($new_value);
      }

      $menu_item->$prop_name = $new_value;
    }

    return $menu_item;
  }

}