<?php
/**
 * Stage WP Staging
 *
 * For staging environments, this plugin will replace your production domain
 * with the staging one.
 *
 * Original credits go to Mark Jaquith: {@link http://github.com/markjaquith/WP-Stack}
 *
 * @package   Stage_WP_Staging
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL2
 * @link      http://github.com/andrezrv/stage-wp/
 * @copyright 2014 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin name: Stage WP Staging
 * Plugin URI: http://github.com/andrezrv/stage-wp/
 * Description: For staging environments, this plugin will replace your production domain with the staging one. Original credits go to <a href="https://github.com/markjaquith/WordPress-Skeleton">Mark Jaquith</a>.
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

	class Stage_WP_Staging_Plugin extends Stage_WP_Plugin {

		public static $instance;

		public function __construct() {

			self::$instance = $this;

			if ( !defined( 'WP_STAGE' ) || WP_STAGE !== 'staging' || !defined( 'STAGING_DOMAIN' ) ) {
				return;
			}

			$this->hook( 'option_home', 'replace_domain' );
			$this->hook( 'option_siteurl', 'replace_domain' );

		}

		public function replace_domain ( $url ) {
			$current_domain = parse_url( $url, PHP_URL_HOST );
			$url = str_replace( '//' . $current_domain, '//' . STAGING_DOMAIN, $url );
			return $url;
		}
	}

	new Stage_WP_Staging_Plugin;

}
