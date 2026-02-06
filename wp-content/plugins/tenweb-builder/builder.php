<?php
namespace Tenweb_Builder;
//Adding comment for checking core plugin build job
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
include_once plugin_dir_path(__FILE__) . 'widgets-list.php';

use \Tenweb_Builder\Modules\ElementorKit\ElementorKit;
use Tenweb_Builder\Modules\Helper;

class Builder {
  public static $prefix = '';
  public static $version = '';
  protected static $instance = NULL;

  private function __construct() {
    $this->setVariables();
    $this->process();
  }

  private function process() {
    $this->checkBuilderUpdate();
    add_action('elementor/init', array( 'Tenweb_Builder\Builder', 'applyUltimateKit' ), 12 );
    add_post_type_support( 'page', 'excerpt' );
	add_action('init', function() {
		// Redirect to homepage editor if user comes from 10Web dashboard
		if ( ! empty( $_GET['from'] ) && 'tenweb_dashboard' === $_GET['from'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['open'] ) && 'homepage' === $_GET['open'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$fronpage_id = get_option( 'page_on_front' );
				if ( $fronpage_id && get_post_meta($fronpage_id, '_elementor_edit_mode', true) ) {
					$url = add_query_arg(
						[
							'post'   => $fronpage_id,
							'action' => 'elementor',
						],
						admin_url( 'post.php' )
					);
				}
				else {
					//phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
					$page = get_posts( [
						'numberposts' => 1,
						'post_type' => ['page'],
						'post_status' => 'publish',
						//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'meta_query' => [
							[
								'key' => '_elementor_edit_mode',
								'compare' => 'EXISTS',
							],
						],
					] );
					if ( !empty( $page ) ) {
						$url = add_query_arg(
							[
								'post'   => $page[0]->ID,
								'action' => 'elementor',
							],
							admin_url( 'post.php' )
						);
					}
					else {
						//phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
						$page = get_posts( [
							'numberposts' => 1,
							'post_type' => ['page'],
							'post_status' => 'any',
							'include' => [2]
						] );
						if ( !empty( $page ) ) {
							$url = add_query_arg(
								[
									'post'   => $page[0]->ID,
									'action' => 'elementor',
								],
								admin_url( 'post.php' )
							);
						}
						else {
							$url = add_query_arg(
								[
									'post_type' => 'page',
								],
								admin_url( 'edit.php' )
							);
						}
					}
				}
				wp_safe_redirect( $url );
				die;
			}
		}
	});

    add_action( 'plugins_loaded', function () {
      load_plugin_textdomain( 'tenweb-builder', false, plugin_basename( dirname( __FILE__ ) ) . '/languages'  );
    });

    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

    //TODO move to separate check processing method.
    if ( ! $this->check_elementor_compatibility() ) {
      add_action( 'admin_notices', array( $this, 'elementor_compatibility_notice' ) );
    } else {
      if ( defined( 'ELEMENTOR_PATH' ) ) {
        //TODO move to Apps
        include_once TWBB_DIR . '/templates/templates.php';
        Templates::get_instance();
        $this->includeApps();
        $this->includeModules();
        $this->registerHooks();

        //TODO move to widgets woocommerce part maybe )
        include_once TWBB_DIR . '/classes/woocommerce.php';
        require_once TWBB_DIR . '/widgets/woocommerce/settings-woocommerce.php';
        \Tenweb_Builder\Classes\Woocommerce\Woocommerce::get_instance();
      }
    }
    $this->checkResellerMode();
  }

	private function checkResellerMode() {
		if( TWBB_RESELLER_MODE ) {
			update_option('twbb_show_co_pilot_tour','no', true);
			update_option('elementor_experiment-co_pilot', 'inactive', true);
		}
	}

	private function setVariables() {
		self::$prefix  = TWBB_PREFIX;
		self::$version = TWBB_VERSION;
		$tour_status = get_option('twbb-coPilot-tour-status');
		$elementor_experiment_co_pilot = get_option('elementor_experiment-co_pilot');
		if ( ( $tour_status === false || $tour_status === 'not_started' ) && $elementor_experiment_co_pilot !== 'active'
		     && ! TENWEB_WHITE_LABEL ) {
			if ( ( $tour_status === false || $tour_status === 'not_started' ) && $elementor_experiment_co_pilot !== 'active' ) {
				update_option( 'twbb_show_co_pilot_tour', 'no' );
				update_option( 'elementor_experiment-co_pilot', 'active' );
			} else if ( TENWEB_WHITE_LABEL ) {
				update_option( 'twbb_show_co_pilot_tour', 'no' );
				update_option( 'elementor_experiment-co_pilot', 'inactive' );
			}
		}
	}

  private function checkBuilderUpdate() {
    if ( get_option( 'twbb_version' ) !== TWBB_VERSION ) {
      self::install();
    }
  }

  public static function install() {
    $version = get_option( 'twbb_version' );
    update_option('twbb_show_co_pilot_tour','no');
    update_option('elementor_experiment-co_pilot', 'active');
    if ( $version === FALSE ) {
      $version = '0.0.0';
      self::setElementorEditorVersion();
    }
    if ( version_compare( $version, TWBB_VERSION, '<=' ) ) {}
    self::afterInstall();
  }

  private static function afterInstall() {
    update_option( 'twbb_version', TWBB_VERSION );
    update_option('elementor_experiment-e_global_styleguide', 'inactive');
    update_option('elementor_experiment-e_element_cache', 'inactive');
    update_option('elementor_experiment-e_optimized_css_loading', 'inactive');
    update_option('elementor_experiment-container', 'active');
    self::maybe_update_elementor_onboarding_option();
    self::setElementorEditorVersion();
    //set force upload true to upload sections every time after new install
    update_option('twbb_sections_force_upload', true);
    if (class_exists('\Elementor\Plugin')) {
		// This can look strange, but this function is called in two different scenarious,
		// so we need to make sure in will work in both cases.
		if ( did_action( 'elementor/init' ) ) {
		  self::repairUltimateKit();
		  \Elementor\Plugin::instance()->files_manager->clear_cache();
		} else {
		  add_action('elementor/init', function () {
		    self::repairUltimateKit();
			//clean Elementor cache update css, Files and data
			\Elementor\Plugin::instance()->files_manager->clear_cache();
		  }, 11);
		}
    }

    if( TWBB_RESELLER_MODE ) {
        self::downloadExtractSectionsCatalogue();
    }
  }

  public static function repairUltimateKit() {
	  $elementorKitObj = new \Tenweb_Builder\Modules\ElementorKit\ElementorKit;
	  add_filter("user_has_cap", [Helper::class, "add_caps_to_allow_adding_new_kit"], 10, 4);
	  $elementorKitObj->repairUltimateKit();
	  remove_filter('user_has_cap', [Helper::class, 'add_caps_to_allow_adding_new_kit']);
  }

  public static function sectionsSync() {
      if ( get_option('elementor_experiment-sections_generation') !== 'inactive' ) {
          self::deleteSectionsPosts();
          \Tenweb_Builder\Modules\ai\TenWebApi::get_instance()->sectionSyncRequest();
          self::createSectionsPost();
      }
  }

  public static function createSectionsPost() {
      \Tenweb_Builder\Modules\ai\TenWebApi::get_instance()->getSectionTypeDescriptions();
      \Tenweb_Builder\Modules\SectionGeneration\GenerateSectionsPostsByType::getInstance()->process(true);
  }

  private static function deleteSectionsPosts()
  {
      $args = array(
          'post_type' => 'twbb_sg_preview',
          'posts_per_page' => -1,
      );
      $posts = get_posts($args);//phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
      foreach ($posts as $post) {
          wp_delete_post($post->ID, true);
      }
  }

  public static function maybe_update_elementor_onboarding_option() {
    $db_option = get_option( 'elementor_onboarded');
    if ( ! $db_option ) {
      update_option( 'elementor_onboarded', true );
    }
  }

  public static function applyUltimateKit(){
    if(!defined('ELEMENTOR_PATH') || !class_exists('\Elementor\Plugin')) {
      return false;
    }

    if(empty(\Elementor\Plugin::instance()->kits_manager)) {
      return false;
    }

    $caps = ['edit_posts', 'edit_published_posts', 'edit_others_posts'];
    foreach($caps as $cap) {
      if(!current_user_can($cap)) {
        return false;
      }
    }

    if(ElementorKit::isUltimateKitActive()) {
      return false;
    }

    if(get_option('twbb_ultimate_kit_installed_by_default') !== false) {
      return false;
    }

    if(!ElementorKit::isElementorDefaultKitActive()) {
      return false;
    }
	//phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
    $posts = get_posts([
      'numberposts' => 1,
      "posts_per_page" => 1,
      'post_type' => ['page', 'post'],
      'post_status' => 'publish',
      'meta_key' => '_elementor_edit_mode',
      'meta_value' => 'builder'//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
    ]);

    if(count($posts) > 0) {
      return false;
    }

    $kit_id = (new ElementorKit)->applyDefaultUltimateKit();
    if($kit_id) {
      update_option('twbb_ultimate_kit_installed_by_default', "yes", false);
    } else {
      update_option('twbb_ultimate_kit_installed_by_default', "no", false);
    }
    return $kit_id;
  }

  private function check_elementor_compatibility() {
    if ( ! defined( 'ELEMENTOR_VERSION' ) || version_compare( ELEMENTOR_VERSION, TWBB_ELEMENTOR_MIN_VERSION, '<' ) || ! did_action( 'elementor/loaded' ) ) {
      return FALSE;
    }

    return TRUE;
  }

  private function includeApps() {
    \Tenweb_Builder\Apps\BuilderTour::getInstance();
    \Tenweb_Builder\Apps\SVGUpload::getInstance();
    \Tenweb_Builder\Apps\InternalSettings::getInstance();
    \Tenweb_Builder\Apps\RemoveUpsell::getInstance();
    \Tenweb_Builder\Apps\PreviewUpgrade::getInstance();
    \Tenweb_Builder\Apps\SectionGeneration::getInstance();
    \Tenweb_Builder\Apps\TextGenerationAI::getInstance();
    \Tenweb_Builder\Apps\ImageGenerationAI::getInstance();
    \Tenweb_Builder\Apps\FastEditorDirector::getInstance();
    \Tenweb_Builder\Apps\WebsiteNavigation::getInstance();
    if (\Tenweb_Builder\Apps\CoPilot::checkVisibility() && !class_exists( 'woocommerce' )) {
        \Tenweb_Builder\Apps\CoPilot::getInstance();
    }
    \Tenweb_Builder\Apps\PostDuplication::getInstance();
    \Tenweb_Builder\Apps\ExperimentsManager::getInstance();
    \Tenweb_Builder\Apps\TrialFlow::getInstance();
    \Tenweb_Builder\Apps\AIBuilder::getInstance();
    \Tenweb_Builder\Apps\TopBanner::getInstance();
  }

  private function includeModules()
  {
      new \Tenweb_Builder\Modules\TenwebRestApi();
      new \Tenweb_Builder\Modules\CLI();
      Modules\SectionGeneration\TemplatePreview::getInstance();
      new \Tenweb_Builder\Modules\ActionsHooksWp();
      new \Tenweb_Builder\Modules\ActionsHooksElementor();
      new \Tenweb_Builder\Modules\ai\Utils();
      new \Tenweb_Builder\Modules\ai\TenWebApi();
  }


  // @TODO Temporary solution.
  private function registerHooks() {
    $this->register_controls();
  }

  public function register_controls() {
    include_once TWBB_DIR . '/controls/select-ajax/controller.php';
    SelectAjaxController::get_instance();
    if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Group_Control_Base' ) ) {
      include_once TWBB_DIR . '/controls/query-control/controller.php';
      include_once TWBB_DIR . '/controls/query-control/controls/group-control-posts.php';
      \Tenweb_Builder\Controls\QueryControl\QueryController::get_instance();
    }
  }

  /* Remove last imported template*/
  public static function get_instance() {
    if ( self::$instance === NULL ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  //TODO find this functionality from elementor
  public static function get_edit_url() {
    if ( ! is_admin() ) {
      $edit_url = FALSE;
      if ( is_singular() ) {
        $document = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( get_the_ID() );
        if ( $document && $document->is_editable_by_current_user() ) {
          $edit_url = $document->get_edit_url();
        }
        if ( is_singular( array( 'product' ) ) ) {
          $loaded_templates = Templates::get_instance()->get_loaded_templates();
          if ( array_key_exists( 'twbb_single', $loaded_templates ) && ! empty( $loaded_templates[ 'twbb_single' ] ) ) {
            $template_id = $loaded_templates[ 'twbb_single' ];
            $document    = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( $template_id );
            if ( $document && $document->is_editable_by_current_user() ) {
              $edit_url = admin_url( 'post.php?post=' . $template_id . '&action=elementor' );
            }
          }
          elseif ( array_key_exists( 'twbb_single_product', $loaded_templates ) && ! empty( $loaded_templates[ 'twbb_single_product' ] ) ) {
            $template_id = $loaded_templates[ 'twbb_single_product' ];
            $document    = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( $template_id );
            if ( $document && $document->is_editable_by_current_user() ) {
              $edit_url = admin_url( 'post.php?post=' . $template_id . '&action=elementor' );
            }
          }
        }
      } else {
        $loaded_templates = Templates::get_instance()->get_loaded_templates();
        if ( array_key_exists( 'twbb_archive', $loaded_templates ) && ! empty( $loaded_templates[ 'twbb_archive' ] ) ) {
          $archive_id = $loaded_templates[ 'twbb_archive' ];
          $document   = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( $archive_id );
          if ( $document && $document->is_editable_by_current_user() ) {
            $edit_url = admin_url( 'post.php?post=' . $archive_id . '&action=elementor' );
          }
        } elseif ( array_key_exists( 'twbb_archive_products', $loaded_templates ) && ! empty( $loaded_templates[ 'twbb_archive_products' ] ) ) {
          $archive_id = $loaded_templates[ 'twbb_archive_products' ];
          $document   = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( $archive_id );
          if ( $document && $document->is_editable_by_current_user() ) {
            $edit_url = admin_url( 'post.php?post=' . $archive_id . '&action=elementor' );
          }
        }
      }

      return $edit_url;
    }
  }



  public static function setElementorEditorVersion() {
    update_option('elementor_experiment-editor_v2', 'active' );
  }

  public function admin_enqueue_scripts() {
    wp_enqueue_script( TWBB_PREFIX . '-admin-script', TWBB_URL . '/assets/admin/js/admin.js', [ 'jquery' ], TWBB_VERSION, TRUE );
    wp_localize_script( TWBB_PREFIX . '-admin-script', 'twbb_admin',
      array(
        'ajax_url' => wp_nonce_url( admin_url( 'admin-ajax.php' ),'twbb_remove_template_ajax', 'twbb_nonce' ),
        'sections_ajax_url' => admin_url( 'admin-ajax.php' ),
        'sections_install_nonce' => wp_create_nonce( 'twbb_sections_install_nonce' ),
        'sections_update' => get_option('twbb_sections_force_upload', false),
        'sections_folder_exists_and_not_empty' => (file_exists( wp_upload_dir()['basedir'] . '/ai20-sections' ) && count( glob( wp_upload_dir()['basedir'] . '/ai20-sections/*' ) ) > 3 ) ? 'do_not_install' : 'install',
      ) );
    wp_enqueue_style( TWBB_PREFIX . '-admin-style', TWBB_URL . '/assets/admin/css/admin.css', [], TWBB_VERSION );
  }

  public function elementor_compatibility_notice() {
    $elementor_notice = NULL;
    add_thickbox();
    $thickbox          = add_query_arg(
      array( 'tab' => 'plugin-information', 'plugin' => 'elementor', 'TB_iframe' => 'true' ),
      admin_url( 'plugin-install.php' )
    );
    $link              = "";
	$script = false;
    $installed_plugins = get_plugins();
    if ( ! isset( $installed_plugins[ 'elementor/elementor.php' ] ) ) {
      $elementor_notice = __( '10Web Builder requires Elementor plugin. Please install and activate the latest version of %s plugin.', 'tenweb-builder');
	  //phpcs:ignore WordPress.Security.NonceVerification.Recommended
      if ( isset( $_GET[ 'from' ] ) && '10web' === $_GET[ 'from' ] ) {
        $link   = 'thickbox';
        $script = true;
      } else {
        $link = add_query_arg(
          array( 's' => 'elementor', 'tab' => 'search', 'type' => 'term', 'from' => '10web' ),
          admin_url( 'plugin-install.php' )
        );
      }
    } else if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
      $elementor_notice = __( '10Web Builder requires Elementor plugin. Please activate %s plugin.', 'tenweb-builder');
      $link             = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=elementor/elementor.php&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_elementor/elementor.php' );
    } else if ( version_compare( ELEMENTOR_VERSION, TWBB_ELEMENTOR_MIN_VERSION, '<' ) ) {
      $elementor_notice = __( '10Web Builder requires latest version of Elementor plugin. Please update %s plugin.', 'tenweb-builder');
      $link             = 'thickbox';
    }
    if ( $elementor_notice !== NULL ) {

      if ( current_user_can( 'activate_plugins' ) ) {

        if ( $link === 'thickbox' ) {
          $link = '<a id="twbb_install_elementor" class="thickbox" href="' . $thickbox . '">Elementor</a>';
        } else {
          $link = '<a href="' . $link . '">Elementor</a>';
        }
      } else {
        $link = 'Elementor';
      }
      echo '<div class="error twbb_notice">' . wp_kses_post( sprintf( $elementor_notice, $link ) ) . "</div>";
      if ( $script ) {
        echo '<script>jQuery(window).load(function() {jQuery("#twbb_install_elementor").trigger("click")});</script>';
      }
    }
  }

    public static function downloadExtractSectionsCatalogue() {
        // Define constants for reuse
        $upload_dir = wp_upload_dir();
        $sections_dir = $upload_dir['basedir'] . '/ai20-sections';
        $theme = get_option('twbb_kit_theme_name', 'classic');
        $download_url = 'https://ai20-sections-prod.s3.us-east-1.amazonaws.com/section_catalogue/' . $theme . '/sections.zip';
        $zip_file = $sections_dir . '/sections.zip';

        // Create ai20-sections directory if it doesn't exist
        if (!is_dir($sections_dir)) {
            if (!wp_mkdir_p($sections_dir)) {
                return;
            }
        }

        // Download the zip file
      //phpcs:disable
        $response = wp_remote_get($download_url, ['timeout' => 300]);
      //phpcs:enable
        if (is_wp_error($response)) {
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return;
        }

        // Save the zip file
        $zip_data = wp_remote_retrieve_body($response);
        //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents
        if (file_put_contents($zip_file, $zip_data) === false) {
            return;
        }

        // Validate and extract the zip file
        $zip = new \ZipArchive();
        if ($zip->open($zip_file) === TRUE) {
            $zip->extractTo($sections_dir);
            $zip->close();

            // Clean up the zip file
            //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
            unlink($zip_file);

            // Set proper permissions
            \Tenweb_Builder\Modules\Utils::setDirectoryPermissions($sections_dir);

        } else {
            //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
            unlink($zip_file); // Clean up failed download
        }
    }
}

