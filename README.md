# TailSignal

A self-hosted WordPress plugin using Expo to send custom push notifications. Own your data, bypass OneSignal, and keep your pack in the loop with a wag.

[![Tests](https://github.com/mrdemonwolf/TailSignal/actions/workflows/test.yml/badge.svg)](https://github.com/mrdemonwolf/TailSignal/actions/workflows/test.yml)
[![Build](https://github.com/mrdemonwolf/TailSignal/actions/workflows/build-zip.yml/badge.svg)](https://github.com/mrdemonwolf/TailSignal/actions/workflows/build-zip.yml)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## Features

- **Self-hosted** - Your data stays on your server, no third-party services
- **Expo Push API** - Works with any Expo/React Native mobile app
- **Auto-notify on publish** - Automatically send a push when a new post is published
- **Manual send** - Send custom notifications with live preview, character counters, and placeholder quick-fill
- **Scheduling** - Schedule notifications for future delivery via WP-Cron
- **Device groups** - Organize devices into groups (e.g., "Beta Testers", "VIP") for targeted sends
- **Rich notifications** - Include featured images for rich push notifications on iOS and Android
- **Dashboard analytics** - Device stat cards, platform badges, monthly notification charts, and success rate tracking
- **Dev Mode** - Test notifications on your own devices without sending to everyone
- **Export/Import** - CSV export and import for device management with token validation
- **Template system** - Customizable title/body templates with `{post_title}`, `{site_name}`, `{author_name}`, `{category}`, and `{post_excerpt}` placeholders
- **Post editor meta box** - Per-post notification control with quick send and history
- **Notification history** - Full log with status tracking, delivery counts, and bulk delete
- **Clean uninstall** - Removes all tables, options, and capabilities on deletion
- **No CDN dependencies** - All assets (Tailwind CSS, Chart.js) bundled locally for security and performance

## Requirements

- PHP 7.4 or higher
- WordPress 6.0 or higher
- An Expo/React Native mobile app that uses Expo Push Tokens

## Installation

### From GitHub Release

1. Download `tailsignal.zip` from the [latest release](https://github.com/mrdemonwolf/TailSignal/releases/latest)
2. In WordPress admin, go to **Plugins > Add New > Upload Plugin**
3. Upload `tailsignal.zip` and click **Install Now**
4. Activate the plugin

### Manual

1. Clone or download this repository
2. Run `composer install --no-dev --optimize-autoloader`
3. Copy the `tailsignal` folder to `/wp-content/plugins/`
4. Activate via the WordPress admin

## Configuration

After activation, go to **TailSignal > Settings** in the WordPress admin:

1. **Dev Mode** - Toggle on while testing to only send to devices flagged as "dev"
2. **Auto-notify on new posts** - Automatically send a push when a post is published
3. **Expo Access Token** - Optional, get one from the [Expo dashboard](https://expo.dev)
4. **Default Title** - Template for notification titles (default: `New from {site_name}`)
5. **Default Body** - Template for notification body (default: `{post_title}`)
6. **Include Featured Image** - Send the post's featured image as a rich notification

### Template Placeholders

Use these in your notification title and body templates:

| Placeholder | Resolves to |
|-------------|-------------|
| `{post_title}` | Post title |
| `{post_excerpt}` | First 20 words of content |
| `{site_name}` | WordPress site name |
| `{author_name}` | Post author display name |
| `{category}` | Primary category name |

## REST API

All endpoints are under the `tailsignal/v1` namespace.

### Public Endpoints

#### Register Device

```
POST /wp-json/tailsignal/v1/register
```

Register a new device or update an existing one.

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `expo_token` | string | Yes | Expo push token (`ExponentPushToken[...]`) |
| `device_type` | string | Yes | `ios` or `android` |
| `device_model` | string | No | Hardware model (e.g., "iPhone 16 Pro") |
| `os_version` | string | No | OS version (e.g., "iOS 18.2") |
| `app_version` | string | No | App version (e.g., "1.2.0") |
| `locale` | string | No | Device locale (e.g., "en-US") |
| `timezone` | string | No | Device timezone (e.g., "America/Chicago") |
| `user_label` | string | No | Friendly name (e.g., "MrDemonWolf - iPhone") |

**Response:** `201 Created`

```json
{
  "success": true,
  "device_id": 42
}
```

#### Unregister Device

```
DELETE /wp-json/tailsignal/v1/register
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `expo_token` | string | Yes | The token to unregister |

**Response:** `200 OK`

### Admin Endpoints

All admin endpoints require the `tailsignal_manage` capability (granted to administrators on activation).

#### Send Notification

```
POST /wp-json/tailsignal/v1/send
```

Send or schedule a push notification.

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `title` | string | Yes | Notification title |
| `body` | string | Yes | Notification body |
| `data` | string | No | Custom JSON data payload |
| `image_url` | string | No | Image URL for rich notifications |
| `target_type` | string | No | `all`, `dev`, `group`, or `specific` (default: `all`) |
| `target_ids` | array | No | Group IDs or device IDs (when target_type is `group` or `specific`) |
| `post_id` | integer | No | Link notification to a post |
| `scheduled_at` | string | No | Schedule for future delivery (datetime string) |

#### Get Statistics

```
GET /wp-json/tailsignal/v1/stats
```

Returns dashboard statistics (device counts, monthly sends, success rate, dev mode status).

#### Export Devices

```
GET /wp-json/tailsignal/v1/devices/export
```

Downloads all devices as a CSV file.

#### Import Devices

```
POST /wp-json/tailsignal/v1/devices/import
```

Import devices from a CSV file upload. Handles duplicates via upsert.

## Admin Pages

| Page | Description |
|------|-------------|
| **Dashboard** | Device stat cards with platform badges, monthly notification chart (Chart.js), success rate, and recent activity |
| **Send** | Compose notifications with live preview, character counters, placeholder quick-fill buttons, targeting (all/dev/group/specific), and scheduling |
| **Devices** | Device list with search/filter, edit labels, toggle dev flag, bulk delete, CSV import/export |
| **Groups** | Create and manage device groups, assign/remove devices with search |
| **History** | Notification log with status, delivery counts, filtering, and delete all |
| **Settings** | Plugin configuration (Dev Mode, templates, Expo token, auto-notify, featured images) |

## Dev Mode

Dev Mode lets you test notifications without sending to all users:

1. Go to **TailSignal > Settings** and toggle **Dev Mode ON**
2. Go to **TailSignal > Devices** and flag your test devices as "dev"
3. While Dev Mode is on, all notification sends (auto and manual) only target `is_dev=1` devices
4. Turn Dev Mode OFF when ready for production

## Development

### Prerequisites

- PHP 7.4+
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) (for Tailwind CSS compilation)

### Setup

```bash
git clone https://github.com/mrdemonwolf/TailSignal.git
cd TailSignal
composer install
npm install
npm run build:css
```

### Running Tests

```bash
make test
# or
composer test
```

The test suite includes 169 tests and 263 assertions covering:

- Database CRUD operations
- Expo SDK integration
- Notification building, sending, and scheduling
- REST API endpoints (registration, send, export, import)
- Admin settings registration and sanitization
- Admin AJAX handlers (send, groups, meta box)
- Plugin activation and deactivation

### Building the Plugin ZIP

```bash
make zip
```

This produces `build/tailsignal.zip` with production dependencies only (no dev packages, tests, or build files).

### CI/CD

GitHub Actions workflows run automatically:

| Workflow | Trigger | What it does |
|----------|---------|--------------|
| **Tests** | Push/PR to main | Runs tests on PHP 7.4, 8.0, 8.1, 8.2, 8.3 with composer validation and security audit |
| **Build ZIP** | PR to main | Builds plugin ZIP as a downloadable artifact (dev builds) |
| **Release** | GitHub release published | Runs full test matrix, then builds and attaches `tailsignal.zip` to the release |

## Database

TailSignal creates 6 custom tables (prefixed with `{$wpdb->prefix}tailsignal_`):

| Table | Purpose |
|-------|---------|
| `devices` | Registered Expo push tokens with device metadata |
| `device_meta` | Extensible key/value metadata per device |
| `groups` | Named device groups for targeted sends |
| `device_groups` | Pivot table linking devices to groups |
| `notifications` | Notification records with status, targeting, and receipt data |
| `notification_history` | Links posts to their notification history |

All tables and options are cleanly removed when the plugin is deleted via WordPress admin.

## Mobile App Integration

In your Expo/React Native app, register for push notifications and send the token to your WordPress site:

```javascript
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';

async function registerForPushNotifications(wordpressSiteUrl) {
  const { status } = await Notifications.requestPermissionsAsync();
  if (status !== 'granted') return;

  const token = (await Notifications.getExpoPushTokenAsync()).data;

  await fetch(`${wordpressSiteUrl}/wp-json/tailsignal/v1/register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      expo_token: token,
      device_type: Device.osName === 'iOS' ? 'ios' : 'android',
      device_model: Device.modelName,
      os_version: Device.osVersion,
      app_version: '1.0.0',
      user_label: 'My Device',
    }),
  });
}
```

## Security

- All user inputs are sanitized via WordPress sanitization functions
- Nonce verification on all AJAX handlers
- Capability checks (`tailsignal_manage`) on all admin endpoints
- Prepared statements for all database queries
- `ABSPATH` guards on all PHP files
- No external CDN dependencies -- all assets (Tailwind CSS, Chart.js) are bundled locally
- Expo token format validation on registration and CSV import
- MIME type validation on file uploads

## License

This project is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

## Author

**[MrDemonWolf, Inc.](https://github.com/mrdemonwolf)**
