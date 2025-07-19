<?php
/**
 * The plugin bootstrap file
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           VerifyWoo
 *
 * @wordpress-plugin
 * Plugin Name:       Verify Woo
 * Plugin URI:        https://github.com/Parsa-mrz/VerifyWoo
 * Description:       VerifyWoo integration for WooCommerce.
 * Version:           1.0.0
 * Author:            Parsa Mirzaie
 * Author URI:        https://parsamirzaie.com
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       verify-woo
 * Domain Path:       /languages
 * Requires at least:    6.4
 * Requires PHP:         7.4
 *
 * WC requires at least: 8.0
 * WC tested up to:      9.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PLUGIN_FILE' ) ) {
	define( 'PLUGIN_FILE', __FILE__ );
}

require __DIR__ . '/app/Autoloader.php';

if ( ! \app\Autoloader::init() ) {
	return;
}

/**
 * The code that runs during plugin activation.
 */
function activate_verify_woo() {
	\app\Plugin\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_verify_woo() {
	\app\Plugin\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_verify_woo' );
register_deactivation_hook( __FILE__, 'deactivate_verify_woo' );

/**
 * Returns the main instance of VerifyWoo.
 *
 * @since   1.0.0
 * @return  \app\Plugin
 */
function verifi_woo() {
	return \app\Plugin::instance();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_verify_woo() {
	$GLOBALS['VerifyWoo'] = verifi_woo();
}

add_action( 'plugins_loaded', 'run_verify_woo', 10 );
