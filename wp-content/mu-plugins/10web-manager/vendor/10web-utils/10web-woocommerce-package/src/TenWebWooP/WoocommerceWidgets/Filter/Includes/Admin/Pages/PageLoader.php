<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Admin\Pages;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Filter\FilterBuilder;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\Component;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\HookManager;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\TemplateLoader;

class PageLoader extends Component {

    private $filterId = null;

    private $filter = null;

    public function attachHooks(HookManager $hook_manager) {
        $hook_manager->addAction('wp_ajax_tww_get_popup', 'getFilterPopup');
        $hook_manager->addFilter('replace_editor', 'ReplaceEditor', 10, 2);
        $hook_manager->addAction('admin_enqueue_scripts', 'registerAssets');
        $hook_manager->addAction('wp_print_scripts', 'twwf_body_ga_scripts');
    }

    //Print Google Analytics script body
    public function twwf_body_ga_scripts() {
        if (is_admin()) {
            echo "<!-- Google Tag Manager -->
                <script class='pointerier'>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://metrics.10web.site/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer','GTM-P7NJPR5C');</script>
                <!-- End Google Tag Manager -->";

            echo '<!-- Google Tag Manager (noscript) -->
            <noscript><iframe class="pointerier" src="https://metrics.10web.site/ns.html?id=GTM-P7NJPR5C"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->';
        }
    }

    public function ReplaceEditor($replace, $post) {
        $FilterBuilder = new FilterBuilder($post->ID);
        $filter = $FilterBuilder->getFilter();
        $this->filter = $filter;

        if ($post->post_type === TWW_FILTER_POST_TYPE) {
            if (! get_current_screen()) {
                return true;
            }
            require_once ABSPATH . 'wp-admin/admin-header.php';
            $template_loader = new TemplateLoader();
            $template_data = array(
                'filter' => $filter,
                'post_status' => $post->post_status,
                'template_loader' => $template_loader,
            );
            $template_loader->render_template('popup.php', $template_data, dirname(__DIR__) . '/Views');

            return true;
        }

        return $replace;
    }

    public function registerAssets() {
        $fieldsList = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getFieldsList();
        global $post;
        $terms = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getTerms();
        $view_type = 'dashboard';

        if (isset($_GET['action']) && $_GET['action'] === 'tww_get_popup') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $view_type = 'elementor';
        }

        if ((isset($post->post_type) && $post->post_type === TWW_FILTER_POST_TYPE) || $view_type === 'elementor') {
            $delete_button_class = '';
            $analyticsData = '';

            if (isset($_GET['post_type']) && ($_GET['post_type'] === 'tww_filter')) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $delete_button_class = 'submitdelete';
                $analyticsData = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getAnalyticsData();
            }
            wp_register_style(
                'tww_filter-admin-style',
                TWW_PRODUCT_FILTER_URL . '/Includes/Admin/assets/css/admin.css',
                array(),
                TWW_PRODUCT_FILTER_VERSION
            );
            wp_register_script(
                'tww_filter-admin-script',
                TWW_PRODUCT_FILTER_URL . '/Includes/Admin/assets/js/admin.js',
                array(
                    'jquery', 'jquery_ui-script',
                ),
                TWW_PRODUCT_FILTER_VERSION,
                array('in_footer' => false)
            );

            wp_register_style(
                'jquery_ui-style',
                TWW_PRODUCT_FILTER_URL . '/Includes/assets/libraries/jquery-ui/jquery-ui.css',
                array(),
                TWW_PRODUCT_FILTER_VERSION
            );
            wp_register_script(
                'jquery_ui-script',
                TWW_PRODUCT_FILTER_URL . '/Includes/assets/libraries/jquery-ui/jquery-ui.js',
                array('jquery'),
                TWW_PRODUCT_FILTER_VERSION,
                array('in_footer' => false)
            );

            wp_register_script(
                'tww_select2-script',
                TWW_PRODUCT_FILTER_URL . '/Includes/assets/libraries/js/select2.min.js',
                array('jquery', 'tww_filter-admin-script'),
                TWW_PRODUCT_FILTER_VERSION,
                array('in_footer' => false)
            );

            wp_register_style(
                'tww_select2-style',
                TWW_PRODUCT_FILTER_URL . '/Includes/assets/libraries/css/select2.min.css',
                array(),
                TWW_PRODUCT_FILTER_VERSION
            );

            if (isset($this->filter) && is_array($this->filter->fields)) {
                $fields = $this->filter->fields;

                foreach ($fields as $key => $field) {
                    if (isset($field->variation, $field->options, $field->type)) {
                        if ($field->type !== 'ColorList' && ($field->variation === 'Category' || $field->variation === 'Attribute' || $field->variation === 'Tag')) {
                            $field->options = array();
                        }
                    }
                }
            } else {
                $fields = (object) array();
            }
            wp_localize_script('tww_filter-admin-script', 'tww_admin_vars', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ajaxnonce' => wp_create_nonce('tww_ajax_nonce'),
                'view_type' => $view_type,
                'terms' => $terms,
                'delete_button_class' => $delete_button_class,
                'analytics_data' => $analyticsData,
                'domain_id' => get_option('tenweb_domain_id'),
                'fields' => $fields,
                'fields_data' => $fieldsList,
            ));

            wp_enqueue_style('tww_filter-admin-style');
            wp_enqueue_style('jquery_ui-style');
            wp_enqueue_style('tww_select2-style');
            wp_enqueue_script('jquery_ui-script');
            wp_enqueue_script('tww_filter-admin-script');
            wp_enqueue_script('tww_select2-script');
        }
    }

    public function getFilterPopup() {
        if (isset($_GET['filter_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $filter_id = (int) sanitize_text_field($_GET['filter_id']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

            if ($filter_id > 0) {
                $this->filterId = $filter_id;
            }
        }

        $template_loader = new TemplateLoader();
        $template_data = array(
            'template_loader' => $template_loader,
            'popup' => 'elementor',
        );

        if (isset($this->filterId)) {
            $FilterBuilder = new FilterBuilder($this->filterId);
            $filter = $FilterBuilder->getFilter();
            $this->filter = $filter;
            $template_data['filter'] = $filter;
            $template_data['post_status'] = 'publish';
        }
        $this->registerAssets();

        wp_print_scripts('tww_select2-script');
        wp_print_styles('tww_select2-style');

        wp_print_styles('tww_filter-admin-style');
        wp_print_scripts('tww_filter-admin-script');
        $template_loader->render_template('popup.php', $template_data, dirname(__DIR__) . '/Views');
        die;
    }
}
