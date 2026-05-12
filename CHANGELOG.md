# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Core**: Centralized state management via `fst_app()` static state container.
- **Core**: Upgraded Middleware system to **Onion Model** supporting recursive `$next()` calls.
- **Security**: Hardened Error Handler with **Double-Layer Safety Net** via `fst_is_safe_to_debug()`.
- **SPA**: Upgraded to support **Fragment Rendering** (target-specific swapping via class/ID selectors).
- **SPA**: Added **Lifecycle Events** support (`fst:unload` and `fst:load`).
- **SPA**: Implemented **Native History Caching** for instant back/forward navigation without re-fetching.
- **SPA**: Added opt-out capability via `data-no-spa` / `no-spa` and respect for `e.defaultPrevented`.

- **Installer**: Added **Auto-Scaffolding** to generate starter project files (`router.php`, `views/`, `assets/`) during installation.
- **Installer**: Added **Zero-Config SPA** toggle to the installation wizard.
- **Core**: Added **Strict Route Detection** to prevent duplicate route definitions.
- **Core**: Added **PostgreSQL** driver support via PDO.
- **Core**: Added `fst-plugins/` Auto-Discovery for modular framework extension.
- **Admin**: Added **Plugin Marketplace** with remote fetching and one-click installation.
- **Admin**: Enhanced **Integrity Monitor** with local hash verification and remote update checker.

### Removed
- **Core**: Removed **Dynamic Routing** mode (dead code amputation) to enforce strict, whitelist-based routing.
- **View**: Removed `fst_serve_dynamic_file` and `fst_show_directory_listing` public functions.

### Changed
- **Core**: Simplified `fullstuck.json` schema by removing nested routing modes (`static_config`/`dynamic_config`).
- **Admin**: Streamlined **System Monitor** by removing routing mode status display.
- **Admin**: Updated **Scan Project** registry to remove deleted view functions and include new core helpers.

### Fixed
- **Compiler**: Fixed aggressive PHP tag removal that corrupted string literals in source files (e.g., scaffolding templates in `install.php`).
- **FIM**: Fixed `fst_check_integrity()` failing on Windows due to CRLF line endings — replaced `explode(" */\n", ...)` with `preg_split` to handle both `\r\n` and `\n`.
- **FIM**: Fixed `fst_check_integrity()` unable to locate `fullstuck.php` when running `php -S` from test subfolders — added `$_SERVER['SCRIPT_FILENAME']` fallback for path resolution.
- **Fix**: Optional route parameter parsing order in `src/router.php`.

### Security (Code Review Hardening)
- **Database**: Fixed **SQL Injection** vulnerability in `fst_db_select()` `order_by` option — user input is now sanitized via `_fst_sanitize_order_by()` with whitelist regex.
- **View**: Fixed **Path Traversal** vulnerability in `fst_view()` — added `realpath()` validation to ensure views cannot escape the project root. Also switched `extract()` to use `EXTR_SKIP` to prevent variable injection.
- **HTTP**: Fixed **Open Redirect** vulnerability in `fst_redirect()` — blocked protocol-relative URLs (`//evil.com`) and added hostname validation for absolute URLs.
- **Admin**: Fixed **XSS** vulnerability in flash message rendering — output is now escaped via `htmlspecialchars()` at the render point in `fst_admin_render_page()`.
- **Admin**: Hardened **Plugin Install** endpoint — enforced HTTPS-only downloads and domain whitelist (GitHub only) to prevent arbitrary code injection.
- **Compiler**: Replaced regex-based comment stripping with PHP's native `token_get_all()` tokenizer — prevents accidental removal of comment-like patterns inside string literals.
- **Core**: Replaced `session_start()` with `session_status()` check to prevent duplicate session errors.
- **Core**: Removed legacy `global` variables in favor of `fst_app()` single-source-of-truth state container.
- **Database**: Simplified redundant double `try/catch` wrapping in database initialization.
- **Database**: Fixed default identifier quoting fallback from `mysql` to `sqlite` (safest common denominator).
- **View**: Added 13 additional MIME types (webp, woff2, gif, json, mp4, etc.) to static file server.
- **Security**: Hardened CSRF check (removed GET support, added header support) to prevent leakage.
- **Security**: Added `realpath` validation in `fst_upload()` to prevent path traversal.
- **Security**: Hardened `fst_db_select()` against SQL injection in `order_by` clause.
- **Security**: Prevented path traversal and variable injection in `fst_view()`.
- **Security**: Implemented open redirect protection in `fst_redirect()`.
- **Security**: Hardened plugin installation with HTTPS requirement and domain white-listing.

### Features & Improvements
- **Feature**: Added `data-spa-ignore` support for scripts in SPA agent.
- **Feature**: Added `min_value` and `max_value` validation rules.
- **Architecture**: Disabled auto-run in CLI mode to support unit testing.
- **Architecture**: Improved state initialization to prevent resets on multiple includes.
- **Improvement**: Replaced regex-based comment stripping in compiler with `token_get_all()`.
- **Improvement**: Expanded MIME types for modern static assets.

## [v0.1.0] - 2026-05-06
- Initial release of FullStuck.php "Two Worlds" architecture.
- Core router with middleware support.
- Zero-dependency design with automatic fallbacks.
- Admin Dashboard for configuration and monitoring.
- Security features: CSRF protection, secure sessions, and basic WAF patterns.
- File Integrity Monitoring (FIM) system.
