# TailSignal

> **Work in Progress** — TailSignal is under active development. Features and APIs may change between releases. Use in production at your own discretion.

> **Disclaimer** — This software is provided "as is" without warranty of any kind. The author(s) are not responsible for any loss of data, revenue, or damages arising from the use of this software. You use TailSignal entirely at your own risk.

A self-hosted WordPress plugin using Expo to send custom push notifications. Own your data, bypass OneSignal, and keep your pack in the loop with a wag.

[![Tests](https://github.com/mrdemonwolf/TailSignal/actions/workflows/test.yml/badge.svg)](https://github.com/mrdemonwolf/TailSignal/actions/workflows/test.yml)
[![Build](https://github.com/mrdemonwolf/TailSignal/actions/workflows/build-zip.yml/badge.svg)](https://github.com/mrdemonwolf/TailSignal/actions/workflows/build-zip.yml)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**[Documentation](https://mrdemonwolf.github.io/tailsignal)** · **[Download Latest Release](https://github.com/MrDemonWolf/tailsignal/releases/latest)**

## Features

- **Self-hosted** - Your data stays on your server, no third-party services
- **Expo Push API** - Works with any Expo/React Native mobile app
- **Auto-notify on publish** - Automatically send a push when a new post or portfolio item is published
- **Manual send** - Send custom notifications with live preview, character counters, and placeholder quick-fill
- **iOS/Android preview** - Toggle between iOS and Android notification previews on the Send page
- **Scheduling** - Schedule notifications for future delivery via WP-Cron
- **Device groups** - Organize devices into groups (e.g., "Beta Testers", "VIP") for targeted sends
- **Rich notifications** - Include featured images for rich push notifications on iOS and Android
- **Dashboard analytics** - Device stat cards, platform badges, monthly notification charts, and success rate tracking
- **Dev Mode** - Test notifications on your own devices without sending to everyone
- **Export/Import** - CSV export and import for device management with token validation
- **Template system** - Customizable title/body templates with `{post_title}`, `{site_name}`, `{author_name}`, `{category}`, and `{post_excerpt}` placeholders
- **Portfolio support** - Separate notification templates for portfolio post types with deep linking
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
2. Run `composer install --no-dev --optimize-autoloader` in the `src/` directory
3. Copy the plugin files to `/wp-content/plugins/tailsignal/`
4. Activate via the WordPress admin

## Repository Structure

```
├── src/                    # WordPress plugin source
│   ├── tailsignal.php      # Plugin bootstrap
│   ├── includes/            # Core PHP classes
│   ├── admin/               # Admin UI (PHP, CSS, JS)
│   ├── rest-api/            # REST API controller
│   └── vendor/              # Composer dependencies
├── docs/                    # Fumadocs documentation site
│   ├── content/docs/        # MDX documentation pages
│   └── src/                 # Next.js app
├── tests/                   # PHPUnit test suite
├── .github/workflows/       # CI/CD workflows
├── composer.json            # Dev dependencies (PHPUnit, Brain Monkey)
├── package.json             # npm workspaces (src + docs)
└── Makefile                 # Build commands
```

## Development

### Prerequisites

- PHP 7.4+
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) 22+ (for docs site and Tailwind CSS)

### Setup

```bash
git clone https://github.com/mrdemonwolf/TailSignal.git
cd TailSignal
composer install          # Dev deps (PHPUnit, etc.)
cd src && composer install && cd ..  # Plugin deps (Expo SDK)
npm install               # Workspace deps
npm run build:css         # Compile Tailwind CSS
```

### Running Tests

```bash
make test
# or
composer test
```

### Building the Plugin ZIP

```bash
make zip
```

### Docs Site

```bash
npm run docs:dev    # Local dev server
npm run docs:build  # Static export to docs/out/
```

## Documentation

Full documentation is available at **[mrdemonwolf.github.io/tailsignal](https://mrdemonwolf.github.io/tailsignal)**.

## License

This project is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

## Author

**[MrDemonWolf, Inc.](https://github.com/mrdemonwolf)**
