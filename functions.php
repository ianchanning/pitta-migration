<?php

/**
 * Main migrate function
 * 
 * @global type $wpdb
 */
function iccPittaMigrate() {
    global $wpdb;

    // these must be defined for it to work
    if (!defined('WP_HOME') || !defined('WP_SITEURL')) {
        include 'views' . DIRECTORY_SEPARATOR . 'setup.php';
        return;
    }

    // get the original home url
    $fromHome = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'home'");
    $fromSiteurl = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'");

    // don't need to migrate if they're the same
    if (WP_HOME == $fromHome && WP_SITEURL == $fromSiteurl) {
        return;
    }

    iccPittaUpdateWpOptions();

    // update wp_posts
    $sql = '';
    $success = iccPittaUpdateWpPosts($fromHome, $sql);

    if ($success !== FALSE) {
        $newHome = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'home'");
        $newSiteurl = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'");

        include 'views' . DIRECTORY_SEPARATOR . 'success.php';
    } else {
        include 'views' . DIRECTORY_SEPARATOR . 'error.php';
    }
}

/**
 * Update wp_options
 * 
 * @global object $wpdb
 */
function iccPittaUpdateWpOptions() {
    global $wpdb;
    // update_option doesn't update the database
    $wpdb->update($wpdb->options, array('option_value' => WP_HOME), array('option_name' => 'home'));
    $wpdb->update($wpdb->options, array('option_value' => WP_SITEURL), array('option_name' => 'siteurl'));
}

/**
 * Update wp_posts
 * 
 * @global object $wpdb
 * @param string $fromHome
 * @param string $sql
 * @return success
 */
function iccPittaUpdateWpPosts($fromHome, &$sql) {
    global $wpdb;

    $toHome = get_home_url();
    $sql = $wpdb->prepare(
            "UPDATE $wpdb->posts
            SET guid = REPLACE(guid, '%s', '%s')
            WHERE guid LIKE '%s'
            ", $fromHome, $toHome, $fromHome . '%'
    );

    return $wpdb->query($sql);
}
