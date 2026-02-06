<?php

namespace Tenweb_Builder\Apps;

class SVGUpload extends BaseApp
{
    protected static $instance = null;

    /**
    * Fixes uploads for 4.7.1 and 4.7.2 wp versions
    */
    //phpcs:ignore WordPressVIPMinimum.Hooks.AlwaysReturnInFilter.MissingReturnStatement
    public function allowSvgUploadWpFix($data, $file, $filename, $mimes){
	    if ( self:: visibilityCheck() ) {
		    global $wp_version;
		    if ( $wp_version !== '4.7.1' || $wp_version !== '4.7.2' ) {
			    return $data;
		    }

		    $filetype = wp_check_filetype( $filename, $mimes );

		    return [
			    'ext'             => $filetype[ 'ext' ],
			    'type'            => $filetype[ 'type' ],
			    'proper_filename' => $data[ 'proper_filename' ]
		    ];
	    }
    }

    /**
    * Allows to upload svg files
    */
    public function allowSvgUpload($mimes){
	    if ( self:: visibilityCheck() ) {
		    $mimes[ 'svg' ] = 'image/svg+xml';
	    }
        return $mimes;
    }

    public function sanitizeSvg($file) {

	    if ( $file[ 'type' ] !== 'image/svg+xml' ) {
		    return $file;
	    }

	    if ( ! class_exists( '\Elementor\Core\Utils\Svg\Svg_Sanitizer' ) ) {
		    $file[ 'error' ] = "Sorry, SVG sanitizer is not found";

		    return $file;
	    }

	    $sanitizer = new \Elementor\Core\Utils\Svg\Svg_Sanitizer();
	    if ( ! $sanitizer->sanitize_file( $file[ 'tmp_name' ] ) ) {
		    $file[ 'error' ] = "Sorry, this file couldn't be sanitized and wasn't uploaded";
	    }

	    return $file;
    }

    public static function getInstance(){
        if(self::$instance === null) {
          self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct(){
	    add_filter('wp_check_filetype_and_ext', [$this, 'allowSvgUploadWpFix'], 10, 4);
	    add_filter('upload_mimes', [$this, 'allowSvgUpload']); //phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.upload_mimes

	    // sanitize svg
	    add_filter('wp_handle_upload_prefilter', [$this, 'sanitizeSvg']);
    }

    private static function visibilityCheck()
    {
	    return apply_filters('twbb_svg_support', current_user_can('unfiltered_html'));
    }

}
