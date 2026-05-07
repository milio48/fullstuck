# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Zero-Config SPA architecture (SSR-to-SPA mode).
- **Core**: Added `fst-plugins/` Auto-Discovery for modular framework extension.
- **SPA**: Implemented Virtual Output Buffer and HTML Clipping for Zero-Config SPA navigation.
- **Admin**: Added **Plugin Marketplace** with remote fetching from GitHub `store.json`.
- **Admin**: Implemented **One-Click Plugin Installer** for direct PHP plugin deployment.
- **Admin**: Enhanced **Integrity Monitor** with local hash verification and remote GitHub sync.
- **Admin**: Added `fst_check_integrity` and `fst_config` to the Project Scan registry.
- **Admin**: Updated **Scan Project** to include new SPA and Admin helper functions.
### Fixed
- **Core**: Routes file missing now only triggers 500 error in production; in development, it allows the framework to boot so the Admin Dashboard can be used to troubleshoot.

## [v0.1.0] - 2026-05-06
- Initial release of FullStuck.php "Two Worlds" architecture.
- Core router with middleware support.
- Zero-dependency design with automatic fallbacks.
- Admin Dashboard for configuration and monitoring.
- Security features: CSRF protection, secure sessions, and basic WAF patterns.
- File Integrity Monitoring (FIM) system.
