<?php
/*
Plugin Name: WP Stack Manual DB Upgrades
Version: 0.1
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

// Convenience methods
if(!class_exists('WP_Stack_Plugin')){class WP_Stack_Plugin{function hook($h){$p=10;$m=$this->sanitize_method($h);$b=func_get_args();unset($b[0]);foreach((array)$b as $a){if(is_int($a))$p=$a;else $m=$a;}return add_action($h,array($this,$m),$p,999);}private function sanitize_method($m){return str_replace(array('.','-'),array('_DOT_','_DASH_'),$m);}}}

// The plugin
class WP_Stack_Manual_DB_Upgrades_Plugin extends WP_Stack_Plugin {
	public static $instance;

	public function __construct() {
		self::$instance = $this;
		$this->hook( 'plugins_loaded' );
	}

	public function plugins_loaded() {
		$this->hook( 'option_db_version' );
	}

	public function option_db_version( $version ) {
		if ( strpos( $_SERVER['REQUEST_URI'], '/wp-admin/upgrade.php' ) === false )
			return $GLOBALS['wp_db_version'];
		else
			return $version;
	}

}

new WP_Stack_Manual_DB_Upgrades_Plugin;
