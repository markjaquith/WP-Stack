<?php
/*
Plugin Name: WP Stack Staging
Version: 0.1
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

// Convenience methods
if(!class_exists('WP_Stack_Plugin')){class WP_Stack_Plugin{function hook($h){$p=10;$m=$this->sanitize_method($h);$b=func_get_args();unset($b[0]);foreach((array)$b as $a){if(is_int($a))$p=$a;else $m=$a;}return add_action($h,array($this,$m),$p,999);}private function sanitize_method($m){return str_replace(array('.','-'),array('_DOT_','_DASH_'),$m);}}}

// The plugin
class WP_Stack_Staging_Plugin extends WP_Stack_Plugin {
	public static $instance;

	public function __construct() {
		self::$instance = $this;
		if ( !defined( 'WP_STAGE' ) || WP_STAGE !== 'staging' || !defined( 'STAGING_DOMAIN' ) )
			return;
		$this->hook( 'option_home', 'replace_domain' );
		$this->hook( 'option_siteurl', 'replace_domain' );
	}

	public function replace_domain ( $url ) {
		$current_domain = parse_url( $url, PHP_URL_HOST );
		$url = str_replace( '//' . $current_domain, '//' . STAGING_DOMAIN, $url );
		return $url;
	}
}

new WP_Stack_Staging_Plugin;

