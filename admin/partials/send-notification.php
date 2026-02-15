<?php
/**
 * Send Notification admin page template.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tailsignal-app" class="wrap">
	<div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
		<h1 class="tw-text-2xl tw-font-bold"><?php esc_html_e( 'Send Notification', 'tailsignal' ); ?></h1>
		<?php if ( $dev_mode ) : ?>
			<span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">
				<?php esc_html_e( 'Dev Mode: ON', 'tailsignal' ); ?>
			</span>
		<?php endif; ?>
	</div>

	<?php if ( $dev_mode ) : ?>
		<div class="tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg tw-p-4 tw-mb-6">
			<p class="tw-text-yellow-800 tw-text-sm tw-m-0">
				<?php
				printf(
					/* translators: %d: dev device count */
					esc_html__( 'Dev Mode ON — "All devices" will only send to %d dev devices.', 'tailsignal' ),
					$dev_count
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Fill Test Data Banner -->
	<div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-lg tw-p-4 tw-mb-6 tw-flex tw-items-center tw-justify-between">
		<div class="tw-flex tw-items-center tw-gap-3">
			<span class="tw-text-blue-400 tw-text-lg">&#x1F9EA;</span>
			<p class="tw-text-blue-700 tw-text-sm tw-m-0"><?php esc_html_e( 'Testing? Pre-fill the form with sample data.', 'tailsignal' ); ?></p>
		</div>
		<button type="button" id="tailsignal-fill-test" class="button" style="border-color: #93c5fd; color: #1d4ed8; background: #eff6ff;">
			<?php esc_html_e( 'Fill Test Data', 'tailsignal' ); ?>
		</button>
	</div>

	<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
		<!-- Send Form (2/3 width) -->
		<div class="lg:tw-col-span-2">
			<form id="tailsignal-send-form">
				<!-- Content Section -->
				<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6 tailsignal-section">
					<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Content', 'tailsignal' ); ?></h3>
					<div class="tw-space-y-4">
						<!-- Title -->
						<div>
							<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-title">
								<?php esc_html_e( 'Title', 'tailsignal' ); ?>
							</label>
							<input type="text" id="tailsignal-title" name="title" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" required />
							<div class="tw-flex tw-items-center tw-justify-between tw-mt-1">
								<div class="tailsignal-placeholder-btns">
									<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-title" data-value="{site_name}">{site_name}</button>
									<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-title" data-value="{post_title}">{post_title}</button>
								</div>
								<span class="tw-text-xs tailsignal-char-count" data-target="tailsignal-title" data-limit="65">0 / 65</span>
							</div>
						</div>

						<!-- Body -->
						<div>
							<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-body">
								<?php esc_html_e( 'Body', 'tailsignal' ); ?>
							</label>
							<textarea id="tailsignal-body" name="body" rows="3" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" required></textarea>
							<div class="tw-flex tw-items-center tw-justify-between tw-mt-1">
								<div class="tailsignal-placeholder-btns">
									<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-body" data-value="{post_title}">{post_title}</button>
									<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-body" data-value="{post_excerpt}">{post_excerpt}</button>
									<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-body" data-value="{author_name}">{author_name}</button>
									<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-body" data-value="{category}">{category}</button>
								</div>
								<span class="tw-text-xs tailsignal-char-count" data-target="tailsignal-body" data-limit="178">0 / 178</span>
							</div>
						</div>

						<!-- Image URL -->
						<div>
							<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-image-url">
								<?php esc_html_e( 'Image URL (optional)', 'tailsignal' ); ?>
							</label>
							<input type="url" id="tailsignal-image-url" name="image_url" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" placeholder="https://example.com/image.jpg" />
							<p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-m-0"><?php esc_html_e( 'Shows as rich notification on iOS/Android.', 'tailsignal' ); ?></p>
						</div>
					</div>
				</div>

				<!-- Targeting Section -->
				<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6 tailsignal-section">
					<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Targeting', 'tailsignal' ); ?></h3>
					<div class="tw-space-y-2">
						<label class="tw-flex tw-items-center tw-gap-2">
							<input type="radio" name="target_type" value="all" checked class="tailsignal-target-radio" />
							<span class="tw-text-sm"><?php esc_html_e( 'All devices', 'tailsignal' ); ?></span>
						</label>
						<label class="tw-flex tw-items-center tw-gap-2">
							<input type="radio" name="target_type" value="dev" class="tailsignal-target-radio" />
							<span class="tw-text-sm"><?php esc_html_e( 'Dev devices only', 'tailsignal' ); ?></span>
						</label>
						<?php if ( ! empty( $groups ) ) : ?>
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="target_type" value="group" class="tailsignal-target-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Group:', 'tailsignal' ); ?></span>
								<select name="group_id" id="tailsignal-group-select" class="tw-rounded-md tw-border tw-border-gray-300 tw-px-2 tw-py-1 tw-text-sm" disabled>
									<?php foreach ( $groups as $group ) : ?>
										<option value="<?php echo esc_attr( $group->id ); ?>"><?php echo esc_html( $group->name ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
						<?php endif; ?>
						<label class="tw-flex tw-items-center tw-gap-2">
							<input type="radio" name="target_type" value="specific" class="tailsignal-target-radio" />
							<span class="tw-text-sm"><?php esc_html_e( 'Specific devices...', 'tailsignal' ); ?></span>
						</label>
					</div>

					<!-- Specific Devices Picker -->
					<div id="tailsignal-specific-devices" class="tw-mt-3 tw-border tw-border-gray-200 tw-rounded-md tw-p-3 tw-max-h-48 tw-overflow-y-auto" style="display:none;">
						<?php if ( ! empty( $devices['items'] ) ) : ?>
							<?php foreach ( $devices['items'] as $device ) : ?>
								<label class="tw-flex tw-items-center tw-gap-2 tw-py-1">
									<input type="checkbox" name="target_ids[]" value="<?php echo esc_attr( $device->id ); ?>" class="tailsignal-device-checkbox" />
									<span class="tw-text-sm">
										<?php
										echo esc_html(
											! empty( $device->user_label )
												? $device->user_label
												: substr( $device->expo_token, 0, 25 ) . '...'
										);
										?>
										<?php if ( $device->is_dev ) : ?>
											<span class="tw-text-xs tw-text-yellow-600">(DEV)</span>
										<?php endif; ?>
									</span>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<p class="tw-text-sm tw-text-gray-500 tw-m-0"><?php esc_html_e( 'No devices registered.', 'tailsignal' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Scheduling Section -->
				<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6 tailsignal-section">
					<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Scheduling', 'tailsignal' ); ?></h3>
					<div class="tw-flex tw-items-center tw-gap-4">
						<label class="tw-flex tw-items-center tw-gap-2">
							<input type="radio" name="send_when" value="now" checked class="tailsignal-when-radio" />
							<span class="tw-text-sm"><?php esc_html_e( 'Now', 'tailsignal' ); ?></span>
						</label>
						<label class="tw-flex tw-items-center tw-gap-2">
							<input type="radio" name="send_when" value="schedule" class="tailsignal-when-radio" />
							<span class="tw-text-sm"><?php esc_html_e( 'Schedule:', 'tailsignal' ); ?></span>
						</label>
						<input type="datetime-local" name="scheduled_at" id="tailsignal-schedule-datetime" class="tw-rounded-md tw-border tw-border-gray-300 tw-px-2 tw-py-1 tw-text-sm" disabled />
					</div>
				</div>

				<!-- Advanced Section -->
				<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6 tailsignal-section">
					<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Advanced', 'tailsignal' ); ?></h3>
					<div>
						<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-data">
							<?php esc_html_e( 'Custom Data (JSON, optional)', 'tailsignal' ); ?>
						</label>
						<textarea id="tailsignal-data" name="data" rows="2" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-font-mono" placeholder='{ "screen": "home" }'></textarea>
					</div>
				</div>

				<!-- Submit -->
				<div class="tw-flex tw-items-center tw-gap-3">
					<button type="submit" id="tailsignal-send-btn" class="button button-primary">
						<?php esc_html_e( 'Signal the Pack', 'tailsignal' ); ?>
					</button>
					<span id="tailsignal-send-status" class="tw-text-sm"></span>
				</div>
			</form>
		</div>

		<!-- Live Preview (1/3 width) -->
		<div class="lg:tw-col-span-1">
			<div class="tw-sticky tw-top-8">
				<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tailsignal-section">
					<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Preview', 'tailsignal' ); ?></h3>
					<!-- iOS-style notification mockup -->
					<div class="tailsignal-preview-card">
						<div class="tailsignal-preview-header">
							<span class="tailsignal-preview-icon">&#x1F43E;</span>
							<span class="tailsignal-preview-app"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
							<span class="tailsignal-preview-time"><?php esc_html_e( 'now', 'tailsignal' ); ?></span>
						</div>
						<div class="tailsignal-preview-body">
							<div class="tailsignal-preview-text">
								<div id="tailsignal-preview-title" class="tailsignal-preview-title"><?php esc_html_e( 'Notification Title', 'tailsignal' ); ?></div>
								<div id="tailsignal-preview-body" class="tailsignal-preview-body-text"><?php esc_html_e( 'Notification body text will appear here...', 'tailsignal' ); ?></div>
							</div>
							<div id="tailsignal-preview-image" class="tailsignal-preview-image" style="display:none;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Scheduled Notifications -->
	<?php if ( ! empty( $scheduled ) ) : ?>
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-mt-6 tailsignal-section">
			<div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100">
				<h2 class="tw-text-sm tw-font-semibold tw-m-0 tw-uppercase tw-tracking-wide tw-text-gray-500"><?php esc_html_e( 'Scheduled Notifications', 'tailsignal' ); ?></h2>
			</div>
			<table class="tw-w-full">
				<thead>
					<tr class="tw-border-b tw-border-gray-100 tw-bg-gray-50">
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Title', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Target', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Scheduled For', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Actions', 'tailsignal' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $scheduled as $item ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-6 tw-py-4 tw-text-sm"><?php echo esc_html( $item->title ); ?></td>
							<td class="tw-px-6 tw-py-4 tw-text-sm"><?php echo esc_html( ucfirst( $item->target_type ) ); ?></td>
							<td class="tw-px-6 tw-py-4 tw-text-sm">
								<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item->scheduled_at ) ) ); ?>
							</td>
							<td class="tw-px-6 tw-py-4">
								<button type="button" class="button button-small tailsignal-cancel-scheduled" data-id="<?php echo esc_attr( $item->id ); ?>">
									<?php esc_html_e( 'Cancel', 'tailsignal' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
