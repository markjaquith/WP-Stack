<?php
/*
Plugin Name: WP Stack Multisite Uploads
Version: 0.3
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

// Convenience methods
if(!class_exists('WP_Stack_Plugin')){class WP_Stack_Plugin{function hook($h){$p=10;$m=$this->sanitize_method($h);$b=func_get_args();unset($b[0]);foreach((array)$b as $a){if(is_int($a))$p=$a;else $m=$a;}return add_action($h,array($this,$m),$p,999);}private function sanitize_method($m){return str_replace(array('.','-'),array('_DOT_','_DASH_'),$m);}}}

// The plugin
class WP_Stack_MS_Uploads_Plugin extends WP_Stack_Plugin {
	public static $instance;

	public function __construct() {
		self::$instance = $this;
		if ( is_multisite() )
			$this->hook( 'init' );
	}

	public function init() {
		global $blog_id;
		if ( $blog_id != 1 ) {
			$this->hook( 'option_fileupload_url' );
			$this->hook( 'upload_dir' );
		}
	}

	public function upload_dir( $upload ) {
		/*
		array(
		'subdir' => '/2012/07',
		'basedir' => '/Users/mark/Sites/wp.git/wp-content/uploads',
		'path' => '/Users/mark/Sites/wp.git/wp-content/uploads/2012/07',
		'baseurl' => 'http://wp.git/wp-files/1',
		'url' => 'http://wp.git/wp-content/uploads/2012/07',
		'error' => false
		)
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

new WP_Stack_MS_Uploads_Plugin;
