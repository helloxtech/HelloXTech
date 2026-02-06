<?php

namespace Tenweb_Builder\Apps;

class WebsiteNavigation extends BaseApp
{
    protected static $instance = null;

    public static function getInstance(){
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();
        $this->process();
    }

    public function setTemplates() {
        require_once(TWBB_DIR . '/Apps/WebsiteNavigation/templates/templates.php');
        require_once(TWBB_DIR . '/Apps/WebsiteNavigation/templates/error_templates.php');
    }

    public function enqueueEditorScripts() {
        $twbb_wn_nonce = wp_create_nonce('twbb');
        wp_enqueue_script('jquery-ui-sortable');
        if ( TWBB_DEV === TRUE ) {
            wp_enqueue_script(
                'website_navigation-controller-script',
                TWBB_URL . '/Apps/WebsiteNavigation/assets/script/WPMenuController.js',
                ['jquery', 'jquery-ui-sortable'],
                TWBB_VERSION,
                TRUE
            );
            wp_enqueue_script(
                'website_navigation-sortable-script',
                TWBB_URL . '/Apps/WebsiteNavigation/assets/script/WebsiteNavigationSortable.js',
                ['jquery', 'jquery-ui-sortable','website_navigation-controller-script'],
                TWBB_VERSION,
                TRUE
            );
            wp_enqueue_script(
                'website_navigation-script',
                TWBB_URL . '/Apps/WebsiteNavigation/assets/script/website_navigation.js',
                [
                    'jquery',
                    'elementor-editor',
                    'twbb-editor-scripts-v2',
                    'jquery-ui-sortable',
                    'website_navigation-controller-script',
                    'website_navigation-sortable-script',
                ],
                TWBB_VERSION,
                TRUE
            );
            wp_enqueue_script(
                'website_navigation-inner-settings-script',
                TWBB_URL . '/Apps/WebsiteNavigation/assets/script/WebsiteNavigationInnerSettings.js',
                ['jquery', 'website_navigation-script'],
                TWBB_VERSION,
                TRUE
            );
        } else {
            wp_enqueue_script(
                'website_navigation-script',
                TWBB_URL . '/Apps/WebsiteNavigation/assets/script/website_navigation.min.js',
                ['jquery', 'elementor-editor', 'twbb-editor-scripts-v2','jquery-ui-sortable'],
                TWBB_VERSION,
                TRUE
            );
        }
        wp_localize_script(
            'website_navigation-script',
            'twbb_website_nav',
            array(
                'nonce' => $twbb_wn_nonce,
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'auto_added_menus' => \Tenweb_Builder\Modules\Helper::is_menu_auto_add_enabled(),
                'home_edit_url' => \Tenweb_Builder\Modules\Helper::get_homepage_edit_url(),
            )
        );
    }

    public function enqueueEditorStyles() {
        wp_register_style('twbb-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700,800&display=swap');
        if ( TWBB_DEV === TRUE ) {
            wp_enqueue_style(
                'website-nav-editor-style',
                TWBB_URL . '/Apps/WebsiteNavigation/assets/style/website_navigation.css',
                ['twbb-open-sans'],
                TWBB_VERSION
            );
        } else {
            //custom-select-css is concated to the section_generation_editor.min.css css file
            wp_enqueue_style(
                'website-nav-editor-style',
                TWBB_URL . '/Apps/WebsiteNavigation/assets/style/website_navigation.min.css',
                ['twbb-open-sans'],
                TWBB_VERSION
            );
        }
    }

    private function process()
    {
        if ( self::visibilityCheck() ) {
            $this->init();
        }
    }

    private static function visibilityCheck(){
        if ( is_admin() && get_option('elementor_experiment-website_navigation') !== 'inactive') {
            return true;
        }
        return false;
    }

    private function init() {
        \Tenweb_Builder\Modules\WebsiteNavigation\WPMenuController::getInstance();
        $this->addActions();
    }

    private function addActions() {
        add_action( 'elementor/editor/v2/scripts/enqueue/after', array( $this, 'enqueueEditorScripts' ) , 13);
        add_action( 'elementor/editor/v2/styles/enqueue', array( $this, 'enqueueEditorStyles' ), 13 );
        add_action( 'elementor/editor/footer', array($this, 'setTemplates' ) );
    }

}
