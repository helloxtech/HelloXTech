<?php
/**
 * Copyright 2021 AlexaCRM
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

namespace AlexaCRM\Nextgen\API\Endpoints;

use AlexaCRM\Nextgen\API\AdministrativeEndpoint;
use AlexaCRM\Nextgen\API\BadRequestResponse;
use AlexaCRM\Nextgen\Webhooks\Runner;

/**
 * Provides an endpoint to get webhook data.
 */
class WebhookGet extends AdministrativeEndpoint {

    public string $name = 'webhooks/(?P<id>\d+)';

    public array $methods = [ 'GET' ];

    /**
     * Responds to a WP REST request.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function respond( \WP_REST_Request $request ) {
        $urlParams = $request->get_url_params();
        $id = $urlParams['id'] ?? null;

        if ( $id === null ) {
            return new BadRequestResponse( 'invalid-id', __( 'Webhook ID is invalid.', 'integration-cds' ) );
        }

        $webhooks = get_posts( [
            'post_type' => Runner::POST_TYPE,
            'post_status' => [ Runner::ENABLED_STATUS, Runner::DISABLED_STATUS, ],
            'include' => [ $id ],
            'numberposts' => 1,
        ] );

        $result = array_map( function( $item ) {
            return [
                'id' => $item->ID,
                'post_name' => $item->post_name,
                'enabled' => $item->post_status === Runner::ENABLED_STATUS,
                Runner::TOPIC_KEY => get_post_meta( $item->ID, Runner::TOPIC_KEY, true ),
                Runner::TARGET_KEY => get_post_meta( $item->ID, Runner::TARGET_KEY, true ),
                Runner::TARGET_FORM_TYPE => get_post_meta( $item->ID, Runner::TARGET_FORM_TYPE, true ),
                Runner::TARGET_FORM_NAME => get_post_meta( $item->ID, Runner::TARGET_FORM_NAME, true ),
                Runner::TARGET_FORM_ID => get_post_meta( $item->ID, Runner::TARGET_FORM_ID, true ),
                Runner::TARGET_DESCRIPTION => get_post_meta( $item->ID, Runner::TARGET_DESCRIPTION, true ),
            ];
        }, $webhooks );
        $result = array_pop( $result );

        return new \WP_REST_Response( $result );
    }
}
