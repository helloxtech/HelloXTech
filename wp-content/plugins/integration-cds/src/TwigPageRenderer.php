<?php
/**
 * Copyright 2018-2025 AlexaCRM
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
 * OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace AlexaCRM\Nextgen;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Renders page content as a single twig block.
 */
class TwigPageRenderer {

    const META_KEY = 'icds_twig_page';
    const MODE_NONE = 'none';
    const MODE_CONTENT = 'content';
    const MODE_FULL = 'full';
    const TWIG_PRIORITY = 99;
    
    private $buffering = false;
    private $buffer_post_id = 0;
    private $buffer_level = 0;

    public function __construct() {
        // Register filters and actions
        add_filter( 'the_content', [ $this, 'renderContentAsTwig' ], self::TWIG_PRIORITY );
        add_filter( 'the_title', [ $this, 'renderTitleAsTwig' ], self::TWIG_PRIORITY, 2 );
        add_filter( 'document_title', [ $this, 'renderContentAsTwig' ], self::TWIG_PRIORITY, 1 );
        add_action( 'template_redirect', [ $this, 'startTwigBuffer' ], 0 );
        add_action( 'shutdown', [ $this, 'shutdownTwigBuffer' ], 0);
        add_action( 'add_meta_boxes', [ $this, 'addMetaBox' ] );
        add_action( 'save_post', [ $this, 'saveMeta' ] );
    }

    /**
     * Register the meta box for marking a page as Twig-enabled.
     */
    public function addMetaBox() {
        add_meta_box(
            'datapress_twig_flag',
            'Twig Page',
            [ $this, 'metaBoxHtml' ],
            'page',
            'side',
            'default'
        );
    }

    public function metaBoxHtml( $post ) {
        wp_nonce_field( 'datapress_twig_flag_nonce', 'datapress_twig_flag_nonce' );
        $mode = get_post_meta( $post->ID, self::META_KEY, true );
        ?>
        <p class="description">Choose how Twig should process this page.</p>
        <label for="datapress_twig_mode">Twig rendering mode</label>
        <select name="datapress_twig_mode" id="datapress_twig_mode">
            <option value="" <?php selected( $mode, '' ); ?>>No twig</option>
            <option value="<?php echo esc_attr( self::MODE_CONTENT ); ?>" <?php selected( $mode, self::MODE_CONTENT ); ?>>Page content and title</option>
            <option value="<?php echo esc_attr( self::MODE_FULL ); ?>" <?php selected( $mode, self::MODE_FULL ); ?>>Entire HTML</option>
        </select>
        <?php
    }

    public function saveMeta( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! isset( $_POST['datapress_twig_flag_nonce'] ) || ! wp_verify_nonce( $_POST['datapress_twig_flag_nonce'], 'datapress_twig_flag_nonce' ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $allowed = [ self::MODE_CONTENT, self::MODE_FULL ];
        $val = isset( $_POST['datapress_twig_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['datapress_twig_mode'] ) ) : '';
        if ( '' === $val ) {
            delete_post_meta( $post_id, self::META_KEY );
        } elseif ( in_array( $val, $allowed, true ) ) {
            update_post_meta( $post_id, self::META_KEY, $val );
        }
    }

    private function detexturizeTwigRegions( $text ) {
        return preg_replace_callback(
            '/({{.*?}}|{%.*?%})/s',
            function( $m ) {
                $decoded = html_entity_decode( $m[0], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                $decoded = str_replace(["‘", "’", "“", "”"], ["'", "'", '"', '"'], $decoded);
                return $decoded;
            },
            $text
        );
    }

    private function renderAsTwig( $string ) {
        $template = $this->detexturizeTwigRegions( $string );
        return TwigProvider::instance()->renderString( $template );
    }


    public function renderTitleAsTwig( $title, $post_id = null ) {
        if ( ! $post_id ) {
            global $post;
            if ( ! $post ) return $title;
            $post_id = $post->ID;
        }

        return $this->renderAsTwigIfEnabled( $title, $post_id );
    }

    public function renderContentAsTwig( $content ) {
        if ( ! is_singular() ) return $content;

        global $post;
        if ( ! $post ) return $content;
        
        return $this->renderAsTwigIfEnabled( $content, $post->ID );
    }

    public function renderAsTwigIfEnabled( $content, $post_id ) {
        $mode = get_post_meta( $post_id, self::META_KEY, true );
        if ( $mode !== self::MODE_CONTENT ) return $content;

        return $this->renderAsTwig( $content );
    }

    public function startTwigBuffer() {
        if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) return;

        if ( ! is_singular() ) return;
        global $post;
        if ( ! $post || 'page' !== get_post_type( $post ) ) return;

        $mode = get_post_meta( $post->ID, self::META_KEY, true );
        if ( $mode !== self::MODE_FULL ) return;

        if ( $this->buffering ) return;

        $level_before = ob_get_level();
        ob_start();
        $this->buffering = true;
        $this->buffer_level = $level_before + 1;
        $this->buffer_post_id = $post->ID;
    }

    public function shutdownTwigBuffer() {
        if ( ! $this->buffering || $this->buffer_level <= 0 ) {
            return;
        }
        if ( ! is_singular() ) return;
        
        global $post;
        if ( ! $post ) return;
        
        $mode = get_post_meta( $post->ID, self::META_KEY, true );
        if ( $mode !== self::MODE_FULL ) return;

        $collected = '';
        while ( ob_get_level() >= $this->buffer_level ) {
            $collected = ob_get_clean() . $collected;
        }

        try {
            $rendered = $this->renderAsTwig( $collected );
            if ( is_string( $rendered ) && $rendered !== '' ) {
                echo $rendered;
                return;
            }
            echo $collected;
        } catch ( \Throwable $e ) {
            echo $collected;
        }
    }
}
