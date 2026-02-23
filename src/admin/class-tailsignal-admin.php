<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_Admin {

	/**
	 * Register the admin menu pages.
	 */
	public function add_menu_pages() {
		// Main menu.
		add_menu_page(
			__( 'TailSignal', 'tailsignal' ),
			__( 'TailSignal', 'tailsignal' ),
			'tailsignal_manage',
			'tailsignal',
			array( $this, 'render_dashboard_page' ),
			'dashicons-bell',
			30
		);

		// Dashboard (same as main).
		add_submenu_page(
			'tailsignal',
			__( 'Dashboard', 'tailsignal' ),
			__( 'Dashboard', 'tailsignal' ),
			'tailsignal_manage',
			'tailsignal',
			array( $this, 'render_dashboard_page' )
		);

		// Send Notification.
		add_submenu_page(
			'tailsignal',
			__( 'Send Notification', 'tailsignal' ),
			__( 'Send', 'tailsignal' ),
			'tailsignal_manage',
			'tailsignal-send',
			array( $this, 'render_send_page' )
		);

		// Devices.
		add_submenu_page(
			'tailsignal',
			__( 'Devices', 'tailsignal' ),
			__( 'Devices', 'tailsignal' ),
			'tailsignal_manage',
			'tailsignal-devices',
			array( $this, 'render_devices_page' )
		);

		// Groups.
		add_submenu_page(
			'tailsignal',
			__( 'Groups', 'tailsignal' ),
			__( 'Groups', 'tailsignal' ),
			'tailsignal_manage',
			'tailsignal-groups',
			array( $this, 'render_groups_page' )
		);

		// History.
		add_submenu_page(
			'tailsignal',
			__( 'History', 'tailsignal' ),
			__( 'History', 'tailsignal' ),
			'tailsignal_manage',
			'tailsignal-history',
			array( $this, 'render_history_page' )
		);

		// Settings.
		add_submenu_page(
			'tailsignal',
			__( 'Settings', 'tailsignal' ),
			__( 'Settings', 'tailsignal' ),
			'tailsignal_manage',
			'tailsignal-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_styles( $hook ) {
		if ( ! $this->is_tailsignal_page( $hook ) ) {
			return;
		}

		wp_enqueue_style(
			'tailsignal-tailwind',
			TAILSIGNAL_PLUGIN_URL . 'admin/css/tailsignal-tailwind.css',
			array(),
			TAILSIGNAL_VERSION,
			'all'
		);

		wp_enqueue_style(
			'tailsignal-admin',
			TAILSIGNAL_PLUGIN_URL . 'admin/css/tailsignal-admin.css',
			array( 'tailsignal-tailwind' ),
			TAILSIGNAL_VERSION,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Enqueue on TailSignal pages and post editor.
		$is_tailsignal = $this->is_tailsignal_page( $hook );
		$is_post_edit  = in_array( $hook, array( 'post.php', 'post-new.php' ), true );

		if ( ! $is_tailsignal && ! $is_post_edit ) {
			return;
		}

		wp_enqueue_script(
			'tailsignal-admin',
			TAILSIGNAL_PLUGIN_URL . 'admin/js/tailsignal-admin.js',
			array( 'jquery' ),
			TAILSIGNAL_VERSION,
			true
		);
		wp_script_add_data( 'tailsignal-admin', 'strategy', 'defer' );

		wp_localize_script( 'tailsignal-admin', 'tailsignal', array(
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'rest_url'  => rest_url( 'tailsignal/v1/' ),
			'nonce'     => wp_create_nonce( 'tailsignal_nonce' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'strings'   => array(
				'choose_image'    => __( 'Choose Image', 'tailsignal' ),
				'use_image'       => __( 'Use this image', 'tailsignal' ),
				'confirm_delete'  => __( 'Are you sure you want to delete this?', 'tailsignal' ),
				'sending'         => __( 'Sending...', 'tailsignal' ),
				'sent'            => __( 'Sent!', 'tailsignal' ),
				'error'           => __( 'An error occurred.', 'tailsignal' ),
				'scheduled'       => __( 'Scheduled!', 'tailsignal' ),
				'cancelled'          => __( 'Cancelled.', 'tailsignal' ),
				'confirm_delete_all' => __( 'Are you sure you want to delete ALL notification history? This cannot be undone.', 'tailsignal' ),
				'deleting'           => __( 'Deleting...', 'tailsignal' ),
				'delete_all_history' => __( 'Delete All History', 'tailsignal' ),
			),
		) );

		// Load WP Media Library on Send page and post editor.
		if ( 'tailsignal_page_tailsignal-send' === $hook || $is_post_edit ) {
			wp_enqueue_media();
		}

		// Load Chart.js on dashboard page only.
		if ( 'toplevel_page_tailsignal' === $hook ) {
			wp_enqueue_script(
				'chartjs',
				TAILSIGNAL_PLUGIN_URL . 'admin/js/vendor/chart.min.js',
				array(),
				'4.4.7',
				true
			);
			wp_script_add_data( 'chartjs', 'strategy', 'defer' );
		}
	}

	/**
	 * Check if the current page is a TailSignal admin page.
	 *
	 * @param string $hook The current admin page hook.
	 * @return bool
	 */
	private function is_tailsignal_page( $hook ) {
		$pages = array(
			'toplevel_page_tailsignal',
			'tailsignal_page_tailsignal-send',
			'tailsignal_page_tailsignal-devices',
			'tailsignal_page_tailsignal-groups',
			'tailsignal_page_tailsignal-history',
			'tailsignal_page_tailsignal-settings',
		);

		return in_array( $hook, $pages, true );
	}

	/**
	 * Render the dashboard page.
	 */
	public function render_dashboard_page() {
		$dashboard = new TailSignal_Admin_Dashboard();
		$dashboard->render();
	}

	/**
	 * Render the send notification page.
	 */
	public function render_send_page() {
		$send = new TailSignal_Admin_Send();
		$send->render();
	}

	/**
	 * Render the devices page.
	 */
	public function render_devices_page() {
		$devices = new TailSignal_Admin_Devices();
		$devices->render();
	}

	/**
	 * Render the groups page.
	 */
	public function render_groups_page() {
		$groups = new TailSignal_Admin_Groups();
		$groups->render();
	}

	/**
	 * Render the history page.
	 */
	public function render_history_page() {
		$history = new TailSignal_Admin_History();
		$history->render();
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		include TAILSIGNAL_PLUGIN_DIR . 'admin/partials/settings.php';
	}
}
