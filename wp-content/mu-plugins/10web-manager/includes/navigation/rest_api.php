<?php

namespace Tenweb_Manager\Navigation;

use Tenweb_Manager\Manager;

class RestApi {
  protected static $instance = null;

  private static $ROUTE_NAMESPACE = TENWEB_REST_NAMESPACE. "/navigation";

  private function __construct(){
    add_action('rest_api_init', array($this, 'register_routes'));
  }

  public function register_routes(){
    $this->register_menu_all();
  }

  private function register_menu_all(){
    register_rest_route(self::$ROUTE_NAMESPACE, '/menu/all', array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_all_menus'),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu/list', array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_menus_list'),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu/(?P<id>\d+)', array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_single_menu'),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu/add', array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => array($this, 'add_menu'),
        'args' => array(
          'name' => array(
            'type' => 'string',
            'required' => true,
            'validate_callback' => array(self::class, 'not_empty_string'),
            'sanitize_callback' => 'sanitize_text_field'
          ),
        ),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu/order_items/(?P<id>\d+)', array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => array($this, 'order_menu_items'),
        'args' => array(
          'items' => array(
            'type' => 'array',
            'required' => true,
          ),
        ),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu/delete/(?P<id>\d+)', array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => array($this, 'delete_menu'),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu/update/(?P<id>\d+)', array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => array($this, 'update_menu'),
        'args' => array(
          'name' => array(
            'type' => 'string',
            'required' => true,
            'validate_callback' => array(self::class, 'not_empty_string'),
            'sanitize_callback' => 'sanitize_text_field'
          ),
          'items' => array(
            'type' => 'array',
          ),
        ),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu_item/add', array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => array($this, 'add_menu_item'),
        'args' => array(
          'menu_id' => array(
            'type' => 'integer', // $allowed_types = array( 'array', 'object', 'string', 'number', 'integer', 'boolean', 'null' );
            'required' => true,
          ),
          'type' => array(
            'type' => 'string',
            'required' => true,
            'validate_callback' => function($param, \WP_REST_Request $request){
              if(!in_array($param, ['custom', 'taxonomy', 'post_type'])) {
                return false;
              }

              if($param === "custom") {
                $required_params = ['custom_url', 'link_text'];
              } else {
                $required_params = ['object_id', 'object_type'];
              }

              foreach($required_params as $tmp) {
                $val = trim(sanitize_text_field($request->get_param($tmp)));
                if(empty($val)) {
                  return new \WP_Error("$tmp parameter is missing");
                }
              }

              return true;
            },
            'sanitize_callback' => 'sanitize_text_field'
          ),
          'order' => array(
            'type' => 'integer',
            'required' => true,
          ),
          'parent_id' => array(
            'type' => 'integer',
            'required' => true,
          ),
          'custom_url' => array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => array(self::class, 'not_empty_string')
          ),
          'link_text' => array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => array(self::class, 'not_empty_string')
          ),
          'object_id' => array(
            'type' => 'integer',
          ),
          'object_type' => array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => array(self::class, 'not_empty_string'),
          ),
        ),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu_item/delete/(?P<id>\d+)', array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => array($this, 'delete_menu_item'),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu_item/update/(?P<id>\d+)', array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => array($this, 'update_menu_item'),
        'args' => array(
          'title' => array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => array(self::class, 'not_empty_string'),
          ),
          'url' => array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_url',
            'validate_callback' => array(self::class, 'not_empty_string'),
          ),
          'order' => array(
            'type' => 'integer',
          ),
        ),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu_item_types/all', array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_menu_item_types_all'),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu_item_types/types', array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_menu_item_types_list'),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

    register_rest_route(self::$ROUTE_NAMESPACE, '/menu_item_types/items', array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_menu_item_type_items_list'),
        'args' => array(
          'type' => array(
            'type' => 'string',
            'required' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => array(self::class, 'not_empty_string'),
          ),
          'object_type' => array(
            'type' => 'string',
            'required' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => array(self::class, 'not_empty_string'),
          )
        ),
        'permission_callback' => array($this, 'check_authorization'),
      )
    );

  }


  public function get_all_menus(\WP_REST_Request $request){
    try {
      return new \WP_REST_Response(MenuController::get_all_menus(), 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public function get_menus_list(\WP_REST_Request $request){
    try {
      return new \WP_REST_Response(MenuController::get_menus_list(), 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public function get_single_menu(\WP_REST_Request $request){
    try {
      return new \WP_REST_Response(MenuController::get_menu($request->get_param("id")), 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public function order_menu_items(\WP_REST_Request $request){
    try {
      MenuController::order_items($request->get_param("id"), $request->get_param("items"));
      return new \WP_REST_Response(['success' => true], 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public function delete_menu(\WP_REST_Request $request){
    try {
      $delete_menu = MenuController::delete_menu($request->get_param("id"));

      if(is_wp_error($delete_menu)) {
        return new \WP_REST_Response(["error_code" => $delete_menu->get_error_code()], 409);
      }

      return new \WP_REST_Response(['success' => true], 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public function update_menu(\WP_REST_Request $request){
    try {
      $update_menu = MenuController::update_menu($request->get_param("id"), $request->get_param("name"), $request->get_param("items"));

      if(is_wp_error($update_menu)) {
        return new \WP_REST_Response(["error_code" => $update_menu->get_error_code()], 409);
      }

      return new \WP_REST_Response($update_menu, 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public function add_menu(\WP_REST_Request $request){
    try {
      $menu_name = trim($request->get_param("name"));
      $added_menu = MenuController::add_menu($menu_name, $request->get_param("items"));

      if(is_wp_error($added_menu)) {
        return new \WP_REST_Response(["error_code" => $added_menu->get_error_code()], 409);
      }

      return new \WP_REST_Response($added_menu, 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public static function add_menu_item(\WP_REST_Request $request){
    try {
      $item_id = MenuItemController::add_item($request->get_params());

      if(is_wp_error($item_id)) {
        return new \WP_REST_Response(["error_code" => $item_id->get_error_code()], 409);
      }

      return new \WP_REST_Response(["id" => $item_id], 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }

  }

  public static function delete_menu_item(\WP_REST_Request $request){
    try {
      $item_id = $request->get_param("id");

      $response = MenuItemController::delete_item($item_id);

      if(is_wp_error($response)) {
        return new \WP_REST_Response(["error_code" => $response->get_error_code()], 409);
      }

      return new \WP_REST_Response(['success' => true], 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public static function update_menu_item(\WP_REST_Request $request){
    try {

      $response = MenuItemController::update_item($request->get_param("id"), $request->get_params());

      if(is_wp_error($response)) {
        return new \WP_REST_Response(["error_code" => $response->get_error_code()], 409);
      }

      return new \WP_REST_Response(['success' => true], 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public static function get_menu_item_types_all(\WP_REST_Request $request){
    try {
      $response = MenuItemTypesController::get_all();
      return new \WP_REST_Response($response, 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }


  public static function get_menu_item_types_list(\WP_REST_Request $request){
    try {
      $response = MenuItemTypesController::get_menu_item_types_list();

      return new \WP_REST_Response($response, 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public static function get_menu_item_type_items_list(\WP_REST_Request $request){
    try {
      $response = MenuItemTypesController::get_menu_item_type_items_list($request->get_param('type'), $request->get_param('object_type'));

      if(is_wp_error($response)) {
        return new \WP_REST_Response(["error_code" => $response->get_error_code()], 409);
      }

      return new \WP_REST_Response($response, 200);
    } catch(\Exception $exception) {
      return new \WP_REST_Response([], 500);
    }
  }

  public function check_authorization(\WP_REST_Request $request){
    if(!class_exists('\Tenweb_Authorization\Login')) {
      return false;
    }

    $login_instance = \Tenweb_Authorization\Login::get_instance();
    $check_logged_in = $login_instance->check_request($request);
    if($check_logged_in instanceof \WP_REST_Response) {
      return new \WP_Error($check_logged_in->data['code'], $check_logged_in->data['message'], ['status' => 401]);
    }

    $authorize = $login_instance->authorize($request);
    if(is_array($authorize)) {
      return new \WP_Error($authorize['code'], $authorize['message'], ['status' => 401]);
    }

    return true;
  }

  public static function not_empty_string($param){
    return !empty(trim($param));
  }


  public static function get_instance(){
    if(self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }

}
