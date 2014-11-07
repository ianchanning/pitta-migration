<?php

namespace Pitta\Migration;

/**
 * Database Migration Object
 * 
 * @package PittaMigration
 * @since 0.2.0
 */
class PittaMigration {

    private static $instance = null;
    private $pluginPath;
    private $pluginUrl;
    private $textDomain = 'pitta-migration';
    private $viewVars;

    /**
     * Creates or returns an Singleton instance of this class.
     */
    public static function getInstance() {
        // If an instance hasn't been created and set to $instance create an instance and set it to $instance.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Start me up...
     * 
     * @since 0.2.0
     * @access private
     */
    private function __construct() {
        $this->pluginPath = plugin_dir_path(__FILE__);
        $this->pluginUrl = plugin_dir_url(__FILE__);
        $this->viewVars = array();
        \load_plugin_textdomain($this->textDomain, false, 'lang');
        $this->run();
    }

    /**
     * @since 0.2.0
     */
    public function getPluginUrl() {
        return $this->pluginUrl;
    }

    /**
     * @since 0.2.0
     */
    public function getPluginPath() {
        return $this->pluginPath;
    }

    /**
     * Core code.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @global wpdb $wpdb
     */
    private function run() {
        global $wpdb;

        // these must be defined for it to work
        if (!defined('WP_HOME') || !defined('WP_SITEURL')) {
            \add_action('admin_notices', array(&$this, 'setup'));
            return;
        }

        // get the original home url
        $fromHome = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'home'");
        $fromSiteurl = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'");

        // don't need to migrate if they're the same
        if (WP_HOME == $fromHome && WP_SITEURL == $fromSiteurl) {
            return;
        }

        $this->updateOptions();

        // update wp_posts
        $sql = '';
        $success = $this->updatePosts($fromHome, $sql);

        if ($success !== FALSE) {
            \add_action('admin_notices', array(&$this, 'success'));
        } else {
            /**
             * @todo Does this pass through wpdb correctly?
             */
            $this->set('error', compact('sql', 'wpdb'));
            \add_action('admin_notices', array(&$this, 'error'));
        }
    }

    /**
     * Update wp_options.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @global wpdb $wpdb
     */
    private function updateOptions() {
        global $wpdb;
        // update_option doesn't update the database
        $wpdb->update($wpdb->options, array('option_value' => WP_HOME), array('option_name' => 'home'));
        $wpdb->update($wpdb->options, array('option_value' => WP_SITEURL), array('option_name' => 'siteurl'));
    }

    /**
     * Update wp_posts.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @global wpdb $wpdb
     * 
     * @param string $fromHome
     * @param string $sql
     * @return success
     */
    private function updatePosts($fromHome, &$sql) {
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

    /**
     * Returns a rendered view file.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @param string $method
     * @return string
     */
    private function renderView($method) {
        if (isset($this->viewVars[$method])) {
            extract($this->viewVars[$method]);
        }
        ob_start();
        /**
         * Pull in the view file.
         */
        require_once $this->getPluginPath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $method . '.php';
        return do_shortcode(ob_get_clean());
    }

    /**
     * Merges arrays of vars for a particular method.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @param string $method
     * @param array $vars
     */
    private function set($method, $vars) {
        $this->viewVars[$method] = array_merge((array) $this->viewVars[$method], $vars);
    }

    /**
     * Success admin message.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @global wpdb $wpdb
     */
    public function success() {
        global $wpdb;
        $newHome = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'home'");
        $newSiteurl = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'");
        $this->set(__FUNCTION__, compact('newHome', 'newSiteurl'));
        echo $this->renderView(__FUNCTION__);
    }

    /**
     * Error admin message.
     * 
     * @since 0.2.0
     * @access private
     */
    public function error() {
        echo $this->renderView(__FUNCTION__);
    }

    /**
     * Setup admin message.
     * 
     * @since 0.2.0
     * @access private
     */
    public function setup() {
        echo $this->renderView(__FUNCTION__);
    }

}
