<?php

namespace TenWebWooP;

use WP_REST_Attachments_Controller;
use WP_REST_Request;
use WP_REST_Server;

class Media extends WP_REST_Attachments_Controller {

    use CheckAuthorization;

    public function register_routes() {
        register_rest_route(
            'tenweb_woop/v1',
            'create_media_bulk',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_media_bulk' ),
                'permission_callback' => array($this, 'check_authorization'),
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'media',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'check_authorization' ),
                'args' => $this->get_collection_params(),
            )
        );
        register_rest_route(
            'tenweb_woop/v1',
            'media/(?P<id>[\d]+)',
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_item' ),
                'permission_callback' => array( $this, 'check_authorization' ),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the post.'),
                        'type' => 'integer',
                    ),
                ),
            )
        );
    }

    public function create_media_bulk(WP_REST_Request $request) {
        $return_data = array();
        $files = $request->get_file_params();

        foreach ($files as $key => $file) {
            $files['file'] = $file;
            $request->set_file_params($files);
            $return_data[$key] = parent::create_item($request);
            unset($files[$key]);
        }

        return $return_data;
    }

    /**
     * Allow 10web authorized users to delete media.
     *
     * @param $post
     *
     * @return true
     */
    protected function check_delete_permission($post) {
        return true;
    }
}
