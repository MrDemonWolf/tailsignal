<?php
/**
 * Dashboard admin page.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_Admin_Dashboard {

	/**
	 * Render the dashboard page.
	 */
	public function render() {
		$device_count    = TailSignal_DB::get_device_count();
		$platform_counts = TailSignal_DB::get_device_count_by_platform();
		$dev_count       = TailSignal_DB::get_dev_device_count();
		$monthly_sent    = TailSignal_DB::get_monthly_send_count();
		$success_rate    = TailSignal_DB::get_success_rate();
		$recent          = TailSignal_DB::get_recent_notifications( 10 );
		$dev_mode        = '1' === get_option( 'tailsignal_dev_mode', '0' );

		include TAILSIGNAL_PLUGIN_DIR . 'admin/partials/dashboard.php';
	}
}
