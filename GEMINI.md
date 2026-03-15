# TailSignal: Project Overview & Development Guide

TailSignal is a self-hosted WordPress plugin designed to send push notifications via the Expo Push API. It allows WordPress site owners to send automated notifications when posts are published, manual notifications with live previews, and manage device groups—all without third-party services like OneSignal.

## Project Structure

The project is structured as a monorepo with the following main directories:

-   `src/`: The WordPress plugin source code.
    -   `admin/`: Admin UI (PHP partials, CSS/Tailwind, JS/Chart.js).
    -   `includes/`: Core PHP logic (DB, Expo SDK integration, Cron, Notifications).
    -   `rest-api/`: REST API controller for device registration.
    -   `vendor/`: Composer dependencies for the plugin (Expo SDK, Guzzle).
-   `docs/`: Documentation site built with Next.js and Fumadocs.
-   `tests/`: PHPUnit test suite for the plugin.
-   `build/`: Output directory for generated plugin ZIP files.

## Key Technologies

-   **Backend:** PHP 7.4+ (WordPress 6.0+).
-   **Frontend:** Tailwind CSS (Admin UI), Chart.js (Analytics).
-   **Dependencies:** Composer (PHP), NPM (Build scripts & Docs).
-   **Notifications:** Expo Push API.

## Building and Development

### Setup

1.  **Install dependencies:**
    -   Root dev dependencies (PHPUnit, Brain Monkey): `composer install`
    -   Plugin production dependencies: `cd src && composer install`
    -   Build and docs dependencies: `npm install`

2.  **Compile Assets:**
    -   Build Tailwind CSS: `npm run build:css` (or `make css`)

### Common Commands

-   **Test:** `make test` (Runs PHPUnit)
-   **Lint:** `make lint` (Runs PHPCS)
-   **Fix Linting:** `make lint-fix` (Runs PHPCBF)
-   **Build ZIP:** `make zip` (Generates a production-ready plugin ZIP in `build/`)
-   **Run Docs:** `npm run docs:dev`

## Development Conventions

### Coding Standards
-   The project follows **WordPress Coding Standards**.
-   Use `make lint` before committing to ensure compliance.

### Architecture
-   **Hook-based:** Uses a `TailSignal_Loader` class to manage and register all WordPress actions and filters centrally.
-   **Object-Oriented:** Logic is encapsulated in specific classes (e.g., `TailSignal_DB` for database operations, `TailSignal_Expo` for API interaction).
-   **AJAX:** Admin interactions use WordPress AJAX handlers (`wp_ajax_*`) localized to specific admin classes.

### Testing
-   Uses **PHPUnit** and **Brain Monkey** for testing.
-   Always add or update tests in the `tests/` directory when modifying core logic.

### Assets
-   **No CDN dependencies:** All assets (Tailwind CSS, Chart.js) are bundled locally for security and performance.
-   Tailwind is compiled from `src/admin/css/tailwind-input.css` to `src/admin/css/tailsignal-tailwind.css`.

## CI/CD
-   GitHub Actions are used for:
    -   `test.yml`: Runs PHPUnit and PHPCS on pull requests and pushes.
    -   `build-zip.yml`: Packages the plugin for distribution.
    -   `deploy-docs.yml`: Deploys the documentation site to GitHub Pages.
    -   `release.yml`: Automates release creation.
