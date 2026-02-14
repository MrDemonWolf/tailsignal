<?php
/**
 * Post editor meta box template.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tailsignal-meta-box">
	<?php if ( ! $notified ) : ?>
		<p>
			<label>
				<input type="checkbox" name="tailsignal_notify" value="1" <?php checked( '1', $notify ); ?> />
				<?php esc_html_e( 'Send notification on publish', 'tailsignal' ); ?>
			</label>
		</p>
	<?php else : ?>
		<p class="description">
			<?php esc_html_e( 'Auto-notification already sent for this post.', 'tailsignal' ); ?>
		</p>
	<?php endif; ?>

	<p>
		<label>
			<input type="checkbox" name="tailsignal_include_image" value="1" <?php checked( '1', $include_img ); ?> />
			<?php esc_html_e( 'Include featured image', 'tailsignal' ); ?>
		</label>
	</p>

	<hr />

	<h4 style="margin-bottom: 8px;"><?php esc_html_e( 'Quick Send', 'tailsignal' ); ?></h4>

	<p>
		<label><?php esc_html_e( 'Title:', 'tailsignal' ); ?></label>
		<input type="text" name="tailsignal_custom_title" class="widefat tailsignal-quick-title"
			value="<?php echo esc_attr( ! empty( $custom_title ) ? $custom_title : get_option( 'tailsignal_default_title', 'New from {site_name}' ) ); ?>" />
	</p>

	<p>
		<label><?php esc_html_e( 'Body:', 'tailsignal' ); ?></label>
		<input type="text" name="tailsignal_custom_body" class="widefat tailsignal-quick-body"
			value="<?php echo esc_attr( ! empty( $custom_body ) ? $custom_body : get_option( 'tailsignal_default_body', '{post_title}' ) ); ?>" />
	</p>

	<p>
		<label><?php esc_html_e( 'Send To:', 'tailsignal' ); ?></label><br />
		<label style="display:block; margin: 2px 0;">
			<input type="radio" name="tailsignal_quick_target" value="all" checked />
			<?php esc_html_e( 'All devices', 'tailsignal' ); ?>
		</label>
		<label style="display:block; margin: 2px 0;">
			<input type="radio" name="tailsignal_quick_target" value="dev" />
			<?php esc_html_e( 'Dev devices only', 'tailsignal' ); ?>
		</label>
		<?php if ( ! empty( $groups ) ) : ?>
			<label style="display:block; margin: 2px 0;">
				<input type="radio" name="tailsignal_quick_target" value="group" />
				<?php esc_html_e( 'Group:', 'tailsignal' ); ?>
				<select name="tailsignal_quick_group_id" class="tailsignal-quick-group-select">
					<?php foreach ( $groups as $group ) : ?>
						<option value="<?php echo esc_attr( $group->id ); ?>"><?php echo esc_html( $group->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php endif; ?>
	</p>

	<?php if ( $dev_mode ) : ?>
		<p class="description" style="color: #b26200;">
			<?php esc_html_e( 'Dev Mode is ON — "All devices" will only send to dev devices.', 'tailsignal' ); ?>
		</p>
	<?php endif; ?>

	<p>
		<button type="button" class="button button-primary tailsignal-quick-send-btn" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
			<?php esc_html_e( 'Send Now', 'tailsignal' ); ?>
		</button>
		<span class="tailsignal-quick-send-status" style="margin-left: 8px;"></span>
	</p>

	<?php if ( ! empty( $history ) ) : ?>
		<hr />
		<h4 style="margin-bottom: 4px;"><?php esc_html_e( 'Send History', 'tailsignal' ); ?></h4>
		<ul style="margin: 0; padding: 0; list-style: none;">
			<?php foreach ( $history as $entry ) : ?>
				<li style="font-size: 12px; color: #666; padding: 2px 0;">
					<?php
					echo esc_html(
						date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->history_created_at ) )
					);
					?>
					&rarr;
					<?php
					printf(
						/* translators: %d: device count */
						esc_html__( '%d devices', 'tailsignal' ),
						$entry->total_devices
					);
					?>
					<?php if ( in_array( $entry->status, array( 'sent', 'receipts_checked' ), true ) ) : ?>
						<span style="color: green;">&#10003;</span>
					<?php else : ?>
						<span style="color: red;">&#10007;</span>
					<?php endif; ?>
					(<?php echo esc_html( $entry->type ); ?>)
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
