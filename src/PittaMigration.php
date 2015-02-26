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
     * 
     * @since 0.2.0
     */
    public static function getInstance() {
        // If an instance hasn't been created and set to $instance create an instance and set it to $instance.
        if ( null == self::$instance ) {
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
        $this->pluginPath = plugin_dir_path( dirname( __FILE__ ) );
        $this->pluginUrl = plugin_dir_url( dirname( __FILE__ ) );
        $this->viewVars = array();

        \add_action( 'init', array( &$this, 'loadPluginTextdomain' ) );
        /**
         * We use admin_notices as admin_init can be run before the user is logged out when a database is imported
         * Then the admin notice never appears
         */
        \add_action( 'admin_notices', array( &$this, 'run' ) );
    }

    /**
     * Translations
     *
     * @link http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
     * @since 0.4.0
     * @access public
     */
    public function loadPluginTextdomain() {
        // The "plugin_locale" filter is also used in load_plugin_textdomain()
        $locale = apply_filters( 'plugin_locale', get_locale(), $this->textDomain );
        \load_textdomain( $this->textDomain, WP_LANG_DIR . "/$this->textDomain/$this->textDomain-$locale.mo"  );
        \load_plugin_textdomain( $this->textDomain, false, dirname( plugin_basename( dirname( __FILE__ ) ) ) . 'languages/' );
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
     * @access public
     * 
     * @global wpdb $wpdb
     */
    public function run() {
        global $wpdb;

        // these must be defined for it to work
        if ( ! defined( 'WP_HOME' ) || ! defined( 'WP_SITEURL' ) ) {
            $this->setup();
            return;
        }

        // get the originals direct from the database
        $fromHome = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'home'" );
        $fromSiteurl = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'" );

        // don't need to migrate if they're the same
        if ( WP_HOME === $fromHome && WP_SITEURL === $fromSiteurl ) {
            return;
        }

        // update wp_posts first as its more likely to fail
        if ( $this->migratePosts( $fromHome, $fromSiteurl ) ) {
            // update wp_options
            if ( $this->migrateOptions() ) {
                $this->set( 'success', compact( 'fromHome', 'fromSiteurl' ) );
                $this->success();
            } else {
                $message = sprintf( __( 'Failed to update %s', 'pitta-migration' ), "$wpdb->posts.guid" );
                $this->dbError( $message );
            }
        }
    }

    /**
     * Update relevant wp_options.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @global wpdb $wpdb
     * 
     * @return boolean success
     */
    private function migrateOptions() {
        global $wpdb; // must pass this through
        if ( !$this->migrateOption( 'home', WP_HOME ) ) {
            return false;
        }
        if ( !$this->migrateOption( 'siteurl', WP_SITEURL ) ) {
            return false;
        }
        return true;
    }

    /**
     * Update single wp_options row.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @global wpdb $wpdb
     * 
     * @param string $name
     * @param string $value
     * @return boolean success
     */
    private function migrateOption( $name, $value ) {
        global $wpdb;
        // update_option doesn't update the database
        $updatedSiteurl = $wpdb->update( 
                $wpdb->options, array( 'option_value' => $value ), array( 'option_name' => $name )
         );
        if ( $updatedSiteurl === false ) {
            $message = sprintf( __( 'Failed to update %s', 'pitta-migration' ), "$wpdb->options '$name'" );
            $this->dbError( $message );
            return false;
        } else {
            return true;
        }
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
     * @param string $fromSiteurl
     * @return boolean success
     */
    private function migratePosts( $fromHome, $fromSiteurl ) {
        global $wpdb;

        $sql = $wpdb->prepare( 
                "UPDATE $wpdb->posts SET guid = REPLACE( guid, '%s', '%s' ) WHERE guid LIKE '%s'", $fromHome, WP_HOME, $fromHome . '%'
         );

        $success = $wpdb->query( $sql );
        return $success !== false;
    }

    /**
     * Display a database error.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @global wpdb $wpdb
     * 
     * @param string $message
     */
    private function dbError( $message ) {
        global $wpdb;
        /**
         * Have to print any error here before calling add_action
         * 
         * N.B. To future self = print_error returns false if $wpdb->show_errors is false
         */
        ob_start();
        $wpdb->print_error();
        $dbError = ob_get_clean();
        $this->set( 'error', compact( 'dbError', 'message' ) );
        $this->error();
    }

    /**
     * Get a rendered view file.
     * 
     * @since 0.2.0
     * @access private
     * 
     * @param string $method
     * @return string
     */
    private function renderView( $method ) {
        if ( isset( $this->viewVars[ $method ] ) ) {
            extract( $this->viewVars[ $method ] );
        }
        ob_start();
        /**
         * Pull in the view file.
         */
        require_once $this->getPluginPath() . 'src' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $method . '.php';
        return \do_shortcode( ob_get_clean() );
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
    private function set( $method, $vars ) {
        if ( isset( $this->viewVars[ $method ] ) ) {
            $this->viewVars[ $method ] = array_merge( $this->viewVars[ $method ], $vars );
        } else {
            $this->viewVars[ $method ] = $vars;
        }
    }

    /**
     * Log it.
     * 
     * @param string $message
     */
    private function log( $message ) {
        error_log( "[ $this->textDomain ] " . $message );
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
        $newHome = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'home'" );
        $newSiteurl = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'" );
        $this->set( __FUNCTION__, compact( 'newHome', 'newSiteurl' ) );
        echo $this->renderView( __FUNCTION__ );
    }

    /**
     * Error admin message.
     * 
     * @since 0.2.0
     * @access private
     */
    public function error() {
        echo $this->renderView( __FUNCTION__ );
    }

    /**
     * Setup admin message.
     * 
     * @since 0.2.0
     * @access private
     */
    public function setup() {
        echo $this->renderView( __FUNCTION__ );
    }

}
