# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

---

## [1.0.0] — 2026-06-03

First stable release. All breaking changes listed below are relative to the
pre-release state of the package (no prior tagged release existed).

### Added

- **User model decoupling** — the package no longer references `App\Models\User`
  directly. The model is resolved via `config('kerberos.user_model')` (or
  `config('auth.providers.users.model')` as fallback). Introduces
  `src/Support/Kerberos.php` helper class.
- **Configurable redirect routes** — `kerberos.redirects.success` and
  `kerberos.redirects.login` replace the previously hardcoded `route('dashboard')`
  and `route('login')` calls throughout middleware, Livewire components, and
  notifications.
- **`fallback_auth` config key** — when `false` (default), an unauthenticated
  request (no `REMOTE_USER`) results in a 403. When `true`, the middleware falls
  back to the standard Laravel login redirect.
- **`admin_notification_emails` config key** — when populated, admin notifications
  are sent on-demand to these addresses without requiring any `Role` relation on the
  user model.
- **`admin_role` config key** — the admin role name (used by `getAdminUsers()`) is
  now configurable (default: `'Admin'`).
- **`remember_login` config key** — controls whether `Auth::login()` sets a
  remember-me cookie (default: `true`).
- **`user_id` column on `kerberos_attempts`** — nullable FK to `users`; persisted
  when a matching user is found at authentication time.
- **Production guard on `KerberosSetupSeeder`** — the seeder early-returns with a
  warning log when running in a `production` environment.
- **Test harness** — full Pest + Testbench suite (38 tests / 70 assertions)
  covering `KerberosAuthService`, `KerberosAuthentication` middleware,
  `AccessRequestService`, `KerberosSetupSeeder`, and migration rollbacks.
- **PHPStan level 5** via `larastan/larastan` — zero errors.
- **Laravel Pint** — `laravel` preset enforced via CI.
- **GitHub Actions CI** — matrix over PHP 8.2 / 8.3 / 8.4 running Pint, PHPStan,
  and Pest on every push/PR to `main`.
- **`declare(strict_types=1)`** added to all package PHP files.
- **Internationalization** — translation files `resources/lang/en/kerberos.php`
  and `resources/lang/fr/kerberos.php`; all user-facing strings now go through
  `__('kerberos-auth::kerberos.*')`. Publishable via `--tag=kerberos-lang`.

### Changed

- `UserAccessCheckInterface::check()` now types its parameter on
  `Illuminate\Contracts\Auth\Authenticatable` instead of `App\Models\User`.
- `AuthResult::$user` is now typed as `?Illuminate\Contracts\Auth\Authenticatable`.
- `KerberosAuthService::authenticate()` reads the server variable via
  `request()->server()` instead of `$_SERVER` directly.
- `KerberosSetupSeeder` resolves the user model dynamically via
  `config('kerberos.user_model')`.
- Migration `down()` methods are now idempotent: foreign keys and indexes are
  dropped only when they actually exist (`Schema::getForeignKeys()` /
  `Schema::getIndexes()`), preventing errors on rollback after partial installs.
- `role_id` column on `users` is managed by
  `2025_11_18_100000_create_roles_table` (runs only when roles are enabled)
  rather than the generic kerberos columns migration.

### Fixed

- `2025_11_18_100001_add_kerberos_columns_to_users_table` no longer fails when
  the `kerberos` column already exists on the host project's `users` table.
- `down()` of the roles migration no longer crashes on SQLite when the FK and
  index were absent (e.g. after a failed partial install).
- `->after('email')` modifier in the roles migration is now placed before
  `->constrained()`, fixing a silent ordering issue on some database drivers.

---

## Versioning strategy

Releases follow **Semantic Versioning 2.0.0**:

- **PATCH** (`1.0.x`) — bug fixes, no new public API, backwards-compatible.
- **MINOR** (`1.x.0`) — new backwards-compatible features or config keys.
- **MAJOR** (`x.0.0`) — breaking changes to the public API (interfaces, config
  key renames, dropped support for a Laravel / PHP version).

Branches:
- `main` — stable, tagged releases only.
- `feat/*` — feature branches, merged via PR.
- `fix/*` — bug-fix branches, merged via PR.

Release checklist:
1. Update `CHANGELOG.md` (move items from `[Unreleased]` to the new version section).
2. Tag: `git tag -s v1.x.y -m "v1.x.y"`.
3. Push tag: `git push origin v1.x.y`.
4. Create a GitHub Release from the tag, copy the CHANGELOG section as release notes.
