<?php


namespace Tenweb_Manager;


class TenwebWhiteLabel
{
    private static $instance = null;
    private $white_labeled_plugins = array(
        'io-wd.php',
        'backup-wd.php',
        '10web-security.php',
        'tenweb_speed_optimizer.php',
        'seo-by-10web.php',
        'tenweb-builder.php',
    );

    private $hidden_sub_menus = array(
        'buwd_jobs'         => 'Backup',
        'wdseo_overview'    => 'SEO',
        'iowd_settings'     => 'Image Optimization',
        'two_settings_page' => 'Speed Optimization'
    );

    private $hidden_submenu_slugs;

    private $whitelabeled_menus = array(
        'Backup By 10Web',
        'Seo By 10Web',
        '10web speed optimizer',
        '10web Booster'
    );

    private $top_bar_menus = array(
        'wdseo-menu',
        'two_options'
    );

    private $company_name = '10Web';

    private $is_full_white_label = false;

    private $current_user_have_access;
    private $is_custom_whitelabel = false;


    private function __construct()
    {
        $this->company_name = Helper::get_company_name();


        $this->current_user_have_access = Helper::current_user_have_access();
        $this->is_full_white_label = Helper::is_full_white_label();
        $this->hidden_submenu_slugs = array_keys($this->hidden_sub_menus);
    }

    public function register_hooks()
    {
        if (strtolower($this->company_name) === '10web') {
            return;
        }

        add_action('pre_current_active_plugins', array($this, 'whiteLabelPluginsList'), 999999);
        add_action('admin_menu', array($this, 'whiteLabelSubMenus'), 999999);
        add_action('in_admin_header', array($this, 'whiteLabelPluginPages'), 999999);
        add_action('admin_menu', array($this, 'whiteLabelTopMenus'), 999999);
        add_action('admin_bar_menu', array($this, 'whiteLabelAdminBarMenus'), 999999);

    }

    public function whiteLabelPluginsList()
    {
        global $wp_list_table;

        $plugins = $wp_list_table->items;

        foreach ($plugins as $slug => $plugin) {
            $main_php = explode('/', $slug);

            if (is_array($main_php)) {
                $main_php = end($main_php);
            }

            if (in_array($main_php, $this->white_labeled_plugins)) {
                if($main_php == "backup-wd.php"){
                    unset($wp_list_table->items[$slug]);
                }else{
                    $name = str_ireplace('10web', $this->company_name, $plugin['Name']);
                    $Description = str_ireplace('10web', $this->company_name, $plugin['Description']);

                    $wp_list_table->items[$slug]['Name'] = $name;
                    $wp_list_table->items[$slug]['Description'] = $Description;
                    unset($wp_list_table->items[$slug]['Author']);
                    unset($wp_list_table->items[$slug]['AuthorURI']);
                    unset($wp_list_table->items[$slug]['AuthorName']);
                    unset($wp_list_table->items[$slug]['PluginURI']);
                    if (isset($wp_list_table->items[$slug]['slug'])) {
                        unset($wp_list_table->items[$slug]['slug']);
                    }
                }
            }

        }

    }

    public function whiteLabelSubMenus()
    {

        //Change speed optimizer plugin submenu
        global $submenu;
        foreach ($submenu['options-general.php'] as $key => $settings) {
            if (str_ireplace($this->whitelabeled_menus, '', $settings[0]) !== $settings[0]) {
                $name = str_ireplace('10web', $this->company_name, $settings[0]);
                $title = str_ireplace('10web', $this->company_name, $settings[0]);
                $submenu['options-general.php'][$key][0] = $name;
                $submenu['options-general.php'][$key][3] = $title;
            }
        }
        if (!$this->is_full_white_label || $this->current_user_have_access) {
            return;
        }

        foreach ($this->hidden_submenu_slugs as $hidden_submenu) {
            unset($submenu[$hidden_submenu]);
        }




    }

    public function whiteLabelPluginPages()
    {

        if (!$this->is_full_white_label || $this->current_user_have_access) {
            return;
        }

        $page = $_GET['page'] ?? '';

        if (in_array($page, $this->hidden_submenu_slugs, true)) {
            $plugin_title = $this->hidden_sub_menus[$page] ?? '';


            include_once TENWEB_DIR . '/views/whitelabel.php';
            exit;
        }

    }

    public function whiteLabelTopMenus()
    {
        global $menu;

        foreach ($menu as $key => $item) {
            if (str_ireplace($this->whitelabeled_menus, '', $item[0]) !== $item[0]) {
                $name = str_ireplace('10web', $this->company_name, $item[0]);
                $title = str_ireplace('10web', $this->company_name, $item[0]);
                $menu[$key][0] = $name;
                $menu[$key][3] = $title;
            }
        }


    }

    public function whiteLabelAdminBarMenus($admin_menu)
    {

        foreach ($this->top_bar_menus as $menu) {
            $wl_menu = $admin_menu->get_node($menu);

            if (empty($wl_menu)) {
                continue;
            }

            if ($this->is_full_white_label && !$this->current_user_have_access) {
                $admin_menu->remove_node($menu);
                continue;
            }

            $wl_menu->title = str_ireplace('10web', $this->company_name, $wl_menu->title);
            $admin_menu->add_node($wl_menu);

        }

    }


    public static function get_instance()
    {
        if (null == self::$instance) {

            self::$instance = new self;
        }

        return self::$instance;
    }


}