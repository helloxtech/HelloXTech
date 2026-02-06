<?php

namespace Tenweb_Manager {

    class MigrationDB extends Migration
    {
        private $password;
        private $iv;
        private $tables;
        private $not_allowed_plugins;
        private $current_table_key;
        private $select_index;
        private $current_rows_count = 0;
        private $wpdb = null;

        public function __construct($password, $iv)
        {
            parent::__construct();

            $this->password = $password;
            $this->iv = $iv;
            $this->tables = null;
            $this->not_allowed_plugins = $this->get_not_allowed_plugins();
            $this->current_table_key = 0;
            $this->select_index = 0;
        }

        /**
         * @param $run_type
         *
         * @return string
         * @throws \Exception
         */
        public function run($run_type)
        {
            global $wpdb;
            $this->wpdb = $wpdb;

            if ($run_type == 'run') {
                //Helper::store_migration_log('run_db_run_type', 'Run db type is run');
                $this->tables = $this->get_tables();
            }
            $sql_header_string = "SET NAMES utf8mb4;" . PHP_EOL . "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";" . PHP_EOL . "SET time_zone = \"+00:00\";" . PHP_EOL . "SET foreign_key_checks = 0;" . PHP_EOL;

            if (Helper::get_configs('TENWEB_MIGRATION_SFTP')) {
              //  Helper::save_configs(['migration_max_db_rows_restart'=> 50, 'migration_bulk_db_rows_count'=> 50]);
                if ($run_type == 'run') {
                    Helper::update_migration_state('db_tables_count', count($this->tables));
                    // add sql headers
                    $this->send_config_and_db($sql_header_string. PHP_EOL);
                }
                $this->insert_table_data(null);
                $this->update_active_plugins_options(null);
                update_site_option('tenweb_migration_db_backup_ended', 1);
            }
            else {
                $file_to_save_database_data = $this->archive_dir . "/" . self::MIGRATION_DB_FILE_NAME;

                if (($db_file = fopen($file_to_save_database_data, "a+")) !== false) {
                    if ($run_type == 'run') {
                        Helper::update_migration_state('db_tables_count', count($this->tables));
                        // add sql headers
                        $this->add_to_file($db_file, $sql_header_string . PHP_EOL);
                    }
                    $this->insert_table_data($db_file);
                    $this->update_active_plugins_options($db_file);

                    if ($this->configs['TENWEB_MIGRATION_ENCRYPT_DB'] && function_exists('openssl_encrypt') && !empty($this->password) && !empty($this->iv)) {
                        $this->encrypt_db_file($db_file, $file_to_save_database_data);
                    } else {
                        // after all close file
                        fclose($db_file);
                    }

                    update_site_option('tenweb_migration_db_backup_ended', 1);

                    return $file_to_save_database_data;
                } else {
                    throw new \Exception("Unable open database sql file");
                }
            }
            return '';
        }


        /**
         * @param $table_name
         *
         * @throws \Exception
         */
        private function insert_data($table_name, $db_file)
        {
            $columns = $this->get_table_structure($table_name);
            $table_columns = $this->get_table_columns($columns);

            $table_name = stripslashes($table_name);
            $where = '';
            if( $table_name == $this->wpdb->prefix . 'posts' ) {
              $where = ' WHERE post_type <> \'revision\' ORDER BY ID ASC';
            } elseif ($table_name == $this->wpdb->prefix . 'options') {
                $where = ' WHERE option_name not like \'rocket_partial_preload_batch%\'';
            } elseif ( $table_name == $this->wpdb->prefix . 'comments' ) {
              $where = ' WHERE comment_approved <> \'spam\' AND comment_approved <> \'trash\' ORDER BY comment_ID ASC';
            }

            $table_record_count = $this->wpdb->get_row('SELECT count(1) FROM ' . $table_name . $where, ARRAY_N);
            $select_limit = $this->configs['TENWEB_MIGRATION_BULK_DB_ROWS_COUNT'];
            // Number of group insert queries during import.
            $concat_query_count = 1000;
            if ( $select_limit < $concat_query_count ) {
                $concat_query_count = $select_limit;
            }
            $select_count = (int)ceil($table_record_count[0] / $select_limit);

            for ($select_i = $this->select_index; $select_i < $select_count; $select_i++) {
                $offset = $select_i * $select_limit;

                if ($offset == 0) {
                    Helper::store_migration_log('inserting_data_table_' . $table_name, 'Adding data for ' . $table_name . ' table. Select count: ' . $select_count . ' Rows count: ' . $table_record_count[0]);
                }

                $backed_up_rows = min($table_record_count[0], $offset + $select_limit);

                Helper::store_migration_log('inserted_data_table_' . $table_name, 'Adding data for ' . $table_name . ' table (' . $backed_up_rows . '/' . $table_record_count[0] . ' rows) ');

                $table_data = $this->wpdb->get_results('SELECT * FROM ' . $table_name . $where . ' LIMIT ' . $offset . ', ' . $select_limit, ARRAY_A);

                $insert_data = "INSERT INTO `{$table_name}` (" . implode(",", $table_columns) . ") VALUES ";
                if ($table_data) {
                    $i = 0;
                    $current_insert_data = '';
                    foreach ($table_data as $table_row) {
                        $insert_values = array();
                        foreach ($table_row as $field_name => $field_value) {
                            if (empty($field_value)) {
                                if ($columns[$field_name]["Null"] != "NO") {
                                    $insert_values[$field_name] = 'NULL';
                                } else {
                                    if (strpos(strtolower($columns[$field_name]["Type"]), "int") !== false || $field_value === '0') {
                                        $insert_values[$field_name] = '0';
                                    } else {
                                        $insert_values[$field_name] = "''";
                                    }
                                }
                            } else {
                                $insert_values[$field_name] = "'" . addslashes($field_value) . "'";
                            }
                        }
                        // Creates group queries.
                        if ( $i % $concat_query_count == 0 ) {
                            $current_insert_data = rtrim($current_insert_data, ",");
                            $current_insert_data .= ";" .  $insert_data . "(" . implode(",", array_values($insert_values)) . "),";
                        }
                        else {
                            $current_insert_data .= "(" . implode(",", array_values($insert_values)) . "),";
                        }
                        $i++;
                    }

                    if ($i > 0) {
                        $current_insert_data = ltrim($current_insert_data, ";");
                        $current_insert_data = rtrim($current_insert_data, ",");
                        if (Helper::get_configs('TENWEB_MIGRATION_SFTP')) {
                            $this->send_config_and_db($current_insert_data . ';' . PHP_EOL . PHP_EOL);
                        }
                        else {
                            $this->add_to_file($db_file, $current_insert_data . ';' . PHP_EOL . PHP_EOL);
                        }
                        $this->current_rows_count += $i;
                        $this->select_index = $select_i + 1;
                        $this->check_for_restart();
                    }
                }
            }

            $this->select_index = 0;
        }

        /**
         * @param $table_structure
         *
         * @return array
         */
        private function get_table_columns($table_structure)
        {
            $columns = array_keys($table_structure);
            $columns = array_map(function ($value) {
                return "`" . $value . "`";
            }, $columns);

            return $columns;
        }

        /**
         * @param $table_name
         *
         * @return array
         */
        private function get_table_structure($table_name)
        {
            $table_columns = array();
            $table_structure = $this->wpdb->get_results("DESCRIBE `{$table_name}`", ARRAY_A);

            foreach ($table_structure as $column) {
                $table_columns[$column["Field"]] = $column;
            }

            return $table_columns;
        }

        /**
         * @param $table_name
         *
         * @return mixed
         */
        private function get_create_table_str($table_name)
        {
            // get create table syntax
            $create_table = $this->wpdb->get_row("SHOW CREATE TABLE `{$table_name}`", ARRAY_N);

            return !empty($create_table[1]) ? $create_table[1] : false;
        }

        /**
         * @return array
         */
        private function get_tables()
        {
            Helper::store_migration_log('start_get_tables', 'Start get_tables function.');
            $tables = array();
            $data = $this->wpdb->get_results("SHOW FULL TABLES FROM `{$this->wpdb->dbname}` WHERE Table_Type != 'VIEW'");
            if (!empty($data)) {
                $key = 'Tables_in_' . $this->wpdb->dbname;
                foreach ($data as $table) {
                    if ( strpos($table->$key, $this->wpdb->prefix) === 0 ) {
                        $is_tbl_continue = FALSE;
                        // Not add excluded plugins tables.
                        if ( !empty($this->not_allowed_plugins) ) {
                            foreach ( $this->not_allowed_plugins as $plugin ) {
                                if ( !empty($plugin['db_prefix'] ) ) {
                                    $tbl_name = $this->wpdb->prefix . '' . $plugin['db_prefix'];
                                    $pattern = '/^' . $tbl_name . '(.*)+/i';
                                    if ( preg_match($pattern, $table->$key) ) {
                                        $is_tbl_continue = TRUE;
                                    }
                                }

                            }
                        }
                        if ( $is_tbl_continue ) {
                            continue;
                        }
                        $tables[] = $table->$key;
                    }
                }
            }

            Helper::store_migration_log('end_get_tables', 'End get_tables function.');
            return $tables;
        }

        /**
         * @param $file
         * @param $data
         *
         * @throws \Exception
         */
        private function add_to_file($file, $data)
        {
            if (fwrite($file, $data) === false) {
                throw new \Exception("Unable write data to db file");
            }
        }


        private function encrypt_db_file($file, $db_file_path)
        {
            $dest = tempnam($this->archive_dir, 'db');

            $key = $this->password;
            //initialization vector
            $iv = $this->iv;
            fseek($file, 0);
            $error = false;
            if ($fpOut = fopen($dest, 'w')) {
                while (!feof($file)) {
                    $plaintext = fread($file, 16 * 1000);
                    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, 0, $iv);
                    fwrite($fpOut, $ciphertext);
                }
                fclose($fpOut);
            } else {
                $error = true;
            }
            try {
                //if rename and copy is unsuccessful, throw error
                if ( !rename($dest, $db_file_path) ) {
                    if ( copy($dest, $db_file_path) ) {
                        unlink($dest);
                    } else {
                        throw new \Exception("Error related to rename or copy");
                    }
                }
            } catch (\Exception $e) {
                Helper::store_migration_log('db_encrypt_failed', 'Database file encryption is failed.', 0);
                //set option if failed saving encrypted db file
                update_option(TENWEB_PREFIX . '_is_db_encrypt_failed', 1);
            }

            return $error ? false : true;
        }

        protected function check_for_restart()
        {
            $max_exec_time_server = ini_get('max_execution_time');
            $start = get_site_transient(TENWEB_PREFIX . "_migration_start_time");
            $script_exec_time = microtime(true) - $start;

            if ($this->current_rows_count >= $this->configs['TENWEB_MIGRATION_MAX_DB_ROWS_RESTART'] || $script_exec_time >= ((int)$max_exec_time_server - $this->configs['TENWEB_MIGRATION_EXEC_TIME_OFFSET'])) {
                $this->write_object_file();
                $this->restart();

                return false;
            }
        }

        protected function write_object_file()
        {
            $this->disconnectSFTP();
            //Helper::store_migration_log('start_write_object_file_db_' . current_time('timestamp'), 'Starting write object file in db.');
            $this->wpdb = null;
            $content = serialize($this);
            file_put_contents($this->archive_dir . '/content_object.txt', $content);
            //Helper::store_migration_log('end_write_object_file_db_' . current_time('timestamp'), 'End write object file in db.');
        }

        private function get_altered_engine_str($create_str, $from_engine = array('MyISAM', 'utf8mb4_0900_ai_ci'), $to_engine = array('InnoDB', 'utf8mb4_general_ci'))
        {
            return str_ireplace($from_engine, $to_engine, $create_str);
        }

        public function insert_table_data($db_file = null) {

            $this->current_rows_count = 0;
            foreach ($this->tables as $key => $table) {
                if ($this->current_table_key > $key) {
                    //Helper::store_migration_log('skip_table_' . $key, 'Skip table ' . $table);
                    continue;
                }

                if (!$this->select_index) {
                    // get create table syntax
                    if (($create_table = $this->get_create_table_str($table)) !== false) {
                        // change table engine to innodb
                        $create_table = $this->get_altered_engine_str($create_table);

                        // run drop table if table exists
                        $drop_string = "DROP TABLE IF EXISTS `{$table}`";
                        if (Helper::get_configs('TENWEB_MIGRATION_SFTP')) {
                            $this->send_config_and_db($drop_string . ';' . PHP_EOL);
                            $this->send_config_and_db($create_table . ';' . PHP_EOL . PHP_EOL);
                        } else {
                            $this->add_to_file($db_file, $drop_string . ';' . PHP_EOL);
                            // run create table
                            $this->add_to_file($db_file, $create_table . ';' . PHP_EOL . PHP_EOL);
                        }
                        // if table has data, run insert data
                        $this->insert_data($table, $db_file);
                    }
                } else {
                    $this->insert_data($table, $db_file);
                }


                $this->current_table_key++;
                Helper::update_migration_state('inserted_tables_count', $this->current_table_key);
            }
        }

        /**
         * Get not allowed plugins.
         *
         * @return array
         */
        private function get_not_allowed_plugins() {
            $data = array(
              '10web-manager' => array(
                'db_prefix' => 'tenweb',
                'main_file' => '10web-manager.php',
              ),
              'sg-cachepress' => array(
                'db_prefix' => '',
                'main_file' => 'sg-cachepress.php',
              ),
              'wp-asset-clean-up' => array(
                'db_prefix' => 'tm',
                'main_file' => 'wpacu.php',
              ),
              'imagemagick-engine' => array(
                'db_prefix' => '',
                'main_file' => 'imagemagick-engine.php',
              ),
                'wordfence_locs' => array(
                    'db_prefix' => 'wflocs',
                    'main_file' => '',
                ),
                'wordfence_logins' => array(
                    'db_prefix' => 'wflogins',
                    'main_file' => '',
                ),
                'wordfence_issues' => array(
                    'db_prefix' => 'wfissues',
                    'main_file' => '',
                ),
                'wordfence_log' => array(
                    'db_prefix' => 'wfblockediplog',
                    'main_file' => '',
                )
            );

            return $data;
        }

        /**
         * Update active plugins options.
         *
         * @param $db_file
         *
         * @throws \Exception
         */
        private function update_active_plugins_options( $db_file = NULL ) {
            if ( !empty($this->not_allowed_plugins) ) {
                // Get active plugins.
                $active_plugins = get_option('active_plugins', array());
                $active_plugins = array_unique($active_plugins);
                $deactivate_plugins = array();
                foreach ( $this->not_allowed_plugins as $key_plugin => $val_plugin ) {
                    $deactivate_plugins[] = $key_plugin . '/' . $val_plugin['main_file'];
                }
                if ( !empty($active_plugins) && !empty($deactivate_plugins) ) {
                    // Removing not allowed plugins in active plugins.
                    foreach ( $active_plugins as $key => $plugin ) {
                        if ( in_array($plugin, $deactivate_plugins) ) {
                            unset($active_plugins[$key]);
                        }
                    }
                    $active_plugins = wp_slash(serialize($active_plugins));
                    // Updating 'active_plugins' options exception not allowed plugins.
                    $update_plugins_active_options = " UPDATE `" . $this->wpdb->prefix . "options` SET `option_value` = '" . $active_plugins . "' WHERE `option_name` = 'active_plugins' ";

                    // Adding an update query to the DB file.
                    if ( Helper::get_configs('TENWEB_MIGRATION_SFTP') ) {
                        $this->send_config_and_db($update_plugins_active_options . PHP_EOL);
                    }
                    else {
                        $this->add_to_file($db_file, $update_plugins_active_options . PHP_EOL);
                    }
                }
            }
        }
    }
}
