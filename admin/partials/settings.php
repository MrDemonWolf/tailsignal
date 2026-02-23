<?php
/**
 * Settings admin page template.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tailsignal-app" class="wrap">
	<!-- Page Header -->
	<div class="tailsignal-page-header">
		<h1>
			<span class="tailsignal-page-header-icon"><span class="dashicons dashicons-admin-generic"></span></span>
			<?php esc_html_e( 'Settings', 'tailsignal' ); ?>
		</h1>
		<p class="tailsignal-page-desc"><?php esc_html_e( 'Configure TailSignal behavior, templates, and integrations.', 'tailsignal' ); ?></p>
	</div>

	<form method="post" action="options.php">
		<?php settings_fields( 'tailsignal_settings' ); ?>

		<div class="tailsignal-settings-section">
			<?php do_settings_sections( 'tailsignal-settings' ); ?>
		</div>

		<?php submit_button( __( 'Save Settings', 'tailsignal' ), 'tailsignal-btn-brand' ); ?>
	</form>

	<!-- Data Management -->
	<div class="tw-mt-6">
		<div class="tailsignal-card">
			<div class="tailsignal-card-header">
				<h2><?php esc_html_e( 'Data Management', 'tailsignal' ); ?></h2>
			</div>
			<div class="tailsignal-card-body">
				<div class="tw-flex tw-gap-3">
					<a href="<?php echo esc_url( wp_nonce_url( rest_url( 'tailsignal/v1/devices/export' ), 'wp_rest', '_wpnonce' ) ); ?>" class="button">
						<?php esc_html_e( 'Export All Devices (CSV)', 'tailsignal' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=tailsignal-devices' ) ); ?>" class="button">
						<?php esc_html_e( 'Import Devices (CSV)', 'tailsignal' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
