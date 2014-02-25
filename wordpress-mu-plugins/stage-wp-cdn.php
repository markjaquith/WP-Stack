<?php
/**
 * Stage WP CDN
 *
 * This is a very simple CDN plugin. Simply configure the constant
 * STAGE_WP_CDN_DOMAIN in your wp-config.php or hook in and override the
 * stage_wp_cdn_domain option. Provide a domain name only, like
 * static.example.com. The plugin will look for static file URLs on your domain
 * and repoint them to the CDN domain.
 *
 * Original credits go to Mark Jaquith: {@link http://github.com/markjaquith/WP-Stack}
 *
 * @package   Stage_WP_CDN
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL2
 * @link      http://github.com/andrezrv/stage-wp/
 * @copyright 2014 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin name: Stage WP CDN
 * Plugin URI: http://github.com/andrezrv/stage-wp/
 * Description: This is a very simple CDN plugin. Simply configure the constant <code>STAGE_WP_CDN_DOMAIN</code> in your <code>wp-config.php</code> or hook in and override the <code>stage_wp_cdn_domain</code> option. Provide a domain name only, like <code>static.example.com</code>. The plugin will look for static file URLs on your domain and repoint them to the CDN domain. Original credits go to <a href="https://github.com/markjaquith/WordPress-Skeleton">Mark Jaquith</a>.
 * Author: Andrés Villarreal
 * Author URI: http://about.me/andrezrv
 * Version: 1.1
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

	class Stage_WP_CDN_Plugin extends Stage_WP_Plugin {

		public static $instance;
		public $site_domain;
		public $cdn_domain;
		public $upload_dir;
		public $uploads_only;
		public $extensions;

		public function __construct() {
			self::$instance = $this;
			$this->hook( 'plugins_loaded' );
		}

		public function plugins_loaded() {

			$domain_set_up = get_option( 'stage_wp_cdn_domain' ) || ( defined( 'STAGE_WP_CDN_DOMAIN' ) && STAGE_WP_CDN_DOMAIN );
			$production = defined( 'WP_STAGE' ) && WP_STAGE === 'production';
			$staging = defined( 'WP_STAGE' ) && WP_STAGE === 'staging';
			$uploads_only = defined( 'STAGE_WP_CDN_UPLOADS_ONLY' ) && STAGE_WP_CDN_UPLOADS_ONLY;

			if ( $domain_set_up && !$staging && ( $production || $uploads_only ) ) {
				$this->hook( 'init' );
			}

		}

		public function init() {

			$this->uploads_only = apply_filters( 'stage_wp_cdn_uploads_only', defined( 'STAGE_WP_CDN_UPLOADS_ONLY' ) ? STAGE_WP_CDN_UPLOADS_ONLY : false );
			$this->extensions = apply_filters( 'stage_wp_cdn_extensions', array( 'jpe?g', 'gif', 'png', 'css', 'bmp', 'js', 'ico' ) );

			if ( !is_admin() ) {

				$this->hook( 'template_redirect' );

				if ( $this->uploads_only ) {
					$this->hook( 'stage_wp_cdn_content', 'filter_uploads_only' );
				} else {
					$this->hook( 'stage_wp_cdn_content', 'filter' );
				}

				$this->site_domain = parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
				$this->cdn_domain = defined( 'STAGE_WP_CDN_DOMAIN' ) ? STAGE_WP_CDN_DOMAIN : get_option( 'stage_wp_cdn_domain' );

			}

		}

		public function filter_uploads_only( $content ) {

			$upload_dir = wp_upload_dir();
			$upload_dir = $upload_dir['baseurl'];
			$domain = preg_quote( parse_url( $upload_dir, PHP_URL_HOST ), '#' );
			$path = parse_url( $upload_dir, PHP_URL_PATH );
			$preg_path = preg_quote( $path, '#' );

			// Targeted replace just on uploads URLs
			return preg_replace( "#=([\"'])(https?://{$domain})?$preg_path/((?:(?!\\1]).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#", '=$1//' . $this->cdn_domain . $path . '/$3.$4$5$1', $content );

		}

		public function filter( $content ) {
			return preg_replace( "#=([\"'])(https?://{$this->site_domain})?/([^/](?:(?!\\1).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#", '=$1//' . $this->cdn_domain . '/$3.$4$5$1', $content );
		}

		public function template_redirect() {
			ob_start( array( $this, 'ob' ) );
		}

		public function ob( $contents ) {
				return apply_filters( 'stage_wp_cdn_content', $contents, $this );
		}
	}

	new Stage_WP_CDN_Plugin;

}
