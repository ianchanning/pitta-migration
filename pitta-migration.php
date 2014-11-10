<?php

/*
 * Plugin Name: Pitta Migration
 * Plugin URI: http://vsni.co.uk
 * Description: Migrate WordPress databases using WP_HOME and WP_SITEURL constants
 * Version: 0.2.1
 * Author: Ian Channing @ VSN International
 * Author URI: http://vsni.co.uk
 * License: GPL V3
 */

require_once plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'PittaMigration.php';

Pitta\Migration\PittaMigration::getInstance();
