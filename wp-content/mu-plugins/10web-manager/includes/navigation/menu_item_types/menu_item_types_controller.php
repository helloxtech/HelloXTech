<?php

namespace Tenweb_Manager\Navigation;

class MenuItemTypesController {


  public static function get_all(){
    $types = self::get_menu_item_types_list();
    $items = [];

    foreach($types as $type) {
      $tmp_items = self::get_menu_item_type_items_list($type->type, $type->object_type);
      $items[$type->type . '_' . $type->object_type] = (!is_wp_error($tmp_items)) ? $tmp_items : [];
    }

    return [
      'types' => $types,
      'items' => $items
    ];
  }

  public static function get_menu_item_types_list(){
    $types = [];
    // see wp_nav_menu_post_type_meta_boxes, wp_nav_menu_taxonomy_meta_boxes functions
    $post_types = get_post_types(array('show_in_nav_menus' => true), 'objects');

    foreach($post_types as $post_type) {
      $types[] = new MenuItemType($post_type->labels->name, 'post_type', $post_type->name);
    }
    usort($types, [MenuItemTypesController::class, 'sort_post_types']);

    $types[] = new MenuItemType("Custom Links", 'custom', 'custom');

    $taxonomies = get_taxonomies(array('show_in_nav_menus' => true), 'objects');
    foreach($taxonomies as $tax) {
      $types[] = new MenuItemType($tax->labels->name, 'taxonomy', $tax->name);
    }

    return $types;
  }

  public static function get_menu_item_type_items_list($type, $object_type){

    if($type === "post_type") {
      return self::get_post_type_items($object_type);
    }

    if($type === "taxonomy") {
      return self::get_taxonomy_items($object_type);
    }

    return [];
  }


  public static function get_post_type_items($post_type){
    $front_page = false;
    $privacy_policy_page_id = false;

    if($post_type === "page") {
      $front_page = 'page' === get_option('show_on_front') ? (int)get_option('page_on_front') : 0;
      $privacy_policy_page_id = (int)get_option('wp_page_for_privacy_policy');
    }

    $items = [];
    $args = array(
      'offset' => 0,
      'order' => 'ASC',
      'orderby' => 'title',
      'posts_per_page' => 100,
      'post_type' => $post_type,
      'post_status' => 'publish',
      'suppress_filters' => true,
      'update_post_term_cache' => false,
      'update_post_meta_cache' => false,
    );

    $posts = get_posts($args);


    foreach($posts as $post) {

      if($post->ID === $front_page) {
        $special_status = "front_page";
      } else if($post->ID === $privacy_policy_page_id) {
        $special_status = "privacy_policy_page";
      } else {
        $special_status = null;
      }

      $items[] = new MenuItemTypeItem($post->ID, $post->post_title, 'post_type', $post->post_type, $special_status);
    }

    return $items;
  }

  public static function get_taxonomy_items($taxonomy){
    $args = array(
      'taxonomy' => $taxonomy,
      'child_of' => 0,
      'exclude' => '',
      'hide_empty' => false,
      'hierarchical' => true,
      'include' => '',
      'number' => 100,
      'offset' => 0,
      'order' => 'ASC',
      'orderby' => 'name',
      'pad_counts' => false,
    );
    $terms = get_terms($args);

    if(is_wp_error($terms)) {
      return $terms;
    }

    $items = [];
    foreach($terms as $term) {
      $items[] = new MenuItemTypeItem($term->term_id, $term->name, 'taxonomy', $term->taxonomy);
    }

    return $items;
  }

  public static function sort_post_types($p1, $p2){

    if($p2->object_type == "page") {
      return 1;
    }

    return 0;
  }

}