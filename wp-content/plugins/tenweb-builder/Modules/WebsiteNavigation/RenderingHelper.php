<?php

namespace Tenweb_Builder\Modules\WebsiteNavigation;

class RenderingHelper
{
    public static function addMenuItemSecondaryTooltip($title, $object_type, $type, $items = [], $page = 1, $add_parent_container = false) {
        $level = 'twbb-wn-secondary-level';
        ob_start();
        if( $add_parent_container === true ) {
        ?>
        <div class="twbb-wn-search-wrapper">
            <input type="text" name="twbb_wn_search" class="twbb-wn-search" placeholder="<?php esc_attr_e('Search', 'tenweb-builder');?>">
            <span class="twbb-wn-clear-search"></span>
        </div>
    <div class="twbb-wn-action-tooltip-items <?php esc_attr_e($level);?> twbb-wn-type-<?php esc_attr_e($object_type);?>">
        <div class="twbb-wn-search-noresult"><?php esc_html_e('No results found.','tenweb-builder');?></div>
        <?php
        }
        foreach ($items as $key => $item) {
            if( $key === 'exclude' ) continue;
            $id = '';
            $title = '';
            if( $type === 'post' ) {
                if( is_array($items['exclude']) && in_array($item->ID, $items['exclude'], true) ) continue;
                $id = $item->ID;
                $title = $item->post_title;
                $nav_post_type = 'post_type';
                //get post url
                $url = get_permalink($id);
            } else if( $type === 'taxonomy' ) {
                if( is_array($items['exclude']) && in_array($item->term_id, $items['exclude'], true) ) continue;
                $id = $item->term_id;
                $title = $item->name;
                $nav_post_type = 'taxonomy';
                //get term url
                $url = get_term_link($id);
            }?>
            <div class="twbb-wn-action-tooltip-item twbb-wn-flex-space-between"
            <?php self::dataAttrRenderer('type', $nav_post_type );?>
            <?php self::dataAttrRenderer('post_type', $object_type );?>
            <?php self::dataAttrRenderer('id', $id );?>
            <?php self::dataAttrRenderer('title', $title );?>
            <?php self::dataAttrRenderer('object', $object_type );?>
            <?php self::dataAttrRenderer('url', $url, true );?>>
            <span><?php esc_html_e($title);?></span><span class="twbb-wn-add-item-to-page"></span></div>
        <?php }
        if( $add_parent_container === 'true' ) {
        ?>
        </div>
        <?php
        }
        return ob_get_clean();
    }

    /**
     * Renders a navigation item for the website navigation sidebar in the 10Web Builder interface.
     *
     * This function outputs a formatted HTML block representing a navigation item such as a page or template.
     * It handles various navigation types (e.g., nav_menu, page), marks the current page as active,
     * adds relevant CSS classes and data attributes, and renders edit links or settings if available.
     *
     * @param array $item {
     *     Array of item data.
     *
     *     @type int    $id               Post ID of the item.
     *     @type string $title            Title of the item.
     *     @type string $url              URL of the item (optional).
     *     @type string $slug             Slug of the item (optional).
     *     @type string $status           Post status (e.g., 'publish', 'draft').
     *     @type string $post_type        Post type (e.g., 'page', 'elementor_library').
     *     @type string $type_label       Label describing the item type.
     *     @type string $nav_label        Label used in navigation context.
     *     @type string $nav_item_title   Title used in nav menu display.
     *     @type string $template_link    Link to edit the associated template.
     *     @type string $template_title   Title of the template.
     *     @type string $content_edit_link Link to edit the page content.
     * }
     * @param array $args {
     *     Optional. Array of additional arguments to control rendering.
     *
     *     @type string     $wn_type         Type of navigation context ('nav_menu', 'page', etc.).
     *     @type WP_Post    $nav_item        Original nav menu WP object (required for nav_menu).
     *     @type int        $depth           Menu depth level (for indentation).
     *     @type int        $current_page_id ID of the currently active page (for marking active state).
     * }
     *
     * @return string HTML output of the navigation item block.
     */
    public static function twbb_renderNavigationItem($item, $args = []) {
        $args = wp_parse_args($args, [
            'wn_type' => '',
            'nav_item' => null,
            'depth' => 0,
            'current_page_id' => 0,
        ]);
        $wn_type = $args['wn_type'];
        $nav_item = $args['nav_item'];
        $depth = $args['depth'];
        $current_page_id = $args['current_page_id'];

        //check if this is the editing page
        $item_class = '';
        $depth_class = '';
        if ( (int) $item['id'] === get_the_ID() || (int) $item['id'] === $current_page_id ) {
            $item_class = 'twbb-wn-item-active';
        }
        if( ( $item['post_type'] === 'page' && $wn_type === 'nav_menu' ) ||
            ( $item['status'] === 'publish' && $wn_type === 'page' ) ) {
            $item_class .= ' twbb-good-for-action';
        }
        if( $wn_type === 'nav_menu' ) {
            $depth_class = 'menu-item-depth-' . $depth;
            $item_type = $nav_item->type;
        } else if ( $wn_type === 'page' ) {
            $depth_class = 'menu-item-depth-0';
            $item_type = 'post_type';
        }
        if( !isset($item['nav_label']) ) {
            $item['nav_label'] = 'Page';
        }

        $home_page = '';
        if ((int)get_option('page_on_front') === $item['id']) {
            $home_page = 'twbb-wn-home-page';
        }
        $wn_type_title = $wn_type === 'nav_menu' ? $item['nav_item_title'] : $item['title'];
        $wn_type_menu_side_label = ucfirst( $item['nav_label'] );
        $wn_type_page_side_label = ucfirst( $item['status'] === 'publish' ? '' : 'draft' );

        $nav_menu_info = \Tenweb_Builder\Modules\WebsiteNavigation\GetWPData::getNavMenuItems();
        $item['nav_menu_status'] = in_array($item['id'], $nav_menu_info['nav_menu_item_ids'], true) ? 'in_menu' : 'not_in_menu';
        ob_start(); ?>
        <div class="twbb-website-nav-sidebar-item twbb-wn-item menu-item <?php echo esc_attr($item_class) . ' ' . esc_attr($depth_class);?> "
        <?php if (isset($item['id'])) self::dataAttrRenderer('id', $item['id']); ?>
        <?php if (isset($item['title'])) self::dataAttrRenderer('title', $item['title']); ?>
        <?php if (isset($item['url'])) self::dataAttrRenderer('url', $item['url'], true); ?>
        <?php if (isset($item['slug'])) self::dataAttrRenderer('slug', $item['slug']); ?>
        <?php if (isset($item['status'])) self::dataAttrRenderer('status', $item['status']); ?>
        <?php if (isset($item['post_type'])) self::dataAttrRenderer('object', $item['post_type']); ?>
        <?php if (isset($item_type)) self::dataAttrRenderer('type', $item_type); ?>
        <?php if (isset($item['type_label'])) self::dataAttrRenderer('type_label', $item['type_label']); ?>
        <?php if (isset($item['nav_label'])) self::dataAttrRenderer('nav_label', $item['nav_label']); ?>
        <?php if (isset($item['nav_menu_status'])) self::dataAttrRenderer('nav_menu_status', $item['nav_menu_status']); ?>
        <?php if (isset($item['nav_item_title'])) self::dataAttrRenderer('nav_item_title', $item['nav_item_title']); ?>
        <?php if (isset($item['template_link'])) self::dataAttrRenderer('template_link', $item['template_link'], true); ?>
        <?php if (isset($item['template_title'])) self::dataAttrRenderer('template_title', $item['template_title']); ?>
        <?php if (isset($item['content_edit_link'])) self::dataAttrRenderer('content_edit_link', $item['content_edit_link']); ?>>
        <div class="menu-item-handle">
            <div class="twbb-website-nav-sidebar-item__title <?php esc_attr_e($home_page);?>">
                <span class="twbb-wn-title"> <?php esc_html_e($wn_type_title);?></span>
                <span class="twbb-wn-status"><?php esc_html_e($wn_type_page_side_label);?></span>
                <span class="twbb-wn-item-info"><?php esc_html_e($wn_type_menu_side_label);?></span>
            </div>
        </div>
        <div class="twbb-website-nav-sidebar-item__actions">
            <?php
            if( !empty($item['content_edit_link']) && !empty($item['template_link'])) { ?>
            <div class="twbb-wn-action-edit twbb-wn-tooltip-parent">
                <div class="wn-action-tooltip">
                <a class="twbb-wn-tooltip-links twbb-wn-template_link" href="<?php echo esc_url($item['template_link']); ?>"
                   target="_blank"><?php esc_html_e('Edit ' . $item['template_title']  . ' template');?>
                </a>
                <a class="twbb-wn-tooltip-links twbb-wn-content_edit_link" href="<?php echo esc_url($item['content_edit_link']); ?>"
                   target="_blank"><?php echo $item['post_type'] === 'page' ? esc_html('Edit ' . $item['nav_label']) : esc_html('Edit ' . $item['nav_label'] . ' content'); ?>
                </a>
                </div>
            </div>
            <?php }
            //there is no way that template_edit_link will present and the content_edit_link not
            elseif( !empty($item['content_edit_link']) ) { ?>
                <a class="twbb-wn-action-edit twbb-tooltip-parent-container-item"  data-tooltip-text="<?php esc_attr_e('Edit', 'tenweb-builder');?>"
                   href="<?php echo esc_url($item['content_edit_link']); ?>"
                   target="_blank"></a>
            <?php }
            if( $wn_type !== 'elementor_library' ) { ?>
            <span class="twbb-wn-action-settings twbb-tooltip-parent-container-item" data-tooltip-text="<?php esc_attr_e('Settings', 'tenweb-builder');?>"></span>
            <?php
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo self::twbb_renderNavigationItemSettings($nav_item);
            } ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public static function navItemsFromObject($posts, $post_type, $current_page_id) {
        $items_info = array();
        foreach ($posts as $post) {
            $nav_label = $post->post_type;
            $id = $post->ID;
            $item_editing_links = self::getItemEditingLinks($post, $nav_label, $id);
            $template_link = $item_editing_links['template_link'];
            $template_title = $item_editing_links['template_title'];
            $content_edit_link = $item_editing_links['content_edit_link'];
            $items_info[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'url' => get_permalink($post->ID),
                'status' => $post->post_status,
                'post_type' => $post->post_type,
                'template_link' => $template_link,
                'template_title' => $template_title,
                'content_edit_link' => $content_edit_link,
                '_elementor_template_type' => $post->_elementor_template_type,
            );
        }



        if ( $post_type === 'elementor_library' ) {
            $template_titles = [
                'twbb_single'           => __( 'Single', 'tenweb-builder'),
                'twbb_single_post'      => __( 'Single Post', 'tenweb-builder'),
                'twbb_single_product'   => __( 'Single Product', 'tenweb-builder'),
                'twbb_archive'          => __( 'List', 'tenweb-builder'),
                'twbb_archive_posts'    => __( 'Posts list', 'tenweb-builder'),
                'twbb_archive_products' => __( 'Products list', 'tenweb-builder'),
                'twbb_slide'            => __( 'Slides', 'tenweb-builder'),
                'twbb_header'           => __( 'Header', 'tenweb-builder'),
                'twbb_footer'           => __( 'Footer', 'tenweb-builder'),
            ];

            $grouped_items = [];
            $extra_types = [];

            foreach ( $items_info as $post ) {
                $type = $post['_elementor_template_type'] ?? 'other';
                if ( array_key_exists( $type, $template_titles ) ) {
                    $grouped_items[ $type ][] = $post;
                } else {
                    $extra_types[ $type ][] = $post;
                }
            }

            $output = '';

            foreach ( $template_titles as $type => $title ) {
                if ( ! empty( $grouped_items[ $type ] ) ) {
                    $output .= '<div class="twbb-wn-template-group-title">' . esc_html( $title ) . '</div>';
                    foreach ( $grouped_items[ $type ] as $post ) {
                        $post['template_title'] = $post['title'];
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        $output .= \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItem( $post, ['wn_type' => $post_type, 'current_page_id' => $current_page_id] );
                    }
                }
            }

            /* phpcs:disable Squiz.PHP.CommentedOutCode.Found */
            /*TODO need to remove this part if no needed to show */
            // Render unknown (additional) types
/*            if ( ! empty( $extra_types ) ) {
                foreach ( $extra_types as $type => $posts ) {
                    $title = ucfirst( str_replace( '_', ' ', $type ) ); // Make readable label
                    $output .= '<div class="twbb-wn-template-group-title">' . esc_html( $title ) . '</div>';
                    foreach ( $posts as $post ) {
                        $post['template_title'] = $post['title'];
                        $post['template_link'] = $post['content_edit_link'];
                        unset($post['content_edit_link']);
                        $output .= \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItem( $post, ['wn_type' => $post_type, 'current_page_id' => $current_page_id] );
                    }
                }
            }*/
        }
        else {
            $output = '';
            foreach ($items_info as $post) {
                $post['nav_label'] = $post_type;
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                $output .= \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItem($post, ['wn_type' => $post_type, 'current_page_id' => $current_page_id]);
            }
        }
        return $output;
    }

    public static function twbb_reformattingNavItem($nav_item) {
        $nav_label = $nav_item->object;
        $item_title = get_the_title((int) $nav_item->object_id);
        //get slug of page by id
        $item_slug = get_post_field('post_name', (int) $nav_item->object_id);
        $status = get_post_status((int) $nav_item->object_id);
        $nav_label = $nav_item->object;
        $nav_item_id = $nav_item->object_id;
        $item_editing_links = self::getItemEditingLinks($nav_item, $nav_label, $nav_item_id);
        $template_link = $item_editing_links['template_link'];
        $template_title = $item_editing_links['template_title'];
        $content_edit_link = $item_editing_links['content_edit_link'];

        $id = (int) $nav_item->object_id;
        if( !in_array($nav_label, ['page', 'post', 'product', 'custom'], true) ) {
            $nav_label = $nav_item->type_label;
            //get term name by id
            $item_title_term = get_term((int) $nav_item->object_id);
            $item_title = isset($item_title_term->name) ? $item_title_term->name : '';
            $item_slug_term = get_term((int) $nav_item->object_id);
            $item_slug = isset($item_slug_term->slug) ? $item_slug_term->slug : '';
        }
        $item_object = [
            'id' => $id,
            'title' => $item_title,
            'slug' => $item_slug,
            'url' => $nav_item->url,
            'status' => $status,
            'post_type' => $nav_item->object,
            'type_label' => $nav_item->type_label,
            'nav_label' => $nav_label,
            'nav_item_title' => $nav_item->title,
            'template_link' => $template_link,
            'template_title' => $template_title,
            'content_edit_link' => $content_edit_link,
        ];
        return $item_object;
    }

    public static function getItemEditingLinks($nav_item,$nav_label, $id) {
        $template_link = '';
        $template_title = '';
        $content_edit_link = '';
        if( $nav_label === 'product' ) {
            $template_id = \Tenweb_Builder\Condition::get_instance()->get_product_template((int) $id);
            if( !TENWEB_WHITE_LABEL ) {
                $domain_id = get_option(TENWEB_PREFIX . '_domain_id');
                $content_edit_link = TENWEB_DASHBOARD . '/websites/'. $domain_id . '/ecommerce/products/edit-product/' . $id;
            } else {
                $content_edit_link = get_edit_post_link($id);
            }
        } else if( $nav_label === 'post' ) {
            $template_id = \Tenweb_Builder\Condition::get_instance()->get_post_type_template((int) $id,'singular', 'twbb_single');
            $content_edit_link = get_edit_post_link($id);
        } else if( $nav_item->type === 'taxonomy') {
            //check if $nav_label contains 'product'
            if( strpos($nav_label, 'product') !== false ) {
                //template_type argument should be changed to twbb_archive_products after
                $template_id = \Tenweb_Builder\Condition::get_instance()->get_post_type_template((int) $id,'archive', 'twbb_archive_products');
                if ( !$template_id ) {
                    $template_id = \Tenweb_Builder\Condition::get_instance()->get_post_type_template((int) $id,'archive', 'twbb_archive');
                }
            } else {
                $template_id = \Tenweb_Builder\Condition::get_instance()->get_post_type_template((int) $id,'archive', 'twbb_archive');
            }

        } else if( $nav_label === 'page' ) {
            // check if page is edited with Elementor
            if( \Elementor\Plugin::instance()->documents->get( (int) $id ) &&
                \Elementor\Plugin::instance()->documents->get( (int) $id )->is_built_with_elementor() ) {
                $content_edit_link = admin_url( 'post.php?post=' . $id . '&action=elementor' );
            } else {
                $content_edit_link = get_edit_post_link($id);
            }
        } else if( $nav_label === 'elementor_library' ) {
            $content_edit_link = admin_url( 'post.php?post=' . $id . '&action=elementor' );
        }
        if( !empty( $template_id ) ) {
            $template_link = admin_url( 'post.php?post=' . $template_id . '&action=elementor' );
            if( $nav_label === 'product' ) {
                $template_link = add_query_arg( 'twbb_preview_id', $id, $template_link );
            }
            $template_title = get_the_title($template_id);
        }
        $item_editing_links = [
            'template_link' => $template_link,
            'template_title' => $template_title,
            'content_edit_link' => $content_edit_link,
        ];
        return $item_editing_links;
    }

    public static function twbb_renderNavigationItemSettings($item_object)
    {
        if( empty($item_object) ) {
            $item_object = new class {
                public $db_id = '';
                public $object_id = '';
                public $object = '';
                public $menu_item_parent = '';
                public $menu_order = '';
                public $type = '';
            };
        }
        $db_id = self::twbb_checkValue($item_object->db_id);
        ob_start();?>
        <div class="menu-item-settings wp-clearfix" id="menu-item-settings-<?php esc_attr_e($db_id);?>">
            <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php esc_attr_e($db_id);?>]"
            value="<?php esc_attr_e(self::twbb_checkValue($item_object->db_id));?>">
            <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php esc_attr_e($db_id);?>]"
            value="<?php esc_attr_e(self::twbb_checkValue($item_object->object_id));?> ">
            <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php esc_attr_e($db_id);?>]"
            value="<?php esc_attr_e(self::twbb_checkValue($item_object->object));?>">
            <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php esc_attr_e($db_id);?>]"
            value="<?php esc_attr_e(self::twbb_checkValue($item_object->menu_item_parent));?>">
            <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php esc_attr_e($db_id);?>]"
            value="<?php esc_attr_e(self::twbb_checkValue($item_object->menu_order));?>">
            <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php esc_attr_e($db_id);?>]"
            value="<?php esc_attr_e(self::twbb_checkValue($item_object->type));?>">
        </div>
        <div class="menu-item-transport"></div>
        <?php return ob_get_clean();
    }

//function for checking whether the value is set
    public static function twbb_checkValue($value) {
        return $value ?? '';
    }

    public static function twbb_rederAddMenuItemTooltip($title, $type, $items = []) {
        $level = 'twbb-wn-secondary-level';
        if( $type === 'all_types' ) {
            $level = 'twbb-wn-main-level';
        }
        ob_start(); ?>
        <div class="twbb-wn-action-tooltip-title-container">
            <div class="twbb-wn-action-tooltip-title">
                <?php esc_html_e($title,'tenweb-builder');?>
            </div>
        </div>
        <div class="twbb-wn-action-tooltip-items <?php esc_attr_e($level);?> twbb-wn-type-<?php esc_attr_e($type);?>">
        <?php foreach ($items as $item) {
            $item_availability = '';
            if( !$item['available'] ) {
                $item_availability = 'twbb-wn-item-not-available';
            }
            ?>
            <div class="twbb-wn-action-tooltip-item <?php esc_attr_e($item_availability);?>" data-type="<?php esc_attr_e($item['type']);?>" data-post-type="<?php esc_attr_e($item['post_type']);?>">
                <?php esc_html_e($item['title']);?>
            </div>
        <?php } ?>
        </div>

        <?php return ob_get_clean();
    }

    public static function dataAttrRenderer($data, $value, $url = false) {
        //check if value is not empty
        if( !empty($value) ) {
            if( $url ) {
                $output = ' data-' . $data . '="' . esc_url($value) . '"';
            } else {
                $output = ' data-' . $data . '="' . esc_attr($value) . '"';
            }
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $output;
        }
        echo '';
    }

    public static function navMenuSidebarTemplate($nav_menu_info) {

        $pages_info = \Tenweb_Builder\Modules\WebsiteNavigation\GetWPData::filteredPagesList();
        $nav_menu_items = $nav_menu_info['nav_menu_items'];
        $nav_menu_id = $nav_menu_info['nav_menu_id'];
        $page_where_is_menu = $nav_menu_info['page_where_is_menu'];
        $nav_widget_id = $nav_menu_info['nav_widget_id'];
        $current_page_id = $nav_menu_info['current_page_id'] ?? 0;
        if( !empty($nav_menu_id) ) {
            $sortable_id = 'nav_menu_items';
        } else {
            $sortable_id = '';
        }
        ob_start(); ?>

        <div class="twbb-website-nav-sidebar-container twbb-website-nav-sidebar-main twbb-animated-sidebar-hide"
             data-page_where_is_menu="<?php esc_attr_e($page_where_is_menu);?>"
             data-current-page_id="<?php esc_attr_e($current_page_id);?>"
             data-nav_widget_id="<?php esc_attr_e($nav_widget_id);?>">
            <span class="twbb-tooltip-parent-container">
                <span class="twbb-tooltip"></span>
             </span>
            <div class="twbb-website-nav-sidebar-header">
                <span class="twbb-website-nav-sidebar-title"><?php esc_html_e( 'Website structure', 'tenweb-builder'); ?></span>
                <span class="twbb-website-nav-sidebar-desc"><?php esc_html_e( 'All navigation & page changes save automatically.', 'tenweb-builder'); ?></span>
                <span class="twbb-website-nav-sidebar-header-close twbb-tooltip-parent-container-item"
                      onclick="twbb_animate_sidebar('close', jQuery('.twbb-website-nav-sidebar-main'), 380, 'twbb-website-navigation-sidebar-opened', twbb_closeWebsiteNavigation);" data-tooltip-text="<?php esc_attr_e('Close', 'tenweb-builder');?>">
                </span>
            </div>
            <div class="twbb-website-nav-sidebar-content">
                <div class="twbb-website-nav-sidebar-navigation-container">
                    <div class="twbb-website-nav-sidebar-navigation-header twbb-wn-type-header">
                        <div class="twbb-website-nav-sidebar-navigation-title">
                            <?php esc_html_e( 'Navigation menu', 'tenweb-builder'); ?>
                            <span class="twbb-saved-label">
                                <i class="fas fa-check"></i>
                                <?php esc_html_e('Saved','tenweb-builder');?>
                            </span>
                        </div>
                        <div class="twbb-wn-add-item wn-add-menu-item twbb-wn-tooltip-parent  twbb-tooltip-parent-container-item <?php empty($nav_menu_items) ? esc_attr_e('twbb-wn-not-visible') : esc_attr_e('');?>"
                             data-tooltip-text="<?php esc_attr_e('Add new item', 'tenweb-builder');?>"></div>
                    </div>

                    <?php
                    if( $nav_menu_id && !empty($nav_menu_items) ) {
                        $data_nav_id = 'data-nav_id="' . esc_attr($nav_menu_id) . '"';
                        $class = 'twbb-website-nav-sidebar-nav-menus-items twbb-website-nav-sidebar-items twbb_connectedSortable';
                        $args = [
                            'items_wrap'      => '<div id="'. $sortable_id .'" class="%2$s"' . $data_nav_id . '>%3$s</div>',
                            'container'       => '',
                            'container_id'    => '',
                            'container_class' => '',
                            'menu'         	  => $nav_menu_id,
                            'menu_class'      => $class,
                            'depth'           => 2,
                            'echo'            => true,
                            'fallback_cb'     => 'wp_page_menu',
                            'walker'          => (class_exists('\Tenweb_Builder\Modules\WebsiteNavigation\MenuWalker') ? new \Tenweb_Builder\Modules\WebsiteNavigation\MenuWalker() : '' )
                        ];

                        // WP 6.1 submenu issue
                        if(version_compare(get_bloginfo('version'), '6.1', '>=')){
                            unset($args['depth']);
                        }

                        wp_nav_menu($args);
                    }
                    else if( $nav_menu_id ){  ?>
                        <div class="twbb-website-nav-sidebar-nav-menus-items twbb-website-nav-sidebar-items twbb_connectedSortable"
                             id="<?php esc_attr_e($sortable_id); ?>" data-nav_id="<?php esc_attr_e($nav_menu_id); ?>">
                            <div class="twbb-wn-button twbb-wn-add-menu-item twbb-wn-bordered twbb-wn-add-menu-item-blue-button">
                                <?php esc_html_e('Add Menu Item', 'tenweb-builder'); ?>
                                <div class="wn-add-menu-item twbb-wn-tooltip-parent twbb-empty-nav-tooltip-container"></div>
                            </div>
                        </div>
                    <?php } else {
                        //the case where no Menu is existing in page or in all website ?>
                        <div class="twbb-website-nav-sidebar-nav-menus-items twbb-website-nav-sidebar-items">
                            <a class="twbb-wn-button twbb-wn-add-menu-item twbb-wn-bordered"
                               href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" target="_blank">
                                <?php esc_html_e('Create Menu', 'tenweb-builder'); ?>
                            </a>
                        </div>
                        <?php
                    }
                    ?>

                </div>
                <div class="twbb-website-nav-sidebar-pages-container" data-post-type="page">
                    <div class="twbb-website-nav-sidebar-pages-header twbb-wn-type-header">
                        <div class="twbb-website-nav-sidebar-pages-title">
                            <?php esc_html_e( 'Pages', 'tenweb-builder');
                            //check if there is no pages
                            if (!empty($pages_info)) {
                                ?>
                                <div class="twbb-wn-manage-trash">
                                    <span class="wn-menu-icon"></span>
                                    <div class="twbb-wn-manage-trash-button">
                                        <?php esc_html_e('Manage trash', 'tenweb-builder'); ?>
                                    </div>

                                </div>
                                <span class="twbb-saved-label">
                                    <i class="fas fa-check"></i>
                                    <?php esc_html_e('Saved','tenweb-builder');?>
                                </span>
                                <?php
                            }
                            ?>
                        </div>
                        <?php if( !empty($pages_info) ) { ?>
                            <div class="twbb-wn-add-item wn-add-page-item twbb-wn-tooltip-parent twbb-tooltip-parent-container-item"
                                 data-tooltip-text="<?php esc_attr_e('Add new page', 'tenweb-builder');?>">
                                <div class="wn-action-tooltip">
                                    <div class="wn-action-tooltip-container">
                                        <div class="twbb-wn-add-blank-page">
                                            <?php esc_html_e('Add a Blank Page', 'tenweb-builder'); ?>
                                            <p class="twbb-wn-button-description"><?php esc_html_e('Start with a blank page', 'tenweb-builder');?></p>
                                        </div>
                                        <?php if( !TENWEB_WHITE_LABEL ) { ?>
                                            <a class="twbb-wn-generate-page"
                                               href="<?php echo esc_url( TENWEB_DASHBOARD . '/websites/' . get_option('tenweb_domain_id') . '/generate-page/'); ?>"
                                               target="_blank"> <?php esc_html_e('Generate a New Page with AI', 'tenweb-builder'); ?>
                                                <p class="twbb-wn-button-description"><?php esc_html_e('Describe your page, and AI will design it', 'tenweb-builder');?></p>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                    if( !empty($pages_info) ) {
                        $sortable_id = 'pages_items';
                    } else {
                        $sortable_id = '';
                    }
                    ?>
                    <div class="twbb-website-nav-sidebar-pages-items twbb-website-nav-sidebar-items twbb_connectedSortable"
                         id="<?php esc_attr_e($sortable_id); ?>">
                        <?php
                        if (!empty($pages_info)) {
                            foreach ($pages_info as $page_info) {
                                $page_info['nav_label'] = 'Page';
                                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                echo  \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItem($page_info, ['wn_type' => 'page']);
                            }
                        }
                        else { ?>
                            <div class="twbb-wn-button twbb-wn-add-blank-page twbb-wn-bordered">
                                <?php esc_html_e('Add a Blank Page', 'tenweb-builder'); ?>
                            </div>
                            <?php if( !TENWEB_WHITE_LABEL ) { ?>
                                <a class="twbb-wn-button twbb-wn-bordered twbb-wn-generate-page"
                                   href="<?php echo esc_url( TENWEB_DASHBOARD . '/websites/' . get_option('tenweb_domain_id') . '/generate-page/'); ?>"
                                   target="_blank"> <?php esc_html_e('Generate a New Page with AI', 'tenweb-builder'); ?>
                                </a>
                            <?php } ?>
                        <?php }
                        ?>
                    </div>
                </div>
                <div class="twbb-website-nav-sidebar-other-items-container">
                    <?php
                    //check if there is posts available in website
                    $post_counts = wp_count_posts();
                    if( ( isset( $post_counts->publish ) && $post_counts->publish > 0 ) || ( isset($post_counts->draft) && $post_counts->draft > 0 ) ){ ?>
                        <div class="twbb-website-nav-sidebar-other-item twbb-with-bottom-border" data-type="post" data-post-type="post" data-type-title="<?php esc_attr_e('Posts','tenweb-builder');?>">
                        <?php esc_html_e('Posts', 'tenweb-builder'); ?></div>
                    <?php }
                    //check if there is products available in website
                    if( is_plugin_active('woocommerce/woocommerce.php') &&
                        ( ( isset( wp_count_posts('product')->publish ) && wp_count_posts('product')->publish )
                            || ( isset( wp_count_posts('product')->draft ) && wp_count_posts('product')->draft ) ) ) { ?>
                        <div class="twbb-website-nav-sidebar-other-item twbb-with-bottom-border" data-type="post" data-post-type="product" data-type-title="<?php esc_attr_e('Products','tenweb-builder');?>">
                            <?php esc_html_e('Products', 'tenweb-builder'); ?></div>
                    <?php } ?>
                    <div class="twbb-website-nav-sidebar-other-item" data-type="post" data-post-type="elementor_library" data-other-type="templates" data-type-title="<?php esc_attr_e('Templates','tenweb-builder');?>">
                        <?php esc_html_e('Templates', 'tenweb-builder'); ?></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
