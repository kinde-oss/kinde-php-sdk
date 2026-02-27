# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased] - Since v2.3.4

### Added

- **Webhook decoder** – Decode and verify Kinde webhook payloads with JWKS-based validation
- **Prompt login** – Support for prompt=login in authentication flows
- **Invitation code** – Invitation code functionality for user flows
- **JWKS security** – Guard JWKS fetches against untrusted domains; fail-closed domain validation; 5s timeout on JWKS fetch requests
- **JWKS cache** – Namespace JWKS cache per JWKS URL to avoid cross-tenant cache collisions
- Testing overhaul – Expanded test suite with no-token scenarios, force API tests, feature flag mapping tests, and production logic tests

### Fixed

- **KindeManagementClient** – Initialize JWKS URL to resolve uninitialized property (fixes #76)
- **PHP 8.4** – Resolve deprecation issue in customer-related code
- **PHP 8.1+** – Fix deprecation warning when `exit()` is passed `null`
- **PHP 7.4** – Compatibility fixes across the SDK
- JWKS domain guard moved into `WebhookDecoder`; circular validation removed from `Utils`
- Enforce JWKS host per call without global mutation; seed webhook JWKS host for validation

### Changed

- Document domain guard trade-off; group organization constants; use reflection in `all()`
- API fetch and feature flag mapping helpers changed to `protected`; duplicate force API implementations removed
- Code quality and CodeRabbit-suggested changes

---

*Generated from commits since tag `v2.3.4`.*
