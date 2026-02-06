<?php
namespace Tenweb_Builder\Apps;

use Elementor\Plugin;
use Elementor\Core\Experiments\Manager;

class ExperimentsManager {
    protected static $instance = null;
    protected $features_definitions = [];
    public static function getInstance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * ExperimentsManager constructor.
     * Initializes default feature definitions and hooks into WordPress/Elementor actions.
    */
    private function __construct(){
        add_action( 'init', array($this, 'registerExperiments'));
        if ( ! defined( 'TENWEB_WHITE_LABEL' ) || ! TENWEB_WHITE_LABEL ) {
            add_action( 'elementor/admin/after_create_settings/elementor', array($this, 'add10WebTab'), 20);
        }
        add_action('admin_enqueue_scripts', [$this, 'twbb_enqueue_scripts']);
    }

    public function twbb_enqueue_scripts() {
        wp_enqueue_script(
            'twbb-experiments-manager',
            TWBB_URL . '/Apps/ExperimentsManager/assets/js/script.js',
            ['jquery'],
            TWBB_VERSION,
            true
        );
    }


    /**
     * Define default experiments and feature flags, with conditional filtering for white label mode.
    */
    public function setDefaults() {
        $this->features_definitions = [
            'co_pilot' => [
                'title' => esc_html__( 'Ai Co-Pilot', 'tenweb-builder' ),
                'description' => '',
                'release_status' => Manager::RELEASE_STATUS_BETA,
                'internal' => false,
                'new_site' => [
                    'default_active' => true,
                    'minimum_installation_version' => '3.1.0-beta',
                ],
            ],
            'fast_editing_tools' => [
                'title' => esc_html__( 'Fast Editing Tools', 'tenweb-builder'),
                'description' => esc_html__( 'Fast Editing Tools in Editor Preview part.', 'tenweb-builder'),
                'release_status' => Manager::RELEASE_STATUS_BETA,
                'internal' => false,
                'new_site' => [
                    'default_active' => true,
                    'minimum_installation_version' => '3.1.0-beta',
                ],
            ],
            'sections_generation' => [
                'title' => esc_html__( 'Sections Generation', 'tenweb-builder'),
                'release_status' => Manager::RELEASE_STATUS_BETA,
                'internal' => false,
                'new_site' => [
                    'default_active' => true,
                    'minimum_installation_version' => '3.1.0-beta',
                ],
            ],
            'smart_scale' => [
                'title' => esc_html__( 'Smart scale', 'tenweb-builder'),
                'description' => esc_html__( 'Automatically adjusts the website’s size proportionally to fit within the editor’s available space, preventing visual distortions.', 'tenweb-builder'),
                'release_status' => Manager::RELEASE_STATUS_BETA,
                'internal' => false,
                'default' => 'active',
                'new_site' => [
                    'default_active' => false,
                    'minimum_installation_version' => '3.1.0-beta',
                ],
            ],
            'wmpl_translation' => [
                'title' => esc_html__( 'WPML Translation', 'tenweb-builder'),
                'description' => esc_html__( 'Enabling this feature fixes the header/footer translation issue', 'tenweb-builder'),
                'release_status' => Manager::RELEASE_STATUS_BETA,
                'internal' => false,
                'default' => 'inactive',
                'new_site' => [
                    'default_active' => false,
                    'minimum_installation_version' => '3.1.0-beta',
                ],
            ],
            'website_navigation' => [
                'title' => esc_html__( 'Website Navigation', 'tenweb-builder'),
                'description' => esc_html__( 'Adding Website and Nav Menu Navigation from Elementor editor.', 'tenweb-builder'),
                'release_status' => Manager::RELEASE_STATUS_BETA,
                'internal' => false,
                'default' => 'active',
                'new_site' => [
                    'default_active' => true,
                    'minimum_installation_version' => '3.1.0-beta',
                ],
            ],
        ];

        if ( defined('TWBB_RESELLER_MODE') && TWBB_RESELLER_MODE ) {
          unset($this->features_definitions['co_pilot']);
        }
    }

    /**
     * Register defined experiments with Elementor's Experiments Manager.
     * Skips if Elementor is not loaded or experiments API is not available.
    */
    public function registerExperiments() {
        if ( ! class_exists( '\Elementor\Plugin' ) || ! Plugin::instance()->experiments ) {
            return;
        }
        $this->setDefaults();
        $this->unsetFeaturesForEcommerce();
        foreach ( $this->features_definitions as $name => $args ) {
            Plugin::instance()->experiments->add_feature( array_merge( [ 'name' => $name ], $args ) );
        }

    }

    public function unsetFeaturesForEcommerce() {
        if( class_exists( 'woocommerce' ) ) {
            unset( $this->features_definitions['co_pilot'] );
        }
    }

    /**
     * Build an array of feature fields to be rendered in the 10Web settings tab.
     *
     * @return array
    */
    public function getFeatureFields() {
        $fields = [];

        $experiments_manager = Plugin::$instance->experiments;

        try {
            $ref = new \ReflectionClass(get_class($experiments_manager));
            $features_prop = $ref->getProperty('features');
            $features_prop->setAccessible(true);
            $all_features = $features_prop->getValue($experiments_manager);
        } catch ( \ReflectionException $e ) {
            return [];
        }

        foreach ( $this->features_definitions as $feature_key => $val ) {
            if ( isset( $all_features[ $feature_key ] ) ) {
                $experiment_data = $all_features[ $feature_key ];

                $title = (is_object($experiment_data) && method_exists($experiment_data, 'get_title'))
                    ? $experiment_data->get_title()
                    : ((is_array($experiment_data) && isset($experiment_data['title'])) ? $experiment_data['title'] : $feature_key);

                $description = is_object($experiment_data) && method_exists($experiment_data, 'get_description')
                    ? $experiment_data->get_description()
                    : (is_array($experiment_data) && isset($experiment_data['description']) ? $experiment_data['description'] : '');
                $tag = is_object($experiment_data) && method_exists($experiment_data, 'get_tag')
                    ? $experiment_data->get_tag()
                    : (is_array($experiment_data) && isset($experiment_data['tag']) ? $experiment_data['tag'] : '');

                $field_id = 'elementor_experiment-' . $feature_key;

                $option_key = 'elementor_experiment-' . $feature_key;
                $saved_value = get_option( $option_key, false );

                // Always use this for the dropdown
                $input_value = $saved_value !== false ? $saved_value : 'default';

                // Now resolve effective behavior:
                if ( $input_value === 'active' || $input_value === 'inactive' ) {
                    $effective_value = $input_value;
                } else {
                    // input is 'default' — resolve based on defaults
                    if ( ! empty( $experiment_data['new_site']['default_active'] ) ) {
                        $effective_value = 'active';
                    } elseif ( ! empty( $experiment_data['new_site']['default_inactive'] ) ) {
                        $effective_value = 'inactive';
                    } else {
                        $effective_value = is_array($experiment_data) && isset($experiment_data['default'])
                            ? $experiment_data['default']
                            : 'inactive';
                    }
                }


                $fields[ $feature_key ] = [
                    'render' => function () use ( $feature_key, $title, $description, $field_id, $tag, $input_value, $effective_value, $experiment_data ) {
                        $status = (is_array($experiment_data) && isset($experiment_data['release_status']))
                            ? $experiment_data['release_status']
                            : '';
                        ?>
                        <tr class="elementor_experiment-<?php echo esc_attr( $feature_key ); ?>">
                            <th scope="row">
                                <div class="e-experiment__title">
                                    <div class="e-experiment__title__indicator <?php echo $effective_value === 'active' ? 'e-experiment__title__indicator--active' : 'e-experiment__title__indicator--inactive'; ?>" data-tooltip="<?php echo esc_attr( ucfirst( $effective_value ) ); ?>"></div>
                                    <label class="e-experiment__title__label" for="<?php echo esc_attr( $field_id ); ?>">
                                        <?php echo esc_html__( $title , 'tenweb-builder'); ?>
                                    </label>
                                    <?php if ( $tag ) : ?>
                                        <span class="e-experiment__title__tag e-experiment__title__tag__default"><?php echo esc_html__( $tag , 'tenweb-builder'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </th>
                            <td>
                                <div class="e-experiment__content">
                                    <select
                                            class="e-experiment__select"
                                            id="twbb_experiment-<?php echo esc_attr($feature_key); ?>"
                                            data-sync-name="elementor_experiment-<?php echo esc_attr($feature_key); ?>"
                                            data-experiment-id="<?php echo esc_attr($feature_key); ?>">
                                        <option value="default" <?php selected( $input_value, 'default' ); ?>>Default</option>
                                        <option value="active" <?php selected( $input_value, 'active' ); ?>>Active</option>
                                        <option value="inactive" <?php selected( $input_value, 'inactive' ); ?>>Inactive</option>
                                    </select>
                                    <p class="description"><?php echo esc_html__( $description , 'tenweb-builder'); ?></p>
                                    <?php if ( $status ) : ?>
                                        <div class="e-experiment__status">
                                            <?php esc_html_e( 'Status:', 'tenweb-builder');  echo esc_html( ucfirst( $status ) ); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                    },
                    'label' => '',
                    'field_args' => [],
                    'setting_args' => [],
                ];
            }
        }

        return $fields;
    }

    /* Need to add Title and Description of tab in this function */
    public function render_settings_intro() {
        echo '<h2>' . esc_html__( '10Web Experiments and Features', 'tenweb-builder') . '</h2>';
        echo '<p>' . esc_html__( 'To use an experiment or feature on your site, simply click on the dropdown next to it and switch to Active. You can always deactivate them at any time.', 'tenweb-builder') . '</p>';
    }

    /**
     * Add a custom 10Web tab with experiment controls to the Elementor settings UI.
     *
     * @param \Elementor\Core\Settings\Page\Manager $settings_page
    */
    public function add10WebTab( $settings_page ) {
        $settings_page->add_tab( '10Web', [
            'label'    => __( '10Web Features', 'tenweb-builder'),
            'sections' => [
                'smart_tools' => [
                    'callback' => function() {
                        $this->render_settings_intro();
                    },
                    'fields' => $this->getFeatureFields(), // defer heavy logic here
                ],
            ],
        ] );
    }
}
