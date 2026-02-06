<?php


namespace Tenweb_Manager {

    use phpseclib3\Net\SFTP;

    class MigrationSFTP extends Migration
    {
        private $files = null;
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * @param $run_type
         * @return int|void
         * @throws \Exception
         */
        public static function set_up($run_type = 'run')
        {
            if ($run_type == 'run') {
                //Helper::store_migration_log('run_type_' . current_time('timestamp'), 'Run type is run.');
                $migration_content = new self();

                $migration_content->run($run_type);
            }

            if ($run_type == 'restart') {
                //Helper::store_migration_log('run_type_' . current_time('timestamp'), 'Run type is restart.');
                $migration_content = Migration::get_object_file_content();

                $migration_content->run($run_type);
            }
        }

        /**
         * @param $run_type
         * @return void
         */
        public function run($run_type)
        {
            $this->check_if_sftp_logged_in();
       //     Helper::save_configs(['migration_max_files_restart'=> 20]);
            $this->uploads_dir = Helper::get_uploads_dir();
            if ($run_type == 'run') {
                $this->files = $this->get_content_files();
            }
            if (!empty($this->files)) {
                foreach ($this->files as $type => &$files) {
                    if (!empty($files)) {
                        foreach ($files as $key => &$files_chunk) {
                            foreach($files_chunk as $key => $file_path) {
                                $file = str_replace(WP_CONTENT_DIR, "", $file_path);

                                $dir = TENWEB_WP_DIR . '/wp-content/' . dirname($file);

                                $this->check_if_sftp_logged_in();

                                try {
                                    if ($this->sftp && !$this->sftp->is_dir($dir)) {
                                        $this->sftp->mkdir($dir, 0755, true);
                                    }
                                    //Helper::store_migration_log('send_sftp_wp-content_files' . current_time('timestamp'), $file_path);
                                    // $this->sftp->put(TENWEB_WP_DIR . '/wp-content/' . $file, $file_path, SFTP::SOURCE_LOCAL_FILE | SFTP::RESUME_START);
                                    if (is_file($file_path)) {
                                        $this->sftp->put(TENWEB_WP_DIR . '/wp-content/' . $file, file_get_contents($file_path));
                                        Helper::store_migration_log('uploaded files count', $this->files_count);
                                    }
                                } catch (\Exception $e) {
                                    Helper::store_migration_log('sftp error message ' . current_time('timestamp'), $e->getMessage(), 0);
                                    Helper::store_migration_log('sftp error errors ' . current_time('timestamp'), implode("\n", $this->sftp->getErrors()), 0);
                                    $this->write_object_file();
                                    $this->restart();
                                }

                                $this->files_count++;
                                unset($files_chunk[$key]);
                                Helper::update_migration_state('current_files_count', $this->files_count);
                                if ($this->files_count % $this->configs['TENWEB_MIGRATION_SFTP_STATE_FILES_COUNT'] == 0) {
                                    Helper::update_migration_state('sftp_last_uploaded_file',$file_path);
                                }
                                if ($this->files_count >= $this->total_files_count) {
                                    update_site_option('tenweb_migration_content_ended', 1);
                                } else {
                                    $this->check_for_restart();
                                }
                            }
                        }

                    }
                }
            }
        }

    }
}
