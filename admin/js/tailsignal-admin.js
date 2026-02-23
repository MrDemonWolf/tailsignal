/**
 * TailSignal Admin JavaScript
 *
 * Handles AJAX operations for send, device management, groups, and meta box.
 */
(function($) {
	'use strict';

	// ── Send Notification Page ──────────────────────────────────

	// Target type radio toggle
	$(document).on('change', '.tailsignal-target-radio', function() {
		var value = $(this).val();
		$('#tailsignal-specific-devices').toggle(value === 'specific');
		$('#tailsignal-group-select').prop('disabled', value !== 'group');
	});

	// Schedule radio toggle
	$(document).on('change', '.tailsignal-when-radio', function() {
		var isSchedule = $(this).val() === 'schedule';
		$('#tailsignal-schedule-datetime').prop('disabled', !isSchedule);
		$('#tailsignal-send-btn').text(isSchedule ?
			tailsignal.strings.scheduled || 'Schedule' :
			'Signal the Pack'
		);
	});

	// Send notification form
	$(document).on('submit', '#tailsignal-send-form', function(e) {
		e.preventDefault();

		var $btn = $('#tailsignal-send-btn');
		var $status = $('#tailsignal-send-status');

		$btn.prop('disabled', true).text(tailsignal.strings.sending);
		$status.text('').removeClass('tw-text-green-600 tw-text-red-600');

		var data = {
			action: 'tailsignal_send_notification',
			nonce: tailsignal.nonce,
			title: $('[name="title"]', this).val(),
			body: $('[name="body"]', this).val(),
			image_url: $('[name="image_url"]', this).val(),
			data: $('[name="data"]', this).val(),
			target_type: $('[name="target_type"]:checked', this).val()
		};

		// Target IDs
		if (data.target_type === 'group') {
			data.target_ids = [$('#tailsignal-group-select').val()];
		} else if (data.target_type === 'specific') {
			data.target_ids = [];
			$('.tailsignal-device-checkbox:checked').each(function() {
				data.target_ids.push($(this).val());
			});
		}

		// Schedule
		var sendWhen = $('[name="send_when"]:checked').val();
		if (sendWhen === 'schedule') {
			data.scheduled_at = $('#tailsignal-schedule-datetime').val();
		}

		$.post(tailsignal.ajax_url, data, function(response) {
			if (response.success) {
				$status.text(response.data.message).addClass('tw-text-green-600');
				if (sendWhen !== 'schedule') {
					$btn.text(tailsignal.strings.sent);
				} else {
					$btn.text(tailsignal.strings.scheduled);
					setTimeout(function() { location.reload(); }, 1500);
				}
			} else {
				$status.text(response.data.message).addClass('tw-text-red-600');
				$btn.text('Signal the Pack');
			}
			$btn.prop('disabled', false);
		}).fail(function() {
			$status.text(tailsignal.strings.error).addClass('tw-text-red-600');
			$btn.prop('disabled', false).text('Signal the Pack');
		});
	});

	// Cancel scheduled notification
	$(document).on('click', '.tailsignal-cancel-scheduled', function() {
		if (!confirm(tailsignal.strings.confirm_delete)) return;

		var $btn = $(this);
		var id = $btn.data('id');

		$.post(tailsignal.ajax_url, {
			action: 'tailsignal_cancel_scheduled',
			nonce: tailsignal.nonce,
			notification_id: id
		}, function(response) {
			if (response.success) {
				$btn.closest('tr').fadeOut(function() { $(this).remove(); });
			} else {
				alert(response.data.message);
			}
		});
	});

	// Placeholder quick-fill buttons
	$(document).on('click', '.tailsignal-placeholder-btn', function() {
		var targetId = $(this).data('target');
		var value = $(this).data('value');
		var $field = $('#' + targetId);
		var el = $field[0];

		if (el && el.setSelectionRange) {
			var start = el.selectionStart;
			var end = el.selectionEnd;
			var text = $field.val();
			$field.val(text.substring(0, start) + value + text.substring(end));
			el.selectionStart = el.selectionEnd = start + value.length;
			$field.trigger('input');
		} else {
			$field.val($field.val() + value);
			$field.trigger('input');
		}
		$field.focus();
	});

	// Character counters
	function updateCharCount($field) {
		var $counter = $('.tailsignal-char-count[data-target="' + $field.attr('id') + '"]');
		if (!$counter.length) return;
		var len = $field.val().length;
		var limit = parseInt($counter.data('limit'), 10);
		$counter.text(len + ' / ' + limit);
		$counter.toggleClass('tailsignal-char-over', len > limit);
	}

	$(document).on('input', '#tailsignal-title, #tailsignal-body', function() {
		updateCharCount($(this));
	});

	// Live preview update
	$(document).on('input', '#tailsignal-title, #tailsignal-body, #tailsignal-image-url', function() {
		var title = $('#tailsignal-title').val();
		var body = $('#tailsignal-body').val();
		var imageUrl = $('#tailsignal-image-url').val();

		$('#tailsignal-preview-title').text(title || 'Notification Title');
		$('#tailsignal-preview-body').text(body || 'Notification body text will appear here...');

		var $img = $('#tailsignal-preview-image');
		if (imageUrl && /^https?:\/\/.+/i.test(imageUrl)) {
			$img.css('background-image', 'url("' + imageUrl.replace(/["()]/g, '') + '")').show();
		} else {
			$img.hide().css('background-image', '');
		}
	});

	// Fill Test Data button
	$(document).on('click', '#tailsignal-fill-test', function() {
		$('#tailsignal-title').val('Test Notification from TailSignal').trigger('input');
		$('#tailsignal-body').val('This is a test push notification. If you received this, TailSignal is working correctly!').trigger('input');
		$('#tailsignal-image-url').val('https://placehold.co/1200x630/0FACED/white?text=TailSignal+Test').trigger('input');
		$('input[name="target_type"][value="dev"]').prop('checked', true).trigger('change');
	});

	// WordPress Media Library picker
	$(document).on('click', '#tailsignal-choose-image', function(e) {
		e.preventDefault();

		if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
			return;
		}

		var frame = wp.media({
			title: tailsignal.strings.choose_image || 'Choose Image',
			button: { text: tailsignal.strings.use_image || 'Use this image' },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			$('#tailsignal-image-url').val(attachment.url).trigger('input');
		});

		frame.open();
	});

	// ── History Page ───────────────────────────────────────────

	// Delete All History
	$(document).on('click', '#tailsignal-delete-all-history', function() {
		if (!confirm(tailsignal.strings.confirm_delete_all)) return;

		var $btn = $(this);
		$btn.prop('disabled', true).text(tailsignal.strings.deleting);

		$.post(tailsignal.ajax_url, {
			action: 'tailsignal_delete_all_notifications',
			nonce: tailsignal.nonce
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data.message);
				$btn.prop('disabled', false).text(tailsignal.strings.delete_all_history);
			}
		}).fail(function() {
			alert(tailsignal.strings.error);
			$btn.prop('disabled', false).text(tailsignal.strings.delete_all_history);
		});
	});

	// ── Dashboard Clear All Recent ────────────────────────────

	$(document).on('click', '#tailsignal-clear-recent', function() {
		if (!confirm(tailsignal.strings.confirm_delete_all)) return;

		var $btn = $(this);
		$btn.prop('disabled', true);

		$.post(tailsignal.ajax_url, {
			action: 'tailsignal_delete_all_notifications',
			nonce: tailsignal.nonce
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data.message);
				$btn.prop('disabled', false);
			}
		}).fail(function() {
			alert(tailsignal.strings.error);
			$btn.prop('disabled', false);
		});
	});

	// ── Dashboard Chart ────────────────────────────────────────

	if (typeof Chart !== 'undefined' && typeof tailsignal_chart !== 'undefined' && tailsignal_chart.labels.length) {
		var ctx = document.getElementById('tailsignal-chart');
		if (ctx) {
			// Format month labels (2025-01 → Jan)
			var monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
			var formattedLabels = tailsignal_chart.labels.map(function(label) {
				var parts = label.split('-');
				return monthNames[parseInt(parts[1], 10) - 1] || label;
			});

			new Chart(ctx, {
				type: 'bar',
				data: {
					labels: formattedLabels,
					datasets: [
						{
							label: 'Successful',
							data: tailsignal_chart.success,
							backgroundColor: 'rgba(34, 197, 94, 0.8)',
							hoverBackgroundColor: '#22c55e',
							borderRadius: 4,
							borderSkipped: false
						},
						{
							label: 'Failed',
							data: tailsignal_chart.failed,
							backgroundColor: 'rgba(239, 68, 68, 0.8)',
							hoverBackgroundColor: '#ef4444',
							borderRadius: 4,
							borderSkipped: false
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					interaction: {
						intersect: false,
						mode: 'index'
					},
					plugins: {
						legend: {
							position: 'bottom',
							labels: {
								boxWidth: 10,
								boxHeight: 10,
								padding: 20,
								font: { size: 12, weight: '500' },
								usePointStyle: true,
								pointStyle: 'rectRounded'
							}
						},
						tooltip: {
							backgroundColor: '#1e293b',
							titleFont: { size: 13, weight: '600' },
							bodyFont: { size: 12 },
							padding: 10,
							cornerRadius: 6,
							displayColors: true,
							boxPadding: 4
						}
					},
					scales: {
						x: {
							stacked: true,
							grid: { display: false },
							border: { display: false },
							ticks: {
								font: { size: 11, weight: '500' },
								color: '#94a3b8'
							}
						},
						y: {
							stacked: true,
							beginAtZero: true,
							grid: {
								color: 'rgba(0, 0, 0, 0.04)',
								drawBorder: false
							},
							border: { display: false },
							ticks: {
								stepSize: 1,
								font: { size: 11 },
								color: '#94a3b8',
								padding: 8
							}
						}
					}
				}
			});
		}
	}

	// ── Devices Page ────────────────────────────────────────────

	// Edit device label
	$(document).on('click', '.tailsignal-edit-device', function(e) {
		e.preventDefault();
		var id = $(this).data('id');
		var label = $(this).data('label') || '';
		$('#tailsignal-edit-device-id').val(id);
		$('#tailsignal-edit-label').val(label);
		$('#tailsignal-edit-dialog').show();
	});

	$('#tailsignal-edit-cancel').on('click', function() {
		$('#tailsignal-edit-dialog').hide();
	});

	$('#tailsignal-edit-save').on('click', function() {
		var id = $('#tailsignal-edit-device-id').val();
		var label = $('#tailsignal-edit-label').val();

		$.post(tailsignal.ajax_url, {
			action: 'tailsignal_update_device',
			nonce: tailsignal.nonce,
			device_id: id,
			user_label: label
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data.message);
			}
		});
	});

	// Toggle dev flag
	$(document).on('click', '.tailsignal-toggle-dev', function(e) {
		e.preventDefault();
		var id = $(this).data('id');
		var isDev = $(this).data('dev');

		$.post(tailsignal.ajax_url, {
			action: 'tailsignal_toggle_dev',
			nonce: tailsignal.nonce,
			device_id: id,
			is_dev: isDev
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data.message);
			}
		});
	});

	// Import CSV toggle
	$('#tailsignal-import-btn').on('click', function() {
		$('#tailsignal-import-form').toggle();
	});

	$('#tailsignal-import-cancel').on('click', function() {
		$('#tailsignal-import-form').hide();
	});

	// Import CSV upload
	$(document).on('submit', '#tailsignal-import-upload', function(e) {
		e.preventDefault();
		var formData = new FormData(this);
		var $status = $('#tailsignal-import-status');

		$.ajax({
			url: tailsignal.rest_url + 'devices/import',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			headers: { 'X-WP-Nonce': tailsignal.rest_nonce },
			success: function(response) {
				$status.text(response.message).css('color', 'green');
				setTimeout(function() { location.reload(); }, 2000);
			},
			error: function(xhr) {
				var msg = xhr.responseJSON ? xhr.responseJSON.message : tailsignal.strings.error;
				$status.text(msg).css('color', 'red');
			}
		});
	});

	// ── Groups Page ─────────────────────────────────────────────

	// Show create group form
	$('#tailsignal-create-group-btn').on('click', function() {
		$('#tailsignal-group-form').show();
		$('#tailsignal-group-form input[name="group_id"]').val('');
		$('#tailsignal-group-name').val('');
		$('#tailsignal-group-description').val('');
		$('#tailsignal-group-form input[type="checkbox"]').prop('checked', false);
		updateDeviceSelectedCount();
	});

	// Update the selected device count display
	function updateDeviceSelectedCount() {
		var $counter = $('#tailsignal-device-selected-count');
		if ( ! $counter.length ) return;
		var checked = $('#tailsignal-group-save-form input[name="device_ids[]"]:checked').length;
		var total   = $('#tailsignal-group-save-form input[name="device_ids[]"]').length;
		$counter.text( checked + ' / ' + total + ' selected' );
	}

	// Device search in groups
	$('#tailsignal-group-device-search').on('input', function() {
		var query = $(this).val().toLowerCase();
		$('.tailsignal-device-option').each(function() {
			var label = $(this).data('label') || '';
			$(this).toggle(label.indexOf(query) !== -1);
		});
	});

	// Select all / Deselect all
	$('#tailsignal-select-all-devices').on('click', function() {
		$('.tailsignal-device-option:visible input[type="checkbox"]').prop('checked', true);
		updateDeviceSelectedCount();
	});

	$('#tailsignal-deselect-all-devices').on('click', function() {
		$('.tailsignal-device-option:visible input[type="checkbox"]').prop('checked', false);
		updateDeviceSelectedCount();
	});

	// Update count on any checkbox change
	$(document).on('change', '#tailsignal-group-save-form input[name="device_ids[]"]', function() {
		updateDeviceSelectedCount();
	});

	// Save group
	$(document).on('submit', '#tailsignal-group-save-form', function(e) {
		e.preventDefault();

		var $status = $('#tailsignal-group-status');
		var deviceIds = [];
		$('#tailsignal-group-save-form input[name="device_ids[]"]:checked').each(function() {
			deviceIds.push($(this).val());
		});

		$.post(tailsignal.ajax_url, {
			action: 'tailsignal_save_group',
			nonce: tailsignal.nonce,
			group_id: $('[name="group_id"]', this).val(),
			name: $('[name="name"]', this).val(),
			description: $('[name="description"]', this).val(),
			device_ids: deviceIds
		}, function(response) {
			if (response.success) {
				$status.text(response.data.message).css('color', 'green');
				setTimeout(function() {
					window.location.href = tailsignal.ajax_url.replace('admin-ajax.php', 'admin.php?page=tailsignal-groups');
				}, 1000);
			} else {
				$status.text(response.data.message).css('color', 'red');
			}
		});
	});

	// Delete group
	$(document).on('click', '.tailsignal-delete-group', function() {
		if (!confirm(tailsignal.strings.confirm_delete)) return;

		var $btn = $(this);
		var id = $btn.data('id');

		$.post(tailsignal.ajax_url, {
			action: 'tailsignal_delete_group',
			nonce: tailsignal.nonce,
			group_id: id
		}, function(response) {
			if (response.success) {
				$btn.closest('tr').fadeOut(function() { $(this).remove(); });
			} else {
				alert(response.data.message);
			}
		});
	});

	// ── Meta Box (Quick Send) ───────────────────────────────────

	$(document).on('click', '.tailsignal-quick-send-btn', function() {
		var $btn = $(this);
		var $status = $btn.siblings('.tailsignal-quick-send-status');
		var postId = $btn.data('post-id');
		var $metaBox = $btn.closest('.tailsignal-meta-box');

		var targetType = $metaBox.find('[name="tailsignal_quick_target"]:checked').val();
		var targetIds = null;

		if (targetType === 'group') {
			targetIds = [$metaBox.find('.tailsignal-quick-group-select').val()];
		}

		$btn.prop('disabled', true).text(tailsignal.strings.sending);
		$status.text('');

		$.post(tailsignal.ajax_url, {
			action: 'tailsignal_quick_send',
			nonce: tailsignal.nonce,
			post_id: postId,
			title: $metaBox.find('.tailsignal-quick-title').val(),
			body: $metaBox.find('.tailsignal-quick-body').val(),
			target_type: targetType,
			target_ids: targetIds,
			include_image: $metaBox.find('[name="tailsignal_include_image"]:checked').length ? '1' : '0'
		}, function(response) {
			if (response.success) {
				$status.text(response.data.message).css('color', 'green');
			} else {
				$status.text(response.data.message).css('color', 'red');
			}
			$btn.prop('disabled', false).text('Send Now');
		}).fail(function() {
			$status.text(tailsignal.strings.error).css('color', 'red');
			$btn.prop('disabled', false).text('Send Now');
		});
	});

})(jQuery);
