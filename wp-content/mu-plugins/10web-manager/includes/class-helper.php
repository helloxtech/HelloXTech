<?php
/**
 * Created by PhpStorm.
 * User: mher
 * Date: 9/14/17
 * Time: 3:12 PM
 */

namespace Tenweb_Manager {

    use \Tenweb_Authorization\Helper as AuthHelper;
    use Tenweb_Authorization\InstalledPlugin;
    use Tenweb_Authorization\Product;
    use Tenweb_Authorization\ProductState;

    class Helper
    {

        private static $plugins_state = array();
        private static $themes_state = array();
        private static $addons_state = array();
        private static $site_state = array();
        private static $error_logs = array();
        private static $migration_logs = array();
        private static $user_info = null;
        private static $user_agreements = null;
        private static $notices = array();
        private static $installed_plugins_wp_info = null;
        private static $installed_themes_wp_info = null;
        private static $expiration = array(
            'send_states'     => array(
                'expiration' => 43200,//12 hour 43200
                'block_time' => 300,//5 minute 300
            ),
            'user_info'       => array(
                'expiration' => 43200,//12 hour
                'block_time' => 300,//5 minute
            ),
            'user_agreements' => array(
                'expiration' => 43200,//12 hour
                'block_time' => 300,//5 minute
            ),
            'user_products'   => array(
                'expiration' => 86400,//24 hour
                'block_time' => 300,//5 minute
            ),
        );

        private static $white_label_users = array('tenweb_manager_plugin');
        public static $is_full_white_label = false;
        public static $current_user_have_access = true;

        public static function get_products_objects($plugins_data = array(), $themes_data = array(), $addons_data = array())
        {
            $result = AuthHelper::get_products_objects($plugins_data, $themes_data, $addons_data);
            $manager_exists = false;
            if(!empty($result)){
                foreach ($result['plugins']['installed_products'] as $plugin_data) {
                    if ($plugin_data->state->product_id == TENWEB_MANAGER_ID) {
                        $manager_exists = true;
                    }

                }
                if ($manager_exists == false && is_admin()) {
                    $plugin = self::create_manager_plugin_object();
                    $result['plugins']['installed_products'][] = $plugin;
                    $notice = "Fail on connection with api. <a href='#' class='tenweb_clear_cache_button'>Try again</a>";
                    self::add_notices($notice);
                }
                return $result;
            }

        }

        private static function create_manager_plugin_object()
        {
            $plugin_slug = explode('/', TENWEB_SLUG);
            $plugin_slug = $plugin_slug[0];


            $state = new ProductState(
                TENWEB_MANAGER_ID,
                $plugin_slug,
                "10WEB Manager",
                "",
                'plugin',
                "0.0.0",
                1
            );

            $state->active = true;
            $state->is_paid = false;

            self::$plugins_state[] = $state;

            $plugin = new InstalledPlugin(
                $state,
                TENWEB_MANAGER_ID,
                $plugin_slug,
                "10WEB Manager",
                "",
                TENWEB_SLUG
            );

            return $plugin;
        }

        public static function check_site_state($force_send = false, $screen_id = null, $current_blog_id = null)
        {
            AuthHelper::check_site_state($force_send, $screen_id, $current_blog_id);
        }

        public static function send_state_before_deactivation()
        {
            AuthHelper::send_state_before_deactivation();
        }


        public static function get_blogs_info()
        {
            return AuthHelper::get_blogs_info();
        }


        public static function get_site_info($blog_id = null, $reset = false)
        {
            return AuthHelper::get_site_info($blog_id, $reset);
        }

        public static function get_manager_info()
        {
            return AuthHelper::get_manager_info();
        }

        public static function get_site_full_state()
        {
            return AuthHelper::get_site_full_state();
        }

        public static function get_fs_method()
        {
            AuthHelper::get_fs_method();
        }

        public static function check_fs_configs()
        {
            AuthHelper::check_fs_configs();
        }


        public static function store_migration_log($key, $msg, $success = 1)
        {
            if (self::get_configs('TENWEB_MIGRATION_DEBUG')) {
                if (self::get_configs('TENWEB_MIGRATION_LOGS_IN_DB')) {
                    self::set_migration_log($key, $msg, $success);
                } else {
                    self::set_migration_log_to_file($key, $msg, $success);
                }
            }
        }

        public static function set_error_log($key, $msg)
        {
            $logs = self::get_error_logs();
            $logs[$key] = array('msg' => $msg, 'date' => date('Y-m-d H:i:s'));
            $expiration = 31 * 24 * 60 * 60;
            set_site_transient(TENWEB_PREFIX . '_error_logs', $logs, $expiration);
            self::$error_logs = $logs;
        }

        public static function set_migration_log($key, $msg, $success = 1)
        {
            $logs = self::get_migration_logs();
            $milisec = number_format(microtime(true), 2, '.', '' );
            $logs[$key] = array('msg' => $msg, 'timestamp' => $milisec, 'success' => $success);
            $expiration = 3 * 24 * 60 * 60;
            set_site_transient(TENWEB_PREFIX . '_migration_logs', $logs, $expiration);
            self::$migration_logs = $logs;
        }

        /**
         * Get migration log file path or dir path
         *
         * @return string path
         */
        public static function get_migration_log_path() {
            return Helper::get_tmp_dir() . "/tenweb_migration_logs.txt";
        }

        public static function set_migration_log_to_file($key, $msg, $success = 1)
        {
            try {
                $logfile = self::get_migration_log_path();
                $milisec = number_format(microtime(true), 2, '.', '' );

                $msg = $key . '*|*' . $msg . '*|*' . $milisec . '*|*' . $success . '<%>';
                $logs = array('msg' => $msg, 'timestamp' => $milisec, 'success' => $success);
                file_put_contents($logfile, $msg, FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {
                $milisec = number_format(microtime(true), 2, '.', '' );
                $logs[$key] = array('msg' => $e->getMessage(), 'timestamp' => $milisec, 'success' => 0);
            }
            self::$migration_logs = $logs;
        }

        public static function delete_migration_log_file() {
            $logfile = self::get_migration_log_path();
            if ( file_exists($logfile) ) {
                unlink($logfile);
            }
        }

        public static function update_migration_state($key, $value)
        {
            $state = get_site_option(TENWEB_PREFIX . '_migration_state', array());
            $state[$key] = $value;
            update_site_option(TENWEB_PREFIX . '_migration_state', $state);
        }

        public static function flush_migration_log()
        {
            delete_site_transient(TENWEB_PREFIX . '_migration_logs');
            self::delete_migration_log_file();
            self::$migration_logs = array();
        }

        public static function get_error_logs()
        {

            if (self::$error_logs == null) {
                $logs = get_site_transient(TENWEB_PREFIX . '_error_logs');
                if (!is_array($logs)) {
                    $logs = array();
                }
                self::$error_logs = $logs;
            }

            return self::$error_logs;
        }
        public static function get_migration_logs()
        {
            $logs = array();
            if (self::$migration_logs == null) {
                if (self::get_configs('TENWEB_MIGRATION_LOGS_IN_DB')) {
                    $logs = get_site_transient(TENWEB_PREFIX . '_migration_logs');
                    if ( !is_array($logs) ) {
                        $logs = array();
                    }
                }
                else {
                    $logfile = self::get_migration_log_path();
                    if ( !file_exists($logfile) ) {
                        $logs = array();
                    } else {
                        try {
                            $file_data = file_get_contents($logfile);
                            $rows = explode("<%>", $file_data);
                            array_pop($rows);
                            foreach ( $rows as $row ) {
                                $col = explode("*|*", $row);
                                $key = isset($col[0]) ? $col[0] : '';
                                if ( $key == '' ) {
                                    continue;
                                }
                                $logs[$key]['msg'] = isset($col[1]) ? $col[1] : '';
                                $logs[$key]['timestamp'] = isset($col[2]) ? $col[2] : '';
                                $logs[$key]['success'] = isset($col[3]) ? $col[3] : 1;
                            }
                        } catch (\Exception $e) {
                            $logs = array();
                        }
                    }
                }
                self::$migration_logs = $logs;
            }
            return self::$migration_logs;
        }

        public static function states_to_array($states = array())
        {
            AuthHelper::states_to_array($states);
        }

        public static function get_tenweb_user_info($refresh = false, $forceLocalData = false)
        {

            if (self::$user_info != null && $refresh == false) {
                return self::$user_info;
            }

            $transient = get_site_transient(TENWEB_PREFIX . '_user_info_transient');
            $user_info_cache = get_site_option(TENWEB_PREFIX . '_user_info');

            //This should be done after user management release because we do not get client data from core after update
            $old_agreement_info = get_site_option(TENWEB_PREFIX . '_user_agreements', null);
            if (!empty($old_agreement_info) && !isset($user_info_cache['agreement_info'])) {
                update_site_option(TENWEB_PREFIX . '_user_info', array('client_info' => $user_info_cache, 'agreement_info' => $old_agreement_info));
                delete_site_option(TENWEB_PREFIX . '_user_agreements');
                $user_info_cache = get_site_option(TENWEB_PREFIX . '_user_info');
            }


            if (!$forceLocalData && ($transient == false || $user_info_cache == false || $refresh == true)) {

                $user_info = Api::get_instance()->get_user_info();

                if (!is_null($user_info)) {
                    $expiration = self::$expiration['user_info']['expiration'];
                    Helper::calc_request_block('user_info', true);
                } else {
                    $user_info['client_info'] = array(
                        'name'            => 'Unknown',
                        'timezone_offset' => 0
                    );
                    $user_info['agreement_info'] = array();
                    $block_count = Helper::calc_request_block('user_info');
                    $expiration = self::$expiration['user_info']['block_time'] * $block_count;
                }

                set_site_transient(TENWEB_PREFIX . '_user_info_transient', '1', $expiration);
                update_site_option(TENWEB_PREFIX . '_user_info', $user_info);

            }

            self::$user_info = (!empty($user_info)) ? $user_info : $user_info_cache;

            return self::$user_info;
        }

        public static function calc_request_block($key, $reset = false)
        {
            return AuthHelper::calc_request_block($key, $reset);
        }

        public static function get_products_state()
        {
            return AuthHelper::get_products_state();
        }

        public static function clear_cache()
        {
            AuthHelper::clear_cache();
        }

        public static function clear_optimizer_cache($regenerateCriticalCss = false)
        {
            $two_token = uniqid("two_", true);
            set_transient("two_token_clear_cache", $two_token, HOUR_IN_SECONDS);
            wp_remote_post(
                admin_url('admin-ajax.php'),
                array(
                    'blocking'  => false,
                    'timeout' => 0.1,
                    'sslverify' => false,
                    'body'      => array(
                        'action'                  => 'two_manager_clear_cache',
                        'regenerate_critical_css' => $regenerateCriticalCss,
                        'two_token'               => $two_token
                    ),
                )
            );
        }

        public static function save_configs($data)
        {
            $config_json = file_get_contents(TENWEB_DIR . '/config.json');
            $config = json_decode($config_json, true);
            if (isset($data['migration_debug'])) {
                $config['TENWEB_MIGRATION_DEBUG'] = $data['migration_debug'];
            }
            if (isset($data['migration_logs_in_db'])) {
                $config['TENWEB_MIGRATION_LOGS_IN_DB'] = $data['migration_logs_in_db'];
            }
            if (isset($data['migration_encrypt_db'])) {
                $config['TENWEB_MIGRATION_ENCRYPT_DB'] = $data['migration_encrypt_db'];
            }
            if (isset($data['migration_archive_type'])) {
                $config['TENWEB_MIGRATION_ARCHIVE_TYPE'] = $data['migration_archive_type'];
            }
            if (isset($data['migration_max_files_restart'])) {
                $config['TENWEB_MIGRATION_MAX_FILES_RESTART'] = ceil($data['migration_max_files_restart']);
            }
            if (isset($data['migration_max_db_rows_restart'])) {
                $config['TENWEB_MIGRATION_MAX_DB_ROWS_RESTART'] = ceil($data['migration_max_db_rows_restart']);
            }
            if (isset($data['migration_bulk_files_count'])) {
                $config['TENWEB_MIGRATION_BULK_FILES_COUNT'] = ceil($data['migration_bulk_files_count']);
            }
            if (isset($data['migration_bulk_db_rows_count'])) {
                $config['TENWEB_MIGRATION_BULK_DB_ROWS_COUNT'] = ceil($data['migration_bulk_db_rows_count']);
            }
            if (isset($data['migration_file_size_limit'])) {
                $config['TENWEB_MIGRATION_FILE_SIZE_LIMIT'] = ceil($data['migration_file_size_limit']);
            }
            if (isset($data['migration_multiple_archives'])) {
                $config['TENWEB_MIGRATION_MULTIPLE_ARCHIVES'] = ceil($data['migration_multiple_archives']);
            }
            if (isset($data['migration_exec_time_offset'])) {
                $config['TENWEB_MIGRATION_EXEC_TIME_OFFSET'] = ceil($data['migration_exec_time_offset']);
            }
            if (isset($data['migration_upload_archive_s3'])) {
                $config['TENWEB_MIGRATION_UPLOAD_ARCHIVE_S3'] = $data['migration_upload_archive_s3'];
            }
            if (isset($data['migration_multipart_upload_chunk_size'])) {
                $config['TENWEB_MIGRATION_MULTIPART_UPLOAD_CHUNK_SIZE'] = ceil($data['migration_multipart_upload_chunk_size']);
            }

            if (isset($data['migration_sftp'])) {
                $config['TENWEB_MIGRATION_SFTP'] = $data['migration_sftp'];
            }
            if (isset($data['migration_sftp_state_files_count'])) {
                $config['TENWEB_MIGRATION_SFTP_STATE_FILES_COUNT'] = $data['migration_sftp_state_files_count'];
            }
            file_put_contents(TENWEB_DIR . '/config.json', json_encode($config));
        }

        public static function delete_banned_ip($ips)
        {
            if (is_file(WPMU_PLUGIN_DIR . '/10web-manager/10web-manager.php')) {
                if (class_exists('TWIPBanning')) {
                    $banning = new \TWIPBanning();
                    $ips = explode(',', $ips);
                    foreach ($ips as $ip) {
                        $banning->setIp($ip);
                        $banning->remove();
                    }
                }
            }
        }


        public static function get_tmp_dir($force_upload_dir = false)
        {
            $sys_tmp_dir = sys_get_temp_dir();
            if (!$force_upload_dir && !self::get_configs('TENWEB_MIGRATION_DEBUG') && is_dir($sys_tmp_dir) && is_readable($sys_tmp_dir)) {
                $tmp_dir = $sys_tmp_dir . "/10web_tmp";

            } else {
                $tmp_dir = self::get_uploads_dir() . "/10web_tmp";
            }


            if (!is_dir($tmp_dir)) {
                mkdir($tmp_dir);
            }

            return $tmp_dir;
        }


        public static function get_uploads_dir()
        {
            $uploads_dir = wp_upload_dir();

            return $uploads_dir["basedir"];
        }


        public static function get_configs($option = null)
        {
            $config_json = array();
            if (file_exists(TENWEB_DIR . '/config.json')) {
                $config_json = file_get_contents(TENWEB_DIR . '/config.json');
                $config_json = json_decode($config_json, true);
            }

            $configs = array();

            $configs['TENWEB_MIGRATION_DEBUG'] = isset($config_json['TENWEB_MIGRATION_DEBUG']) ? $config_json['TENWEB_MIGRATION_DEBUG'] : 1;
            $configs['TENWEB_MIGRATION_LOGS_IN_DB'] = isset($config_json['TENWEB_MIGRATION_LOGS_IN_DB']) ? $config_json['TENWEB_MIGRATION_LOGS_IN_DB'] : 0;
            $configs['TENWEB_MIGRATION_ENCRYPT_DB'] = isset($config_json['TENWEB_MIGRATION_ENCRYPT_DB']) ? $config_json['TENWEB_MIGRATION_ENCRYPT_DB'] : 1;
            $configs['TENWEB_MIGRATION_ARCHIVE_TYPE'] = isset($config_json['TENWEB_MIGRATION_ARCHIVE_TYPE']) ? $config_json['TENWEB_MIGRATION_ARCHIVE_TYPE'] : "nelexa_zip";
            $configs['TENWEB_MIGRATION_MAX_FILES_RESTART'] = isset($config_json['TENWEB_MIGRATION_MAX_FILES_RESTART']) ? $config_json['TENWEB_MIGRATION_MAX_FILES_RESTART'] : 10000;
            $configs['TENWEB_MIGRATION_MAX_DB_ROWS_RESTART'] = isset($config_json['TENWEB_MIGRATION_MAX_DB_ROWS_RESTART']) ? $config_json['TENWEB_MIGRATION_MAX_DB_ROWS_RESTART'] : 100000;
            $configs['TENWEB_MIGRATION_BULK_FILES_COUNT'] = isset($config_json['TENWEB_MIGRATION_BULK_FILES_COUNT']) ? $config_json['TENWEB_MIGRATION_BULK_FILES_COUNT'] : 10000;
            $configs['TENWEB_MIGRATION_BULK_DB_ROWS_COUNT'] = isset($config_json['TENWEB_MIGRATION_BULK_DB_ROWS_COUNT']) ? $config_json['TENWEB_MIGRATION_BULK_DB_ROWS_COUNT'] : 5000;
            $configs['TENWEB_MIGRATION_FILE_SIZE_LIMIT'] = isset($config_json['TENWEB_MIGRATION_FILE_SIZE_LIMIT']) ? $config_json['TENWEB_MIGRATION_FILE_SIZE_LIMIT'] : 300000000;
            $configs['TENWEB_MIGRATION_MULTIPLE_ARCHIVES'] = isset($config_json['TENWEB_MIGRATION_MULTIPLE_ARCHIVES']) ? $config_json['TENWEB_MIGRATION_MULTIPLE_ARCHIVES'] : 1;
            $configs['TENWEB_MIGRATION_EXEC_TIME_OFFSET'] = isset($config_json['TENWEB_MIGRATION_EXEC_TIME_OFFSET']) ? $config_json['TENWEB_MIGRATION_EXEC_TIME_OFFSET'] : 10;
            $configs['TENWEB_MIGRATION_UPLOAD_ARCHIVE_S3'] = isset($config_json['TENWEB_MIGRATION_UPLOAD_ARCHIVE_S3']) ? $config_json['TENWEB_MIGRATION_UPLOAD_ARCHIVE_S3'] : 1;
            $configs['TENWEB_MIGRATION_MULTIPART_UPLOAD_CHUNK_SIZE'] = isset($config_json['TENWEB_MIGRATION_MULTIPART_UPLOAD_CHUNK_SIZE']) ? $config_json['TENWEB_MIGRATION_MULTIPART_UPLOAD_CHUNK_SIZE'] : 5;
            $configs['TENWEB_MIGRATION_SFTP'] = isset($config_json['TENWEB_MIGRATION_SFTP']) ? $config_json['TENWEB_MIGRATION_SFTP'] : 0;
            $configs['TENWEB_MIGRATION_SFTP_STATE_FILES_COUNT'] = isset($config_json['TENWEB_MIGRATION_SFTP_STATE_FILES_COUNT']) ? $config_json['TENWEB_MIGRATION_SFTP_STATE_FILES_COUNT'] : 100;


            if ($option !== null) {
                return isset($configs[$option]) ? $configs[$option] : false;
            }

            return $configs;
        }

        public static function get_default_configs()
        {
            return array(
                'TENWEB_MIGRATION_DEBUG_DEFAULT'               => 1,
                'TENWEB_MIGRATION_LOGS_IN_DB'                  => 0,
                'TENWEB_MIGRATION_ENCRYPT_DB_DEFAULT'          => 1,
                'TENWEB_MIGRATION_ARCHIVE_TYPE_DEFAULT'        => 'nelexa_zip',
                'TENWEB_MIGRATION_MAX_FILES_RESTART_DEFAULT'   => 10000,
                'TENWEB_MIGRATION_MAX_DB_ROWS_RESTART_DEFAULT' => 100000,
                'TENWEB_MIGRATION_BULK_FILES_COUNT_DEFAULT'    => 10000,
                'TENWEB_MIGRATION_BULK_DB_ROWS_COUNT_DEFAULT'  => 5000,
                'TENWEB_MIGRATION_FILE_SIZE_LIMIT_DEFAULT'     => 300000000,
                'TENWEB_MIGRATION_MULTIPLE_ARCHIVES_DEFAULT'   => 1,
                'TENWEB_MIGRATION_EXEC_TIME_OFFSET_DEFAULT'    => 10,
                'TENWEB_MIGRATION_RETRY_ARCHIVE_TYPE'          => 'gzip',
                'TENWEB_MIGRATION_UPLOAD_ARCHIVE_S3'           => 1,
                'TENWEB_MIGRATION_MULTIPART_UPLOAD_CHUNK_SIZE' => 5,
                'TENWEB_MIGRATION_SFTP'                     => 0,
                'TENWEB_MIGRATION_SFTP_STATE_FILES_COUNT'   => 100
            );
        }


        public static function add_notices($notice_text, $error = true)
        {
            $container_class = "notice";
            if ($error) {
                $container_class .= " error";
            }
            if (!function_exists('get_current_screen')) {
                return false;
            }

            $screen = get_current_screen();
            $notice = '<div class="' . $container_class . ' tenweb_manager_notice ' . ($screen->parent_base == "tenweb_menu" ? "tenweb_menu_notice" : "") . '"><img src="'.TENWEB_URL.'/assets/images/error.svg" />
<p>' . $notice_text . '</p>'
                . '</div>';
            self::$notices[] = $notice;
        }

        public static function get_notices()
        {
            return self::$notices;
        }
//
//        private static function get_theme_screenshot_url($slug)
//        {
//            return AuthHelper::get_theme_screenshot_url($slug);
//
//        }

        public static function check_if_manager_mu()
        {
            return AuthHelper::check_if_manager_mu();
        }

        public static function get_migration_archive_type()
        {
            $configs = self::get_configs();


            if (isset($configs['TENWEB_MIGRATION_ARCHIVE_TYPE'])) {
                return $configs['TENWEB_MIGRATION_ARCHIVE_TYPE'];
            }

            if (class_exists('ZipArchive') && extension_loaded('zip')) {
                return 'zip';
            }

            return 'gzip';
        }

        public static function get_company_name()
        {

            if (defined('TENWEB_COMPANY_NAME') && TENWEB_COMPANY_NAME && strtolower(TENWEB_COMPANY_NAME) != '10web manager') {
                return TENWEB_COMPANY_NAME;
            }

            return '10WEB';

        }

        public static function get_cache_text()
        {
            if (defined('TENWEB_WHITELABEL_DIR') && is_file(TENWEB_WHITELABEL_DIR . '/text/cache_text.txt')) {
                $content = file_get_contents(TENWEB_WHITELABEL_DIR . '/text/cache_text.txt');

                if ($content != '') {
                    return htmlentities($content);
                }
            }

            return '<span>We use NGINX FastCGI Static Page Caching to cache everything from pages to feeds to 301-redirects on sub-domains.<br class="line-break"/> It helps your site to load super fast for your non-logged-in visitors.</span>';

        }

        public static function get_cache_icon()
        {
            if (defined('TENWEB_WHITELABEL_DIR') && is_dir(TENWEB_WHITELABEL_DIR . '/images')) {
                $icon_dir = TENWEB_WHITELABEL_DIR . '/images';
                $icons = scandir($icon_dir);

                //return first element
                if (isset($icons[2]) && $icons[2] != '.' && $icons != '..') {
                    return TENWEB_URL_WHITELABEL . '/images/' . $icons[2];
                }
            }


            return TENWEB_URL_IMG . '/cache-logo.svg';
        }

        /**
         * Check if plugin or theme activate
         *
         * @return bool
         */
        public static function get_products_diff()
        {
            $active_products_list = get_option("tenweb_active_products_list");
            $active_products_current = get_option('active_plugins');
            $active_theme = get_option('stylesheet');
            array_push($active_products_current, $active_theme);
            if (is_multisite()) {
                $active_plugins_network = get_site_option('active_sitewide_plugins');
                $active_products_current = array_merge($active_products_current, $active_plugins_network);
            }
            if (is_array($active_products_list) && is_array($active_products_current)) {
                $diff = array_merge(array_diff($active_products_current, $active_products_list), array_diff($active_products_list, $active_products_current));
                if (!empty($diff)) {
                    update_option("tenweb_active_products_list", $active_products_current);
                    return true;
                } else {
                    return false;
                }
            } else {
                update_option("tenweb_active_products_list", $active_products_current);
            }

            return true;
        }

        /**
         * @param $screen
         *
         * @return bool
         */
        public static function get_site_info_diff($screen)
        {
            return AuthHelper::get_site_info_diff($screen);
        }

        public static function current_user_have_access()
        {
            $current_user = wp_get_current_user();
            $username = $current_user->user_login;
            $users = self::set_white_label_users();

            return in_array($username, $users, true);

        }

        public static function set_white_label_users()
        {
            $users = array();

            if (defined('TENWEB_WL_USERS')) {
                $users = array_map('trim', explode(',', TENWEB_WL_USERS));;
            }

            return array_merge(self::$white_label_users, $users);
        }

        public static function is_full_white_label()
        {

            return defined('TENWEB_WL_FULL') && TENWEB_WL_FULL === true;

        }

        public static function is_manager_user()
        {
            $tenweb_current_user = wp_get_current_user();
            if ($tenweb_current_user->user_login === "tenweb_manager_plugin") {
                return true;
            }

            return false;
        }

        /**
         * Get MimeType from file.
         *
         * @param string $file
         *
         * @return string
         */
        public static function getMimeTypeFromFile( $file = '' ) {
            $mime = 'application/octet-stream';
            if ( !empty($file) ) {
                if ( function_exists('mime_content_type') ) {
                    $mime = mime_content_type($file);
                }
                else {
                    // get a file extension.
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $mime_types = wp_get_mime_types();
                    if ( array_key_exists($ext, $mime_types) ) {
                        $mime = $mime_types[$ext];
                    }
                }
            }

            return $mime;
        }

        /**
         * Check if current site is an agency customer
         * Returns true if tenweb_agency_dashboard_url option exists and is not empty, false otherwise
         *
         * @return bool
         */
        public static function is_agency_customer()
        {
            $agency_dashboard_url = get_option('tenweb_agency_dashboard_url', '');
            
            return !empty($agency_dashboard_url);
        }

    }
}
