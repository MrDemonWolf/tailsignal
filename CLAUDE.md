# TailSignal - Project Conventions

## Overview
TailSignal is a self-hosted WordPress plugin that sends push notifications to mobile devices via the Expo Push Service.

## Tech Stack
- PHP 7.4+ (WordPress 6.0+)
- Expo Push API via `ctwillie/expo-server-sdk-php`
- PHPUnit 9.x + Brain Monkey for tests
- Tailwind CSS (CDN with `tw-` prefix) for admin UI

## Code Style
- WordPress PHP Coding Standards
- Class-based architecture: `TailSignal_ClassName` naming
- Files: `class-tailsignal-name.php` (lowercase, hyphenated)
- All DB tables use `{$wpdb->prefix}tailsignal_` prefix
- All options use `tailsignal_` prefix
- Custom capability: `tailsignal_manage`

## Database
- 6 custom tables: devices, device_meta, groups, device_groups, notifications, notification_history
- Schema version tracked via `tailsignal_db_version` option
- Clean uninstall drops all tables and options

## Testing
- `composer test` runs PHPUnit
- Brain Monkey mocks WordPress functions
- Tests in `tests/` directory

## Building
- `make zip` produces `build/tailsignal.zip`
- `.distignore` controls what's excluded from ZIP

## Key Patterns
- Hooks registered via TailSignal_Loader
- REST API under `tailsignal/v1` namespace
- Admin pages use Tailwind CSS with `tw-` prefix to avoid WP conflicts
- All admin content wrapped in `#tailsignal-app` container
- Template placeholders: {post_title}, {post_excerpt}, {site_name}, {author_name}, {category}
