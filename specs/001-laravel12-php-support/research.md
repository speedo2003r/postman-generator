# Research: Laravel 12 & PHP 8.2–8.4 Compatibility

**Feature**: `001-laravel12-php-support`  
**Date**: 2026-02-21  
**Status**: Complete — all unknowns resolved

---

## 1. Dependency Version Strategy

### Decision
Use the Composer logical-OR constraint `"^11.0|^12.0"` for all `illuminate/*` packages, keeping `"^8.2"` for PHP.

### Rationale
- The caret operator (`^`) follows SemVer: `^11.0` permits 11.x, `^12.0` permits 12.x. The `|` operator lets Composer satisfy either range.
- This is the standard and widely-adopted pattern in the Laravel ecosystem for packages that support multiple major framework versions simultaneously.
- No separate branch per Laravel version is needed; a single `composer.json` handles both.

### Alternatives Considered
- **Separate branches per Laravel version**: Rejected — doubles maintenance burden and is unnecessary given Composer's constraint syntax.
- **`>=11.0,<13.0`**: Technically equivalent but less explicit about intentional support; community convention favors `|`.

---

## 2. `orchestra/testbench` Version for Dev Dependencies

### Decision
Change `"orchestra/testbench"` from `"^9.0"` to `"^9.0|^10.0"` in `require-dev`.

### Rationale
- `orchestra/testbench` **9.x** targets Laravel 11.x (PHP 8.2+).
- `orchestra/testbench` **10.x** targets Laravel 12.x (PHP 8.2+).
- Using the OR constraint allows local development and CI to resolve the correct testbench version depending on which Laravel version is installed during the matrix run.
- Both versions support PHP 8.2, 8.3, and 8.4.

### Version Compatibility Matrix (confirmed)

| Laravel | Testbench | PHP            |
|---------|-----------|----------------|
| 11.x    | ~9.x      | 8.2, 8.3, 8.4  |
| 12.x    | ~10.x     | 8.2, 8.3, 8.4  |

### Alternatives Considered
- **Pin to `^10.0` only**: Rejected — drops Laravel 11 support in dev and makes testing harder.
- **Use `composer update` with `--prefer-lowest`**: Complementary tool, not a substitue for the correct constraint.

---

## 3. `pestphp/pest` Version

### Decision
Upgrade `pestphp/pest` from `"^2.0"` to `"^3.0"` in `require-dev`.

### Rationale
- Pest 2.x targets PHP 8.1+. Pest 3.x targets PHP 8.2+ and is the version that ships with Laravel 11+ new projects.
- Pest 3.x is fully compatible with PHP 8.2, 8.3, and 8.4.
- Pest 4.x requires PHP 8.3+ — excluded to maintain PHP 8.2 support.
- Moving from Pest 2→3 does not require test rewriting since the DSL API is stable.

### Alternatives Considered
- **Stay on `^2.0`**: Rejected — Pest 2 is not compatible with the orchestra/testbench 10.x dependency tree for Laravel 12.
- **Upgrade to `^4.0`**: Rejected — requires PHP 8.3 minimum, breaking PHP 8.2 support.

---

## 4. CI Matrix Design

### Decision
Expand the GitHub Actions matrix from `{php: [8.2, 8.3], laravel: [11.*]}` to `{php: [8.2, 8.3, 8.4], laravel: [11.*, 12.*]}`.

### Rationale
- This produces 6 jobs: every combination of 3 PHP versions × 2 Laravel versions.
- The `composer require "laravel/framework:${{ matrix.laravel }}"` step already in the workflow dynamically pins the Laravel version, so the Composer OR-constraint strategy resolves the correct testbench version automatically.
- `fail-fast: false` is already set — no change needed; all 6 jobs run to completion regardless of individual failures.

### Alternatives Considered
- **Separate workflows per Laravel version**: Rejected — harder to maintain and adds unnecessary noise.
- **Using `include:` overrides**: An option for exclude-specific combinations (e.g., if a combo is known broken), but not needed here.

---

## 5. Source Code PHP 8.4 Risk Assessment

### Decision
No source code changes are required for PHP 8.4 compatibility.

### Rationale
Audit of all 18 source files reveals:
- Only stable, long-standing PHP and Laravel APIs are used (facades, route introspection, service provider bindings, Artisan command, `File` facade).
- No usage of functions or features deprecated in PHP 8.2, 8.3, or 8.4 (e.g., no `${var}` string interpolation, no `utf8_encode`/`utf8_decode` usage, no `ldap_connect`, no implicit nullable parameters without explicit `?Type`).
- Constructor promotion and named arguments are used correctly and are compatible with PHP 8.2–8.4.
- No internal Laravel APIs that changed between Laravel 11 and 12 are used (e.g., `RouteServiceProvider` is not used directly; only the route facade is called).

### Alternatives Considered
- **Full static analysis pass (PHPStan/Rector)**: Recommended as a follow-up quality gate but not required for this change scope.

---

## 6. README Update Scope

### Decision
Update the compatibility table / badges in `README.md` to reflect Laravel 11–12 and PHP 8.2–8.4.

### Rationale
Accurate documentation prevents confusion for users installing the package and checking requirements.

### Scope
- Update any "Requires" / "Supports" sections.
- Update PHP/Laravel version badges if present.
- No other documentation changes are in scope.
