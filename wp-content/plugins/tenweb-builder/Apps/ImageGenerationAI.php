<?php
namespace Tenweb_Builder\Apps;

class ImageGenerationAI {

    public const HIDDEN_IMAGE_META_KEY = '_twbb_hidden_attachment';
    private $image_styles;
    private $image_ratio;
    private $image_resolution;

    /* TODO should be changed after unlimited cancel */
    private $available_credits = 100000;
    private $images_plan_limit = 0;
    private $resetDate = '';
    private $limitations = [];

    protected static $instance = null;

    public static function getInstance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        if ( self::visibilityCheck() ) {
            $this->set_defaults();

            add_action('elementor/editor/before_enqueue_scripts', array($this, 'enqueue_editor_scripts'));
            add_action('elementor/editor/footer', array($this, 'set_templates'));

            add_action('wp_ajax_twbb_use_image', array($this, 'use_image'));
            add_action('wp_ajax_twbb_upload_image', array($this, 'upload_image'));
        }
    }

    private static function visibilityCheck(){
        return true;
    }

    /**
     * Set default values for image generation
     */
    public function set_defaults() {
        $this->image_styles = [
            [ "value" => "anime", "title" => "Anime", "img" => "anime.webp" ],
            [ "value" => "photographic", "title" => "Photographic", "img" => "photographic.webp" ],
            [ "value" => "comic-book", "title" => "Comic Book", "img" => "comic_book.webp" ],
            [ "value" => "fantasy-art", "title" => "Fantasy art", "img" => "fantasy_art.webp" ],
            [ "value" => "analog-film", "title" => "Analog film", "img" => "analog_film.webp" ],
            [ "value" => "neon-punk", "title" => "Neon punk", "img" => "neon_punk.webp"],
            [ "value" => "isometric", "title" => "Isometric", "img" => "isometric.webp" ],
            [ "value" => "low-poly", "title" => "Low poly", "img" => "low_poly.webp" ],
            [ "value" => "origami", "title" => "Origami", "img" => "origami.webp"],
            [ "value" => "line-art", "title" => "Line art", "img" => "line_art.webp" ],
            [ "value" => "modeling-compound", "title" => "Craft clay", "img" => "craft_clay.webp" ],
            [ "value" => "cinematic", "title" => "Cinematic", "img" => "cinematic.webp" ],
            [ "value" => "3D-model", "title" => "3D model", "img" => "3D_model.webp" ],
            [ "value" => "pixel-art", "title" => "Pixel art", "img" => "pixel_art.webp" ],
        ];

        $this->image_ratio = [
            "Landscape (4:3)",
            "Landscape (16:9)",
            "Ultrawide (21:9)",
            "Landscape (3:2)",
            "Square (1:1)",
            "Portrait (2:3)",
            "Portrait (9:16)"
        ];

        $this->image_resolution = ["4x"];
    }


    /**
     * Use image ajax action
    */
    public function use_image() {
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
        if ( !wp_verify_nonce( $nonce, 'twbb_img' ) ) {
            wp_send_json_error("invalid_nonce");
        }
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $image_url = isset( $_POST['image'] ) ? sanitize_url( $_POST['image'] ) : '';
        $id = \Tenweb_Builder\Modules\Helper::insertAttachmentFromUrl( $image_url );
        if( $id ) {
            wp_send_json_success( array( 'id' => $id, 'url' => wp_get_attachment_url( $id ) ) );
        }
        wp_send_json_error("Something went wrong, please try again.");
    }

    /**
     * Handles multiple image uploads via AJAX, processes up to 3 images,
     * and returns their attachment IDs and URLs.
     */
    public function upload_image() {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'twbb_img')) {
            wp_send_json_error("invalid_nonce");
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        if (!isset($_FILES['upload_files'])) {
            wp_send_json_error("No files uploaded.");
        }

        $uploaded_files = $_FILES['upload_files'];  //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $uploaded_urls = [];
        $allowed_types = ['image/jpeg', 'image/png']; // Allowed MIME types
        $max_size = 2 * 1024 * 1024; // Max size: 2MB

        // Limit to a maximum of 3 images
        $files_to_process = array_slice($uploaded_files['name'], 0, 3);

        foreach ($files_to_process as $index => $name) {
            if ($uploaded_files['error'][$index] === 0) {
                $file = [
                    'name'     => sanitize_file_name($name), // Sanitize filename
                    'type'     => $uploaded_files['type'][$index],
                    'tmp_name' => $uploaded_files['tmp_name'][$index],
                    'error'    => $uploaded_files['error'][$index],
                    'size'     => $uploaded_files['size'][$index]
                ];

                // Validate MIME type
                $file_type = mime_content_type($file['tmp_name']);
                if (!in_array($file_type, $allowed_types, true)) {
                    continue; // Skip invalid file types
                }

                // Validate file size
                if ($file['size'] > $max_size) {
                    continue; // Skip oversized files
                }

                $upload_overrides = ['test_form' => false];
                $move_file = wp_handle_upload($file, $upload_overrides);

                if ($move_file && !isset($move_file['error'])) {
                    $attachment_id = $this->handle_image_upload($move_file, true);
                    if ($attachment_id) {
                        $uploaded_urls[] = [
                            'id'  => $attachment_id,
                            'url' => wp_get_attachment_url($attachment_id)
                        ];
                    }
                }
            }

            // Stop if 3 images have been successfully uploaded
            if (count($uploaded_urls) >= 3) {
                break;
            }
        }

        if (!empty($uploaded_urls)) {
            wp_send_json_success($uploaded_urls);
        }

        wp_send_json_error("Something went wrong, please try again.");
    }


    /**
     * Upload image from URL
     *
     * @param $image_url string url
     *
     * @return int
    */
    public function upload_file_by_url( $image_url ) {

        // it allows us to use download_url() and wp_handle_sideload() functions
        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        // download to temp dir
        $temp_file = download_url( $image_url );

        if( is_wp_error( $temp_file ) ) {
            return false;
        }

        $name = explode("?", basename( $image_url ));
        $name = $name[0];

        // move the temp file into the uploads directory
        $file = array(
            'name'     => $name,
            'type'     => mime_content_type( $temp_file ),
            'tmp_name' => $temp_file,
            'size'     => filesize( $temp_file ),
        );
        $sideload = wp_handle_sideload(
            $file,
            array(
                'test_form'   => false // no needs to check 'action' parameter
            )
        );
        return $this->handle_image_upload($sideload);
    }

    /**
     * Include HTML templates
    */
    public function set_templates() {
        require_once (TWBB_DIR . '/Apps/ImageGenerationAI/templates.php');
    }

    /**
     * Enqueue js/css files
    */
    public function enqueue_editor_scripts() {
        wp_enqueue_script( 'twbb-images-versions-js', TWBB_URL . '/Apps/ImageGenerationAI/assets/js/ImagesVersions.js', [ 'jquery' ], TWBB_VERSION, TRUE );
        wp_enqueue_script( 'twbb-images-ratio-js', TWBB_URL . '/Apps/ImageGenerationAI/assets/js/ImageRatio.js', [ 'jquery' ], TWBB_VERSION, TRUE );
        if( TWBB_DEV ) {
            $dep = [ 'jquery', 'twbb-ai-js', 'twbb-ai-main-js', 'twbb-images-versions-js', 'twbb-images-ratio-js' ];
        } else {
            $dep = [ 'jquery', 'twbb-ai-js', 'twbb-editor-scripts', 'twbb-images-versions-js', 'twbb-images-ratio-js' ];
        }
        wp_enqueue_script( 'twbb-image-generation-js', TWBB_URL . '/Apps/ImageGenerationAI/assets/js/image-generation.js', $dep, TWBB_VERSION, TRUE );
        wp_localize_script('twbb-image-generation-js', 'twbb_img', array(
            'ajaxnonce' => wp_create_nonce('twbb_img'),
            'limitations' => $this->limitations,
        ));
        wp_enqueue_style('twbb-image-generation-css', TWBB_URL . '/Apps/ImageGenerationAI/assets/css/image-generation.css', array(), TWBB_VERSION);
    }

    private function handle_image_upload(array $imageUploadData, $hidden = false)
    {
        if( ! empty( $imageUploadData[ 'error' ] ) ) {
            // you may return error message if you want
            return false;
        }

        // it is time to add our uploaded image into WordPress media library
        $attachment_id = wp_insert_attachment(
            array(
                'guid'           => $imageUploadData[ 'url' ],
                'post_mime_type' => $imageUploadData[ 'type' ],
                'post_title'     => basename( $imageUploadData[ 'file' ] ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $imageUploadData[ 'file' ]
        );

        if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
            return false;
        }

        // update medatata, regenerate image sizes
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata( $attachment_id, $imageUploadData[ 'file' ] )
        );

        if ($hidden) {
            update_post_meta($attachment_id, self::HIDDEN_IMAGE_META_KEY, '1');
        }

        return $attachment_id;
    }

}
