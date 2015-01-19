<?php
/**
 * Stage WP Bitbucket Connector
 *
 * Connect the current site with Bitbucket through a POST hook
 * and deploy using Stage WP.
 *
 * The user running your web server (usually www-data, apache or httpd)
 * needs to be able to access your repository and deploy user using a
 * public key for this plugin to work as expected.
 *
 * @package   Stage_WP_Bitbucket_Connector
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL2
 * @link      http://github.com/andrezrv/stage-wp-bitbucket-connector/
 * @copyright 2015 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin name: Stage WP Bitbucket Connector
 * Plugin URI: http://github.com/andrezrv/stage-wp/
 * Description: Connect the current site with Bitbucket through a POST hook and deploy using Stage WP.
 * Author: Andrés Villarreal
 * Author URI: http://about.me/andrezrv
 * Version: 1.0.0
 * License: GPL2
 */

if ( ! class_exists( 'Stage_WP_Bitbucket_Connector' ) ) :
/**
 * Class Stage_WP_Bitbucket_Connector
 *
 * Setup connection between this WordPress website and Bitbucket POST hook.
 *
 * @since 1.0.0
 */
final class Stage_WP_Bitbucket_Connector {
	/**
	 * Internal options for this class.
	 *
	 * @var   array|mixed|void
	 * @since 1.0.0
	 */
	private $options = array();

	/**
	 * Option ID to retrieve an array of options using the Options API.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	private static $options_id = 'stage_wp_bitbucket_connector_options';

	/**
	 * ID used to initialize and retrieve results when interacting with the
	 * Settings API.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	private $settings_id = 'stage-wp-bitbucket-connector-settings';

	/**
	 * Text domain for this plugin.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	private $text_domain = 'stage_wp';

	/**
	 * Initialize internal options and plugin hooks.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Return early if running in AJAX context.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Obtain internal options.
		$this->options = self::get_options();

		// Schedule internal hooks.
		$this->setup_hooks();
	}

	/**
	 * Make sure this class is instantiated only once.
	 *
	 * @return Stage_WP_Bitbucket_Connector Self instance of this class.
	 * @since  1.0
	 */
	final public static function get_instance() {
		static $instances = array();

		// PHP >= 5.3 required.
		$called_class = get_called_class();

		if ( ! isset( $instances[$called_class] ) ) {
			$instances[$called_class] = new $called_class();
		}

		return $instances[$called_class];
	}

	/**
	 * Schedule plugin hooks.
	 *
	 * @since 1.0.0
	 */
	private function setup_hooks() {
		// Register a sub-menu page.
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );

		// Register plugin settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Initialize internal options.
	 *
	 * @since 1.0.0
	 */
	private static function initialize_options() {
		$options = array(
			'key'           => md5( time() ),
			'log'           => '',
			'branch'        => 'master',
			'stage_wp_path' => '',
		);

		update_option( self::$options_id, $options );
	}

	/**
	 * Obtain internal options.
	 *
	 * @since  1.0.0
	 * @return mixed|void
	 */
	private static function get_options() {
		$options = get_option( self::$options_id );

		// If we don't have any options, initialize them and get back here again.
		if ( ! $options ) {
			self::initialize_options();
			$options = self::get_options();
		}

		return $options;
	}

	/**
	 * Perform deployment process.
	 *
	 * @since 1.0.0
	 */
	public function deploy() {
		// Return early if the deployment can't be authorized.
		if ( ! $this->allow_deployment() ) {
			return;
		}

		// Fire deployment process through Stage WP.
		$this->prepare_deployment();

		// Set a message with the date and time of the process.
		$message = sprintf( __( 'Deployment performed at %s', $this->text_domain ), date( 'Y-m-d H:i:s' ) );

		// Log previous message.
		$this->log( $message );

		// Die showing message.
		die( $message );
	}

	/**
	 * Check if the deployment process can be fired.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	private function allow_deployment() {
		if ( ! $this->requested_deployment() || ! $this->request_has_valid_key() || ! $this->request_has_valid_branch() ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the deployment process was correctly requested.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	private function requested_deployment() {
		if ( empty( $_REQUEST['deploy'] ) || 'true' != $_REQUEST['deploy'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the deployment process was requested using a valid key.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	private function request_has_valid_key() {
		if ( empty( $_REQUEST['key'] ) || $this->options['key'] != urldecode( $_REQUEST['key'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if at least one of the pushed commits belongs to the deployment branch.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	private function request_has_valid_branch() {
		if ( empty( $_REQUEST['payload'] ) ) {
			return false;
		}

		$payload = json_decode( $_REQUEST['payload'], true );

		if ( empty( $payload['commits'] ) ) {
			return false;
		}

		$has_allowed_branch = false;
		foreach ( $payload['commits'] as $commit ) {
			if ( $this->options['branch'] == $commit['branch'] ) {
				$has_allowed_branch = true;
				break;
			}
		}

		return $has_allowed_branch;
	}

	private function prepare_deployment() {
		// Log received data.
		$this->log( 'Preparing deployment with the following data: ' . serialize( $_REQUEST ) );

		// Construct command to run through Stage WP.
		$cmd = 'cd ' . $this->options['stage_wp_path'] . '; cap deploy 2>&1';

		// Execute command.
		exec( $cmd, $output, $return_var );

		// Log results of deployment process.
		$this->log( 'Deployment results: ' . "\n" . implode( "\n", $output ) );
	}

	/**
	 * Add a submenu page to WP admin.
	 *
	 * @since 1.0.0
	 */
	public function add_submenu_page() {
		// Avoid this function from running inside any other action.
		if ( 'admin_menu' != current_action() ) {
			return false;
		}

		add_submenu_page(
			'options-general.php',
			__( 'Stage WP Bitbucket Connector' ),
			__( 'Deployment Settings' ),
			'manage_options',
			'stage-wp-bitbucket-connector',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Output HTML for settings page.
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {
		// Avoid this function from running inside any other action.
		if ( 'settings_page_stage-wp-bitbucket-connector' != current_action() ) {
			return false;
		}

		// Check that the user is allowed to update options.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		$options = self::get_options();
		?>
		<div class="wrap">

			<h2><?php _e( 'Stage WP Bitbucket Connector Settings '); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( $this->settings_id ); ?>
				<?php do_settings_sections( $this->settings_id ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Deployment Key', $this->text_domain ); ?></th>
						<td><input type="text" name="stage_wp_bitbucket_connector_options[key]" value="<?php echo esc_attr( $options['key'] ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Deployment Branch', $this->text_domain ); ?></th>
						<td><input type="text" name="stage_wp_bitbucket_connector_options[branch]" value="<?php echo esc_attr( $options['branch'] ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Deployment Log File', $this->text_domain ); ?></th>
						<td><input type="text" name="stage_wp_bitbucket_connector_options[log]" value="<?php echo esc_attr( $options['log'] ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Stage WP Installation Path', $this->text_domain ); ?></th>
						<td><input type="text" name="stage_wp_bitbucket_connector_options[stage_wp_path]" value="<?php echo esc_attr( $options['stage_wp_path'] ); ?>" /></td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
	<?php
	}

	/**
	 * Register internal settings against the Settings API.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		// Avoid this function from running inside any other action.
		if ( 'admin_init' != current_action() ) {
			return false;
		}

		register_setting( $this->settings_id, 'stage_wp_bitbucket_connector_options' );
	}

	/**
	 * Writes a message to the log file.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $message  The message to write
	 * @param string  $type     The type of log message (e.g. INFO, DEBUG, ERROR, etc.)
	 */
	private function log( $message, $type = 'INFO' ) {
		if ( $this->options['log'] ) {
			// Set the name of the log file
			$filename = $this->options['log'];

			if ( ! file_exists( $filename ) ) {
				// Create the log file
				file_put_contents( $filename, '' );

				// Allow anyone to write to log files
				chmod( $filename, 0666 );
			}

			// Write the message into the log file
			// Format: time --- type: message
			file_put_contents( $filename, date( 'Y-m-d H:i:s' ) . ' --- ' . $type . ': ' . $message . PHP_EOL, FILE_APPEND );
		}
	}
}
endif;

if ( ! function_exists( 'stage_wp_bitbucket_connector_init' ) ) :
add_action( 'plugins_loaded', 'stage_wp_bitbucket_connector_init' );
/**
 * Initialize Bitbucket connector and deploy when requested.
 *
 * @since 1.0.0
 */
function stage_wp_bitbucket_connector_init() {
	// Avoid this function from running inside any other action.
	if ( 'plugins_loaded' != current_action() ) {
		return false;
	}

	global $stage_wp_bitbucket_connector;

	if ( ! $stage_wp_bitbucket_connector ) {
		$stage_wp_bitbucket_connector = Stage_WP_Bitbucket_Connector::get_instance();

		if ( ! empty( $_REQUEST['deploy'] ) ) {
			$stage_wp_bitbucket_connector->deploy();
		}
	}
}
endif;
