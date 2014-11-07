<?php

/*
  Plugin Name: Pitta Migration
  Plugin URI: http://www.vsni.co.uk
  Description: Migrate WordPress databases using WP_HOME and WP_SITEURL constants
  Version: 0.1
  Author: Ian Channing
  Author URI: http://klever.co.uk
 */

include 'functions.php';

add_action('admin_notices', 'iccPittaMigrate');
