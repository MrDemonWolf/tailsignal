<?php
/**
 * WP-Cron handlers for TailSignal.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_Cron {

	/**
	 * Check receipts for a notification.
	 *
	 * @param int $notification_id The notification ID.
	 */
	public function check_receipts( $notification_id = null ) {
		if ( $notification_id ) {
			$this->check_single_receipt( $notification_id );
			return;
		}

		// Check all pending receipts in a single batch API call.
		$notifications = TailSignal_DB::get_pending_receipt_notifications();
		if ( empty( $notifications ) ) {
			return;
		}

		// Collect all ticket IDs across notifications for one batch call.
		$all_ticket_ids = array();
		$ticket_map     = array(); // ticket_id => notification_id.

		foreach ( $notifications as $notification ) {
			$ticket_ids = json_decode( $notification->ticket_ids, true );
			if ( empty( $ticket_ids ) ) {
				continue;
			}
			foreach ( $ticket_ids as $ticket_id ) {
				$all_ticket_ids[]          = $ticket_id;
				$ticket_map[ $ticket_id ] = $notification->id;
			}
		}

		if ( empty( $all_ticket_ids ) ) {
			return;
		}

		// Single batch API call for all receipts.
		$receipts = TailSignal_Expo::check_receipts( $all_ticket_ids );

		// Group results by notification.
		$notification_results = array();
		$stale_tokens         = array();

		foreach ( $receipts as $receipt_id => $receipt ) {
			$notif_id = isset( $ticket_map[ $receipt_id ] ) ? $ticket_map[ $receipt_id ] : null;
			if ( ! $notif_id ) {
				continue;
			}
			if ( ! isset( $notification_results[ $notif_id ] ) ) {
				$notification_results[ $notif_id ] = array(
					'success' => 0,
					'failed'  => 0,
					'data'    => array(),
				);
			}

			$notification_results[ $notif_id ]['data'][ $receipt_id ] = $receipt;

			if ( isset( $receipt['status'] ) && 'ok' === $receipt['status'] ) {
				$notification_results[ $notif_id ]['success']++;
			} else {
				$notification_results[ $notif_id ]['failed']++;
				if ( isset( $receipt['details']['error'] ) && 'DeviceNotRegistered' === $receipt['details']['error'] ) {
					$stale_tokens[] = $receipt_id;
				}
			}
		}

		// Deactivate stale tokens in bulk.
		if ( ! empty( $stale_tokens ) ) {
			TailSignal_DB::deactivate_tokens( $stale_tokens );
		}

		// Update each notification with its results.
		foreach ( $notification_results as $notif_id => $result ) {
			TailSignal_DB::update_notification( $notif_id, array(
				'total_success' => $result['success'],
				'total_failed'  => $result['failed'],
				'status'        => 'receipts_checked',
				'receipt_data'  => wp_json_encode( $result['data'] ),
			) );
		}
	}

	/**
	 * Check receipts for a single notification.
	 *
	 * @param int $notification_id The notification ID.
	 */
	private function check_single_receipt( $notification_id ) {
		$notification = TailSignal_DB::get_notification( $notification_id );

		if ( ! $notification || 'sent' !== $notification->status ) {
			return;
		}

		$ticket_ids = json_decode( $notification->ticket_ids, true );
		if ( empty( $ticket_ids ) ) {
			return;
		}

		$receipts = TailSignal_Expo::check_receipts( $ticket_ids );

		$success_count = 0;
		$failed_count  = 0;
		$stale_tokens  = array();

		foreach ( $receipts as $receipt_id => $receipt ) {
			if ( isset( $receipt['status'] ) && 'ok' === $receipt['status'] ) {
				$success_count++;
			} else {
				$failed_count++;

				// Track stale tokens for cleanup.
				if ( isset( $receipt['details']['error'] ) && 'DeviceNotRegistered' === $receipt['details']['error'] ) {
					$stale_tokens[] = $receipt_id;
				}
			}
		}

		// Deactivate stale tokens.
		if ( ! empty( $stale_tokens ) ) {
			TailSignal_DB::deactivate_tokens( $stale_tokens );
		}

		// Update notification with receipt data.
		TailSignal_DB::update_notification( $notification_id, array(
			'total_success' => $success_count,
			'total_failed'  => $failed_count,
			'status'        => 'receipts_checked',
			'receipt_data'  => wp_json_encode( $receipts ),
		) );
	}

	/**
	 * Send a scheduled notification.
	 *
	 * @param int $notification_id The notification ID.
	 */
	public function send_scheduled_notification( $notification_id ) {
		$notification = TailSignal_DB::get_notification( $notification_id );

		if ( ! $notification || 'scheduled' !== $notification->status ) {
			return;
		}

		// Get tokens based on target type.
		$target_ids = ! empty( $notification->target_ids ) ? json_decode( $notification->target_ids, true ) : null;
		$tokens     = TailSignal_DB::get_tokens_by_target( $notification->target_type, $target_ids );

		if ( empty( $tokens ) ) {
			TailSignal_DB::update_notification( $notification_id, array(
				'status'        => 'failed',
				'total_devices' => 0,
			) );
			return;
		}

		$params = array(
			'title'     => $notification->title,
			'body'      => $notification->body,
			'data'      => $notification->data,
			'image_url' => $notification->image_url,
		);

		// Send via Expo.
		$result = TailSignal_Expo::send( $tokens, $params );

		// Update notification record.
		TailSignal_DB::update_notification( $notification_id, array(
			'total_devices' => count( $tokens ),
			'total_success' => $result['success_count'],
			'total_failed'  => $result['failed_count'],
			'status'        => 'sent',
			'ticket_ids'    => ! empty( $result['ticket_ids'] ) ? wp_json_encode( $result['ticket_ids'] ) : null,
		) );

		// Link to post history if applicable.
		if ( $notification->post_id ) {
			TailSignal_DB::insert_notification_history( $notification->post_id, $notification_id );
		}

		// Schedule receipt check.
		if ( ! empty( $result['ticket_ids'] ) ) {
			wp_schedule_single_event(
				time() + ( 15 * MINUTE_IN_SECONDS ),
				'tailsignal_check_receipts',
				array( $notification_id )
			);
		}
	}
}
