<?php
/**
 * Stage WP Manual Database Upgrades
 *
 * Normally, after you updgraded WordPress to a version that requires database
 * modifications, it redirects <code>/wp-admin/</code> requests to the WordPress
 * database upgrade screen. On large sites, or sites with a lot of active
 * authors, this may not be desired. This plugin prevents the automatic
 * redirect and instead lets you manually go to
 * <code>/wp-admin/upgrade.php</code> to upgrade a site.
 *
 * Original credits go to Mark Jaquith: {@link http://github.com/markjaquith/WP-Stack}
 *
 * @package   Stage_WP_Manual_Database_Upgrades
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL2
 * @link      http://github.com/andrezrv/stage-wp/
 * @copyright 2014 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin name: Stage WP Manual Database Upgrades
 * Plugin URI: http://github.com/andrezrv/stage-wp/
 * Description: Normally, after you updgraded WordPress to a version that requires database modifications, it redirects <code>/wp-admin/</code> requests to the WordPress database upgrade screen. On large sites, or sites with a lot of active authors, this may not be desired. This plugin prevents the automatic redirect and instead lets you manually go to <code>/wp-admin/upgrade.php</code> to upgrade a site. Original credits go to <a href="https://github.com/markjaquith/WordPress-Skeleton">Mark Jaquith</a>.
 * Author: Andrés Villarreal
 * Author URI: http://about.me/andrezrv
 * Version: 1.0
 * License: GPL2
 */
// Load required class.
if ( !class_exists( 'Stage_WP_Plugin' ) ) {
	if ( defined( 'STAGE_WP_PLUGIN_LOCATION' ) && file_exists( $file = STAGE_WP_PLUGIN_LOCATION ) ) {
		require( $file );
	} elseif ( file_exists( $file = dirname( __FILE__ ) . '/stage-wp-plugin.class.php' ) ) {
		require( $file );
	}
}

// Once class is loaded, extend it.
if ( class_exists( 'Stage_WP_Plugin' ) ) {

	class Stage_WP_Manual_DB_Upgrades_Plugin extends Stage_WP_Plugin {
		
		public static $instance;

		public function __construct() {
			self::$instance = $this;
			$this->hook( 'plugins_loaded' );
		}

		public function plugins_loaded() {
			$this->hook( 'option_db_version' );
		}

		public function option_db_version( $version ) {
			
			if ( strpos( $_SERVER['REQUEST_URI'], '/wp-admin/upgrade.php' ) === false ) {
				return $GLOBALS['wp_db_version'];
			}
			
			return $version;

		}

	}

	new Stage_WP_Manual_DB_Upgrades_Plugin;

}
