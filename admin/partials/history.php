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
	<h1 class="tw-text-2xl tw-font-bold tw-mb-6"><?php esc_html_e( 'Notification History', 'tailsignal' ); ?></h1>

	<?php
	$table = new TailSignal_History_List_Table();
	$table->prepare_items();
	?>

	<form method="get">
		<input type="hidden" name="page" value="tailsignal-history" />
		<?php $table->display(); ?>
	</form>
</div>
