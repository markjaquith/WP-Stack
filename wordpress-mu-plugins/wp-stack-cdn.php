<?php
/*
Plugin Name: WP Stack CDN
Version: 0.2
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

// Convenience methods
if(!class_exists('WP_Stack_Plugin')){class WP_Stack_Plugin{function hook($h){$p=10;$m=$this->sanitize_method($h);$b=func_get_args();unset($b[0]);foreach((array)$b as $a){if(is_int($a))$p=$a;else $m=$a;}return add_action($h,array($this,$m),$p,999);}private function sanitize_method($m){return str_replace(array('.','-'),array('_DOT_','_DASH_'),$m);}}}

// The plugin
class WP_Stack_CDN_Plugin extends WP_Stack_Plugin {
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
		$domain_set_up = get_option( 'wp_stack_cdn_domain' ) || ( defined( 'WP_STACK_CDN_DOMAIN' ) && WP_STACK_CDN_DOMAIN );
		$production = defined( 'WP_STAGE' ) && WP_STAGE === 'production';
		$staging = defined( 'WP_STAGE' ) && WP_STAGE === 'staging';
		$uploads_only = defined( 'WP_STACK_CDN_UPLOADS_ONLY' ) && WP_STACK_CDN_UPLOADS_ONLY;
		if ( $domain_set_up && !$staging && ( $production || $uploads_only ) )
			$this->hook( 'init' );
	}

	public function init() {
		$this->uploads_only = apply_filters( 'wp_stack_cdn_uploads_only', defined( 'WP_STACK_CDN_UPLOADS_ONLY' ) ? WP_STACK_CDN_UPLOADS_ONLY : false );
		$this->extensions = apply_filters( 'wp_stack_cdn_extensions', array( 'jpe?g', 'gif', 'png', 'css', 'bmp', 'js', 'ico' ) );
		if ( !is_admin() ) {
			$this->hook( 'template_redirect' );
			if ( $this->uploads_only )
				$this->hook( 'wp_stack_cdn_content', 'filter_uploads_only' );
			else
				$this->hook( 'wp_stack_cdn_content', 'filter' );
			$this->site_domain = parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
			$this->cdn_domain = defined( 'WP_STACK_CDN_DOMAIN' ) ? WP_STACK_CDN_DOMAIN : get_option( 'wp_stack_cdn_domain' );
		}
	}

	public function filter_uploads_only( $content ) {
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['baseurl'];
		$domain = preg_quote( parse_url( $upload_dir, PHP_URL_HOST ), '#' );
		$path = parse_url( $upload_dir, PHP_URL_PATH );
		$preg_path = preg_quote( $path, '#' );

		// Targeted replace just on uploads URLs
		return preg_replace( "#=([\"'])(https?://{$domain})?$preg_path/((?:(?!\\1]).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#", '=$1http://' . $this->cdn_domain . $path . '/$3.$4$5$1', $content );
	}

	public function filter( $content ) {
		return preg_replace( "#=([\"'])(https?://{$this->site_domain})?/([^/](?:(?!\\1).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#", '=$1http://' . $this->cdn_domain . '/$3.$4$5$1', $content );
	}

	public function template_redirect() {
		ob_start( array( $this, 'ob' ) );
	}

	public function ob( $contents ) {
			return apply_filters( 'wp_stack_cdn_content', $contents, $this );
	}
}

new WP_Stack_CDN_Plugin;
