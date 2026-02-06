<?php


namespace Tenweb_Manager {

    use Araqel\Archive\Archive as Archive;

    class MigrationContent extends Migration
    {

        private $archive_path = null;
        private $files = null;
//        protected $files_count = null;
//        private $total_files_count = null;
        private $method;
       // private $uploads_dir;


        public function __construct()
        {

            parent::__construct();
            // if everything ok, create zip or tar archive
            $this->archive_path = $this->archive_dir . "/" . self::getMigrationArchive();
            $this->method = Helper::get_migration_archive_type();
            $this->current_archive_index = 1;
        }

        /**
         * @param string $run_type
         *
         * @return int|void
         * @throws \Exception
         */
        public static function set_up($run_type = 'run')
        {
            if ($run_type == 'run') {
                //Helper::store_migration_log('run_type_' . current_time('timestamp'), 'Run type is run.');
                $migration_content = new self();

                return $migration_content->run($run_type);
            }

            if ($run_type == 'restart') {
                //Helper::store_migration_log('run_type_' . current_time('timestamp'), 'Run type is restart.');
                $migration_content = Migration::get_object_file_content();

                return $migration_content->run($run_type);
            }
        }


        /**
         * @param $run_type
         *
         * @return int
         * @throws \Exception
         */
        public function run($run_type)
        {
            $this->uploads_dir = Helper::get_uploads_dir();

            if ($this->configs['TENWEB_MIGRATION_MULTIPLE_ARCHIVES']) {
                $this->archive_path = $this->archive_dir . "/" . self::getMigrationArchive($this->current_archive_index);
            }

            $this->archive = Archive::create($this->archive_path, $this->method);

            if ($run_type == "run") {
                Helper::store_migration_log('wp_content_dir', WP_CONTENT_DIR);
                Helper::store_migration_log('uploads_dir', $this->uploads_dir);

                // check if zip or zlib extension exists
                if ($this->check_if_zip_extension_exists() === false && $this->check_if_zlib_extension_exists() === false) {
                    throw new \Exception("PHP zip or zlib extension is missing");
                }

                // check if config json exists
                if (!file_exists($this->archive_dir . "/" . self::MIGRATION_CONFIG_FILE_NAME)) {
                    throw new \Exception("Config json file is missing");
                }

                // check if db sql exists
                if (!file_exists($this->archive_dir . "/" . self::MIGRATION_DB_FILE_NAME)) {
                    throw new \Exception("Database file is missing");
                }
                $this->files = $this->get_content_files();
                $root_files = $this->get_root_files();

                Helper::store_migration_log('add_meta_data_to_archive', "Adding meta data..");
                $conf = $this->archive_dir . "/" . self::MIGRATION_CONFIG_FILE_NAME;
                $db = $this->archive_dir . "/" . self::MIGRATION_DB_FILE_NAME;
                Helper::store_migration_log('meta_conf', $conf);
                Helper::store_migration_log('meta_db', $db);
                Helper::update_migration_state('meta_data_added', 0);
                $this->archive->addFiles(array($conf, $db), '10web_meta', $this->archive_dir);
                Helper::update_migration_state('meta_data_added', 1);
                Helper::store_migration_log('added_meta_data_to_archive', "Meta data added..");

                // Added root files in archive.
                if ( !empty($root_files) ) {
                  $this->archive->addFiles($root_files, '', ABSPATH .'/');
                }
            }

            Helper::store_migration_log('start_create_archive_' . current_time('timestamp'), $this->archive_path);
            $this->archive->setIgnoreRegexp($this->get_exclude_regex());
            $this->create_archive();

            update_site_option('tenweb_migration_archive_count', $this->current_archive_index);

            return $this->current_archive_index;
        }

        /**
         * @return bool
         */
        private function check_if_zip_extension_exists()
        {
            if (!extension_loaded('zip')) {
                return false;
            }

            Helper::store_migration_log('zip_extension_exists', 'Zip extension exists.');

            return true;
        }

        /**
         * @return bool
         */
        private function check_if_zlib_extension_exists()
        {
            if (!extension_loaded('zlib')) {
                return false;
            }

            Helper::store_migration_log('zlib_extension_exists', 'Zlib extension exists.');

            return true;
        }

        private function create_archive()
        {
            foreach ($this->files as $type => &$files) {
                if (!empty($files)) {
                    foreach ($files as $key => $files_chunk) {
                        unset($files[$key]);

                        if ($type == "wp-content") {
                            $addDir = 'wp-content';

                            if (WP_CONTENT_DIR == '/wp-content') {
                                $rmdir = "/";
                                $addDir = "";
                            } else if (WP_CONTENT_DIR == '//wp-content' || WP_CONTENT_DIR == '//wp-content/') {
                                $rmdir = "//";
                                $addDir = "";
                            } else {
                                $rmdir = WP_CONTENT_DIR;
                                
                                if (strpos(WP_CONTENT_DIR, '../') !== false or strpos(WP_CONTENT_DIR, './') !== false) {
                                    $rmdir = strpos(WP_CONTENT_DIR, ABSPATH) !== false ? realpath( WP_CONTENT_DIR ) : realpath( ABSPATH . WP_CONTENT_DIR );
                                }
                            }
                        } else {
                            $addDir = 'wp-content/uploads';

                            if ($this->uploads_dir == '/wp-content/uploads') {
                                $rmdir = "/";
                                $addDir = "";
                            } else if ($this->uploads_dir == '//wp-content/uploads' || $this->uploads_dir == '//wp-content/uploads/') {
                                $rmdir = "//";
                                $addDir = "";
                            } else {
                                $rmdir = $this->uploads_dir;
                            }
                        }

                        $this->archive->addFiles($files_chunk, $addDir, $rmdir);

                        $this->files_count += count($files_chunk);
                        Helper::update_migration_state('current_files_count', $this->files_count);
                        Helper::store_migration_log('current_files_count_in_archive', $this->files_count);

                        if ($this->files_count >= $this->total_files_count) {
                            //update_site_option('tenweb_migration_ended', 1);
                            update_site_option('tenweb_migration_content_ended', 1);
                        } else {
                            $this->check_for_restart();
                        }
                    }
                }
            }

            $this->archive = null;
            Helper::store_migration_log('end_create_archive', 'End create archive function.');
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

        /**
         * @return array
         */
        private function get_root_files() {
            Helper::store_migration_log('start_get_root_files', 'Starting get_root_files function.');
            $root_files = array();

            $all_root_files = scandir(ABSPATH);
            if( empty($all_root_files) ) {
              return $root_files;
            }
            foreach ( $all_root_files as $file_name ) {
              /* Getting google analytics file, robots.txt, favicon.ico, sitemap.xml */
              if ( ( strpos( $file_name, 'google' ) === 0 && strpos( $file_name, '.htm') > 0 ) || in_array( $file_name, self::ROOT_FILES_NAME ) ) {
                  $root_files[] = ABSPATH . '/' . $file_name;
                  Helper::store_migration_log('added_root_file', sprintf('Added %s file in metadata.', $file_name));
              }
            }
            Helper::store_migration_log('end_get_root_files', 'End get_root_files function.');

            return $root_files;
        }
    }
}
