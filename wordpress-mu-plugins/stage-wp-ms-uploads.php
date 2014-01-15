<?php
/**
 * Stage WP Multisite Uploads
 * 
 * The way WordPress Multisite serves uploads is not ideal. It streams them
 * through a PHP file. Professional sites should not do this. This plugin allows
 * one NGINX rewrite rule to handle all uploads, eliminating the need for PHP
 * streaming. It uses the following URL scheme for uploads:
 * `{scheme}://{domain}/wp-files/{blog_id}/`. By inserting the `$blog_id`, one
 * rewrite rule can make sure file requests go to the correct blog.
 * 
 * **Note:** You will need to implement this NGINX rewrite rule for this to
 * work: `rewrite ^/wp-files/([0-9]+)/(.*)$ /wp-content/blogs.dir/$1/files/$2;`
 *
 * Original credits go to Mark Jaquith: {@link http://github.com/markjaquith/WP-Stack}
 *
 * @package   Stage_WP_Multisite_Uploads
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL2
 * @link      http://github.com/andrezrv/stage-wp/
 * @copyright 2014 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin name: Stage WP Multisite Uploads
 * Plugin URI: http://github.com/andrezrv/stage-wp/
 * Description: The way WordPress Multisite serves uploads is not ideal. It streams them through a PHP file. Professional sites should not do this. This plugin allows one NGINX rewrite rule to handle all uploads, eliminating the need for PHP streaming. It uses the following URL scheme for uploads: <code>{scheme}://{domain}/wp-files/{blog_id}/</code>. By inserting the <code>$blog_id</code>, one rewrite rule can make sure file requests go to the correct blog. <strong>Note:</strong> You will need to implement this NGINX rewrite rule for this to work: <code>rewrite ^/wp-files/([0-9]+)/(.*)$ /wp-content/blogs.dir/$1/files/$2;</code> Original credits go to <a href="https://github.com/markjaquith/WordPress-Skeleton">Mark Jaquith</a>.
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

	class Stage_WP_MS_Uploads_Plugin extends Stage_WP_Plugin {

		public static $instance;

		public function __construct() {
			self::$instance = $this;
			if ( is_multisite() ) {
				$this->hook( 'init' );
			}
		}

		public function init() {
			global $blog_id;
			if ( $blog_id != 1 ) {
				$this->hook( 'option_fileupload_url' );
				$this->hook( 'upload_dir' );
			}
		}

		public function upload_dir( $upload ) {
			
			/**
			 * $upload expected format:
			 * $upload = array(
			 * 				'subdir'  => '/2012/07',
			 * 				'basedir' => '/Users/mark/Sites/wp.git/wp-content/uploads',
			 * 				'path'    => '/Users/mark/Sites/wp.git/wp-content/uploads/2012/07',
			 * 				'baseurl' => 'http://wp.git/wp-files/1',
			 * 				'url'     => 'http://wp.git/wp-content/uploads/2012/07',
			 * 				'error'   => false
			 * );
			 */
			
			global $blog_id;
			$parsed = parse_url( $upload['baseurl'] );
			$upload['baseurl'] = $parsed['scheme'] . '://' . $parsed['host'] . '/wp-files/' . $blog_id;
			$upload['url'] = $upload['baseurl'] . $upload['subdir'];
			var_export( $upload ); die();
			return $upload;

		}

		// Does core even use this anymore?
		public function option_fileupload_url( $url ) {
			global $blog_id;
			$parsed = parse_url( $url );
			$url = $parsed['scheme'] . '://' . $parsed['host'] . '/wp-files/' . $blog_id . '/';
			return $url;
		}

	}

	new Stage_WP_MS_Uploads_Plugin;

}
