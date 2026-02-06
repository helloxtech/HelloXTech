<?php

namespace Tenweb_Builder\Apps;

class TopBanner extends BaseApp
{
    protected static $instance = null;
    private $banner_data = array();

    public static function getInstance(){
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->process();
    }

    public function enqueueEditorScripts() {
        if ( TWBB_DEV === true ) {
            wp_enqueue_script(
                'twbb-top-banner-script',
                TWBB_URL . '/Apps/TopBanner/assets/script/top_banner.js',
                [ 'jquery' ],
                TWBB_VERSION,
                true
            );

        } else {
            wp_enqueue_script(
                'twbb-top-banner-script',
                TWBB_URL . '/Apps/TopBanner/assets/script/top_banner.min.js',
                [ 'jquery' ],
                TWBB_VERSION,
                true
            );
        }

    }

    public function enqueueEditorStyles() {
        if ( TWBB_DEV === true ) {
            wp_enqueue_style(
                'twbb-top-banner-style',
                TWBB_URL . '/Apps/TopBanner/assets/style/top_banner.css',
                [],
                TWBB_VERSION
            );
        } else {
            wp_enqueue_style(
                'twbb-top-banner-style',
                TWBB_URL . '/Apps/TopBanner/assets/style/top_banner.min.css',
                [],
                TWBB_VERSION
            );
        }
    }

    public function setTemplates() {
        $banner_data = $this->banner_data;
        require_once(TWBB_DIR . '/Apps/TopBanner/templates/view.php');
    }

    private function process()
    {
        $this->setVariables();
        if ( self::visibilityCheck() ) {
            $this->addActions();
        }
    }

    private function setVariables()
    {
        $subscription_info = self::getUserSubscriptionInfo();
        $is_trial = $subscription_info['is_trial'];
        $is_monthly = $subscription_info['is_monthly'];
        if (defined('TENWEB_DASHBOARD') && strpos(TENWEB_DASHBOARD, 'test') !== false) {
            $url = 'https://testmy.10web.io';
        } else {
            $url = 'https://my.10web.io';
        }
        if( $is_trial ) {
            $this->banner_data = array(
                'content_text' => '30% off your first payment – Black Friday & Cyber Monday week special!',
                'button_text' => 'Grab the deal now',
                'button_link' => $url . '/upgrade-plan'
            );
        } else if( $is_monthly ) {
            $this->banner_data = array(
                'content_text' => 'Get 30% off on annual subscription – Black Friday & Cyber Monday week special!',
                'button_text' => 'Upgrade & save 30%',
                'button_link' => $url . '/upgrade-plan'
            );
        }
    }

    private function addActions()
    {
        add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueueEditorScripts' ) , 12);
        add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueueEditorStyles' ), 12 );
        add_action( 'elementor/editor/after_enqueue_scripts', array($this, 'setTemplates' ) );
    }

    private static function getUserSubscriptionInfo(){
        $is_trial = false;
        $is_monthly = false;
        $is_reseller = false;
        
        if ( class_exists( '\Tenweb_Manager\Manager' ) ) {
            $user_agreements_info = \Tenweb_Manager\Helper::get_tenweb_user_info();
            if ( is_array($user_agreements_info) && !empty($user_agreements_info) ) {
                $agreement_info = isset( $user_agreements_info['agreement_info'] ) ? $user_agreements_info['agreement_info'] : '';
                $is_trial = $agreement_info['subscription_category'] === 'starter';
                $is_monthly = $agreement_info['plan']['period_type'] === 'm';
                $is_reseller = isset($agreement_info['plan']['services']['agency_suite']);
            }
        }
        
        return array(
            'is_trial' => $is_trial,
            'is_monthly' => $is_monthly,
            'is_reseller' => $is_reseller
        );
    }

    private static function isCountdownValid(){
        // Target date: December 3, 2025 at 24:00 GMT+0000 (which is December 4, 2025 00:00:00 UTC)
        $target_date = new \DateTime('2025-12-06T00:00:00Z', new \DateTimeZone('UTC'));
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        
        return $now < $target_date;
    }

    private static function visibilityCheck(){
        // First check if countdown is still valid
        if ( !self::isCountdownValid() ) {
            return false;
        }
        
        $subscription_info = self::getUserSubscriptionInfo();
        return !$subscription_info['is_reseller'] && ( $subscription_info['is_trial'] || $subscription_info['is_monthly'] );
    }

}

