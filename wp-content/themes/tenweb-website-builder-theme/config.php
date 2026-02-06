<?php
define( 'TWBT_DEV', FALSE );
define( 'TWBT_DEBUG', FALSE );
define( '10WEB_BUILDER_THEME_PREFIX', 'twbt' );
define( 'TWBT_SLUG', 'tenweb-website-builder-theme' );
define( 'TWBT_TITLE', 'Builder Theme' );
define( 'TWBT_VERSION', wp_get_theme(TWBT_SLUG)->get('Version') );
/* DIRECTORIES */
define( 'TWBT_DIR', get_template_directory() );
/* URLS */
define( 'TWBT_URL', get_template_directory_uri() );

if ( class_exists( '\Tenweb_Manager\Helper' ) &&
    method_exists( '\Tenweb_Manager\Helper', 'get_company_name' ) &&
    strtolower( \Tenweb_Manager\Helper::get_company_name() ) !== '10web'  &&
    !defined( 'TENWEB_WHITE_LABEL') ){
    define( 'TENWEB_WHITE_LABEL', TRUE );
} else if( !defined( 'TENWEB_WHITE_LABEL') ){
    define( 'TENWEB_WHITE_LABEL', FALSE );
}
