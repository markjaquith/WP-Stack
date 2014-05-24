<?php
/**
 * Stage WP Plugin Manager
 *
 * Gives you the option to define which plugins must be active for a particular
 * stage only. You can activate plugins separatedly for
 * local, staging and production stages.
 *
 * To use this plugin, you need to add a constant named `WP_ENV_PLUGINS` to your
 * `wp-config.php` file, with one of the following values: `local`, `staging`,
 * `production`.
 *
 * This plugin supports WordPress Multisite, and can manage network activated plugins.
 *
 * @package   Stage_WP_Plugin_Manager
 * @version   1.0
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL-2.0
 * @link      http://github.com/andrezrv/stage-wp-plugin-manager
 * @copyright 2014 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin Name: Stage WP Plugin Manager
 * Plugin URI: http://wordpress.org/extend/plugins/stage-wp-plugin-manager/
 * Description:  Gives you the option to define which plugins must be active for a particular stage only. You can activate plugins separatedly for local, staging and production stages. To use this plugin, you need to add a constant named <code>WP_ENV_PLUGINS</code> to your <code>wp-config.php</code> file, with one of the following values: <code>local</code>, <code>staging</code>, <code>production</code>. This plugin supports WordPress Multisite, and can manage network activated plugins.
 * Author: Andr&eacute;s Villarreal
 * Author URI: http://www.andrezrv.com
 * Version: 1.0
 */
require dirname( __FILE__ ) . '/stage-wp-plugin-manager/stage-wp-plugin-manager.php';
