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

use AlexaCRM\Nextgen\LoggerProvider;
use AlexaCRM\Nextgen\Settings;
use AlexaCRM\Nextgen\SettingsProvider;
use AlexaCRM\Nextgen\WebhookSettings;
use WP_Query;

class  WebhookForm implements WebhooksRunnerInterface {

    protected array $parameters;

    private Settings $webhookSettings;

    private string $topic;

    private ?string $formId;

    private ?string $formType;

    public function __construct( string $topic, ?string $formType = null, ?string $formId = null ) {
        $this->webhookSettings = SettingsProvider::instance()->getSettings( WebhookSettings::SETTINGS_TYPE_NAME );
        $this->topic = $topic;
        $this->formId = $formId;
        $this->formType = $formType;
    }

    public function prepareFileList( $fieldName, $fileName, $filePath, $fileUrl ): array {
        $directFileLimit = $this->webhookSettings->settings['direct_file_upload'] ?? 2048;
        $fileContent = file_get_contents( $filePath );
        $filesList = [];
        $filesList[ $fieldName ] = [ 'filename' => $fileName ];
        if ( filesize( $filePath ) / 1024 >= $directFileLimit ) {
            $filesList[ $fieldName ] ['type'] = 'url';
            $filesList[ $fieldName ] ['url'] = $fileUrl;
        } else {
            $filesList[ $fieldName ] ['body'] = base64_encode( $fileContent );
            $filesList[ $fieldName ] ['type'] = 'base64';
        }

        return $filesList;
    }

    public function findWebhooks() {

        $topic = $this->topic;
        $formType = $this->formType;
        $formId = $this->formId;
        $query = [
            'post_type' => Runner::POST_TYPE,
            'post_status' => Runner::ENABLED_STATUS,
            'meta_key' => Runner::TOPIC_KEY,
            'meta_value' => $topic,
            'fields' => 'ids',
            'nopaging' => true,
        ];
        $WPQuery = new WP_Query( $query );

        $webhooks = [];
        foreach ( $WPQuery->posts as $id ) {
            $target = trim( get_post_meta( $id, Runner::TARGET_KEY, true ) );
            if ( $target === '' ) {
                LoggerProvider::instance()->getLogger()->debug( __( "Empty target.", 'integration-cds' ) );
                continue;
            }

            if ( $formType ) {
                $targetFormType = trim( get_post_meta( $id, Runner::TARGET_FORM_TYPE, true ) );
                if ( $targetFormType !== 'all' && $formType !== $targetFormType ) {
                    continue;
                }
            }

            if ( $formId && $formType !== 'all' ) {
                $targetFormID = trim( get_post_meta( $id, Runner::TARGET_FORM_ID, true ) );
                if ( $targetFormID !== 'all' && $formId !== $targetFormID ) {
                    continue;
                }
            }
            $webhooks[] = $target;
        }

        return $webhooks;
    }
}
