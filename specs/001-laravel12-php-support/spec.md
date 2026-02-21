# Feature Specification: Laravel 12 & PHP 8.2–8.4 Compatibility

**Feature Branch**: `001-laravel12-php-support`  
**Created**: 2026-02-21  
**Status**: Draft  
**Input**: User description: "محتاج تعديل في الباكدج انه يدعم لارافيل 12 ويدعم php8.2 , php8.3, php8.4"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Install Package on Laravel 12 (Priority: P1)

A developer is starting a new project using Laravel 12 and wants to install the `laravelgenerators/postman-generator` package. They run `composer require laravelgenerators/postman-generator` and expect the package to resolve and install without any compatibility errors.

**Why this priority**: Without Laravel 12 compatibility, the package is entirely unusable on the newest Laravel version, blocking all potential adopters on the latest framework release.

**Independent Test**: Can be fully tested by running `composer require laravelgenerators/postman-generator` in a clean Laravel 12 project and verifying no dependency conflicts arise, then running any generation command to confirm the package operates correctly.

**Acceptance Scenarios**:

1. **Given** a clean Laravel 12 installation with PHP 8.2, **When** the package is required via Composer, **Then** installation completes without version conflict errors.
2. **Given** a clean Laravel 12 installation with PHP 8.3, **When** the package is required via Composer, **Then** installation completes without version conflict errors.
3. **Given** a clean Laravel 12 installation with PHP 8.4, **When** the package is required via Composer, **Then** installation completes without version conflict errors.
4. **Given** the package is installed on Laravel 12, **When** the Postman collection generation command is run, **Then** it produces a valid Postman collection file without runtime errors.

---

### User Story 2 - CI/CD Pipeline Validates Multiple PHP Versions (Priority: P2)

A contributor opens a pull request and expects automated tests to confirm the package works correctly across all declared PHP versions (8.2, 8.3, 8.4) and Laravel versions (11, 12).

**Why this priority**: Continuous validation across the compatibility matrix prevents regressions and gives maintainers confidence when merging changes.

**Independent Test**: Can be fully tested by inspecting the CI pipeline result for a pull request and confirming all matrix jobs (PHP 8.2 × Laravel 11, PHP 8.2 × Laravel 12, PHP 8.3 × Laravel 11, PHP 8.3 × Laravel 12, PHP 8.4 × Laravel 11, PHP 8.4 × Laravel 12) pass.

**Acceptance Scenarios**:

1. **Given** a pull request targeting the main branch, **When** the CI pipeline runs, **Then** test jobs execute for every combination of supported PHP and Laravel versions.
2. **Given** any one combination in the matrix fails, **When** the pipeline finishes, **Then** the pull request is marked as failing and the failing combination is clearly identified.
3. **Given** all matrix combinations pass, **When** the pipeline finishes, **Then** the pull request is marked as passing and ready for review.

---

### User Story 3 - Existing Laravel 11 Projects Are Unaffected (Priority: P3)

A developer using the package on an existing Laravel 11 project updates the package to the latest version. They expect their application to continue working without changes.

**Why this priority**: Backward compatibility ensures existing users are not broken by the compatibility expansion.

**Independent Test**: Can be fully tested by running the existing test suite against Laravel 11 on all three PHP versions and confirming all tests pass.

**Acceptance Scenarios**:

1. **Given** a Laravel 11 project with the updated package, **When** all existing tests are run, **Then** all tests pass without modification.
2. **Given** a Laravel 11 project, **When** the Postman collection generation command is executed, **Then** output is identical to the previous package version.

---

### Edge Cases

- What happens when a developer uses PHP 8.1 (below the minimum)? → Composer must reject installation with a clear version constraint error.
- What happens when a developer uses Laravel 10 (below the minimum)? → Composer must reject installation with a clear version constraint error.
- What happens when a developer uses PHP 8.4 with Laravel 11? → Installation must succeed and the package must function correctly.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The package MUST declare compatibility with Laravel 12 (`illuminate/*` `^12.0`) alongside the existing Laravel 11 constraint (`^11.0`).
- **FR-002**: The package MUST declare a PHP version constraint that allows PHP 8.2, 8.3, and 8.4 (`^8.2`).
- **FR-003**: The `require-dev` testing dependencies MUST be updated to versions that support both Laravel 11 and Laravel 12 across PHP 8.2, 8.3, and 8.4.
- **FR-004**: The CI pipeline MUST include a build matrix covering PHP 8.2, 8.3, and 8.4 combined with Laravel 11 and Laravel 12.
- **FR-005**: All existing tests MUST pass on every combination in the supported matrix without modification to test logic.
- **FR-006**: The package source code MUST not use any PHP features or function signatures deprecated or removed in PHP 8.2, 8.3, or 8.4.
- **FR-007**: The `README.md` MUST be updated to reflect the new supported versions (Laravel 11–12, PHP 8.2–8.4).

### Key Entities

- **Package Manifest** (`composer.json`): Declares the PHP and Laravel version constraints; must be updated to broaden the allowed version ranges.
- **CI Configuration** (`.github/workflows`): Defines the automated test matrix; must be updated to include all supported version combinations.
- **Test Suite**: Existing tests that must pass across the entire compatibility matrix without modification.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: `composer require laravelgenerators/postman-generator` completes successfully in a fresh Laravel 12 project on PHP 8.2, 8.3, and 8.4 — 0 dependency conflicts.
- **SC-002**: The full test suite passes on all 6 matrix combinations (3 PHP versions × 2 Laravel versions) with 0 test failures.
- **SC-003**: No existing tests are modified or removed to achieve compatibility — the test pass rate remains at 100% of the original test count.
- **SC-004**: A developer can generate a Postman collection on Laravel 12 in under the same time as on Laravel 11 — no performance regression.
- **SC-005**: `README.md` accurately lists Laravel 11–12 and PHP 8.2–8.4 as supported versions, verified by manual review.

## Assumptions

- The current package already supports PHP 8.2 via `^8.2` in `composer.json`; the primary gap is Laravel 12 support and CI matrix coverage.
- `orchestra/testbench` for Laravel 12 support requires version `^10.0`; the current `^9.0` only covers Laravel 11. This will need to be updated using Composer conditional overrides or a version range that satisfies both.
- The package's source code does not rely on internal Laravel APIs that changed breaking changes between Laravel 11 and 12; a quick audit at implementation time will confirm this assumption.
- No new features are being added — this is a pure compatibility update.
