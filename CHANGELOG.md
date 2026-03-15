# Changelog

All notable changes to TailSignal will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0-beta.1] - 2026-03-14

### Security
- Added CSRF nonce verification on Groups edit page GET parameters
- CSV import now returns error if MIME type detection (`finfo_open`) fails instead of silently skipping validation
- Added `tailsignal_manage` capability check to meta box `save_meta_box` for notification meta fields
- Added transient-based rate limiting (30 req/min per IP) on public REST endpoints (`register`, `unregister`, `register/status`)

### Performance
- Added 5-minute transient caching to dashboard stat queries (`get_device_summary_stats`, `get_device_count_by_platform`, `get_notification_counts_by_status`, `get_success_rate`, `get_monthly_notification_stats`)
- Added automatic cache invalidation on all device and notification write operations
- Added composite database index on `device_meta(device_id, meta_key)` and index on `notifications(created_at)`
- Batch receipt checking — cron now collects all pending ticket IDs into a single Expo API call
- Conditional script enqueuing — post edit screens only load TailSignal scripts for supported post types
- Added `columns` parameter to `get_notifications()` to allow selecting specific columns for list views
- Wrapped `import_devices()` and `delete_all_notifications()` in database transactions

### Added
- Dark mode support via `@media (prefers-color-scheme: dark)` with full CSS variable overrides
- WordPress admin color scheme detection — dark WP themes (midnight, blue, coffee, ectoplasm, ocean, sunrise) automatically apply dark mode via `data-theme="dark"`
- CSS status classes `.tailsignal-status-success` and `.tailsignal-status-error` for theme-aware status colors

### Changed
- Replaced all inline JavaScript `.css('color', ...)` calls with CSS classes for dark mode compatibility
- All status message containers now use CSS variable-based colors instead of hardcoded values

### Accessibility
- Added `role="dialog"` and `aria-modal="true"` to confirmation modals and device edit dialog
- Added `aria-live="polite"` to status message containers (send status, group status, import status)
- Added `aria-label` and `role="img"` to dashboard chart canvas
- Added `scope="col"` to all custom table headers across dashboard, groups, and send notification pages

## [1.0.0] - 2025-02-12

### Added
- Initial release
- Push notifications via Expo Push Service
- Device registration and management
- Groups for targeted notifications
- Post publish auto-notifications
- Scheduled notifications via WP-Cron
- Receipt checking and stale token cleanup
- CSV import/export for devices
- Dashboard with monthly trends chart
- REST API for mobile app integration
- Dev mode for testing
- Portfolio post type support
