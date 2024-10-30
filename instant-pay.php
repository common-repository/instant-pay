<?php
/**
 * Plugin Name:       Instant for WooCommerce
 * Description:       Is a one-click purchase solution for e-commerce business who want to reduce friction in their checkout experience leveraging the network effects of a platform solution.
 * Version:           1.0.0
 * Author:            k2venture
 * Author URI:        https://profiles.wordpress.org/k2venture/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       instant-pay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'INSTANT_PAY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-instant-pay-activator.php
 */
function activate_instant_pay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-instant-pay-activator.php';
	Instant_Pay_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-instant-pay-deactivator.php
 */
function deactivate_instant_pay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-instant-pay-deactivator.php';
	Instant_Pay_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_instant_pay' );
register_deactivation_hook( __FILE__, 'deactivate_instant_pay' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-instant-pay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_instant_pay() {

	$plugin = new Instant_Pay();
	$plugin->run();

}
run_instant_pay();
