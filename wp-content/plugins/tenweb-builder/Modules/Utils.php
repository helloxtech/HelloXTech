<?php

namespace Tenweb_Builder\Modules;

class Utils
{
    public static function getWoocommerceData() {
        // Check if WooCommerce is active
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            return [
                'woocommerce_active' => false,
                'shop_page_id' => null,
                'products_count' => 0
            ];
        }

        // Get the shop page ID
        $shop_page_id = wc_get_page_id('shop');

        // Get the count of published products
        $products_count = wp_count_posts('product')->publish;

        return [
            'woocommerce_active' => true,
            'shop_page_id' => $shop_page_id,
            'products_count' => $products_count
        ];
    }

    public static function getBlogData() {
        // Get the count of published posts
        $posts_count = wp_count_posts()->publish;

        return [
            'posts_count' => $posts_count,
        ];
    }

    public static function handleArchiveNoContentRender($args) {
        ?>
        <div class="elementor-content-not-found">
        <p class="empty-content-description mobile-desc twbb_no_content_text"> <?php echo wp_kses_post( __($args['mobile_desc'], 'tenweb-builder')); ?></p>
        <div class="elementor-content-not-found-container">
            <?php
            for ($i=1; $i <= $args['el_count']; $i++) {
                echo '<div class="empty-content-default-view ' . esc_attr('empty-default-' . $i)  . '"></div>';
            }
            ?>
        </div>
        <p class="empty-content-description desktop-desc twbb_no_content_text"> <?php echo wp_kses_post( __($args['desktop_desc'], 'tenweb-builder')); ?></p>
        </div><?php
    }

    public static function handleArchiveNoContentPreviewRender($args) {
        ?><div class="elementor-posts-not-found">
        <div class="twbb_no_content_text twbb_no_content-title"><?php esc_html_e( $args['title'], 'tenweb-builder'); ?></div>
        <div class="twbb_no_content_text twbb_no_content-desc"><?php esc_html_e( $args['desc'], 'tenweb-builder'); ?></div>
        </div><?php
    }

    public static function is_swiper_latest() {
        if ( defined( 'ELEMENTOR_VERSION' ) ) {
            // Get Elementor version
            $version = ELEMENTOR_VERSION;

            // Check if feature exists (for older versions before 3.26)
            $has_swiper_latest = method_exists( \Elementor\Plugin::$instance->experiments, 'is_feature_active' ) &&
                \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_swiper_latest' );

            // Use 'swiper' if version is >= 3.26.0 OR if the feature was manually enabled in older versions
            if ( version_compare( $version, '3.26.0', '>=' ) || $has_swiper_latest ) {
                return true;
            }
        }
        return false;
    }


    public static function visibilityCheck(){
        $subscription_id = 0;
        $hosting_trial_expire_date = false;
        $hosting_trial_expire = get_option('hosting_trial_expire') === false;
        $platform_id = defined('TENWEB_PLATFORM_FREE_SUBSCRIPTION_ID') ? TENWEB_PLATFORM_FREE_SUBSCRIPTION_ID : 318;
        if ( class_exists( '\Tenweb_Manager\Helper' ) && method_exists( '\Tenweb_Manager\Helper', 'get_tenweb_user_info' ) ) {
            $user_agreements_info = \Tenweb_Manager\Helper::get_tenweb_user_info()['agreement_info'];
            if ( is_array($user_agreements_info)){
                $subscription_id = isset( $user_agreements_info['subscription_id'] ) ? $user_agreements_info['subscription_id'] : 0;
                $hosting_trial_expire_date = (isset( $user_agreements_info[ 'hosting_trial_expire_date' ] ) && $user_agreements_info[ 'hosting_trial_expire_date' ] !== '');
            }
        }
        if ( isset( $_GET['from'] ) && $_GET['from'] === 'tenweb_dashboard' ) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $type = (isset( $_GET['ecommerce'] ) && $_GET['ecommerce'] === '1') ? 'ecommerce' : 'business';//phpcs:ignore WordPress.Security.NonceVerification.Recommended
            update_option( 'twbb_tf', $type );
        }

        return ( TW_HOSTED_ON_10WEB && $subscription_id === $platform_id && $hosting_trial_expire_date && $hosting_trial_expire);
    }

    public static function resellerWhiteLabelData() {
        // get info from this endpoint https://testbuilderapi.10web.io/whitelabel-data with x-api-key header
        if( defined('AI_BUILDER_KIT_API_KEY') ) {
            $url = TENWEB_BUILDER_API . 'whitelabel-data';
            //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
            $response = wp_remote_get($url, array(
                'headers' => array(
                    'x-api-key' => AI_BUILDER_KIT_API_KEY,
                ),
            ));
            if (is_wp_error($response)) {
                return array(
                    'error' => $response->get_error_message(),
                );
            }
            if( !is_array($response)) {
                return array(
                    'error' => $response,
                );
            }
            $data = json_decode($response['body'])->data;

            return array(
                'company_logo' => isset($data->company_logo) ? $data->company_logo : '',
                'company_name' => isset($data->company_name) ? $data->company_name : '',
            );
        }
    }

    public static function setDirectoryPermissions($dir) {
        // Set directory permissions recursively
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        //phpcs:disable
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                chmod($item->getRealPath(), 0755);
            } else {
                chmod($item->getRealPath(), 0644);
            }
        }
        //phpcs:enable
    }

}
