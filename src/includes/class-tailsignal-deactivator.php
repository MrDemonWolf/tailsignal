<?php
/**
 * Fired during plugin deactivation.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_Deactivator {

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		// Clear all scheduled cron events.
		wp_clear_scheduled_hook( 'tailsignal_check_receipts' );
		wp_clear_scheduled_hook( 'tailsignal_send_scheduled' );

		// Clear any single scheduled events.
		$notifications = TailSignal_DB::get_scheduled_notifications();
		foreach ( $notifications as $notification ) {
			$timestamp = wp_next_scheduled( 'tailsignal_send_scheduled', array( (int) $notification->id ) );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'tailsignal_send_scheduled', array( (int) $notification->id ) );
			}
		}
	}
}
