<?php
/**
 * Plugin Name: 10Web Builder
 * Description: 10Web Builder is an ultimate premium tool, based on Elementor,  to create websites with stunning design.
 * Plugin URI:  https://10web.io/wordpress-website-builder/
 * Author: 10Web
 * Version: 1.37.61
 * Author URI: https://10web.io/plugins/
 * Text Domain: tenweb-builder
 * License: GNU /GPLv3.0 http://www.gnu.org/licenses/gpl-3.0.html
 */

if(!defined('ABSPATH')) {
  exit;
}

//include common code from main file from package
if (file_exists(plugin_dir_path(__FILE__) . 'common-main.php')) {
    include_once plugin_dir_path(__FILE__) . 'common-main.php';
}

//Print Google Analytics script head
if( domain_not_pointed() ) {
    add_action('admin_print_scripts-widgets.php', 'twbb_head_ga_scripts');
    add_action('wp_enqueue_scripts', 'twbb_head_ga_scripts');
}
function twbb_head_ga_scripts() {
    if ( current_user_can('administrator') ) {
        echo "<!-- Google Tag Manager -->
                <script class='pointerier'>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://metrics.10web.site/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer','GTM-P7NJPR5C');</script>
                <!-- End Google Tag Manager -->";
    }
}

//Print Google Analytics script body
if( domain_not_pointed() ) {
    add_action('elementor/editor/after_enqueue_scripts', 'twbb_body_ga_scripts');
    add_action('elementor/frontend/after_enqueue_scripts', 'twbb_body_ga_scripts', 1);
}
function twbb_body_ga_scripts() {
    if ( current_user_can('administrator') ) {
        echo '<!-- Google Tag Manager (noscript) -->
            <noscript><iframe class="pointerier" src="https://metrics.10web.site/ns.html?id=GTM-P7NJPR5C"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->';
    }
}

function domain_not_pointed() {
    $domain = sanitize_text_field( $_SERVER['HTTP_HOST'] ?? '' );
    if ( ( defined("TENWEB_DASHBOARD") && (TENWEB_DASHBOARD === 'https://my.10web.io' ||
                TENWEB_DASHBOARD === 'https://testmy.10web.io' ||
                TENWEB_DASHBOARD === 'https://testmy1.10web.io') ) &&
        (strpos($domain, '.10web.club') !== false ||
            strpos($domain, '.10web.me') !== false ||
            strpos($domain, '.10web.site') !== false ||
            strpos($domain, '.10web.cloud') !== false) ) {
        return true;
    }
    return false;
}

/* Code is adding clarity script to head tag for coPilot recording */
$domain = $_SERVER['HTTP_HOST'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
if ( domain_not_pointed() && get_option('elementor_co_pilot_record') !== '') {
        add_action('elementor/editor/before_enqueue_scripts', 'twbb_record_scripts');
}
function twbb_record_scripts() {
    $domainId = get_site_option('tenweb_domain_id');

    echo '<script type="text/javascript">
            (function(c, l, a, r, i, t, y) {
                c[a] = c[a] || function() { (c[a].q = c[a].q || []).push(arguments) };
                t = l.createElement(r);
                t.async = 1;
                t.src = "https://www.clarity.ms/tag/" + i;
                y = l.getElementsByTagName(r)[0];
                y.parentNode.insertBefore(t, y);
            })(window, document, "clarity", "script", "oq64jpyvbv");
        
            // Ensure this line is included and executed
            var domainID = "' . esc_js($domainId) . '";
            clarity("set", "domainID", domainID);
        </script>';
}
