# Quickstart: Laravel 12 & PHP 8.2–8.4 Compatibility Update

**Feature**: `001-laravel12-php-support`  
**Branch**: `001-laravel12-php-support`  
**Date**: 2026-02-21

---

## What This Change Does

Updates the `laravelgenerators/postman-generator` package to officially support:
- **Laravel 12** (alongside existing Laravel 11)
- **PHP 8.4** (alongside existing PHP 8.2 and 8.3)

No features are added. No source code changes are required. Only three files are modified.

---

## Files Changed

| File | Change |
|------|--------|
| `composer.json` | Broaden `illuminate/*` to `^11.0\|^12.0`; upgrade Pest to `^3.0`; broaden testbench to `^9.0\|^10.0` |
| `.github/workflows/tests.yml` | Add `8.4` to PHP matrix; add `12.*` to Laravel matrix |
| `README.md` | Update supported versions table/badges |

---

## How to Run Tests Locally

### Test Against Laravel 11 (current default)
```bash
composer install
vendor/bin/pest
```

### Test Against Laravel 12 (after change)
```bash
composer require "laravel/framework:12.*" --no-interaction --no-update
composer update --prefer-dist --no-interaction
vendor/bin/pest
```

### Test Against Specific PHP Version
Use a local PHP switcher (e.g., `phpenv`, `phpbrew`, or Docker):
```bash
# Example with Docker
docker run --rm -v $(pwd):/app -w /app php:8.4-cli \
  bash -c "composer update && vendor/bin/pest"
```

---

## CI Matrix (After Change)

After the workflow update, GitHub Actions will run 6 jobs automatically on every push and pull request:

| Job | PHP | Laravel | Expected Result |
|-----|-----|---------|-----------------|
| 1   | 8.2 | 11.*    | ✅ Pass         |
| 2   | 8.2 | 12.*    | ✅ Pass         |
| 3   | 8.3 | 11.*    | ✅ Pass         |
| 4   | 8.3 | 12.*    | ✅ Pass         |
| 5   | 8.4 | 11.*    | ✅ Pass         |
| 6   | 8.4 | 12.*    | ✅ Pass         |

---

## Verifying the Change

After implementing, verify:

1. **Composer resolves on both versions** — no conflicts:
   ```bash
   # Laravel 11
   composer require "laravel/framework:^11.0" --dry-run
   # Laravel 12  
   composer require "laravel/framework:^12.0" --dry-run
   ```

2. **All tests pass on both versions** — see "How to Run Tests" above.

3. **README accurately reflects** updated compatibility table.

---

## Next Step

Run `/speckit.tasks` to generate the actionable task list for implementation.
