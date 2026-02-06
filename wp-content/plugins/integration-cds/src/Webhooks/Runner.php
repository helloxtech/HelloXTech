<?php
/*
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

namespace AlexaCRM\Nextgen\Webhooks;

use AlexaCRM\Nextgen\ConnectionService;
use AlexaCRM\Nextgen\LoggerProvider;
use AlexaCRM\Nextgen\SingletonTrait;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

/**
 * Provides a facility to notify webhook handlers about events in WordPress.
 */
class Runner {

    use SingletonTrait;

    /**
     * Custom post type name that is used to identify webhooks in the database.
     */
    public const POST_TYPE = 'icds_webhook';

    /**
     * Meta key to store the webhook topic.
     */
    public const TOPIC_KEY = 'icds_webhook_topic';

    /**
     * Meta key to store the webhook target.
     */
    public const TARGET_KEY = 'icds_webhook_target';

    /**
     * Meta key to store the webhook description.
     */
    public const TARGET_DESCRIPTION = 'icds_webhook_description';

    /**
     * Meta key to store the webhook form.
     */
    public const TARGET_FORM_TYPE = 'icds_webhook_form_type';

    /**
     * Meta key to store the webhook form name.
     */
    public const TARGET_FORM_NAME = 'icds_webhook_form_name';

    /**
     * Meta key to store the webhook form name.
     */
    public const TARGET_FORM_ID = 'icds_webhook_form_id';

    /**
     * Wodpress 'post_status' field value for enabled webhook.
     */
    public const ENABLED_STATUS = 'publish';

    /**
     * Wodpress 'post_status' field value for disabled webhook.
     */
    public const DISABLED_STATUS = 'private';

    const FORM_CREATE = 'form/created';

    const FORM_UPDATE = 'form/updated';

    const USER_CREATE = 'user/created';

    const USER_UPDATE = 'user/updated';

    const USER_DELETE = 'user/deleted';

    const FORM_TYPE_ALL = 'all';

    const FORM_TYPE_GRAVITY = 'gravity';

    const FORM_TYPE_ELEMENTOR = 'elementor';

    const FORM_TYPE_PREMIUM = 'premium';

    const FORM_TYPE_CUSTOM = 'custom';

    protected string $topic;

    protected WebhooksRunnerInterface $webhookRunner;

    public function __construct( WebhooksRunnerInterface $webhookRunner, array $params = [] ) {
        $this->webhookRunner = $webhookRunner;
    }

    /**
     * Sends the payload to every registered webhook.
     *
     * @param $payload
     *
     * @throws \Throwable
     */
    public function trigger( $payload ): void {

        $webhooks = $this->webhookRunner->findWebhooks();

        if ( count( $webhooks ) === 0 ) {
            return;
        }

        $client = static::createGuzzleClient();
        $logger = LoggerProvider::instance()->getLogger();

        $promises = [];
        foreach ( $webhooks as $target ) {
            try {
                $promises[] = $client->postAsync( $target, [
                    'json' => $payload,
                ] );
            } catch ( Exception $e ) {
                $logger->error( sprintf( __( "Failed to trigger <%s> on '%s'. %s" ), $target, $this->topic, $e->getMessage() ), [
                    'exception' => $e,
                ] );
            }
        }
        // Wait for all the requests to complete.
        try {
            Promise\Utils::unwrap( $promises );
        }
        catch ( Exception $e ) {
            return;
        }
    }

    protected static function createGuzzleClient(): Client {
        $settings = ConnectionService::instance()->getResolvedSettings();

        $verify = $settings->tlsVerifyPeers;
        if ( $verify && $settings->caBundlePath !== null ) {
            $verify = $settings->caBundlePath;
        }

        return new Client( [
            'verify' => $verify,
            'timeout' => 30,
        ] );
    }

}
