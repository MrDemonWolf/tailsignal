<?php
/**
 * Fired during plugin activation.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		// Create database tables.
		TailSignal_DB::create_tables();

		// Set default options.
		$defaults = array(
			'tailsignal_auto_notify'         => '1',
			'tailsignal_expo_access_token'   => '',
			'tailsignal_default_title'       => 'New from {site_name}',
			'tailsignal_default_body'        => '{post_title}',
			'tailsignal_use_featured_image'  => '1',
			'tailsignal_dev_mode'                      => '0',
			'tailsignal_db_version'                    => TAILSIGNAL_VERSION,
			'tailsignal_portfolio_auto_notify'         => '1',
			'tailsignal_portfolio_default_title'       => 'New Project: {post_title}',
			'tailsignal_portfolio_default_body'        => '{post_title} by {author_name}',
			'tailsignal_portfolio_use_featured_image'  => '1',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}

		// Add custom capability to administrator role.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'tailsignal_manage' );
		}
	}
}
