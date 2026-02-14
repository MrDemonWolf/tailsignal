=== TailSignal ===
Contributors: mrdemonwolf
Tags: push notifications, expo, mobile, notifications, self-hosted
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A self-hosted WordPress plugin using Expo to send custom push notifications. Own your data, bypass OneSignal, and keep your pack in the loop with a wag.

== Description ==

TailSignal lets you send push notifications to your mobile app users directly from WordPress using the Expo Push Service. No third-party notification services needed.

**Key Features:**

* **Self-hosted** — Your data stays on your server
* **Expo Push API** — Works with any Expo/React Native app
* **Auto-notify** — Automatically send notifications when posts are published
* **Manual send** — Send custom notifications from the admin dashboard
* **Scheduling** — Schedule notifications for later delivery
* **Device groups** — Organize devices into groups for targeted sends
* **Rich notifications** — Include featured images in push notifications
* **Dev Mode** — Test notifications on your own devices without sending to everyone
* **Export/Import** — CSV export and import for device management
* **Template system** — Customizable notification templates with placeholders
* **Post editor integration** — Meta box for per-post notification control

**Template Placeholders:**

* `{post_title}` — Post title
* `{post_excerpt}` — First 20 words of content
* `{site_name}` — WordPress site name
* `{author_name}` — Post author display name
* `{category}` — Primary category name

== Installation ==

1. Upload the `tailsignal` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to TailSignal > Settings to configure
4. In your Expo/React Native app, register device tokens via the REST API

== Frequently Asked Questions ==

= What mobile framework does this work with? =

TailSignal works with any app built using Expo/React Native that uses Expo Push Tokens.

= Do I need an Expo account? =

You don't need a paid Expo account. The Expo Push Service is free. You can optionally add an Expo Access Token for authentication.

= What is Dev Mode? =

Dev Mode lets you test notifications by only sending to devices flagged as "dev". This prevents accidentally sending test notifications to all your users.

= Can I send to specific groups of devices? =

Yes! Create device groups (e.g., "Beta Testers", "VIP") and target notifications to specific groups.

== Screenshots ==

1. Dashboard with stats and recent notifications
2. Send Notification page with targeting options
3. Devices management with filtering
4. Settings page with Dev Mode toggle

== Changelog ==

= 1.0.0 =
* Initial release
* Device registration via REST API
* Auto-notify on post publish
* Manual notification sending
* Scheduled notifications
* Device groups
* Rich notifications with featured images
* Dev Mode for testing
* CSV export/import
* Notification history

== Upgrade Notice ==

= 1.0.0 =
Initial release.
