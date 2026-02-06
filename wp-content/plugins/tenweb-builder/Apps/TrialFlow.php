<?php

namespace Tenweb_Builder\Apps;

use Elementor\Utils;

class TrialFlow extends BaseApp
{
	protected static $instance = null;

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
		update_option('twbb_tf_show', '2');
		$twbb_tf_nonce = wp_create_nonce( 'twbb-tf-nonce' );
		$agreement_date = '';

		if ( class_exists( '\Tenweb_Manager\Manager' ) ) {
			$user_agreements_info = \Tenweb_Manager\Helper::get_tenweb_user_info();
			if ( is_array($user_agreements_info) && !empty($user_agreements_info) ) {
				$agreement_info = isset( $user_agreements_info['agreement_info'] ) ? $user_agreements_info['agreement_info'] : '';
				$agreement_date = isset( $agreement_info['agreement_date'] ) ? $agreement_info['agreement_date'] : '';
			}
		}
		if ( TWBB_DEV === true ) {
			wp_enqueue_script(
				'trial-flow-script',
				TWBB_URL . '/Apps/TrialFlow/assets/script/tooltip.js',
				[ 'jquery' ],
				TWBB_VERSION,
				true
			);

		} else {
			wp_enqueue_script(
				'trial-flow-script',
				TWBB_URL . '/Apps/TrialFlow/assets/script/trial_flow.min.js',
				[ 'jquery' ],
				TWBB_VERSION,
				true
			);
		}

		wp_localize_script(
			'trial-flow-script',
			'twbb_tf_tooltip',
			array(
				'twbb_tf_nonce' => $twbb_tf_nonce,
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'agreement_date' => $agreement_date
			)
		);

        wp_enqueue_script(
            'trial-flow-trustpilot',
            'https://widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js',
            TWBB_VERSION,
            true
        );

	}

	public function enqueueEditorStyles() {
		if ( TWBB_DEV === true ) {
			wp_enqueue_style(
				'trial-flow-tooltip-style',
				TWBB_URL . '/Apps/TrialFlow/assets/style/tooltip.css',
				[],
				TWBB_VERSION
			);
		} else {
			wp_enqueue_style(
				'trial-flow-tooltip-style',
				TWBB_URL . '/Apps/TrialFlow/assets/style/trial_flow_style.min.css',
				[],
				TWBB_VERSION
			);
		}
	}

	public function setTemplates() {
		require_once(TWBB_DIR . '/Apps/TrialFlow/templates/tooltip.php');
	}

	public function showTopBarAndIframe(){
		require_once(TWBB_DIR . '/Apps/TrialFlow/templates/popup.php');
	}

	private function process()
	{
		if ( self::visibilityCheck() ) {
			$this->addActions();
		}
	}

	private function addActions()
	{
		$referer = (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ($referer) {
			$host = wp_parse_url($referer, PHP_URL_HOST);
			$thisHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ($host !== $thisHost || strpos($referer, 'wp-admin') !== false) {
				update_option('twbb_tf_show', '2');
			}
		}
		if (isset($_GET['trial_hosted_flow']) && $_GET['trial_hosted_flow'] === '1'){//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			update_option('twbb_tf_show', '1');
		}

		$tf_show_option = get_option('twbb_tf_show');
		if ( $tf_show_option && $tf_show_option === '1' ){
			add_filter('show_admin_bar', '__return_false'); //phpcs:ignore WordPressVIPMinimum.UserExperience.AdminBarRemoval.RemovalDetected
			if(!isset($_GET['in_iframe'])) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_action( 'wp_head', array( $this, 'showTopBarAndIframe' ), 12 );
				add_action( 'wp_head', array( $this, 'enqueueTrialFlowFrontendScripts' ), 12 );
			}
		}
		add_action( 'elementor/editor/v2/scripts/enqueue/after', array( $this, 'enqueueEditorScripts' ) , 12);
		add_action( 'elementor/editor/v2/styles/enqueue', array( $this, 'enqueueEditorStyles' ), 12 );
		//frontend preview scripts are enqueueing in TemplatePreview.php for only be enqueued in preview mode
		add_action( 'elementor/editor/footer', array($this, 'setTemplates' ) );
		add_action( 'wp_ajax_twbb_get_trial_limits', array($this, 'getTrialLimits') );
	}

	public function getTrialLimits() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field($_POST['nonce']) : '';
		if ( !wp_verify_nonce( $nonce, 'twbb-tf-nonce' ) ) {
			wp_send_json_error("invalid_nonce");
		}
		$api_response = \Tenweb_Builder\Modules\ai\TenWebApi::get_instance()->get_trial_limitations( 'ai_trial' );

		if( !$api_response ) {
			wp_send_json_error("error");
			wp_die();
		}
		$limitation = [
			"already_used" => $api_response['alreadyUsed'],
			"plan_limit" =>  $api_response['planLimit'],
			"hosting_trial_expire_date" => $api_response['hostingTrialExpireDate'],
		];
		if ($api_response['hostingTrialExpireDate'] === ''){
			update_option('hosting_trial_expire', '1');
		}
		wp_send_json_success([
			'status' => 'success',
			'data' => $limitation
		]);
		wp_die();
	}

	public function enqueueTrialFlowFrontendScripts() {
		wp_enqueue_script(
			'twbb-trial-flow-script',
			TWBB_URL . '/Apps/TrialFlow/assets/script/trial_flow_frontend.js',
			array('jquery'),
			TWBB_VERSION,
			TRUE
		);
        $domainId = get_site_option('tenweb_domain_id');
		wp_localize_script( 'twbb-trial-flow-script', 'twbb_trial_flow', array(
			'dashboard_url' =>  esc_url(TENWEB_DASHBOARD .  '/websites/' . $domainId .'/main/'),
			'twbb_url' =>  TWBB_URL,
            'twbb_edit_url' => esc_url( get_admin_url() . '?from=tenweb_dashboard&open=homepage' ),
		));
		wp_enqueue_style( 'twbb-trial-flow-style', TWBB_URL . '/Apps/TrialFlow/assets/style/trial_flow_frontend.css', array(), TWBB_VERSION );

        if( !get_option("twbb-trial-flow-canfetti", false) ) {

            $clients_id = 0;
            if ( class_exists( '\Tenweb_Manager\Manager' ) ) {
                $user_agreements_info = \Tenweb_Manager\Helper::get_tenweb_user_info()[ 'agreement_info'];
                if ( is_array($user_agreements_info) && !empty($user_agreements_info) ) {
                    $clients_id = isset( $user_agreements_info['clients_id'] ) ? $user_agreements_info['clients_id'] : 0;
                }
            }
            wp_enqueue_script( 'twbb-editor-helper-script', TWBB_URL . '/assets/editor/js/helper-script.js', array('jquery'), TWBB_VERSION, TRUE );
            wp_localize_script( 'twbb-editor-helper-script', 'twbb_helper', array(
                'domain_id' => $domainId,
                'send_ga_event' => defined('TENWEB_SEND_GA_EVENT') ? TENWEB_SEND_GA_EVENT : 'https://core.10web.io/api/send-ga-event',
                'clients_id' => $clients_id
            ));

            wp_enqueue_script(
                'twbb-trial-flow-confetti',
                TWBB_URL . '/Apps/TrialFlow/assets/script/confetti.browser.min.js',
                array('jquery'),
                TWBB_VERSION,
                TRUE
            );
            update_option("twbb-trial-flow-canfetti", "1");
        }

	}

    private static function visibilityCheck(){
        $user_info = get_site_option(TENWEB_PREFIX . '_user_info');
        $is_reseller_trial = false;
        if( is_array($user_info) && isset($user_info['agreement_info']) && is_array($user_info['agreement_info']) ) {
            $is_reseller_trial   = isset($user_info['agreement_info']['subscription_category']) &&
                $user_info['agreement_info']['subscription_category'] === 'starter' &&
                isset($user_info['agreement_info']['plan']['services']['agency_suite']);
        }
       return !$is_reseller_trial && \Tenweb_Builder\Modules\Utils::visibilityCheck();
    }

}
