<?php
/**
 * Notification History admin page template.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tailsignal-app" class="wrap">
	<div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
		<h1 class="tw-text-2xl tw-font-bold"><?php esc_html_e( 'Notification History', 'tailsignal' ); ?></h1>
		<button type="button" id="tailsignal-delete-all-history" class="button tailsignal-btn-danger">
			<?php esc_html_e( 'Delete All History', 'tailsignal' ); ?>
		</button>
	</div>
</div>

<?php
$table = new TailSignal_History_List_Table();
$table->prepare_items();
?>
<div class="wrap" style="padding-top: 0;">
	<form method="get">
		<input type="hidden" name="page" value="tailsignal-history" />
		<?php $table->display(); ?>
	</form>
</div>
