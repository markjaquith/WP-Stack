<?php
/**
 * Stage WP Plugin Class
 *
 * Just a helper class for Stage WP must-use plugins.
 *
 * Original credits go to Mark Jaquith: {@link http://github.com/markjaquith/WP-Stack}
 *
 * @package   Stage_WP_Plugin_Class
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL2
 * @link      http://github.com/andrezrv/stage-wp/
 * @copyright 2014 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin name: Stage WP Plugin Class
 * Plugin URI: http://github.com/andrezrv/wordpress-bareboner/
 * Description: Just a helper class for <a href="http://github.com/andrezrv/stage-wp">Stage WP</a> must-use plugins. Original credits go to <a href="https://github.com/markjaquith/WordPress-Skeleton">Mark Jaquith</a>.
 * Author: Andrés Villarreal
 * Author URI: http://about.me/andrezrv
 * Version: 1.0
 * License: GPL2
 */
class Stage_WP_Plugin {

	function hook( $h ) {
		
		$p = 10;
		$m = $this->sanitize_method( $h );
		$b = func_get_args();
		
		unset( $b[0] );
		
		foreach( ( array )$b as $a ){
			if ( is_int( $a ) ) {
				$p = $a
			} else {
				$m = $a
			}
		}

		return add_action( $h, array( $this, $m ), $p, 999 );
	}

	private function sanitize_method( $m ){
		return str_replace( array( '.', '-' ), array( '_DOT_', '_DASH_' ), $m );
	}

}
