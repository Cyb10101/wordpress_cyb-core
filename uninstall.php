<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}
$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (WP_UNINSTALL_PLUGIN !== $plugin && $action !== 'delete-plugin') {
    exit();
}

/** @var \wpdb $wpdb */
global $wpdb;

try {
    $wpOptions = $wpdb->get_results('SELECT `option_name` FROM `' . $wpdb->options . '` WHERE `option_name` LIKE \'cyb-core-%\'');
    foreach ($wpOptions as $option) {
        delete_option($option->option_name);
        // delete_site_option($option_name); // @todo For options in multi site?
    }
} catch (Exception $exception) {
    error_log('Error deleting plugin options!');
    error_log($exception);
}

// @todo Currently not developed
/*
try {
    $wpdb->query('DROP TABLE IF EXISTS `' . $wpdb->prefix . 'cyb_core_logs`');
} catch (Exception $exception) {
    error_log('Error deleting plugin tables!');
    error_log($exception);
}
*/