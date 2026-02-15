<?php
/**
 * Devices admin page template.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tailsignal-app" class="wrap">
	<div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
		<h1 class="tw-text-2xl tw-font-bold"><?php esc_html_e( 'Devices', 'tailsignal' ); ?></h1>
		<div class="tw-flex tw-gap-2">
			<a href="<?php echo esc_url( wp_nonce_url( rest_url( 'tailsignal/v1/devices/export' ), 'wp_rest', '_wpnonce' ) ); ?>" class="button">
				<?php esc_html_e( 'Export CSV', 'tailsignal' ); ?>
			</a>
			<button type="button" class="button" id="tailsignal-import-btn">
				<?php esc_html_e( 'Import CSV', 'tailsignal' ); ?>
			</button>
		</div>
	</div>

	<!-- Summary Stats -->
	<div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4 tw-mb-6">
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tailsignal-stat-card tailsignal-stat-card--blue">
			<div class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wide tw-mb-1"><?php esc_html_e( 'Total Active', 'tailsignal' ); ?></div>
			<div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?php echo esc_html( $device_count ); ?></div>
		</div>
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tailsignal-stat-card tailsignal-stat-card--gray">
			<div class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wide tw-mb-1"><?php esc_html_e( 'iOS', 'tailsignal' ); ?></div>
			<div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?php echo esc_html( $platform_counts['ios'] ); ?></div>
		</div>
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tailsignal-stat-card tailsignal-stat-card--green">
			<div class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wide tw-mb-1"><?php esc_html_e( 'Android', 'tailsignal' ); ?></div>
			<div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?php echo esc_html( $platform_counts['android'] ); ?></div>
		</div>
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tailsignal-stat-card tailsignal-stat-card--yellow">
			<div class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wide tw-mb-1"><?php esc_html_e( 'Dev', 'tailsignal' ); ?></div>
			<div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?php echo esc_html( $dev_count ); ?></div>
		</div>
	</div>

	<!-- Import Form (hidden) -->
	<div id="tailsignal-import-form" class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6 tailsignal-section" style="display:none;">
		<form method="post" enctype="multipart/form-data" id="tailsignal-import-upload">
			<div class="tw-flex tw-items-center tw-gap-4">
				<input type="file" name="file" accept=".csv" required class="tw-text-sm" />
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Upload & Import', 'tailsignal' ); ?></button>
				<button type="button" class="button" id="tailsignal-import-cancel"><?php esc_html_e( 'Cancel', 'tailsignal' ); ?></button>
			</div>
			<span id="tailsignal-import-status" class="tw-text-sm tw-mt-2 tw-block"></span>
		</form>
	</div>

	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %d: number of devices deleted */
					esc_html( _n( '%d device deleted.', '%d devices deleted.', intval( $_GET['deleted'] ), 'tailsignal' ) ),
					intval( $_GET['deleted'] )
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Edit Label Dialog (hidden) -->
	<div id="tailsignal-edit-dialog" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50" style="display:none;">
		<div class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-p-6 tw-w-96">
			<h3 class="tw-text-lg tw-font-semibold tw-mb-4 tw-m-0"><?php esc_html_e( 'Edit Device Label', 'tailsignal' ); ?></h3>
			<input type="text" id="tailsignal-edit-label" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-mb-4" />
			<input type="hidden" id="tailsignal-edit-device-id" />
			<div class="tw-flex tw-justify-end tw-gap-2">
				<button type="button" class="button" id="tailsignal-edit-cancel"><?php esc_html_e( 'Cancel', 'tailsignal' ); ?></button>
				<button type="button" class="button button-primary" id="tailsignal-edit-save"><?php esc_html_e( 'Save', 'tailsignal' ); ?></button>
			</div>
		</div>
	</div>

</div>

<?php
$table = new TailSignal_Devices_List_Table();
$table->prepare_items();
?>
<div class="wrap" style="padding-top: 0;">
	<form method="get">
		<input type="hidden" name="page" value="tailsignal-devices" />
		<?php
		$table->search_box( __( 'Search Devices', 'tailsignal' ), 'tailsignal-search' );
		$table->display();
		?>
	</form>
</div>
