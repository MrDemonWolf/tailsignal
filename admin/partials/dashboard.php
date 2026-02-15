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
	<div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
		<h1 class="tw-text-2xl tw-font-bold"><?php esc_html_e( 'TailSignal Dashboard', 'tailsignal' ); ?></h1>
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
					esc_html__( 'Dev Mode is ON — notifications only go to dev devices (%d).', 'tailsignal' ),
					$dev_count
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Stats Cards -->
	<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 tw-mb-8">
		<!-- Devices Card -->
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tailsignal-stat-card tailsignal-stat-card--blue">
			<div class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wide tw-mb-2"><?php esc_html_e( 'Devices', 'tailsignal' ); ?></div>
			<div class="tw-text-3xl tw-font-bold tw-text-gray-900"><?php echo esc_html( $device_count ); ?></div>
			<div class="tw-text-sm tw-text-gray-500 tw-mt-2">
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
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tailsignal-stat-card tailsignal-stat-card--green">
			<div class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wide tw-mb-2"><?php esc_html_e( 'Sent', 'tailsignal' ); ?></div>
			<div class="tw-text-3xl tw-font-bold tw-text-gray-900"><?php echo esc_html( $monthly_sent ); ?></div>
			<div class="tw-text-sm tw-text-gray-500 tw-mt-2"><?php esc_html_e( 'this month', 'tailsignal' ); ?></div>
		</div>

		<!-- Success Rate Card -->
		<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tailsignal-stat-card tailsignal-stat-card--purple">
			<div class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wide tw-mb-2"><?php esc_html_e( 'Success Rate', 'tailsignal' ); ?></div>
			<div class="tw-text-3xl tw-font-bold tw-text-gray-900"><?php echo esc_html( $success_rate ); ?>%</div>
			<div class="tw-text-sm tw-text-gray-500 tw-mt-2"><?php esc_html_e( 'delivery rate', 'tailsignal' ); ?></div>
		</div>
	</div>

	<!-- Monthly Trends Chart -->
	<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-mb-8 tailsignal-section">
		<div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100">
			<h2 class="tw-text-sm tw-font-semibold tw-m-0 tw-uppercase tw-tracking-wide tw-text-gray-500"><?php esc_html_e( 'Monthly Trends', 'tailsignal' ); ?></h2>
		</div>
		<div class="tw-p-6">
			<?php if ( ! empty( $chart_stats ) ) : ?>
				<canvas id="tailsignal-chart" height="280" style="max-height: 280px;"></canvas>
			<?php else : ?>
				<div class="tailsignal-empty-state">
					<div class="tailsignal-empty-state-icon">&#x1F4CA;</div>
					<p><?php esc_html_e( 'No notification data yet. Send your first notification to see trends here.', 'tailsignal' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Recent Notifications -->
	<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tailsignal-section">
		<div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100">
			<h2 class="tw-text-sm tw-font-semibold tw-m-0 tw-uppercase tw-tracking-wide tw-text-gray-500"><?php esc_html_e( 'Recent Notifications', 'tailsignal' ); ?></h2>
		</div>
		<?php if ( ! empty( $recent ) ) : ?>
			<table class="tw-w-full">
				<thead>
					<tr class="tw-border-b tw-border-gray-100 tw-bg-gray-50">
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Title', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Type', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Devices', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Status', 'tailsignal' ); ?></th>
						<th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Date', 'tailsignal' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent as $notification ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-900"><?php echo esc_html( wp_trim_words( $notification->title, 6, '...' ) ); ?></td>
							<td class="tw-px-6 tw-py-4">
								<?php
								$type_classes = array(
									'post'      => 'tw-bg-green-100 tw-text-green-800',
									'manual'    => 'tw-bg-blue-100 tw-text-blue-800',
									'scheduled' => 'tw-bg-purple-100 tw-text-purple-800',
								);
								$type_class = $type_classes[ $notification->type ] ?? 'tw-bg-gray-100 tw-text-gray-800';
								?>
								<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium <?php echo esc_attr( $type_class ); ?>">
									<?php echo esc_html( $notification->type ); ?>
								</span>
							</td>
							<td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-500"><?php echo esc_html( $notification->total_devices ); ?></td>
							<td class="tw-px-6 tw-py-4">
								<?php
								$status_map = array(
									'sent'             => array( 'tw-bg-green-100 tw-text-green-800', 'ok' ),
									'receipts_checked' => array( 'tw-bg-green-100 tw-text-green-800', 'ok' ),
									'pending'          => array( 'tw-bg-gray-100 tw-text-gray-800', 'pending' ),
									'scheduled'        => array( 'tw-bg-yellow-100 tw-text-yellow-800', 'scheduled' ),
									'failed'           => array( 'tw-bg-red-100 tw-text-red-800', 'failed' ),
									'cancelled'        => array( 'tw-bg-gray-100 tw-text-gray-500', 'cancelled' ),
								);
								$status_info = $status_map[ $notification->status ] ?? array( 'tw-bg-gray-100 tw-text-gray-800', $notification->status );
								?>
								<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium <?php echo esc_attr( $status_info[0] ); ?>">
									<?php echo esc_html( $status_info[1] ); ?>
								</span>
							</td>
							<td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-500">
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
