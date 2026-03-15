<?php
/**
 * Dashboard admin page template.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tailsignal-app" class="wrap">
	<!-- Page Header -->
	<div class="tailsignal-page-header tw-flex tw-items-start tw-justify-between">
		<div>
			<h1>
				<span class="tailsignal-page-header-icon"><span class="dashicons dashicons-chart-area"></span></span>
				<?php esc_html_e( 'Dashboard', 'tailsignal' ); ?>
			</h1>
			<p class="tailsignal-page-desc"><?php esc_html_e( 'Overview of your push notification activity.', 'tailsignal' ); ?></p>
		</div>
		<?php if ( $dev_mode ) : ?>
			<span class="tailsignal-dev-pill"><?php esc_html_e( 'Dev Mode: ON', 'tailsignal' ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( $dev_mode ) : ?>
		<div class="tailsignal-dev-banner tw-mb-6">
			<span class="tailsignal-dev-banner-icon">&#x26A0;&#xFE0F;</span>
			<p>
				<?php
				printf(
					/* translators: %d: dev device count */
					esc_html__( 'Dev Mode is ON — notifications only go to dev devices (%d).', 'tailsignal' ),
					$dev_count
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Stats Cards -->
	<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 tw-mb-6">
		<!-- Devices Card -->
		<div class="tailsignal-stat-card tailsignal-stat-card--brand">
			<div class="tailsignal-stat-icon tailsignal-stat-icon--brand"><span class="dashicons dashicons-smartphone"></span></div>
			<div class="tailsignal-stat-label"><?php esc_html_e( 'Devices', 'tailsignal' ); ?></div>
			<div class="tailsignal-stat-value"><?php echo esc_html( $device_count ); ?></div>
			<div class="tailsignal-stat-detail">
				<?php
				printf(
					/* translators: 1: iOS count, 2: Android count */
					esc_html__( '%1$d iOS, %2$d Android', 'tailsignal' ),
					$platform_counts['ios'],
					$platform_counts['android']
				);
				?>
			</div>
		</div>

		<!-- Sent Card -->
		<div class="tailsignal-stat-card tailsignal-stat-card--green">
			<div class="tailsignal-stat-icon tailsignal-stat-icon--green"><span class="dashicons dashicons-email-alt"></span></div>
			<div class="tailsignal-stat-label"><?php esc_html_e( 'Sent This Month', 'tailsignal' ); ?></div>
			<div class="tailsignal-stat-value"><?php echo esc_html( $monthly_sent ); ?></div>
			<div class="tailsignal-stat-detail"><?php esc_html_e( 'notifications delivered', 'tailsignal' ); ?></div>
		</div>

		<!-- Success Rate Card -->
		<div class="tailsignal-stat-card tailsignal-stat-card--purple">
			<div class="tailsignal-stat-icon tailsignal-stat-icon--purple"><span class="dashicons dashicons-yes-alt"></span></div>
			<div class="tailsignal-stat-label"><?php esc_html_e( 'Success Rate', 'tailsignal' ); ?></div>
			<div class="tailsignal-stat-value"><?php echo esc_html( $success_rate ); ?>%</div>
			<div class="tailsignal-stat-detail"><?php esc_html_e( 'delivery rate', 'tailsignal' ); ?></div>
		</div>
	</div>

	<!-- Monthly Trends Chart -->
	<div class="tailsignal-card tw-mb-6">
		<div class="tailsignal-card-header">
			<h2><?php esc_html_e( 'Monthly Trends', 'tailsignal' ); ?></h2>
		</div>
		<div class="tailsignal-card-body" style="<?php echo empty( $chart_stats ) ? 'padding: 24px 20px;' : ''; ?>">
			<?php if ( ! empty( $chart_stats ) ) : ?>
				<canvas id="tailsignal-chart" height="280" style="max-height: 280px;" aria-label="<?php esc_attr_e( 'Monthly notification trends chart', 'tailsignal' ); ?>" role="img"></canvas>
			<?php else : ?>
				<div style="text-align: center; color: var(--ts-text-muted);">
					<span style="font-size: 24px; opacity: 0.4;">&#x1F4CA;</span>
					<p style="margin: 8px 0 0; font-size: 13px;"><?php esc_html_e( 'No notification data yet. Send your first notification to see trends here.', 'tailsignal' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Recent Notifications -->
	<div class="tailsignal-card">
		<div class="tailsignal-card-header">
			<h2><?php esc_html_e( 'Recent Notifications', 'tailsignal' ); ?></h2>
			<?php if ( ! empty( $recent ) ) : ?>
				<button type="button" id="tailsignal-clear-recent" class="button button-small tailsignal-btn-danger">
					<?php esc_html_e( 'Clear All', 'tailsignal' ); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $recent ) ) : ?>
			<table class="tw-w-full">
				<thead>
					<tr>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Title', 'tailsignal' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Type', 'tailsignal' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Devices', 'tailsignal' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Status', 'tailsignal' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Date', 'tailsignal' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent as $notification ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-900 tw-font-medium"><?php echo esc_html( wp_trim_words( $notification->title, 6, '...' ) ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<?php
								$type_badges = array(
									'post'      => 'tailsignal-badge-green',
									'manual'    => 'tailsignal-badge-blue',
									'scheduled' => 'tailsignal-badge-purple',
								);
								$badge_class = $type_badges[ $notification->type ] ?? 'tailsignal-badge-gray';
								?>
								<span class="tailsignal-badge <?php echo esc_attr( $badge_class ); ?>">
									<?php echo esc_html( $notification->type ); ?>
								</span>
							</td>
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-500 tw-tabular-nums"><?php echo esc_html( $notification->total_devices ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<?php
								$status_badges = array(
									'sent'             => array( 'tailsignal-badge-green', __( 'ok', 'tailsignal' ) ),
									'receipts_checked' => array( 'tailsignal-badge-green', __( 'ok', 'tailsignal' ) ),
									'pending'          => array( 'tailsignal-badge-gray', __( 'pending', 'tailsignal' ) ),
									'scheduled'        => array( 'tailsignal-badge-yellow', __( 'scheduled', 'tailsignal' ) ),
									'failed'           => array( 'tailsignal-badge-red', __( 'failed', 'tailsignal' ) ),
									'cancelled'        => array( 'tailsignal-badge-gray-muted', __( 'cancelled', 'tailsignal' ) ),
								);
								$status_info = $status_badges[ $notification->status ] ?? array( 'tailsignal-badge-gray', $notification->status );
								?>
								<span class="tailsignal-badge <?php echo esc_attr( $status_info[0] ); ?>">
									<?php echo esc_html( $status_info[1] ); ?>
								</span>
							</td>
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-500">
								<?php echo esc_html( human_time_diff( strtotime( $notification->created_at ), time() ) ); ?>
								<?php esc_html_e( 'ago', 'tailsignal' ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="tailsignal-empty-state">
				<div class="tailsignal-empty-state-icon">&#x1F514;</div>
				<p><?php esc_html_e( 'No notifications sent yet. Your recent sends will appear here.', 'tailsignal' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
