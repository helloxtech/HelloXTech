<?php

namespace Tenweb_Manager\Navigation;

class MenuController {
  public static function get_all_menus(){
    $menus = [];
    foreach(wp_get_nav_menus() as $wp_menu) {
      $menus[] = self::get_menu($wp_menu->term_id);
    }

    return [
      "menus" => $menus,
      "header_footer_templates" => self::get_header_footer_templates()
    ];
  }

  public static function get_menus_list(){
    $menus = [];
    foreach(wp_get_nav_menus() as $wp_menu) {
      $menus[] = new Menu($wp_menu->term_id, $wp_menu->name, $wp_menu->slug, []);
    }

    return [
      "menus" => $menus,
      "header_footer_templates" => self::get_header_footer_templates()
    ];
  }

  public static function get_menu($id){
    return self::get_menu_from_wp_data(wp_get_nav_menu_object($id));
  }

  public static function add_menu($menu_name, $items){
    $id = wp_create_nav_menu($menu_name);

    if(is_wp_error($id)) {
      return $id;
    }

    if(empty($items)) {
      return self::get_menu($id);
    }

    $flatten_new_items = [];
    self::flatten_tree($items, $flatten_new_items, 0, true);

    $tmp_id_db_id_map = [];
    foreach($flatten_new_items as $item) {

      if(isset($tmp_id_db_id_map[$item['parent_id']])) {
        $item['parent_id'] = $tmp_id_db_id_map[$item['parent_id']];
      } else {
        $item['parent_id'] = 0;
      }

      $item['menu_id'] = $id;
      $db_id = MenuItemController::add_item($item);
      if(!is_wp_error($db_id)) {
        $tmp_id_db_id_map[$item['id']] = $db_id;
      }
    }

    return self::get_menu($id);
  }

  public static function delete_menu($id){
    $response = wp_delete_nav_menu($id);
    if($response === false) {
      return new \WP_Error("failed_to_delete_menu");
    }

    return $response;
  }

  public static function update_menu($id, $name, $items){
    $menu_updated = wp_update_nav_menu_object($id, [
      'menu-name' => trim($name)
    ]);


    if(is_wp_error($menu_updated)) {
      return $menu_updated;
    }

    if(empty($items)) {
      $items = [];
    }

    $saved_menu_items = self::get_menu_items_list($id);

    $flatten_new_items = [];
    self::flatten_tree($items, $flatten_new_items,0, true);

    foreach($saved_menu_items as $saved_item) {
      $delete_item = true;
      foreach($flatten_new_items as $new_item) {
        if(isset($new_item['id']) && $saved_item->id == $new_item['id']) {
          $delete_item = false;
          break;
        }
      }

      if($delete_item) {
        MenuItemController::delete_item($saved_item->id);
      }
    }


    $tmp_id_db_id_map = [];
    foreach($flatten_new_items as $item) {

      if(isset($tmp_id_db_id_map[$item['parent_id']])) {
        $item['parent_id'] = $tmp_id_db_id_map[$item['parent_id']];
      } else {
        $item['parent_id'] = 0;
      }


      if(isset($item['id']) && !str_starts_with($item['id'], "u_")) {
        $db_id = MenuItemController::update_item($item['id'], $item, $id);
      } else {
        $item['menu_id'] = $id;
        $db_id = MenuItemController::add_item($item);
      }

      if(!is_wp_error($db_id)) {
        $tmp_id_db_id_map[$item['id']] = $db_id;
      }
    }


    return self::get_menu($id);
  }


  public static function order_items($id, $tree){
    $items = [];
    self::flatten_tree($tree, $items);

    foreach($items as $item) {
      // see na-menu.php wp_update_nav_menu_item function
      update_post_meta($item['item_id'], '_menu_item_menu_item_parent', (string)((int)$item['parent_id']));
      wp_update_post([
        'ID' => $item['item_id'],
        'menu_order' => $item['order']
      ]);
    }

  }


  public static function get_menu_from_wp_data($wp_menu){
    $menu_items = self::get_menu_items_list($wp_menu->term_id);
    return new Menu($wp_menu->term_id, $wp_menu->name, $wp_menu->slug, self::menu_items_to_tree($menu_items));
  }

  protected static function menu_items_to_tree($menu_items){
    $items_parent_map = [];
    foreach($menu_items as $item) {
      if(!isset($items_parent_map[$item->parent_id])) {
        $items_parent_map[$item->parent_id] = [];
      }
      $items_parent_map[$item->parent_id][] = $item;
    }

    foreach($items_parent_map[0] as $root_item) {
      self::create_tree($root_item, $items_parent_map);
    }

    return ($items_parent_map[0] !== null) ? $items_parent_map[0] : [];
  }

  protected static function create_tree(&$item, &$items_parent_map){
    $children = (!empty($items_parent_map[$item->id])) ? $items_parent_map[$item->id] : [];
    foreach($children as $child) {
      $item->children[] = $child;
      self::create_tree($child, $items_parent_map);
    }
  }

  protected static function flatten_tree($tree, &$items, $parent_id = 0, $add_tmp_ids = false){
    foreach($tree as $item) {
      $tmp_item = $item;
      unset($tmp_item["children"]);
      $tmp_item['order'] = count($items) + 1;
      $tmp_item['parent_id'] = $parent_id;

      if($add_tmp_ids && empty($tmp_item['id'])) {
        $tmp_item['id'] = "u_" . (count($items) + 1);
      }

      $items[] = $tmp_item;
      self::flatten_tree($item['children'], $items, $tmp_item['id'], $add_tmp_ids);
    }
  }

  protected static function get_menu_items_list($term_id){
    $menu_items = [];
    foreach(wp_get_nav_menu_items($term_id) as $wp_menu_item) {
      $menu_items[] = MenuItemController::get_menu_item_from_wp_data($wp_menu_item);
    }

    return $menu_items;
  }

  protected static function get_header_footer_templates(){
    $response = [
      'header' => null,
      'footer' => null,
    ];

    if(!class_exists('\Tenweb_Builder\Condition')) {
      return $response;
    }

    $condition = new \Tenweb_Builder\Condition();
    $header_id = $condition->get_header_template();
    $footer_id = $condition->get_footer_template();


    if($header_id) {
      $response['header'] = [
        'url' => add_query_arg(['post' => $header_id, 'action' => 'elementor',], admin_url('post.php'))
      ];
    }

    if($footer_id) {
      $response['footer'] = [
        'url' => add_query_arg(['post' => $footer_id, 'action' => 'elementor',], admin_url('post.php'))
      ];
    }

    return $response;
  }
}