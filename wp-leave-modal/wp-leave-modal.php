<?php
/**
 * Plugin Name:       Leave Modal
 * Description:       Shows a confirmation modal before leaving to an external URL, with a configurable admin panel.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Jarosław Kłębucki
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-leave-modal
 *
 * @package WP_Leave_Modal
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_LEAVE_MODAL_VERSION', '1.1.0' );
define( 'WP_LEAVE_MODAL_PLUGIN_FILE', __FILE__ );
define( 'WP_LEAVE_MODAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_LEAVE_MODAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_LEAVE_MODAL_OPTION_KEY', 'wp_leave_modal_settings' );

require_once WP_LEAVE_MODAL_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once WP_LEAVE_MODAL_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once WP_LEAVE_MODAL_PLUGIN_DIR . 'includes/class-assets.php';

/**
 * Bootstrap plugin hooks.
 */
function wp_leave_modal_bootstrap() {
	\WP_Leave_Modal\Admin_Settings::instance()->init();
	\WP_Leave_Modal\Shortcode::instance()->init();
	\WP_Leave_Modal\Assets::instance()->init();
}
add_action( 'plugins_loaded', 'wp_leave_modal_bootstrap' );

/**
 * Load plugin text domain for translations.
 */
function wp_leave_modal_load_textdomain() {
	load_plugin_textdomain(
		'wp-leave-modal',
		false,
		dirname( plugin_basename( WP_LEAVE_MODAL_PLUGIN_FILE ) ) . '/languages'
	);
}
add_action( 'init', 'wp_leave_modal_load_textdomain' );
