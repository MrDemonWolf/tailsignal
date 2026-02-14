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
	<h1 class="tw-text-2xl tw-font-bold tw-mb-6"><?php esc_html_e( 'TailSignal Settings', 'tailsignal' ); ?></h1>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'tailsignal_settings' );
		do_settings_sections( 'tailsignal-settings' );
		submit_button( __( 'Save Settings', 'tailsignal' ) );
		?>
	</form>

	<!-- Data Management -->
	<div class="tw-mt-8">
		<h2 class="tw-text-lg tw-font-semibold tw-mb-4"><?php esc_html_e( 'Data Management', 'tailsignal' ); ?></h2>
		<div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
			<div class="tw-flex tw-gap-3">
				<a href="<?php echo esc_url( rest_url( 'tailsignal/v1/devices/export' ) ); ?>&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" class="button">
					<?php esc_html_e( 'Export All Devices (CSV)', 'tailsignal' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tailsignal-devices' ) ); ?>" class="button">
					<?php esc_html_e( 'Import Devices (CSV)', 'tailsignal' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
