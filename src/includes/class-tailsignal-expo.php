<?php
/**
 * Expo Push Service wrapper.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;
use ExpoSDK\Utils;

/**
 * Extended ExpoMessage that supports the top-level "image" field
 * for rich push notifications (not supported by the SDK natively).
 */
class TailSignal_ExpoMessage extends ExpoMessage {

	/**
	 * Image URL for rich notification.
	 *
	 * @var string|null
	 */
	private $image = null;

	/**
	 * Set the image URL.
	 *
	 * @param string|null $image Image URL.
	 * @return $this
	 */
	public function setImage( $image ) {
		$this->image = $image;
		return $this;
	}

	/**
	 * Convert to array, including the image field.
	 *
	 * @return array
	 */
	public function toArray(): array {
		$attributes = parent::toArray();

		if ( null !== $this->image ) {
			$attributes['image'] = $this->image;
		}

		return $attributes;
	}
}

class TailSignal_Expo {

	/**
	 * Expo SDK instance.
	 *
	 * @var Expo|null
	 */
	private static $expo = null;

	/**
	 * Get or create the Expo SDK instance.
	 *
	 * @return Expo
	 */
	public static function get_instance() {
		if ( null === self::$expo ) {
			$access_token = get_option( 'tailsignal_expo_access_token', '' );

			if ( ! empty( $access_token ) ) {
				self::$expo = Expo::driver( 'file' )->setAccessToken( $access_token );
			} else {
				self::$expo = Expo::driver( 'file' );
			}
		}

		return self::$expo;
	}

	/**
	 * Reset the instance (useful for testing).
	 */
	public static function reset_instance() {
		self::$expo = null;
	}

	/**
	 * Validate an Expo push token.
	 *
	 * @param string $token The token to validate.
	 * @return bool True if valid.
	 */
	public static function is_valid_token( $token ) {
		return Utils::isExpoPushToken( $token );
	}

	/**
	 * Build an ExpoMessage.
	 *
	 * @param array $params Message parameters.
	 * @return ExpoMessage
	 */
	public static function build_message( $params ) {
		$attributes = array(
			'title' => $params['title'] ?? '',
			'body'  => $params['body'] ?? '',
			'sound' => 'default',
		);

		// Custom data payload.
		$data = array();
		if ( ! empty( $params['data'] ) ) {
			$parsed = is_string( $params['data'] ) ? json_decode( $params['data'], true ) : $params['data'];
			if ( is_array( $parsed ) ) {
				$data = $parsed;
			}
		}

		if ( ! empty( $data ) ) {
			$attributes['data'] = $data;
		}

		// Rich notification with image — set as top-level Expo API field.
		// mutableContent is required for iOS to process the image attachment.
		if ( ! empty( $params['image_url'] ) ) {
			$attributes['image']          = $params['image_url'];
			$attributes['mutableContent'] = true;
		}

		return new TailSignal_ExpoMessage( $attributes );
	}

	/**
	 * Send push notifications.
	 *
	 * @param array $tokens  Array of Expo push tokens.
	 * @param array $params  Message parameters (title, body, data, image_url).
	 * @return array Result with 'ticket_ids', 'success_count', 'failed_count', 'stale_tokens'.
	 */
	public static function send( $tokens, $params ) {
		$result = array(
			'ticket_ids'    => array(),
			'success_count' => 0,
			'failed_count'  => 0,
			'stale_tokens'  => array(),
		);

		if ( empty( $tokens ) ) {
			return $result;
		}

		// Filter valid tokens.
		$valid_tokens = array_filter( $tokens, array( __CLASS__, 'is_valid_token' ) );
		$result['failed_count'] = count( $tokens ) - count( $valid_tokens );

		if ( empty( $valid_tokens ) ) {
			return $result;
		}

		$expo    = self::get_instance();
		$message = self::build_message( $params );

		$indexed_tokens = array_values( $valid_tokens );

		try {
			$response = $expo->send( $message )->to( $indexed_tokens )->push();

			if ( $response ) {
				$data = $response->getData();

				if ( ! empty( $data ) ) {
					foreach ( $data as $index => $ticket ) {
						if ( isset( $ticket['status'] ) && 'ok' === $ticket['status'] ) {
							$result['success_count']++;
							if ( isset( $ticket['id'] ) ) {
								$result['ticket_ids'][] = $ticket['id'];
							}
						} else {
							$result['failed_count']++;

							// Track DeviceNotRegistered for cleanup.
							if ( isset( $ticket['details']['error'] ) && 'DeviceNotRegistered' === $ticket['details']['error'] ) {
								if ( isset( $indexed_tokens[ $index ] ) ) {
									$result['stale_tokens'][] = $indexed_tokens[ $index ];
								}
							}
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			$result['failed_count'] = count( $valid_tokens );
			error_log( 'TailSignal Expo send error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		// Auto-remove stale tokens.
		if ( ! empty( $result['stale_tokens'] ) ) {
			TailSignal_DB::deactivate_tokens( $result['stale_tokens'] );
		}

		return $result;
	}

	/**
	 * Check receipt status for ticket IDs.
	 *
	 * @param array $ticket_ids Array of Expo ticket IDs.
	 * @return array Receipt data.
	 */
	public static function check_receipts( $ticket_ids ) {
		if ( empty( $ticket_ids ) ) {
			return array();
		}

		$expo = self::get_instance();

		try {
			$response = $expo->getReceipts( $ticket_ids )->check();

			if ( $response ) {
				return $response->getData() ?? array();
			}
		} catch ( \Exception $e ) {
			error_log( 'TailSignal receipt check error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		return array();
	}
}
