<?php

namespace Tenweb_Builder\Modules\WebsiteNavigation;

class WPMenuController
{
    protected static $instance = null;

    /**
     * Constructor.
     * Registers AJAX actions for menu changes and fetching available menu items.
     */
    public function __construct()
    {
        add_action('wp_ajax_wn_nav_menu_changes', [$this, 'navMenuChanges']);
        add_action('wp_ajax_wn_get_available_menu_items', [$this, 'getAvailableMenuItems']);
        add_action('wp_ajax_wn_change_item_settings', [$this, 'changeItemSettings']);
        add_action('wp_ajax_wn_get_navmenu_sidebar_template', [$this, 'getNavMenuSidebarTemplate']);
        add_action('wp_ajax_wn_trash_management', [$this, 'trashManagement']);
        add_action('wp_ajax_wn_get_sidebar_item', [$this, 'getSidebarItem']);
    }

    /**
     * Fetches available menu items based on the provided type and post type.
     *
     * Expects:
     * - $_POST['nonce'] (string): Security nonce.
     * - $_POST['type'] (string): Type of items to fetch ('post' or 'taxonomy').
     * - $_POST['post_type'] (string): Post type or taxonomy name.
     * - $_POST['html_title'] (string): HTML title for the tooltip.
     * - $_POST['nav_menu_id'] (int): ID of the navigation menu.
     *
     * Returns:
     * - JSON response with available menu items and HTML content.
     */
    public function getAvailableMenuItems() {
        //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'twbb' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
        $html_title = isset($_POST['html_title']) ? sanitize_text_field($_POST['html_title']) : '';
        $nav_menu_id = isset($_POST['nav_menu_id']) ? (int) $_POST['nav_menu_id'] : 0;
        $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
        $add_parent_container = isset($_POST['add_parent_container']) ? sanitize_text_field( $_POST['add_parent_container'] ) : '';
        $pages_count_per_requested = isset($_POST['pages_count_per_requested']) ? (int) $_POST['pages_count_per_requested'] : 10;
        $rendering_type = isset($_POST['rendering_type']) ? sanitize_text_field($_POST['rendering_type']) : 'tooltip';
        $exclude_menu_items = isset($_POST['exclude_menu_items']) ? sanitize_text_field($_POST['exclude_menu_items']) : '';
        $exclude_draft = isset($_POST['exclude_draft']) ? sanitize_text_field($_POST['exclude_draft']) : '';
        $current_page_id = isset($_POST['current_page_id']) ? intval($_POST['current_page_id']) : 0;
        //check and remove items which are included in nav menu
        $nav_menu_items = [];
        if( !empty($nav_menu_id) ) {
            $nav_menu_items = wp_get_nav_menu_items($nav_menu_id);
            $nav_menu_items = array_map(function ($item) {
                return (int) $item->object_id;
            }, $nav_menu_items);
        }
        if( $type === 'post' ) {
            $args = [
                'post_type' => $post_type,
                'post_status' => ['publish','draft'],
                'posts_per_page' => $pages_count_per_requested,
                'paged' => $page,
                'orderby' => 'date',
                'order' => 'DESC',
            ];

            if( $post_type === 'elementor_library' ) {
                $args['posts_per_page'] = -1;
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                $args['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'key'     => '_elementor_template_type',
                        'value'   => 'kit',
                        'compare' => '!=',
                    ],
                    [
                        'key'     => '_elementor_template_type',
                        'compare' => 'EXISTS',
                    ],
                ];
            }

            //exclude menu items
            if( $exclude_menu_items !== 'false' ) {
                $args['post__not_in'] = $nav_menu_items; //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn
            }
            //exclude draft
            if( $exclude_draft === 'true' ) {
                $args['post_status'] = 'publish';
            }
            $query = new \WP_Query($args);
            $posts = $query->posts;
        } else if( $type === 'taxonomy' ) {
            //get terms of current taxonomy
            $posts = get_terms([
                'taxonomy' => $post_type,
                'hide_empty' => true,
            ]);
            if( $exclude_menu_items !== 'false' ) {
                $posts['exclude'] = $nav_menu_items; //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
            }
        }
        if( $add_parent_container === 'true' ) {
            if( $rendering_type === 'tooltip' ) {
                $output = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::addMenuItemSecondaryTooltip($html_title, $post_type, $type, $posts, 1, true);
            } else {
                $output = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::navItemsFromObject($posts, $post_type, $current_page_id);
            }
        } else {
            if( $rendering_type === 'tooltip' ) {
                $output = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::addMenuItemSecondaryTooltip($html_title, $post_type, $type, $posts, $page, false);
            } else {
                $output = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::navItemsFromObject($posts, $post_type, $current_page_id);
            }
        }
        //send json success the output html
        wp_send_json_success([
            'content' => $output,
            'items' => $posts,
        ]);
    }

    /**
     * Handles various navigation menu changes (add, remove, edit, etc.).
     *
     * Expects:
     * - $_POST['nonce'] (string): Security nonce.
     * - $_POST['process'] (string): Action to perform (e.g., 'addNavMenuItem').
     *
     * Returns:
     * - JSON response indicating success or failure.
     */
    public function navMenuChanges() {
        $action_available_values = [
            'addNavMenuItem',
            'removeNavMenuItem',
            'editNavMenuItem',
            'editNavMenuBulkItems',
            'updateNavMenuOrdering',
            'changeNavMenuContent'
        ];

        //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if ( !isset($_POST['nonce']) || ! wp_verify_nonce( $_POST['nonce'], 'twbb' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }
        $action = isset($_POST['process']) ? sanitize_text_field($_POST['process']) : '';
        if( !in_array($action, $action_available_values, true) || $action === 'constructor' ||
            !user_can(get_current_user_id(), 'administrator') || !method_exists($this, $action) ) {
            wp_send_json_error('Invalid action');
        }
        $this->$action($_POST);
    }

    /**
     * Updates the content of a navigation menu widget.
     *
     * Expects:
     * - $_POST['nonce'] (string): Security nonce.
     * - $_POST['postID'] (int): Post ID of the Elementor document.
     * - $_POST['elementID'] (string): ID of the Elementor widget element.
     *
     * Returns:
     * - JSON response with the rendered HTML content or an error.
     */
    public function changeNavMenuContent() {
        //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if ( !isset($_POST['nonce']) || ! wp_verify_nonce( $_POST['nonce'], 'twbb' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }
        $post_id = isset($_POST['postID']) ? absint($_POST['postID']) : 0;
        $element_id = isset($_POST['elementID']) ? sanitize_text_field($_POST['elementID']) : '';
        $document = \Elementor\Plugin::$instance->documents->get( $post_id );

        if ( ! $document ) {
            wp_send_json_error( [
                'message' => __( 'Document doesn\'t exist', 'tenweb-builder'),
            ], 404 );
        }

        $element_data = $document->get_elements_data();
        $widget = \Elementor\Utils::find_element_recursive( $element_data, $element_id );

        if ( empty( $widget ) ) {
            wp_send_json_error( [
                'message' => __( 'Widget doesn\'t exist', 'tenweb-builder'),
            ], 404 );
        }

        // Filter to remove menu items pointing to trashed posts
        add_filter('wp_get_nav_menu_items', function($items) {
            return array_filter($items, function($item) {
                $linked_post = get_post( $item->object_id );
                if ( $item->type === 'custom' || $item->object === 'custom' || $item->type === 'taxonomy' ) {
                    return true; // Keep custom links
                }
                return $linked_post && $linked_post->post_status !== 'trash';
            });
        }, 10, 1);

        \Elementor\Plugin::$instance->documents->switch_to_document($document);
        $html = $document->render_element($widget);
        // Remove the temporary nav menu filter to avoid affecting other code or future renders
        remove_all_filters('wp_get_nav_menu_items');

        wp_send_json_success($html);
    }

    /**
     * Adds a new item to the navigation menu.
     *
     * Expects:
     * - $_POST['menu_id'] (int): ID of the navigation menu.
     * - $_POST['item_id'] (int): ID of the item to add.
     * - $_POST['item_title'] (string): Title of the menu item.
     * - $_POST['item_object'] (string): Object type (e.g., 'post', 'taxonomy').
     * - $_POST['item_type'] (string): Type of the menu item.
     * - $_POST['item_position'] (int): Position of the menu item.
     * - $_POST['item_parent_id'] (int): Parent ID of the menu item.
     * - $_POST['item_url'] (string): URL of the menu item.
     * - $_POST['return_last_added_item'] (bool): Whether to return the last added item.
     *
     * Returns:
     * - JSON response with the added menu item or an error.
     */

    public function addNavMenuItem($post) {
        $menu_id = isset($post['menu_id']) ? (int) $post['menu_id'] : 0;
        $item_id = isset($post['item_id']) ? (int) $post['item_id'] : 0;
        $item_title = isset($post['item_title']) ? sanitize_text_field($post['item_title']) : '';
        $item_object = isset($post['item_object']) ? sanitize_text_field($post['item_object']) : '';
        $item_type = isset($post['item_type']) ? sanitize_text_field($post['item_type']) : 'post_type';
        $item_position = isset($post['item_position']) ? (int) $post['item_position'] : 0;
        $item_parent_id = isset($post['item_parent_id']) ? (int) $post['item_parent_id'] : 0;
        $item_url = isset($post['item_url']) ? sanitize_url($post['item_url']) : '';
        $return_last_added_item = isset($post['return_last_added_item']) ? sanitize_text_field($post['return_last_added_item']) : false;
        $args = [
            'menu-item-object-id'   => $item_id,
            'menu-item-object'      => $item_object,
            'menu-item-parent-id'   => $item_parent_id,
            'menu-item-position'    => $item_position,
            'menu-item-type'        => $item_type,
            'menu-item-title'       => wp_slash($item_title),
            'menu-item-url'         => $item_url,
            'menu-item-status'      => 'publish',
            'menu-item-target'      => 'blank',
        ];

        if( $item_object === '' || $item_type === '' )  {
            wp_send_json_error('Error getting add menu');
        }
        $nav_menu_items = wp_get_nav_menu_items($menu_id);
        $existing_item = null;
        foreach ( $nav_menu_items as $item ) {
            if ( (int) $item->object_id === $item_id ) {
                $existing_item = $item;
                break;
            }
        }

        if ( $existing_item ) {
            $item_html = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItemSettings($existing_item);
            wp_send_json_success([
                'message' => 'Item already exists',
                'code' => 201,
                'item_html' => $item_html,
            ]);
        }

        //add menu item
        $menu_item = wp_update_nav_menu_item($menu_id, 0, $args);

        if (is_wp_error($menu_item)) {
            wp_send_json_error($menu_item->get_error_message());
        }
        if( $return_last_added_item ) {
            $menu_item = wp_get_nav_menu_items($menu_id);
            //last item of $menu_item
            if( !is_array($menu_item) || empty($menu_item) ) {
                wp_send_json_error('Error getting menu item');
            }
            $last_item = end($menu_item);
            $item_object = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_reformattingNavItem($last_item);
            //this returns the html to add to nav_menu sortable
            $args = [
                'wn_type' => 'nav_menu',
                'nav_item' => $last_item,
                'depth' => 0,
            ];
            $menu_item = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItem($item_object, $args);
        }

        wp_send_json_success(
            [
                'code' => 200,
                'item_html' => $menu_item,
            ]
        );
    }

    /**
     * Removes an item from the navigation menu.
     *
     * Expects:
     * - $_POST['menu_item_db_id'] (int): Database ID of the menu item to remove.
     *
     * Returns:
     * - JSON response indicating success or failure.
     */
    public function removeNavMenuItem($post) {
        $menu_item_db_id = isset($post['menu_item_db_id']) ? (int) $post['menu_item_db_id'] : 0;
        $menu_item = wp_delete_post($menu_item_db_id);
        if (is_wp_error($menu_item)) {
            wp_send_json_error('Error deleting menu item');
        }
        wp_send_json_success($menu_item);
    }

    /**
     * Edits an existing navigation menu item.
     *
     * Expects:
     * - $_POST['menu_id'] (int): ID of the navigation menu.
     * - $_POST['item_id'] (int): ID of the item to edit.
     * - $_POST['item_title'] (string): New title of the menu item.
     * - $_POST['item_object'] (string): Object type (e.g., 'post', 'taxonomy').
     * - $_POST['item_type'] (string): Type of the menu item.
     * - $_POST['item_position'] (int): New position of the menu item.
     * - $_POST['item_parent_id'] (int): New parent ID of the menu item.
     * - $_POST['item_url'] (string): New URL of the menu item.
     * - $_POST['menu_item_db_id'] (int): Database ID of the menu item.
     * - $_POST['status'] (string): Status of the menu item.
     *
     * Returns:
     * - JSON response with the updated menu item or an error.
     */
    public function editNavMenuItem($post) {
        $menu_id = isset($post['menu_id']) ? (int) $post['menu_id'] : 0;
        $item_id = isset($post['item_id']) ? (int) $post['item_id'] : 0;
        $item_title = isset($post['item_title']) ? sanitize_text_field($post['item_title']) : '';
        $item_object = isset($post['item_object']) ? sanitize_text_field($post['item_object']) : '';
        $item_type = isset($post['item_type']) ? sanitize_text_field($post['item_type']) : '';
        $item_position = isset($post['item_position']) ? (int) $post['item_position'] : 0;
        $item_parent_id = isset($post['item_parent_id']) ? (int) $post['item_parent_id'] : 0;
        $item_url = isset($post['item_url']) ? sanitize_url($post['item_url']) : '';
        $menu_item_db_id = isset($post['menu_item_db_id']) ? (int) $post['menu_item_db_id'] : 0;
        $status = isset($post['status']) ? sanitize_text_field($post['status']) : '';

        $args = [
            'menu-item-object-id'   => $item_id,
            'menu-item-object'      => $item_object,
            'menu-item-parent-id'   => $item_parent_id,
            'menu-item-position'    => $item_position,
            'menu-item-type'        => $item_type,
            'menu-item-title'       => wp_slash($item_title),
            'menu-item-url'         => $item_url,
            'menu-item-status'      => $status,
            'menu-item-target'      => 'blank',
        ];
        $menu_item = wp_update_nav_menu_item($menu_id, $menu_item_db_id, $args);
        if (is_wp_error($menu_item)) {
            wp_send_json_error($menu_item->get_error_message());
        }
        wp_send_json_success($menu_item);
    }

    /**
     * Edits multiple navigation menu items in bulk.
     *
     * Expects:
     * - $_POST['args'] (array): Array of menu items to update, including:
     *   - 'menu_id' (int): ID of the navigation menu.
     *   - 'items' (array): List of items with their properties to update.
     *
     * Returns:
     * - JSON response indicating success or failure.
     */
    public function editNavMenuBulkItems($post) {
        $args = isset($post['args']) ? $post['args'] : [];
        $menu_id = (int) $args['menu_id'];
        $items = $args['items'] ?? [];
        if( empty($items) ) {
            wp_send_json_success('No items to update');
        }
        foreach ( $items as $item ) {
            $menu_item_db_id = (int) $item['menu_item_db_id'];
            $item_args = [
                'menu-item-object-id'   => (int) $item['item_id'],
                'menu-item-object'      => sanitize_text_field($item['object']),
                'menu-item-parent-id'   => (int) $item['item_parent_id'],
                'menu-item-position'    => (int) $item['item_position'],
                'menu-item-type'        => sanitize_text_field($item['item_type']),
                'menu-item-title'       => wp_slash(sanitize_text_field($item['item_title'])),
                'menu-item-url'         => isset($item['item_url']) ?? sanitize_url($item['item_url']),
                'menu-item-status'      => isset($item['status']) ?? sanitize_text_field($item['status']),
                'menu-item-target'      => 'blank',
            ];
            wp_update_nav_menu_item($menu_id, $menu_item_db_id, $item_args);
        }
        wp_send_json_success();
    }

    /**
     * Updates the ordering of navigation menu items.
     *
     * Expects:
     * - $_POST['args'] (array): Array of menu item positions, including:
     *   - 'menu_item_positions' (array): List of items with 'db_id' and 'position'.
     *
     * Returns:
     * - JSON response indicating success or failure.
     */
    public function updateNavMenuOrdering($post) {
        $args = isset($post['args']) ? $post['args'] : [];
        $menu_item_positions = $args['menu_item_positions'];
        foreach ($menu_item_positions as $item) {
            $id = (int) $item['db_id'];
            $position = (int) $item['position'];
            wp_update_post([
                'ID' => $id,
                'menu_order' => $position
            ]);
        }
        wp_send_json_success();
    }

    /**
     * Handles AJAX-based trash management tasks for posts/pages.
     *
     * This method verifies the nonce and delegates the requested task to the corresponding handler:
     * - `wn_move_to_trash`: Move a post to the trash.
     * - `wn_restore_from_trash`: Restore a post from the trash.
     * - `wm_get_trash_items`: Retrieve trashed items of a given post type.
     * - `wm_empty_trash`: Permanently delete all trashed items of a given post type or a single post.
     *
     * Accepts `post_id` and `post_type` via POST and routes the request to the appropriate internal method.
     * Returns a JSON success or error response.
     *
     *
     * @return void Outputs a JSON response using wp_send_json_success() or wp_send_json_error().
    */
    public function trashManagement() {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'twbb' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }

        $task = isset( $_POST['task'] ) ? sanitize_text_field($_POST['task']) : '';
        $args = array(
            'post_id'   => isset( $_POST['post_id'] ) ? intval($_POST['post_id']) : 0,
            'post_type' => isset( $_POST['post_type'] ) ? sanitize_text_field($_POST['post_type']) : '',
        );

        switch ($task) {
            case "wn_move_to_trash":
                $this->moveToTrash( $args );
                break;
            case "wn_restore_from_trash":
                $this->restoreFromTrash( $args );
                break;
            case "wm_get_trash_items":
                $this->get_trash_items( $args );
                break;
            case "wm_empty_trash":
                $this->emptyTrash( $args );
                break;
        }

        wp_send_json_error();
    }

    /**
     * Permanently deletes a specific trashed post or empties the trash for a given post type.
     *
     * If a `post_id` is provided, the function deletes that specific post if it is in the trash.
     * Otherwise, it fetches all trashed posts of the specified `post_type` and permanently deletes them.
     * Returns a JSON success response on success, or an error if the post is invalid or not trashed.
     *
     *
     * @param array $args {
     *     Arguments for trash deletion.
     *
     *     @type int|null    $post_id   Optional. The ID of the single trashed post to delete.
     *     @type string|null $post_type Optional. The post type to empty trash for if `post_id` is not provided.
     * }
     *
     * @return void Outputs a JSON response indicating success or error.
    */
    public function emptyTrash( $args ) {
        if( $args['post_id'] ) {
            $post = get_post($args['post_id']);
            if (!$post || $post->post_status !== 'trash') {
                wp_send_json_error(['error' => 'Invalid or not trashed post.'], 400);
            }
            wp_delete_post($args['post_id'], true);
            wp_send_json_success();
        } else {
            // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
            $trashed_posts = get_posts([
                'post_type'   => $args['post_type'],
                'post_status' => 'trash',
                'numberposts' => -1,
                'fields'      => 'ids',
            ]);

            foreach ($trashed_posts as $id) {
                wp_delete_post($id, true);
            }

            wp_send_json_success();
        }
    }

    /**
     * Retrieves all trashed posts of a given post type and returns an HTML-rendered list of items.
     *
     * This method queries posts with the 'trash' status and builds HTML output
     * for each item including "Restore" and "Delete" action buttons.
     * The resulting HTML is returned via a JSON success response.
     *
     *
     * @param array $args {
     *     Parameters for retrieving trashed items.
     *
     *     @type string $post_type The post type to query (e.g., 'page').
     * }
     *
     * @return void Outputs a JSON response with HTML content for the trashed items.
    */
    public function get_trash_items( $args ) {
        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
        $trashed_pages = get_posts([
            'post_type'      => $args['post_type'],
            'post_status'    => 'trash',
            'numberposts'    => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $output = '';
        foreach ( $trashed_pages as $page ) {
            $output .= '<div class="twbb-wn-trash-item twbb-wn-flex-space-between" data-id="'.esc_attr($page->ID).'">
                <span class="twbb-wn-trash-item-title">'.esc_html($page->post_title).'</span>
                <span class="twbb-wn-restore_from_trash"><span class="twbb-wn-settings-button-text">'.esc_html__("Restore", 'tenweb-builder').'</span><span class="twbb-wn-settings-button-spinner"></span></span>
                <span class="twbb-wn-delete_from_trash"><span class="twbb-wn-settings-button-text">'.esc_html__("Delete", 'tenweb-builder').'</span><span class="twbb-wn-settings-button-spinner"></span></span>
            </div>';
        }
        wp_send_json_success([
            'content' => $output,
        ]);
    }

    /**
     * Moves a post to the trash.
     *
     * Verifies the current user's capability to delete the post, then attempts to move it to the trash.
     * Returns a JSON success response if successful, or an error response otherwise.
     *
     *
     * @param array $args {
     *     Arguments for trashing the post.
     *
     *     @type int $post_id The ID of the post to be moved to the trash.
     * }
     *
     * @return void Outputs a JSON response indicating success or failure.
    */
    public function moveToTrash($args) {
        $post_id = $args['post_id'];
        if ( !current_user_can('delete_post', $post_id) || !$post_id ) {
            wp_send_json_error('Permission denied');
        }

        $result = wp_trash_post($post_id);
        if ( $result ) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }


    /**
     * Restores a post from the trash and returns the rendered website navigation sidebar item.
     *
     * This function checks user permissions and the current status of the post.
     * If the post is in the trash, it is restored, and a navigation item template is rendered and returned.
     * If the post is not in trash or does not exist, an error is returned.
     *
     *
     * @param array $args {
     *     Arguments for the restoration process.
     *
     *     @type int $post_id The ID of the post to restore.
     * }
     *
     * @return void Outputs a JSON success or error response using wp_send_json_*.
    */
    public function restoreFromTrash($args) {
        $post_id = $args['post_id'];
        if ( !current_user_can('edit_post', $post_id) || !$post_id ) {
            wp_send_json_error('Permission denied');
        }

        $post = get_post($post_id);
        if ( $post && $post->post_status === 'trash' ) {
            wp_untrash_post($post_id);
            $post = get_post($post_id);
            $page_info = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'url' => get_permalink($post->ID),
                'status' => 'draft',
                'post_type' => $post->post_type,
                'content_edit_link' => admin_url('post.php?post=' . $post->ID . '&action=elementor'),
            );
            $output = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItem($page_info, ['wn_type' => 'page']);


            wp_send_json_success([
                'content' => $output,
            ]);
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Handles AJAX request to generate a rendered website navigation sidebar item.
     *
     * This method verifies the nonce, retrieves the post by its ID,
     * builds the necessary data for the sidebar item, and returns the rendered HTML template.
     * Only valid posts return a success response; otherwise, an error is returned.
     *
     *
     * @return void Outputs JSON response via wp_send_json_success() or wp_send_json_error().
    */
    public function getSidebarItem() {
        if (
            ! isset($_POST['nonce']) ||
            ! wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'twbb')
        ) {
            wp_send_json_error('Invalid nonce');
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if ( ! $post_id ) {
            wp_send_json_error('Invalid post ID');
        }

        $post = get_post($post_id);
        if ( !$post ) {
            wp_send_json_error('Post not found');
        }

        $page_info = [
            'id'               => $post->ID,
            'title'            => get_the_title($post),
            'slug'             => $post->post_name,
            'url'              => get_permalink($post),
            'status'           => $post->post_status,
            'post_type'        => $post->post_type,
            'content_edit_link'=> admin_url('post.php?post=' . $post->ID . '&action=elementor'),
        ];

        $output = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItem(
            $page_info,
            ['wn_type' => 'page']
        );

        wp_send_json_success([
            'content'    => $output,
            'post_type'  => $post->post_type,
        ]);
    }

    /**
     * Updates the settings of a navigation menu item or a taxonomy term.
     *
     * Expects the following `$_POST` parameters:
     * - `nonce` (string): Security nonce for verification.
     * - `element_object_id` (int): ID of the post or term to update.
     * - `element_db_id` (int): ID of the navigation menu item to update.
     * - `type` (string): Type of the element (`post_type` or `taxonomy`).
     * - `object` (string): Object type (e.g., post type or taxonomy name).
     * - `title` (string): New title for the post or term.
     * - `nav_item_title` (string): New title for the navigation menu item.
     * - `slug` (string): New slug for the post or term.
     * - `status` (string): New status for the post (`true` for publish, `false` for draft).
     * - `home_page` (string): Whether to set the post as the home page (`true` or `false`).
     *
     * Performs the following actions:
     * - Updates the post or term title, slug, and status.
     * - Updates the navigation menu item's title.
     * - Optionally sets or unsets the post as the home page.
     *
     * Returns:
     * - JSON success response on success.
     * - JSON error response on failure.
     */
    function changeItemSettings() {
        //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'twbb' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }
        $element_object_id = isset($_POST['element_object_id']) ? (int) $_POST['element_object_id'] : 0;
        $element_db_id = isset($_POST['element_db_id']) ? (int) $_POST['element_db_id'] : 0;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'post_type';
        $object = isset($_POST['object']) ? sanitize_text_field($_POST['object']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $nav_label = isset($_POST['nav_item_title']) ? sanitize_text_field($_POST['nav_item_title']) : '';
        $url_slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
        $url = sanitize_url($_POST['url'] ?? ''); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $home_page = isset($_POST['home_page']) ? sanitize_text_field($_POST['home_page']) : '';
        if( $type === 'custom' ) {
            $object_args = [
                'ID' => $element_object_id,
                'title' => $title,
            ];
            $post_object = wp_update_post($object_args);
            //update _menu_item_url in meta value in wp_postmeta
            update_post_meta($element_db_id, '_menu_item_url', $url);
            if( is_wp_error($post_object) ) {
                wp_send_json_error($post_object->get_error_message());
            }
            wp_send_json_success($post_object);
        }
        else {
            $object_args = [
                'ID' => $element_object_id,
            ];
            $nav_element_args = [
                'ID' => $element_db_id,
            ];
            if (!empty($title)) {
                $object_args['post_title'] = $title;
            }
            if (!empty($nav_label)) {
                $nav_element_args['post_title'] = $nav_label;
            }
            if (!empty($url_slug)) {
                $object_args['post_name'] = $url_slug;
            }
            if (!empty($status)) {
                if ($status === 'true') {
                    $object_args['post_status'] = 'publish';
                } else {
                    $object_args['post_status'] = 'draft';
                }
            }
            if (!empty($home_page)) {
                //set ad home page or remove from home page
                if ($home_page === 'true') {
                    update_option('show_on_front', 'page');
                    update_option('page_on_front', $element_object_id);
                } else if($home_page === 'unset'){
                    update_option('show_on_front', 'posts');
                    update_option('page_on_front', 0);
                }
            }
            if ($type === 'post_type') {
                $post_object = wp_update_post($object_args);
                if (is_wp_error($post_object)) {
                    wp_send_json_error($post_object->get_error_message());
                }
                //get updated post url
                $url = get_permalink($element_object_id);
            } else if ($type === 'taxonomy') {
                if( empty($title) ) {
                    //get term name by id
                    $title = get_term($element_object_id, $object)->name;
                }
                $term_args = [
                    'term_id' => $element_object_id,
                    'name' => $title,
                ];
                if( !empty($url_slug) ) {
                    $term_args['slug'] = $url_slug;
                }
                $term_object = wp_update_term($element_object_id, $object, $term_args);
                if (is_wp_error($term_object)) {
                    wp_send_json_error($term_object->get_error_message());
                }
                //get update taxonomy url
                $url = get_term_link($element_object_id, $object);
            }

            $post_nav = wp_update_post($nav_element_args);
            if (is_wp_error($post_nav)) {
                wp_send_json_error($post_nav->get_error_message());
            }

            wp_send_json_success(['url'=> $url]);
        }
    }

    function getNavMenuSidebarTemplate() {
        //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'twbb' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }
        $nav_menu_info = \Tenweb_Builder\Modules\WebsiteNavigation\GetWPData::getNavMenuItems();
        $output = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::navMenuSidebarTemplate($nav_menu_info);
        wp_send_json_success($output);
    }

    /**
     * Returns the singleton instance of the class.
     *
     * Returns:
     * - WPMenuController: The singleton instance of the class.
     */
    public static function getInstance(){
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
