<?php
/**
 * Groups admin page template.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tailsignal-app" class="wrap">
	<div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
		<h1 class="tw-text-2xl tw-font-bold"><?php esc_html_e( 'Device Groups', 'tailsignal' ); ?></h1>
		<button type="button" class="button button-primary" id="tailsignal-create-group-btn">
			<?php esc_html_e( '+ Create Group', 'tailsignal' ); ?>
		</button>
	</div>

	<!-- Groups Table -->
	<?php if ( ! empty( $groups ) ) : ?>
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-mb-8 tailsignal-section">
			<table class="tw-w-full">
				<thead>
					<tr class="tw-border-b tw-border-gray-100 tw-bg-gray-50">
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Name', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Devices', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Description', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Actions', 'tailsignal' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $groups as $group ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-6 tw-py-4 tw-text-sm tw-font-medium tw-text-gray-900"><?php echo esc_html( $group->name ); ?></td>
							<td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-500"><?php echo esc_html( $group->device_count ); ?></td>
							<td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-500"><?php echo esc_html( $group->description ); ?></td>
							<td class="tw-px-6 tw-py-4">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=tailsignal-groups&edit=' . $group->id ) ); ?>" class="button button-small">
									<?php esc_html_e( 'Edit', 'tailsignal' ); ?>
								</a>
								<button type="button" class="button button-small tailsignal-delete-group" data-id="<?php echo esc_attr( $group->id ); ?>">
									<?php esc_html_e( 'Delete', 'tailsignal' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-mb-8">
			<div class="tailsignal-empty-state">
				<div class="tailsignal-empty-state-icon">&#x1F465;</div>
				<p><?php esc_html_e( 'No groups created yet. Create a group to organize your devices.', 'tailsignal' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<!-- Create/Edit Group Form -->
	<div id="tailsignal-group-form" class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tailsignal-section" <?php echo ( ! $editing_group && ! isset( $_GET['new'] ) ) ? 'style="display:none;"' : ''; ?>>
		<h2 class="tw-text-lg tw-font-semibold tw-mb-4 tw-m-0">
			<?php echo $editing_group ? esc_html__( 'Edit Group', 'tailsignal' ) : esc_html__( 'Create Group', 'tailsignal' ); ?>
		</h2>
		<form id="tailsignal-group-save-form">
			<input type="hidden" name="group_id" value="<?php echo $editing_group ? esc_attr( $editing_group->id ) : ''; ?>" />

			<div class="tw-space-y-4">
				<div>
					<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-group-name">
						<?php esc_html_e( 'Name', 'tailsignal' ); ?>
					</label>
					<input type="text" id="tailsignal-group-name" name="name" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" value="<?php echo $editing_group ? esc_attr( $editing_group->name ) : ''; ?>" required />
				</div>

				<div>
					<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-group-description">
						<?php esc_html_e( 'Description', 'tailsignal' ); ?>
					</label>
					<textarea id="tailsignal-group-description" name="description" rows="2" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm"><?php echo $editing_group ? esc_textarea( $editing_group->description ) : ''; ?></textarea>
				</div>

				<div>
					<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
						<?php esc_html_e( 'Assign Devices', 'tailsignal' ); ?>
					</label>
					<input type="text" id="tailsignal-group-device-search" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-mb-2" placeholder="<?php esc_attr_e( 'Search devices...', 'tailsignal' ); ?>" />
					<div class="tw-border tw-border-gray-200 tw-rounded-md tw-p-3 tw-max-h-48 tw-overflow-y-auto">
						<?php if ( ! empty( $devices['items'] ) ) : ?>
							<?php foreach ( $devices['items'] as $device ) : ?>
								<label class="tw-flex tw-items-center tw-gap-2 tw-py-1 tailsignal-device-option" data-label="<?php echo esc_attr( strtolower( $device->user_label . ' ' . $device->expo_token ) ); ?>">
									<input type="checkbox" name="device_ids[]" value="<?php echo esc_attr( $device->id ); ?>"
										<?php checked( in_array( (string) $device->id, $editing_devices, true ) || in_array( (int) $device->id, $editing_devices, true ) ); ?> />
									<span class="tw-text-sm">
										<?php
										echo esc_html(
											! empty( $device->user_label )
												? $device->user_label . ' (' . substr( $device->expo_token, 0, 20 ) . '...)'
												: $device->expo_token
										);
										?>
									</span>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<p class="tw-text-sm tw-text-gray-500 tw-m-0"><?php esc_html_e( 'No devices registered.', 'tailsignal' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="tw-flex tw-items-center tw-gap-3">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Group', 'tailsignal' ); ?></button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=tailsignal-groups' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'tailsignal' ); ?></a>
					<span id="tailsignal-group-status" class="tw-text-sm"></span>
				</div>
			</div>
		</form>
	</div>
</div>
