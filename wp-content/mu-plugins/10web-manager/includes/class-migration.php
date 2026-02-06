<?php

namespace Tenweb_Manager {

    use phpseclib3\Net\SFTP;

    class Migration
    {
        const MIGRATION_DB_FILE_NAME     = "10web_migration_db.sql";
        const MIGRATION_CONFIG_FILE_NAME = "10web_migration_config.json";
        const ROOT_FILES_NAME            = array('robots.txt', 'favicon.ico', 'sitemap.xml');
        const MIGRATION_ARCHIVE          = "10web_migration";
        protected $archive_dir = null;
        protected $configs = null;

        protected $current_archive_index;
        protected $files_count = null; // number of files, already added to the archive
        protected $total_files_count = null; // number of founded files
        protected $uploads_dir;
        protected $archive;
        protected $sftp;

        /**
         * Migration constructor.
         *
         */
        public function __construct()
        {
            $this->archive_dir = Helper::get_tmp_dir();
            $this->configs = Helper::get_configs();
        }

        /**
         * @param $i
         * @return string
         */
        public static function getMigrationArchive($i = '')
        {
            // we need zlib to use gzencode function
            if (Helper::get_migration_archive_type() == 'gzip') {
                return self::MIGRATION_ARCHIVE . $i . '.tar.gz';
            } else {
                return self::MIGRATION_ARCHIVE . $i . '.zip';
            }
        }


        /**
         * @param $delete_db_options
         * @param $exclusions array full paths to files to exclude from deletion
         *
         * function for removing  files, if something goes wrong
         */
        public static function rollback($delete_db_options = true, $exclusions = array())
        {
            self::recursive_remove_dir(Helper::get_tmp_dir(), $exclusions);
            if ($delete_db_options === true) {
                self::rollback_db();
            }
        }

        public static function scan_archive_dir()
        {
            $archive_dir = Helper::get_tmp_dir();
            $files = scandir($archive_dir);
            $result = array();

            if ($files) {
                foreach ($files as $file) {
                    if ($file != "." && $file != "..") {
                        $result[$file] = (integer)filesize( $archive_dir . "/" . $file);
                    }
                }
            }

            return $result;
        }

        public static function rollback_db()
        {
            delete_site_transient('tenweb_subdomain');
            delete_site_transient('tenweb_migrate_live');
            delete_site_transient('tenweb_migrate_domain_id');
            delete_site_transient('tenweb_migrate_region');
            delete_site_transient('tenweb_tp_domain_name');
            delete_site_option('tenweb_is_db_encrypt_failed');
        }
        /**
         * @param $dir string
         * @param $exclusions array full paths to files to exclude from deletion
         *
         * function for recursively deletes a directory and all of its contents
         */
        public static function recursive_remove_dir($dir, $exclusions = array())
        {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir . "/" . $object)) {
                            self::recursive_remove_dir($dir . "/" . $object, $exclusions);
                        } else {
                            if (!in_array($dir . "/" . $object, $exclusions)) {
                                unlink($dir . "/" . $object);
                            } else {
                                Helper::store_migration_log('exclusion_while_recursive_remove_dir_' . $object, $dir . "/" . $object . ' is excluded from deletion.');
                            }
                        }
                    }
                }
                if (count(scandir($dir))==2) {
                    rmdir($dir);
                }
            }
        }

        protected function check_for_restart()
        {
            $max_exec_time_server = ini_get('max_execution_time');
            $start = get_site_transient(TENWEB_PREFIX . "_migration_start_time");
            $script_exec_time = microtime(true) - $start;

            if ($script_exec_time >= ((int)$max_exec_time_server - $this->configs['TENWEB_MIGRATION_EXEC_TIME_OFFSET']) || ($this->files_count != 0 && $this->files_count % $this->configs['TENWEB_MIGRATION_MAX_FILES_RESTART'] == 0)) {
                $this->write_object_file();
                $this->restart();

                return false;
            }
        }

        public function restart()
        {
            Helper::store_migration_log('start_restart_' . current_time('timestamp'), 'Starting restart.');
            update_site_option('tenweb_migration_restart', 1);

            $url = add_query_arg(array('rest_route' => '/' . TENWEB_REST_NAMESPACE . '/restart_migration_file', 't' => current_time('timestamp')), get_home_url() . "/");
            $restart_resp = wp_remote_post($url, array('method' => 'POST', 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36', 'timeout' => 0.1, 'body' => array('tenweb_nonce' => wp_create_nonce('wp_rest'))));

            if (is_wp_error($restart_resp)) {
                Helper::store_migration_log('response_restart_err', $restart_resp->get_error_message());
            }

            Helper::store_migration_log('end_restart_' . current_time('timestamp'), 'End restart. ' . $url);
            die('{"tenweb_restart": "1"}');
        }

        protected function write_object_file()
        {
            $this->disconnectSFTP();
            //$t = current_time('timestamp');
            //Helper::store_migration_log('start_write_object_file_' . $t, 'Starting write object file in content.');
            $this->archive = null;
            $this->current_archive_index++;
            $content = serialize($this);
            //Helper::store_migration_log('serialized_object_content_' . $t, 'Object serialized.');
            file_put_contents($this->archive_dir . '/content_object.txt', $content);
            //Helper::store_migration_log('end_write_object_file_' . $t, 'End write object file in content.');
        }

        public static function get_object_file_content()
        {
            $content = file_get_contents(Helper::get_tmp_dir() . '/content_object.txt');

            return unserialize($content);
        }


        /**
         * @param $dir
         *
         * @return array
         */
        protected function get_files($dir)
        {
            $files = array();
            $skipped_files = array();

            $innerIterator = new \RecursiveDirectoryIterator($dir, \RecursiveIteratorIterator::LEAVES_ONLY);
            $filter = $this->get_filter();
            $iterator = new \RecursiveIteratorIterator(new \RecursiveCallbackFilterIterator($innerIterator, $filter));

            foreach ($iterator as $file) {
                $file_path = $file->getRealPath();

                if (!is_dir($file_path)) {
                    try {
                        $file_size = $file->getSize();
                    } catch (\Exception $e) {
                        $file_size = -1;
                    }

                    if ($file_size != -1 && $file_size < $this->configs['TENWEB_MIGRATION_FILE_SIZE_LIMIT']) {
                        $files[] = array('file_path' => $file_path, 'file_size' => $file_size);
                    } else {
                        $skipped_files[] = $file_path;
                    }

                } else if ($this->dir_is_empty($file_path)) {
                    $files[] = array('file_path' => $file_path, 'file_size' => 0);;
                }
            }

            if (count($skipped_files)) {
                Helper::store_migration_log('skipped_files', json_encode($skipped_files));
            }

            return array_unique($files, SORT_REGULAR);
        }

        protected function get_filter()
        {
            $path_regex = $this->get_exclude_regex();

            $filter = function ($file, $key, $iterator) use ($path_regex) {
                return !preg_match($path_regex, $file->getRealPath(), $matches);
            };

            return $filter;
        }


        protected function get_exclude_regex()
        {
            $excluded_files = array(
                'imagecache',
                'wp\-content\/w3tc',
                'wp\-content\/w3\-',
                'wp\-content\/wflogs',
                'wp\-content\/mu\-plugins\/sg\-cachepress',
                'wp\-content\/plugins\/sg\-cachepress',
                'wp\-content\/plugins\/sg\-security',
                'wp\-content\/plugins\/imagemagick\-engine',
                'wp\-content\/plugins\/wp\-asset\-clean\-up',
                'wp\-content\/mu\-plugins\/wpmudev\-hosting\.php',
                'wp\-content\/mu\-plugins\/wpmudev\-hosting',
                'wp\-content\/mu\-plugins\/wpengine\-security\-auditor\.php',
                'wp\-content\/mu\-plugins\/wpe\-wp\-sign\-on\-plugin',
                'wp\-content\/mu\-plugins\/wpe\-wp\-sign\-on\-plugin\.php',
                'wp\-content\/mu\-plugins\/wpe_bnseosnvlsoier_private_ips\.php',
                'wp\-content\/mu\-plugins\/slt\-force\-strong\-passwords\.php',
                'wp\-content\/mu\-plugins\/stop\-long\-comments\.php',
                'wp\-content\/mu\-plugins\/force\-strong\-passwords',
                'wp\-content\/mu\-plugins\/force\-strong\-passwords\-wpe\-edition',
                'wp\-content\/mu\-plugins\/wp\-engine\-cache\-plugin',
                'wp\-content\/mu\-plugins\/wp\-engine\-seamless\-login\-plugin',
                'wp\-content\/mu\-plugins\/wp\-engine\-security\-auditor',
                'wp\-content\/mu\-plugins\/wp\-engine\-system',
                'wp\-content\/mu\-plugins\/wpe\-elasticpress\-autosuggest\-logger',
                'wp\-content\/(?:mu\-)?plugins\/10web-manager',
                'wp\-content\/object\-cache\.php$',
                'wp\-content\/envato\-backups',
                'wp\-content\/Dropbox_Backup',
                'wp\-content\/et\-cache',
                'wp\-content\/backup\-db',
                'wp\-content\/backup$',
                'wp\-content\/upready$',
                'wp\-content\/db$',
                "wp\-content\/.*\-wprbackups$",
                "wp\-content\/.*\-backups$",
                'wp\-content\/updraft$',
                'wp\-content\/updraftplus$',
                'wp\-content\/wpvividbackups',
                'wp\-content\/ew\-backup',
                'wp\-content\/wphb\-cache',
                'wp\-content\/wpo\-cache',
                '\.htaccess',
                '\._htaccess',
                'wp\-config\-sample\.php',
                'mu\-plugins\/wpengine\-common',
                'mu\-plugins\/mu\-plugin\.php',
                'mu\-plugins\/kinsta\-mu\-plugins\.php',
                '\.svn$',
                '\.git$',
                '\.log$',
                '\.tmp$',
                '\.listing$',
                '\.cache$',
                '\.bak$',
                '\.swp$',
                '\~',
                '_wpeprivate',
                'wp\-content\/cache',
                'wp\-content\/cache_old',
                'ics\-importer\-cache',
                'gt\-cache',
                'plugins\/wpengine\-snapshot\/snapshots',
                'wp\-content\/backups',
                'wp\-content\/managewp',
                'wp\-content\/upgrade',
                'kinsta\-mu\-plugins',
                'wp\-content\/advanced\-cache\.php',
                'wp\-content\/wp\-cache\-config\.php',
                'wp\-content\/advanced\-cache\.php',
                'wp\-content\/wp\-cache\-config\.php',
                'ai1wm\-backups$',
                'uploads\/snapshots',
                'uploads\/backup',
                'uploads\/backups',
                'uploads\/em\-cache',
                'uploads\/mainwp\/backup',
                'uploads\/wp\-file\-manager\-pro\/fm_backup',
                'uploads\/ewpt_cache',
                'uploads\/ShortpixelBackups',
                'uploads\/backupbuddy_backups',
                'uploads\/backupbuddy_temp',
                'uploads\/webarx\-backup',
                'uploads\/iw\-backup',
                'uploads\/fw\-backup',
                'uploads\/10web_tmp',
                'uploads\/wp\-clone',
                'uploads\/omgf$',
                'uploads\/fusion\-styles$',
                'uploads\/siteground\-optimizer\-assets$',
                'uploads\/cache',
                'wp\-content\/bps\-backup',
                'wp\-content\/wptouch\-data',
                'aiowps_backups$',
                'aiowps\-backups$',
                'mu\-plugins\/wp\-stack\-cache\.php'
              );

            $excluded_files[] = preg_quote($this->archive_dir, '/');

            return "/(" . implode("|", $excluded_files) . ")/i";
        }


        /**
         * @return array
         */
        protected function get_content_files()
        {
            Helper::store_migration_log('start_get_content_files', 'Starting get_content_files function.');
            $all_files = array();

            // get wp-content files
            $all_files['wp-content'] = $this->get_chunks(WP_CONTENT_DIR, 'wpcontent');

            // get media files
            if (strpos($this->uploads_dir, WP_CONTENT_DIR) === false) {
                $uploads_dir_basename = str_replace(ABSPATH, '', $this->uploads_dir);
                $all_files[$uploads_dir_basename] = $this->get_chunks($this->uploads_dir, 'uploads');
            }

            Helper::store_migration_log('end_get_content_files', 'End get_content_files function.');

            return $all_files;
        }


        /**
         * @param $dir
         *
         * @return array
         */
        protected function get_chunks($dir, $type)
        {
            $unique_files = $this->get_files($dir);

            $files = array();
            $i = 0;//bulk files count
            $j = 0;//bulk files size
            $total_files_count = 0;
            $bulk_files = array();

            if (!empty($unique_files)) {
                foreach ($unique_files as $key => $file) {
                    if ($j + $file['file_size'] > $this->configs['TENWEB_MIGRATION_FILE_SIZE_LIMIT']) {
                        $files[] = $bulk_files;
                        $total_files_count += $i;
                        $i = 0;
                        $j = 0;
                        $bulk_files = array();
                    }
                    $bulk_files[] = $file['file_path'];
                    $i++;
                    $j += $file['file_size'];
                    if ($i == $this->configs['TENWEB_MIGRATION_BULK_FILES_COUNT']) {
                        $files[] = $bulk_files;
                        $total_files_count += $i;
                        $i = 0;
                        $j = 0;
                        $bulk_files = array();
                    }
                }
            }

            //adding the remaining files to a separate chunk
            if ($i > 0) {
                $files[] = $bulk_files;
                $total_files_count += $i;
            }

            $this->total_files_count = $total_files_count;
            Helper::update_migration_state('total_files_count', $total_files_count);
            Helper::store_migration_log('total_files_count_in_archive_in_' . $type, $total_files_count . ' files in ' . $dir);

            return $files;
        }

        /**
         * @param $dir
         *
         * @return bool
         */
        public function dir_is_empty($dir)
        {
            $handle = opendir($dir);
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    return false;
                }
            }

            return true;
        }

        public function login_sftp() {

            $sftp_credentials = get_site_transient('tenweb_sftp_credentials');
            if (empty($sftp_credentials)){
                Helper::store_migration_log('login_sftp_fail', 'error in login sftp', 0);
                return false;
            }
            $this->sftp = new SFTP($sftp_credentials['ip'], $sftp_credentials['port']); // Replace 'server' with your server ip address, or server name
            $restart_count = get_site_transient('tenweb_migration_sftp_restart') ?? 0;
            try {
              //Helper::store_migration_log('sftp credentials', json_encode($sftp_credentials));
                if (!$this->sftp->login($sftp_credentials['username'], $sftp_credentials['password'])){
                    Helper::store_migration_log('checking_login_sftp', 'sftp login failed', 0);
                    $this->write_object_file();
                    $this->restart();
                } else {
                    Helper::store_migration_log('checking_login_sftp', 'sftp login is ok');
                }
            }
            catch (\Exception $e) {
                Helper::set_error_log('error in login sftp', $e->getMessage());
                Helper::store_migration_log('login_sftp_fail', 'error in login sftp', 0);
                $restart_count++;
                set_site_transient('tenweb_migration_sftp_restart', $restart_count, 60 * 15);
                if ($restart_count <= 2) {
                    $this->write_object_file();
                    $this->restart();
                }
            }
        }

        public function check_if_sftp_logged_in() {

            if (!$this->sftp) {
                $this->login_sftp();
            }
        }
        public function send_config_and_db($data, $run_type = 'run_db') {

            $dir = TENWEB_WP_DIR . '/10web_meta/';
            $file = ($run_type !== 'run_config') ? ($dir . self::MIGRATION_DB_FILE_NAME) :
                ($dir . self::MIGRATION_CONFIG_FILE_NAME);

         //   Helper::store_migration_log('send_sftp_config_and_db', $file);

            $this->check_if_sftp_logged_in();

            if (!$this->sftp->is_dir($dir)) {
                $this->sftp->mkdir($dir, 0755, true);
            }
            if ($this->sftp->file_exists($file)) {
                $this->sftp->put($file, $data, SFTP::RESUME);
            } else {
                $this->sftp->put($file, $data, SFTP::RESUME_START);
            }
            Helper::store_migration_log('send_sftp_config_and_db', 'end');
            return true;
        }

        public function disconnectSFTP(){
            try {
                if(!empty($this->sftp)){
                    $this->sftp->disconnect();
                    unset($this->sftp);
                    $this->sftp = null;
                }
            } catch (\Exception $e) {
                Helper::store_migration_log('error in disconnectSFTP', $e->getMessage(), 0);
            }
        }
    }
}
