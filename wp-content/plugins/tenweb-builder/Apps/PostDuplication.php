<?php
namespace Tenweb_Builder\Apps;

class PostDuplication extends BaseApp
{
    protected static $instance = null;
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct(){
        add_action('rest_api_init', function () {
            register_rest_route('tenweb-builder/v1', '/duplicate', [
                'methods' => 'POST',
                'callback' => [$this, 'twbb_duplicate_post'],
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                }
            ]);
        });

        add_action('admin_enqueue_scripts', [$this, 'twbb_enqueue_scripts']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'twbb_enqueue_scripts']);
    }

    public function twbb_enqueue_scripts() {
        wp_enqueue_script(
            'twbb-post-duplicator',
            TWBB_URL . '/Apps/PostDuplication/assets/script.js',
            ['jquery'],
            TWBB_VERSION,
            true
        );

        wp_localize_script('twbb-post-duplicator', 'TwbbPostDuplicator', [
            'nonce'    => wp_create_nonce('wp_rest'),
            'rest_url' => rest_url('tenweb-builder/v1/duplicate'),
        ]);
    }

    public function twbb_duplicate_post( $request ) {
        $post_id  = intval( $request->get_param( 'post_id' ) );
        $url_type = sanitize_text_field( $request->get_param( 'url_type' ) ?: 'edit' );
        $post = get_post($post_id);

        if ( ! $post ) {
            return new WP_REST_Response( [ 'error' => 'Invalid post ID' ], 400 );
        }

        /*
        * if you don't want current user to be the new post author,
        * then change next couple of lines to this: $new_post_author = $post->post_author;
        */
        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
        /* new post data array */
        $args = array(
            'comment_status' => $post->comment_status,
            'ping_status' => $post->ping_status,
            'post_author' => $new_post_author,
            'post_content' => $post->post_content,
            'post_excerpt' => $post->post_excerpt,
            'post_parent' => $post->post_parent,
            'post_password' => $post->post_password,
            'post_status' => 'draft',
            'post_title' => $post->post_title . ' (Copy)',
            'post_name'  => sanitize_title($post->post_title . ' (Copy)'),
            'post_type' => $post->post_type,
            'to_ping' => $post->to_ping,
            'menu_order' => $post->menu_order,
        );
        /*
        * insert the post by wp_insert_post() function
        */
        $new_post_id = wp_insert_post($args);
        if(is_wp_error($new_post_id)){
            return new WP_REST_Response(['error' => 'Failed to duplicate post'], 500);
        }

        /*
        * get all current post terms ad set them to the new post draft
        */
        $taxonomies = array_map('sanitize_text_field',get_object_taxonomies($post->post_type));
        if (!empty($taxonomies) && is_array($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }
        }
        /*
        * duplicate all post meta
        */
        $post_meta_keys = get_post_custom_keys( $post_id );
        if( $post_meta_keys && is_array($post_meta_keys) ) {
            foreach ( $post_meta_keys as $meta_key ) {
                $meta_values = get_post_custom_values( $meta_key, $post_id );
                foreach ( $meta_values as $meta_value ) {
                    $meta_value = maybe_unserialize( $meta_value );
                    update_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
                }
            }
        }

        /**
         * Elementor compatibility fixes
         */
        if ( function_exists( '\Elementor\Plugin' ) && is_plugin_active( 'elementor/elementor.php' ) ) {
            try {
                $css = \Elementor\Core\Files\CSS\Post::create( $new_post_id );
                $css->update();
            } catch ( \Throwable $e ) {
                return new WP_REST_Response(['error' => 'Failed to duplicate post'], 500);
            }
        }

        // Generate the appropriate editor link
        if ( $url_type === 'elementor' ) {
            $new_post_url = admin_url( "post.php?post={$new_post_id}&action=elementor" );
        } else {
            $new_post_url = get_edit_post_link( $new_post_id, 'raw' );
        }

        return rest_ensure_response( [
            'success'       => true,
            'new_post_id'   => $new_post_id,
            'new_post_url'  => $new_post_url,
        ] );
    }
}
