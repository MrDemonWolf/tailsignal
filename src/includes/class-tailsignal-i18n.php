<?php
/**
 * Define the internationalization functionality.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_i18n {

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'tailsignal',
			false,
			dirname( TAILSIGNAL_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
