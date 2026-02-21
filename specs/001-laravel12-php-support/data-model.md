# Data Model: Laravel 12 & PHP 8.2–8.4 Compatibility

**Feature**: `001-laravel12-php-support`  
**Date**: 2026-02-21

> **Note**: This is a pure compatibility update — no new domain entities, database tables, or data structures are introduced. This document captures the *configuration entities* that change as part of this update.

---

## Configuration Entities

### 1. Package Manifest (`composer.json`)

The central configuration file that declares the package's version constraints.

| Field | Current Value | New Value | Reason |
|-------|--------------|-----------|--------|
| `require.php` | `^8.2` | `^8.2` (unchanged) | Already correct |
| `require.illuminate/support` | `^11.0` | `^11.0\|^12.0` | Add Laravel 12 support |
| `require.illuminate/http` | `^11.0` | `^11.0\|^12.0` | Add Laravel 12 support |
| `require.illuminate/routing` | `^11.0` | `^11.0\|^12.0` | Add Laravel 12 support |
| `require-dev.pestphp/pest` | `^2.0` | `^3.0` | Pest 3 supports Laravel 12 dep tree |
| `require-dev.orchestra/testbench` | `^9.0` | `^9.0\|^10.0` | testbench 10.x = Laravel 12 |

**Validation rules**:
- All `illuminate/*` constraints must allow Composer to resolve on both Laravel 11 and 12 installs.
- `pestphp/pest` `^3.0` requires PHP `^8.2` — consistent with the PHP constraint.
- `orchestra/testbench` `^9.0|^10.0` must resolve: v9.x on L11 CI jobs, v10.x on L12 CI jobs.

---

### 2. CI Pipeline Configuration (`.github/workflows/tests.yml`)

The GitHub Actions workflow file that defines the automated test matrix.

**Current matrix**:
```yaml
matrix:
  php: [8.2, 8.3]
  laravel: [11.*]
```

**New matrix**:
```yaml
matrix:
  php: [8.2, 8.3, 8.4]
  laravel: [11.*, 12.*]
```

**Resulting jobs** (6 total):

| Job | PHP | Laravel |
|-----|-----|---------|
| 1   | 8.2 | 11.*    |
| 2   | 8.2 | 12.*    |
| 3   | 8.3 | 11.*    |
| 4   | 8.3 | 12.*    |
| 5   | 8.4 | 11.*    |
| 6   | 8.4 | 12.*    |

**No other workflow fields change** — `fail-fast: false`, `runs-on`, PHP extensions, checkout, and execute steps remain identical.

---

### 3. README.md

**Sections to update**: compatibility/requirements section and any version badges.

| Item | Current | New |
|------|---------|-----|
| Laravel support | 11.x | 11.x, 12.x |
| PHP support | 8.2, 8.3 | 8.2, 8.3, 8.4 |

---

## Source Code Impact

**Zero source file changes required.** All 18 PHP source files use only stable, cross-version Laravel and PHP APIs. No deprecated functions, no framework-internal APIs that changed between L11 and L12.
