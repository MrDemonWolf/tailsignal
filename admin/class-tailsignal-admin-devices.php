<?php
/**
 * Devices admin page with WP_List_Table.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class TailSignal_Devices_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'device',
			'plural'   => 'devices',
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
			'cb'            => '<input type="checkbox" />',
			'user_label'    => __( 'Label', 'tailsignal' ),
			'expo_token'    => __( 'Token', 'tailsignal' ),
			'device_type'   => __( 'Platform', 'tailsignal' ),
			'device_model'  => __( 'Model', 'tailsignal' ),
			'os_version'    => __( 'OS', 'tailsignal' ),
			'app_version'   => __( 'App Ver', 'tailsignal' ),
			'locale'        => __( 'Locale', 'tailsignal' ),
			'last_active_at' => __( 'Last Active', 'tailsignal' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'user_label'     => array( 'user_label', false ),
			'device_type'    => array( 'device_type', false ),
			'created_at'     => array( 'created_at', true ),
			'last_active_at' => array( 'last_active_at', false ),
		);
	}

	/**
	 * Column cb.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="device_ids[]" value="%d" />', $item->id );
	}

	/**
	 * Column: user_label.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_user_label( $item ) {
		$label = ! empty( $item->user_label ) ? esc_html( $item->user_label ) : '<em>' . esc_html__( '(no label)', 'tailsignal' ) . '</em>';

		$badges = '';
		if ( $item->is_dev ) {
			$badges .= ' <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">DEV</span>';
		}
		if ( ! $item->is_active ) {
			$badges .= ' <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">' . esc_html__( 'Inactive', 'tailsignal' ) . '</span>';
		}

		// Groups.
		$groups = TailSignal_DB::get_device_groups( $item->id );
		foreach ( $groups as $group ) {
			$badges .= ' <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">' . esc_html( $group->name ) . '</span>';
		}

		// Row actions.
		$actions = array(
			'edit'       => sprintf(
				'<a href="#" class="tailsignal-edit-device" data-id="%d" data-label="%s">%s</a>',
				$item->id,
				esc_attr( $item->user_label ),
				esc_html__( 'Edit Label', 'tailsignal' )
			),
			'toggle_dev' => sprintf(
				'<a href="#" class="tailsignal-toggle-dev" data-id="%d" data-dev="%d">%s</a>',
				$item->id,
				$item->is_dev ? 0 : 1,
				$item->is_dev ? esc_html__( 'Remove Dev', 'tailsignal' ) : esc_html__( 'Mark Dev', 'tailsignal' )
			),
			'delete'     => sprintf(
				'<a href="%s" class="tailsignal-delete-device" onclick="return confirm(\'%s\')">%s</a>',
				wp_nonce_url(
					admin_url( 'admin.php?page=tailsignal-devices&action=delete&device_id=' . $item->id ),
					'tailsignal_delete_device_' . $item->id
				),
				esc_js( __( 'Are you sure you want to delete this device?', 'tailsignal' ) ),
				esc_html__( 'Delete', 'tailsignal' )
			),
		);

		return $label . $badges . $this->row_actions( $actions );
	}

	/**
	 * Column: expo_token.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_expo_token( $item ) {
		$token = esc_html( $item->expo_token );
		if ( strlen( $token ) > 30 ) {
			$token = substr( $token, 0, 25 ) . '...';
		}
		return '<code class="tw-text-xs">' . $token . '</code>';
	}

	/**
	 * Column: device_type.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_device_type( $item ) {
		if ( 'ios' === $item->device_type ) {
			return '<span class="tw-text-sm">iOS</span>';
		} elseif ( 'android' === $item->device_type ) {
			return '<span class="tw-text-sm">Android</span>';
		}
		return esc_html( $item->device_type );
	}

	/**
	 * Default column handler.
	 *
	 * @param object $item        The item.
	 * @param string $column_name The column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'device_model':
			case 'os_version':
			case 'app_version':
			case 'locale':
				return esc_html( $item->$column_name );
			case 'last_active_at':
				if ( empty( $item->last_active_at ) ) {
					return '<em>' . esc_html__( 'Never', 'tailsignal' ) . '</em>';
				}
				return esc_html( human_time_diff( strtotime( $item->last_active_at ), current_time( 'timestamp' ) ) ) . ' ' . esc_html__( 'ago', 'tailsignal' );
			default:
				return '';
		}
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'tailsignal' ),
		);
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

		$current_type   = isset( $_GET['device_type'] ) ? sanitize_text_field( wp_unslash( $_GET['device_type'] ) ) : '';
		$current_dev    = isset( $_GET['is_dev'] ) ? sanitize_text_field( wp_unslash( $_GET['is_dev'] ) ) : '';
		$current_group  = isset( $_GET['group_id'] ) ? intval( $_GET['group_id'] ) : '';
		$groups         = TailSignal_DB::get_all_groups();

		echo '<div class="alignleft actions">';

		// Platform filter.
		echo '<select name="device_type">';
		echo '<option value="">' . esc_html__( 'All Platforms', 'tailsignal' ) . '</option>';
		echo '<option value="ios"' . selected( $current_type, 'ios', false ) . '>iOS</option>';
		echo '<option value="android"' . selected( $current_type, 'android', false ) . '>Android</option>';
		echo '</select>';

		// Dev filter.
		echo '<select name="is_dev">';
		echo '<option value="">' . esc_html__( 'All Devices', 'tailsignal' ) . '</option>';
		echo '<option value="1"' . selected( $current_dev, '1', false ) . '>' . esc_html__( 'Dev Only', 'tailsignal' ) . '</option>';
		echo '<option value="0"' . selected( $current_dev, '0', false ) . '>' . esc_html__( 'Non-Dev', 'tailsignal' ) . '</option>';
		echo '</select>';

		// Group filter.
		if ( ! empty( $groups ) ) {
			echo '<select name="group_id">';
			echo '<option value="">' . esc_html__( 'All Groups', 'tailsignal' ) . '</option>';
			foreach ( $groups as $group ) {
				echo '<option value="' . esc_attr( $group->id ) . '"' . selected( $current_group, $group->id, false ) . '>' . esc_html( $group->name ) . '</option>';
			}
			echo '</select>';
		}

		submit_button( __( 'Filter', 'tailsignal' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare items for display.
	 */
	public function prepare_items() {
		$per_page = 20;

		$args = array(
			'per_page'    => $per_page,
			'page'        => $this->get_pagenum(),
			'orderby'     => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
			'order'       => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
			'search'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
			'device_type' => isset( $_GET['device_type'] ) ? sanitize_text_field( wp_unslash( $_GET['device_type'] ) ) : '',
			'is_dev'      => isset( $_GET['is_dev'] ) && '' !== $_GET['is_dev'] ? sanitize_text_field( wp_unslash( $_GET['is_dev'] ) ) : '',
			'group_id'    => isset( $_GET['group_id'] ) && '' !== $_GET['group_id'] ? intval( $_GET['group_id'] ) : '',
		);

		$result = TailSignal_DB::get_devices( $args );

		$this->items = $result['items'];

		$this->set_pagination_args( array(
			'total_items' => $result['total'],
			'per_page'    => $per_page,
			'total_pages' => ceil( $result['total'] / $per_page ),
		) );
	}
}

class TailSignal_Admin_Devices {

	/**
	 * Render the devices page.
	 */
	public function render() {
		// Handle single delete action.
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['device_id'] ) ) {
			$device_id = intval( $_GET['device_id'] );
			check_admin_referer( 'tailsignal_delete_device_' . $device_id );
			TailSignal_DB::delete_device( $device_id );
			wp_safe_redirect( admin_url( 'admin.php?page=tailsignal-devices&deleted=1' ) );
			exit;
		}

		// Handle bulk actions.
		if ( isset( $_POST['action'] ) && 'delete' === $_POST['action'] && ! empty( $_POST['device_ids'] ) ) {
			check_admin_referer( 'bulk-devices' );
			$device_ids = array_map( 'intval', $_POST['device_ids'] );
			TailSignal_DB::bulk_delete_devices( $device_ids );
			wp_safe_redirect( admin_url( 'admin.php?page=tailsignal-devices&deleted=' . count( $device_ids ) ) );
			exit;
		}

		include TAILSIGNAL_PLUGIN_DIR . 'admin/partials/devices.php';
	}

	/**
	 * Handle AJAX update device.
	 */
	public function handle_update_device() {
		check_ajax_referer( 'tailsignal_nonce', 'nonce' );

		if ( ! current_user_can( 'tailsignal_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'tailsignal' ) ) );
		}

		$device_id  = isset( $_POST['device_id'] ) ? intval( $_POST['device_id'] ) : 0;
		$user_label = isset( $_POST['user_label'] ) ? sanitize_text_field( wp_unslash( $_POST['user_label'] ) ) : '';

		if ( ! $device_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid device ID.', 'tailsignal' ) ) );
		}

		$result = TailSignal_DB::update_device( $device_id, array( 'user_label' => $user_label ) );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Device updated.', 'tailsignal' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update device.', 'tailsignal' ) ) );
		}
	}

	/**
	 * Handle AJAX toggle dev flag.
	 */
	public function handle_toggle_dev() {
		check_ajax_referer( 'tailsignal_nonce', 'nonce' );

		if ( ! current_user_can( 'tailsignal_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'tailsignal' ) ) );
		}

		$device_id = isset( $_POST['device_id'] ) ? intval( $_POST['device_id'] ) : 0;
		$is_dev    = isset( $_POST['is_dev'] ) ? intval( $_POST['is_dev'] ) : 0;

		if ( ! $device_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid device ID.', 'tailsignal' ) ) );
		}

		$result = TailSignal_DB::update_device( $device_id, array( 'is_dev' => $is_dev ) );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => $is_dev
					? __( 'Device marked as dev.', 'tailsignal' )
					: __( 'Device removed from dev.', 'tailsignal' ),
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update device.', 'tailsignal' ) ) );
		}
	}
}
