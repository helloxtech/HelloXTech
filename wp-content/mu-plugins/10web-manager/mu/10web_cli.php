<?php

use Tenweb_Authorization\Login;

function tenweb_cli_site_state($args, $assoc_args)
{
    \Tenweb_Manager\Helper::check_site_state(true);
    if (class_exists('WP_CLI')) {
        WP_CLI::success('Success');
    }
}

function tenweb_cli_login($args, $assoc_args)
{
    $login = Login::get_instance();
    $login_args = array(
        'domain_hash'       => $args[2],
        'type'              => $args[3],
        'is_10web'          => 1,
        'multisite_type'    => $args[4],
        'migrated_site_url' => $args[5],
        'workspace_id'      => $args[6],
        'instance_id' => isset($args[7]) ? $args[7] : '',

    );
    if (!$login->login($args[0], '10webManager', $args[1], $login_args)) {
        $errors = $login->get_errors();
        if (class_exists('WP_CLI')) {
            WP_CLI::error('Cannot Login, Errors: ' . json_encode($errors));
        }
    }
    if (class_exists('WP_CLI')) {
        WP_CLI::log(get_site_option('tenweb_domain_id'));
    }
}

function tenweb_cli_install_template($args, $assoc_args)
{
    if (!defined('TENWEB_INCLUDES_DIR')) {
        if (class_exists('WP_CLI')) {
            WP_CLI::error('Manager plugin not installed');
        }
    }
    require_once(TENWEB_INCLUDES_DIR . "/class-rest-api.php");
    $rest_api = \Tenweb_Manager\RestApi::get_instance();
    $template_id = $args[1];
    $type = $args[2];
    $action = $args[0];
    $template_import_actions = array('install', 'start-import', 'import-plugins', 'import-site', 'finalize-import');
    $template_url = isset($args[3]) ? $args[3] : ''; //10webX

    foreach ($template_import_actions as $import_action) {

        $response = $rest_api->install_template($template_id, $template_url, $type, $action);

        if (!isset($response['status'])) {
            if (class_exists('WP_CLI')) {
                WP_CLI::error('Error has occurred');
            }
        }

        if ((int)$response['status'] != 200) {
            if (class_exists('WP_CLI')) {
                WP_CLI::error(json_encode($response['data_for_response']));
            }
        }
    }

    if (class_exists('WP_CLI')) {
        WP_CLI::success('Successfully installed.');
    }
}

function tenweb_cli_add_sub_site($args, $assoc_args)
{
    $blog_id = $args[0];
    $multi_site = \Tenweb_Manager\Multisite::get_instance();
    $multi_site->blog_activated($blog_id);
    if (class_exists('WP_CLI')) {
        WP_CLI::success('Successfully added site.');
    }
}

function tenweb_cli_delete_sub_site($args, $assoc_args)
{
    $blog_id = $args[0];
    $multi_site = \Tenweb_Manager\Multisite::get_instance();
    $multi_site->blog_deleted($blog_id);
    if (class_exists('WP_CLI')) {
        WP_CLI::success('Successfully deleted site.');
    }
}


/**
 * Deletes or counts revisions older than the specified time period.
 *
 * ## OPTIONS
 *
 * [--older-than=<time>]
 * : Specify the time period to delete revisions older than. The default is '1 month'.
 *   Accepts values compatible with strtotime (e.g., '2 weeks', '3 months', etc.).
 *
 * [--dry-run]
 * : Log the count of revisions that would be deleted, without actually deleting them.
 *
 * ## EXAMPLES
 *
 *     wp 10web-delete-old-revisions
 *     wp 10web-delete-old-revisions --dry-run
 *     wp 10web-delete-old-revisions --older-than='6 months' --dry-run
 *
 */
function tenweb_cli_delete_old_revisions($args, $assoc_args)
{
    global $wpdb;

    $dry_run = isset($assoc_args['dry-run']);
    $older_than = isset($assoc_args['older-than']) ? $assoc_args['older-than'] : '1 month';

    $older_than_date = date('Y-m-d H:i:s', strtotime('-' . $older_than));

    if (is_multisite()) {
        $sites = get_sites();
        foreach ($sites as $site) {
            switch_to_blog((int)$site->blog_id);
            WP_CLI::log("Processing site: " . get_bloginfo('url'));
            delete_old_revisions_for_site($older_than_date, $dry_run);
            restore_current_blog();
        }
    } else {
        delete_old_revisions_for_site($older_than_date, $dry_run);
    }
}

/**
 * Deletes or logs revisions older than a specified date for a specific site.
 *
 * @param string $older_than_date The date threshold to delete revisions older than.
 * @param bool $dry_run Whether to only count the deletable items without deleting.
 */
function delete_old_revisions_for_site($older_than_date, $dry_run = false)
{
    global $wpdb;

    // Get all revision IDs older than the specified date
    $revision_ids = $wpdb->get_col($wpdb->prepare("
                SELECT ID FROM $wpdb->posts 
                WHERE post_type = 'revision' 
                AND post_modified < %s
            ", $older_than_date));

    if (!empty($revision_ids)) {
        $revision_ids_list = implode(',', $revision_ids);

        if ($dry_run) {
            // Count revisions, postmeta, and term relationships
            $revision_count = count($revision_ids);
            $meta_count = $wpdb->get_var("
                        SELECT COUNT(*) FROM $wpdb->postmeta 
                        WHERE post_id IN ($revision_ids_list)
                    ");
            $term_relationship_count = $wpdb->get_var("
                        SELECT COUNT(*) FROM $wpdb->term_relationships 
                        WHERE object_id IN ($revision_ids_list)
                    ");

            WP_CLI::log("Site: " . get_bloginfo('url'));
            WP_CLI::log("Revisions to be deleted: $revision_count");
            WP_CLI::log("Postmeta entries to be deleted: $meta_count");
            WP_CLI::log("Term relationships to be deleted: $term_relationship_count");
        } else {
            // Delete the revisions
            $deleted_revisions = $wpdb->query("
                        DELETE FROM $wpdb->posts 
                        WHERE ID IN ($revision_ids_list)
                    ");

            // Delete corresponding postmeta entries
            $deleted_meta = $wpdb->query("
                        DELETE FROM $wpdb->postmeta 
                        WHERE post_id IN ($revision_ids_list)
                    ");

            // Optionally, clean term relationships (if needed)
            $deleted_term_relationships = $wpdb->query("
                        DELETE FROM $wpdb->term_relationships 
                        WHERE object_id IN ($revision_ids_list)
                    ");

            WP_CLI::success("Deleted $deleted_revisions revisions, $deleted_meta postmeta entries, and $deleted_term_relationships term relationships older than $older_than_date.");
        }
    } else {
        WP_CLI::log("No revisions older than $older_than_date found.");
    }
}

/**
 * Remove old Action Scheduler data older than a specified period.
 *
 * ## OPTIONS
 *
 * [--dry-run]
 * : If set, the command will only display the number of records to be deleted without deleting them.
 *
 * [--older-than=<period>]
 * : Specify the period (e.g., '3 months', '2 weeks') to delete records older than. Default is '3 months'.
 *
 * ## EXAMPLES
 *
 * wp 10web-actionscheduler-cleanup
 * wp 10web-actionscheduler-cleanup --dry-run
 * wp 10web-actionscheduler-cleanup --older-than='6 months'
 */
function delete_actionscheduler_logs($args, $assoc_args) {
    $older_than = isset($assoc_args['older-than']) ? $assoc_args['older-than'] : '3 months';
    $dry_run = isset($assoc_args['dry-run']);

    $older_than_date = date('Y-m-d H:i:s', strtotime('-' . $older_than));

    if (is_multisite()) {
        $sites = get_sites();
        foreach ($sites as $site) {
            switch_to_blog((int)$site->blog_id);
            WP_CLI::log("Processing site: " . get_bloginfo('url'));
            process_actionscheduler_cleanup($older_than_date, $dry_run);
            restore_current_blog();
        }
    } else {
        process_actionscheduler_cleanup($older_than_date, $dry_run);
    }
}

/**
 * Process Action Scheduler cleanup for a single site.
 *
 * @param string $older_than_date
 * @param bool $dry_run
 */
function process_actionscheduler_cleanup($older_than_date, $dry_run) {
    global $wpdb;

    $logs_table = $wpdb->prefix . 'actionscheduler_logs';
    $actions_table = $wpdb->prefix . 'actionscheduler_actions';

    $logs_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$logs_table'") == $logs_table;
    $actions_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$actions_table'") == $actions_table;

    if (!$logs_table_exists && !$actions_table_exists) {
        WP_CLI::warning("Action Scheduler tables do not exist for this site.");
        return;
    }

    if ($dry_run) {
        $logs_count = 0;
        $actions_count = 0;

        if ($logs_table_exists) {
            $logs_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(log_id) FROM {$logs_table} WHERE log_date_gmt < %s",
                $older_than_date
            ));
        }

        if ($actions_table_exists) {
            $actions_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(action_id) FROM {$actions_table} WHERE scheduled_date_gmt < %s",
                $older_than_date
            ));
        }

        WP_CLI::log("Logs to be deleted: $logs_count");
        WP_CLI::log("Actions to be deleted: $actions_count");
    } else {
        $deleted_logs = 0;
        $deleted_actions = 0;

        if ($logs_table_exists) {
            $deleted_logs = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$logs_table} WHERE log_date_gmt < %s",
                $older_than_date
            ));
        }

        if ($actions_table_exists) {
            $deleted_actions = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$actions_table} WHERE scheduled_date_gmt < %s",
                $older_than_date
            ));
        }

        WP_CLI::success("Deleted $deleted_logs logs and $deleted_actions actions older than $older_than_date.");
    }
}

if (class_exists('WP_CLI')) {
    WP_CLI::add_command('10web-login', 'tenweb_cli_login');
    WP_CLI::add_command('10web-state', 'tenweb_cli_site_state');
    WP_CLI::add_command('10web-template', 'tenweb_cli_install_template');
    WP_CLI::add_command('10web-add-sub-site', 'tenweb_cli_add_sub_site');
    WP_CLI::add_command('10web-delete-sub-site', 'tenweb_cli_delete_sub_site');
    WP_CLI::add_command('10web-delete-old-revisions', 'tenweb_cli_delete_old_revisions');
    WP_CLI::add_command('10web-actionscheduler-cleanup', 'delete_actionscheduler_logs');
}
