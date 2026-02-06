<?php

namespace Tenweb_Builder\Apps\CoPilot\AjaxActionControllers;

use Tenweb_Builder\Apps\CoPilot\Actions\BasePostActions\CloneWpPost;
use Tenweb_Builder\Apps\CoPilot\Actions\BasePostActions\FindPostAndPageByTitle;
use Tenweb_Builder\Apps\CoPilot\Enums\WpPostType;
use Tenweb_Builder\Apps\CoPilot\Traits\RestApiAuthHelper;
use WP_REST_Request;
use WP_REST_Response;

class RestApiController
{
    use RestApiAuthHelper;

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function cloneWpPost($request)
    {
        $id = (int) $request->get_param('id');
        $postType = ! empty($request->get_param('post_type')) ? sanitize_text_field($request->get_param('post_type')) : WpPostType::POST;
        $newTitle = ! empty($request->get_param('new_title')) ? sanitize_text_field($request->get_param('new_title')) : null;
        $newStatus = ! empty($request->get_param('new_status')) ? sanitize_text_field($request->get_param('new_status')) : null;

        if (!empty($request->get_param('title'))) {
            $post = CloneWpPost::runByTitle(esc_sql(sanitize_text_field($request->get_param('title'))), $postType, $newTitle, $newStatus);
        } else {
            $post = CloneWpPost::runById($id, $postType, $newTitle, $newStatus);
        }

        if (empty($post)) {
            return new WP_REST_Response(['status' => 'error', 'message' => 'Could not clone post'], 400);
        }

        return new WP_REST_Response(
            [
                'status' => 'success',
                'message' => ucfirst($post->post_type).' duplicated successfully',
                'new_post_id' => $post->ID,
                'new_post_type' => $post->post_type,
                'new_post_title' => $post->post_title,
                'new_post_url' => get_permalink($post->ID),
                'new_post_edit_url' => get_edit_post_link($post->ID),
                'new_post_status' => $post->post_status,
            ],
            200
        );
    }


    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function searchInPostAndPages($request)
    {
        $searchTerm = ! empty($request->get_param('search')) ? sanitize_text_field($request->get_param('search')) : '';
        $searchTerm = wp_strip_all_tags($searchTerm);
        $searchTerm = esc_sql($searchTerm);

        $searchColumns = ! empty($request->get_param('search_columns')) ? $request->get_param('search_columns') : [];

        $returnArray = [];
        $posts = FindPostAndPageByTitle::find($searchTerm, $searchColumns);

        foreach ($posts as $post) {
            $returnArray[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $post->post_type,
                'url' => get_permalink($post->ID),
                'edit_url' => get_edit_post_link($post->ID),
                'author' => $post->post_author,
                'status' => $post->post_status,
                'date' => $post->post_date,
                'modified' => $post->post_modified,
                'excerpt' => $post->post_excerpt,
            ];
        }

        return new WP_REST_Response(
            $returnArray,
            200
        );
    }

}
