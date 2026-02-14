<?php
/**
 * Notification History admin page with WP_List_Table.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class TailSignal_History_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'notification',
			'plural'   => 'notifications',
			'ajax'     => false,
		) );
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'title'         => __( 'Title', 'tailsignal' ),
			'type'          => __( 'Type', 'tailsignal' ),
			'target_type'   => __( 'Target', 'tailsignal' ),
			'total_devices' => __( 'Devices', 'tailsignal' ),
			'status'        => __( 'Status', 'tailsignal' ),
			'created_at'    => __( 'Date', 'tailsignal' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'title'      => array( 'title', false ),
			'type'       => array( 'type', false ),
			'status'     => array( 'status', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Column: title.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_title( $item ) {
		$title = esc_html( $item->title );

		// Show body preview.
		$body = esc_html( wp_trim_words( $item->body, 10, '...' ) );
		$output = '<strong>' . $title . '</strong>';
		$output .= '<br><span class="tw-text-xs tw-text-gray-500">' . $body . '</span>';

		return $output;
	}

	/**
	 * Column: type.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_type( $item ) {
		$types = array(
			'post'      => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">post</span>',
			'manual'    => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">manual</span>',
			'scheduled' => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800">scheduled</span>',
		);

		return $types[ $item->type ] ?? esc_html( $item->type );
	}

	/**
	 * Column: target_type.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_target_type( $item ) {
		$targets = array(
			'all'      => __( 'All', 'tailsignal' ),
			'dev'      => __( 'Dev', 'tailsignal' ),
			'group'    => __( 'Group', 'tailsignal' ),
			'specific' => __( 'Specific', 'tailsignal' ),
		);

		return $targets[ $item->target_type ] ?? esc_html( $item->target_type );
	}

	/**
	 * Column: total_devices.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_total_devices( $item ) {
		return sprintf( '%d/%d', $item->total_success, $item->total_devices );
	}

	/**
	 * Column: status.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_status( $item ) {
		$statuses = array(
			'pending'          => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-gray-100 tw-text-gray-800">pending</span>',
			'scheduled'        => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">scheduled</span>',
			'sent'             => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">sent</span>',
			'receipts_checked' => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">ok</span>',
			'failed'           => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">failed</span>',
			'cancelled'        => '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-gray-100 tw-text-gray-500">cancelled</span>',
		);

		return $statuses[ $item->status ] ?? esc_html( $item->status );
	}

	/**
	 * Column: created_at.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_created_at( $item ) {
		if ( 'scheduled' === $item->status && ! empty( $item->scheduled_at ) ) {
			return esc_html__( 'Scheduled:', 'tailsignal' ) . '<br>' . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item->scheduled_at ) ) );
		}

		return esc_html( human_time_diff( strtotime( $item->created_at ), current_time( 'timestamp' ) ) ) . ' ' . esc_html__( 'ago', 'tailsignal' );
	}

	/**
	 * Extra table nav for filters.
	 *
	 * @param string $which Top or bottom.
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$current_type   = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
		$current_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		echo '<div class="alignleft actions">';

		echo '<select name="type">';
		echo '<option value="">' . esc_html__( 'All Types', 'tailsignal' ) . '</option>';
		echo '<option value="post"' . selected( $current_type, 'post', false ) . '>' . esc_html__( 'Post', 'tailsignal' ) . '</option>';
		echo '<option value="manual"' . selected( $current_type, 'manual', false ) . '>' . esc_html__( 'Manual', 'tailsignal' ) . '</option>';
		echo '<option value="scheduled"' . selected( $current_type, 'scheduled', false ) . '>' . esc_html__( 'Scheduled', 'tailsignal' ) . '</option>';
		echo '</select>';

		echo '<select name="status">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'tailsignal' ) . '</option>';
		echo '<option value="sent"' . selected( $current_status, 'sent', false ) . '>' . esc_html__( 'Sent', 'tailsignal' ) . '</option>';
		echo '<option value="receipts_checked"' . selected( $current_status, 'receipts_checked', false ) . '>' . esc_html__( 'OK', 'tailsignal' ) . '</option>';
		echo '<option value="scheduled"' . selected( $current_status, 'scheduled', false ) . '>' . esc_html__( 'Scheduled', 'tailsignal' ) . '</option>';
		echo '<option value="failed"' . selected( $current_status, 'failed', false ) . '>' . esc_html__( 'Failed', 'tailsignal' ) . '</option>';
		echo '</select>';

		submit_button( __( 'Filter', 'tailsignal' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items() {
		$per_page = 20;

		$args = array(
			'per_page' => $per_page,
			'page'     => $this->get_pagenum(),
			'orderby'  => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
			'order'    => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
			'type'     => isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '',
			'status'   => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
		);

		$result = TailSignal_DB::get_notifications( $args );

		$this->items = $result['items'];

		$this->set_pagination_args( array(
			'total_items' => $result['total'],
			'per_page'    => $per_page,
			'total_pages' => ceil( $result['total'] / $per_page ),
		) );
	}
}

class TailSignal_Admin_History {

	/**
	 * Render the history page.
	 */
	public function render() {
		include TAILSIGNAL_PLUGIN_DIR . 'admin/partials/history.php';
	}
}
