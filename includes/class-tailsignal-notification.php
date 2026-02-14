<?php
/**
 * Notification builder and sender.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_Notification {

	/**
	 * Handle post status transition for auto-notifications.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       The post object.
	 */
	public function on_post_published( $new_status, $old_status, $post ) {
		// Only trigger on first publish.
		if ( 'publish' !== $new_status || 'publish' === $old_status ) {
			return;
		}

		// Only for posts (not pages, custom types, etc. unless filtered).
		$allowed_types = apply_filters( 'tailsignal_post_types', array( 'post' ) );
		if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
			return;
		}

		// Check global auto-notify setting.
		if ( '1' !== get_option( 'tailsignal_auto_notify', '1' ) ) {
			return;
		}

		// Check per-post meta box toggle.
		$notify = get_post_meta( $post->ID, '_tailsignal_notify', true );
		if ( '0' === $notify ) {
			return;
		}

		// Prevent duplicate notifications.
		if ( get_post_meta( $post->ID, '_tailsignal_notified', true ) ) {
			return;
		}

		// Build notification params.
		$params = $this->build_post_notification_params( $post );

		// Get tokens.
		$tokens = TailSignal_DB::get_all_active_tokens();

		if ( empty( $tokens ) ) {
			return;
		}

		// Send notification.
		$result = self::send_notification( $params, $tokens, 'post', $post->ID );

		if ( $result ) {
			// Mark as notified to prevent duplicates.
			update_post_meta( $post->ID, '_tailsignal_notified', '1' );
		}
	}

	/**
	 * Build notification parameters from a post.
	 *
	 * @param WP_Post $post The post object.
	 * @return array Notification parameters.
	 */
	public function build_post_notification_params( $post ) {
		// Check per-post overrides.
		$custom_title = get_post_meta( $post->ID, '_tailsignal_custom_title', true );
		$custom_body  = get_post_meta( $post->ID, '_tailsignal_custom_body', true );

		$title = ! empty( $custom_title ) ? $custom_title : get_option( 'tailsignal_default_title', 'New from {site_name}' );
		$body  = ! empty( $custom_body ) ? $custom_body : get_option( 'tailsignal_default_body', '{post_title}' );

		// Parse placeholders.
		$title = $this->parse_placeholders( $title, $post );
		$body  = $this->parse_placeholders( $body, $post );

		$params = array(
			'title' => $title,
			'body'  => $body,
			'data'  => wp_json_encode( array(
				'post_id' => $post->ID,
				'url'     => get_permalink( $post ),
			) ),
		);

		// Featured image.
		$include_image = get_post_meta( $post->ID, '_tailsignal_include_image', true );
		if ( '' === $include_image ) {
			$include_image = get_option( 'tailsignal_use_featured_image', '1' );
		}

		if ( '1' === $include_image && has_post_thumbnail( $post->ID ) ) {
			$params['image_url'] = get_the_post_thumbnail_url( $post->ID, 'large' );
		}

		return $params;
	}

	/**
	 * Parse template placeholders.
	 *
	 * @param string  $template The template string.
	 * @param WP_Post $post     The post object.
	 * @return string Parsed string.
	 */
	public function parse_placeholders( $template, $post ) {
		$replacements = array(
			'{post_title}'  => $post->post_title,
			'{site_name}'   => get_bloginfo( 'name' ),
			'{author_name}' => get_the_author_meta( 'display_name', $post->post_author ),
		);

		// Post excerpt: first 20 words of content.
		$content = wp_strip_all_tags( $post->post_content );
		$words   = explode( ' ', $content );
		$excerpt = implode( ' ', array_slice( $words, 0, 20 ) );
		if ( count( $words ) > 20 ) {
			$excerpt .= '...';
		}
		$replacements['{post_excerpt}'] = $excerpt;

		// Primary category.
		$categories = get_the_category( $post->ID );
		$replacements['{category}'] = ! empty( $categories ) ? $categories[0]->name : '';

		// Featured image URL.
		$replacements['{featured_image}'] = has_post_thumbnail( $post->ID )
			? get_the_post_thumbnail_url( $post->ID, 'large' )
			: '';

		return str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$template
		);
	}

	/**
	 * Send a notification and log it.
	 *
	 * @param array       $params      Notification parameters (title, body, data, image_url).
	 * @param array       $tokens      Array of Expo push tokens.
	 * @param string      $type        Notification type: post, manual, scheduled.
	 * @param int|null    $post_id     Associated post ID.
	 * @param string      $target_type Target type: all, dev, group, specific.
	 * @param array|null  $target_ids  Target IDs for group/specific.
	 * @param int|null    $sent_by     WP user ID who sent.
	 * @return int|false Notification ID or false on failure.
	 */
	public static function send_notification( $params, $tokens, $type = 'manual', $post_id = null, $target_type = 'all', $target_ids = null, $sent_by = null ) {
		// Create notification record.
		$notification_id = TailSignal_DB::insert_notification( array(
			'title'         => $params['title'] ?? '',
			'body'          => $params['body'] ?? '',
			'data'          => $params['data'] ?? null,
			'post_id'       => $post_id,
			'type'          => $type,
			'target_type'   => $target_type,
			'target_ids'    => ! empty( $target_ids ) ? wp_json_encode( $target_ids ) : null,
			'image_url'     => $params['image_url'] ?? null,
			'total_devices' => count( $tokens ),
			'status'        => 'pending',
			'sent_by'       => $sent_by,
		) );

		if ( ! $notification_id ) {
			return false;
		}

		// Send via Expo.
		$result = TailSignal_Expo::send( $tokens, $params );

		// Update notification record.
		TailSignal_DB::update_notification( $notification_id, array(
			'total_success' => $result['success_count'],
			'total_failed'  => $result['failed_count'],
			'status'        => 'sent',
			'ticket_ids'    => ! empty( $result['ticket_ids'] ) ? wp_json_encode( $result['ticket_ids'] ) : null,
		) );

		// Link to post history if applicable.
		if ( $post_id ) {
			TailSignal_DB::insert_notification_history( $post_id, $notification_id );
		}

		// Schedule receipt check in 15 minutes.
		if ( ! empty( $result['ticket_ids'] ) ) {
			wp_schedule_single_event(
				time() + ( 15 * MINUTE_IN_SECONDS ),
				'tailsignal_check_receipts',
				array( $notification_id )
			);
		}

		return $notification_id;
	}

	/**
	 * Schedule a notification for later.
	 *
	 * @param array       $params       Notification parameters.
	 * @param string      $scheduled_at Datetime string for when to send.
	 * @param string      $target_type  Target type.
	 * @param array|null  $target_ids   Target IDs.
	 * @param int|null    $post_id      Associated post ID.
	 * @param int|null    $sent_by      WP user ID.
	 * @return int|false Notification ID or false.
	 */
	public static function schedule_notification( $params, $scheduled_at, $target_type = 'all', $target_ids = null, $post_id = null, $sent_by = null ) {
		$notification_id = TailSignal_DB::insert_notification( array(
			'title'        => $params['title'] ?? '',
			'body'         => $params['body'] ?? '',
			'data'         => $params['data'] ?? null,
			'post_id'      => $post_id,
			'type'         => 'scheduled',
			'target_type'  => $target_type,
			'target_ids'   => ! empty( $target_ids ) ? wp_json_encode( $target_ids ) : null,
			'image_url'    => $params['image_url'] ?? null,
			'scheduled_at' => $scheduled_at,
			'status'       => 'scheduled',
			'sent_by'      => $sent_by,
		) );

		if ( ! $notification_id ) {
			return false;
		}

		// Schedule WP-Cron event.
		$timestamp = strtotime( $scheduled_at );
		if ( $timestamp ) {
			wp_schedule_single_event(
				$timestamp,
				'tailsignal_send_scheduled',
				array( $notification_id )
			);
		}

		return $notification_id;
	}

	/**
	 * Cancel a scheduled notification.
	 *
	 * @param int $notification_id The notification ID.
	 * @return bool True on success.
	 */
	public static function cancel_scheduled( $notification_id ) {
		$notification = TailSignal_DB::get_notification( $notification_id );

		if ( ! $notification || 'scheduled' !== $notification->status ) {
			return false;
		}

		// Unschedule the cron event.
		$timestamp = wp_next_scheduled( 'tailsignal_send_scheduled', array( (int) $notification_id ) );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'tailsignal_send_scheduled', array( (int) $notification_id ) );
		}

		// Update status to cancelled.
		return TailSignal_DB::update_notification( $notification_id, array(
			'status' => 'cancelled',
		) );
	}
}
