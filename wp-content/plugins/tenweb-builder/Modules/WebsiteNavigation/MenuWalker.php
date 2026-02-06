<?php

namespace Tenweb_Builder\Modules\WebsiteNavigation;

class MenuWalker extends \Walker_Nav_Menu {

    public $menu_Settings;
    /**
     * Starts the list before the elements are added.
     *
     * @see Walker::start_lvl()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     */
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent  = str_repeat( "\t", $depth );
        $output .= "\n$indent\n";
    }
    /**
     * Ends the list of after the elements are added.
     *
     * @see Walker::end_lvl()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     */
    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent  = str_repeat( "\t", $depth );
        $output .= "$indent\n";
    }
    /**
     * Start the element output.
     *
     * @see Walker::start_el()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item   Menu item data object.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     * @param int    $id     Current item ID.
     */
    public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {
        $item_object = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_reformattingNavItem($data_object);
        $args = [
            'wn_type' => 'nav_menu',
            'nav_item' => $data_object,
            'depth' => $depth,
        ];
        $item_output = \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_renderNavigationItem($item_object, $args);
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $data_object, $depth, $args );
    }
    /**
     * Ends the element output, if needed.
     *
     * @see Walker::end_el()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item   Page data object. Not used.
     * @param int    $depth  Depth of page. Not Used.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     */
    public function end_el( &$output, $data_object, $depth = 0, $args = array() ) {
    }
}
