<?php
// Integrate submodule content
//phpcs:disable
function twbb_integrate_submodule_content() {
    $submodule_path = plugin_dir_path(__FILE__) . '/';

    // Include submodule files if they exist
    if (file_exists($submodule_path . 'builder.php')) {
        include_once $submodule_path . 'builder.php';
    }

    // Include Modules/Utils.php
    if (file_exists($submodule_path . 'Modules/Utils.php')) {
        include_once $submodule_path . 'Modules/Utils.php';
    }

}
//phpcs:enable
twbb_integrate_submodule_content();


include_once plugin_dir_path(__FILE__) . 'config.php';

if(twbb_check_plugin_requirements()) {
    add_action('plugins_loaded', 'twbb_plugins_loaded', 1);
    function twbb_plugins_loaded(){
        include_once TWBB_DIR . '/builder.php';
        \Tenweb_Builder\Builder::get_instance();
    }
}
register_activation_hook(__FILE__, 'twbb_activate');
function twbb_activate(){
    if( !twbb_check_plugin_requirements() ) {
        die("PHP or Wordpress version is not compatible with plugin.");
    }
    include_once TWBB_DIR . '/builder.php';
    Tenweb_Builder\Builder::install();
}

function twbb_check_plugin_requirements(){
    global $wp_version;
    $php_version = explode("-", PHP_VERSION);
    $php_version = $php_version[0];
    $result = (
        version_compare($wp_version, '4.7', ">=") &&
        version_compare($php_version, '5.4', ">=")
    );

    return $result;
}

add_filter('post_row_actions', 'template_list_row_actions', 10, 2);
add_action('pre_get_posts', 'twbb_filter_media_library_by_metadata', 99); //hide copilot chat uploads from media library

/* Change edit links */
function template_list_row_actions($actions, $post){
    // Check for your post type.
    if($post->post_type === "elementor_library") {
        unset($actions['view']);
    }

    return $actions;
}

function get_template_label_by_type($template_type){
    $document_types = \Elementor\Plugin::instance()->documents->get_document_types();
    if(isset($document_types[$template_type])) {
        $template_label = call_user_func([$document_types[$template_type], 'get_title']);
    } else {
        $template_label = ucwords(str_replace(['_', '-'], ' ', $template_type));
    }
    /**
     * Template label by template type.
     * Filters the template label by template type in the template library .
     *
     * @param string $template_label Template label.
     * @param string $template_type Template type.
     *
     * @since 2.0.0
     */
    $template_label = apply_filters('elementor/template-library/get_template_label_by_type', $template_label, $template_type);

    return $template_label;
}

function is_current_screen(){
    global $pagenow, $typenow;

    return 'edit.php' === $pagenow && 'elementor_library' === $typenow;
}

function get_current_tab_group($default = ''){
    $current_tabs_group = 'twbb_templates';
    if(!empty($_REQUEST['elementor_library_type'])) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $doc_type = \Elementor\Plugin::instance()->documents->get_document_type(sanitize_text_field( $_REQUEST['elementor_library_type'] ), '');//phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if($doc_type) {
            $current_tabs_group = $doc_type::get_property('admin_tab_group');
        }
    } elseif(!empty($_REQUEST['tabs_group'])) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $current_tabs_group = sanitize_text_field( $_REQUEST['tabs_group'] );//phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    return $current_tabs_group;
}

function get_library_title(){
    $title = '';
    if(is_current_screen()) {
        $current_tab_group = get_current_tab_group();
        if($current_tab_group) {
            $titles = [
                'library' => __('10Web Templates', 'tenweb-builder'),
                'twbb_templates' => __('10Web Templates', 'tenweb-builder'),
                'twbb_theme' => __('Theme Builder', 'tenweb-builder'),
                'popup' => __('Popups', 'tenweb-builder'),
            ];
            if(!empty($titles[$current_tab_group])) {
                $title = $titles[$current_tab_group];
            }
        }
    }

    return $title;
}

function twbb_filter_media_library_by_metadata(WP_Query $query) {
    if (is_admin() && $query->get('post_type') === 'attachment') {
        $meta_query = $query->get('meta_query'); // phpcs:ignore WordPressVIPMinimum.Hooks.PreGetPosts.PreGetPosts

        $added_query = [
            'key' => \Tenweb_Builder\Apps\ImageGenerationAI::HIDDEN_IMAGE_META_KEY,
            'compare' => 'NOT EXISTS',
        ];

        if (!is_array($meta_query)) {
            $meta_query = [];
        }

        if (!empty($meta_query)) {
            $meta_query = [
                'relation' => 'AND',
                $meta_query,
                $added_query,
            ];
        } else {
            $meta_query[] = $added_query;
        }

        $query->set('meta_query', $meta_query); // phpcs:ignore WordPressVIPMinimum.Hooks.PreGetPosts.PreGetPosts
    }
}



add_filter('pre_set_site_transient_update_plugins', 'twbb_check_for_update');

function twbb_check_for_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    // Your plugin slug and API URL
    $slug = 'tenweb-builder';

    // Fetch remote info
    $remote_data = \Tenweb_Builder\Modules\ai\TenWebApi::get_instance()->getPluginUpdateData($slug);
    $remote = [
        "version"=> $remote_data['version'] ?? '1.0.0', // Default version if not set
        "plugin_url"=> $remote_data['plugin_url'] ?? '',
        "plugin_slug"=> $slug,
        "sections"=> []
    ];
    if (!$remote || !isset($remote['version'])) return $transient;

    // Compare versions
    $local_version = get_plugin_data(ABSPATH . 'wp-content/plugins/' . $slug . '/' . $slug . '.php')['Version'];
    if (version_compare($remote['version'], $local_version, '>')) {
        // Prepare update info
        $plugin_basename = $slug . '/' . $slug . '.php';

        $transient->response[$plugin_basename] = (object) [
            'slug' => $slug,
            'new_version' => $remote['version'],
            'package' => $remote['plugin_url'],
            'url' => '',
        ];
    }
    return $transient;
}


add_filter('plugins_api', 'twbb_plugins_api', 10, 3);

function twbb_plugins_api($res, $action, $args) {
    if ($args->slug !== 'tenweb-builder') {
        return $res;
    }

    $remote_data = \Tenweb_Builder\Modules\ai\TenWebApi::get_instance()->getPluginUpdateData($args->slug);
    $remote = [
        "version"=> $remote_data['version'],
        "plugin_url"=> $remote_data['plugin_url'],
        "plugin_slug"=> $args->slug,
        "sections"=> []
    ];
    if (!$remote) return $res;

    $res = new stdClass();
    if( TWBB_RESELLER_MODE ) {
        $res->name = 'AI Website Builder';
        $res->slug = $args->slug;
        $res->new_version = $remote['version'];
        $res->author = '';
        $res->homepage = '';
        $res->sections = [
            'changelog' => '',
            // add other sections if needed
        ];
    } else {
        $res->name = '10web Builder';
        $res->slug = $args->slug;
        $res->new_version = $remote['version'];
        $res->author = '10Web';
        $res->homepage = 'https://10web.io/plugins/';
        $res->sections = [
            'changelog' => '',
            // add other sections if needed
        ];
    }
    return $res;
}
