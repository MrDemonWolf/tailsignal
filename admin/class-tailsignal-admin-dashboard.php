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
		$device_stats    = TailSignal_DB::get_device_summary_stats();
		$device_count    = $device_stats['total'];
		$platform_counts = array( 'ios' => $device_stats['ios'], 'android' => $device_stats['android'] );
		$dev_count       = $device_stats['dev'];
		$monthly_sent    = TailSignal_DB::get_monthly_send_count();
		$success_rate    = TailSignal_DB::get_success_rate();
		$recent          = TailSignal_DB::get_recent_notifications( 10 );
		$dev_mode        = '1' === get_option( 'tailsignal_dev_mode', '0' );
		$chart_stats     = TailSignal_DB::get_monthly_notification_stats( 12 );

		// Prepare chart data for JS.
		$chart_data = array(
			'labels'  => array(),
			'success' => array(),
			'failed'  => array(),
		);
		foreach ( $chart_stats as $row ) {
			$chart_data['labels'][]  = $row->month;
			$chart_data['success'][] = (int) $row->success;
			$chart_data['failed'][]  = (int) $row->failed;
		}

		// Pass chart data to JS via localize.
		wp_localize_script( 'tailsignal-admin', 'tailsignal_chart', $chart_data );

		include TAILSIGNAL_PLUGIN_DIR . 'admin/partials/dashboard.php';
	}
}
