<?php
/**
 * Plugin Name:     Spam Email Domain Exclusion for CF7
 * Plugin URI:
 * Description:     Spam Email Domain Exclusion for CF7 provides a seamless solution to block submissions from specific email domains.
 * Author:          Bipin Bheda
 * Author URI:      https://bipin54.wordpress.com/
 * Text Domain:     spam-email-domain-exclusion-cf7
 * Domain Path:     /languages
 * Version:         1.0.0
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SEDE_CF7_VERSION', '1.0.0' );
define( 'SEDE_CF7_PATH', plugin_dir_path( __FILE__ ) );

require SEDE_CF7_PATH . 'inc/class-sede-cf7.php';
require SEDE_CF7_PATH . 'inc/class-sede-cf7-global-option.php';

if ( ! function_exists( 'sede_cf7_activate' ) ) {
	/**
	 * The code runs during plugin activation.
	 */
	function sede_cf7_activate() {
		if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			/* translators: %s: search term to update contact from url */
			$text = sprintf( __( 'The %s should be activated to use this addone.', 'spam-email-domain-exclusion-cf7' ), '<a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">Contact Form 7</a>' );

			wp_die( wp_kses_data( $text ) );
		}
	}
	register_activation_hook( __FILE__, 'sede_cf7_activate' );
}


/**
 * The code runs during plugin deactivation.
 */
function sede_cf7_deactivate() {}
register_deactivation_hook( __FILE__, 'sede_cf7_deactivate' );

if ( ! function_exists( 'sede_cf7_load_plugin_textdomain' ) ) {
	/**
	 * Loads Spam Email Domain Exclusion for CF7 plugin textdomain.
	 */
	function sede_cf7_load_plugin_textdomain() {
		load_plugin_textdomain(
			'spam-email-domain-exclusion-cf7',
			false,
			SEDE_CF7_PATH . 'languages/'
		);
	}
}
add_action( 'plugins_loaded', 'sede_cf7_load_plugin_textdomain' );

if ( ! function_exists( 'sede_cf7_run' ) ) {
	/**
	 * Initialize SEDE_CF7 class instance to load its feature.
	 * Initialize SEDE_CF7_Global_Option class instance to load its feature.
	 */
	function sede_cf7_run() {
		new SEDE_CF7\SEDE_CF7();
		new SEDE_CF7\SEDE_CF7_Global_Option();
	}
	sede_cf7_run();
}
