# Tasks: Laravel 12 & PHP 8.2–8.4 Compatibility

**Input**: Design documents from `specs/001-laravel12-php-support/`  
**Prerequisites**: plan.md ✅ | spec.md ✅ | research.md ✅ | data-model.md ✅ | quickstart.md ✅  
**Tests**: Not explicitly requested — no test tasks generated  
**Organization**: Tasks grouped by user story to enable independent delivery

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on each other)  
- **[Story]**: Which user story this task belongs to (US1, US2, US3)  
- Exact file paths included in every description

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare the dependency environment so all three user stories can be validated.  
No source code files exist to create. Setup is purely about updating the package manifest so Composer can resolve correctly.

- [X] T001 Update `composer.json` — change `illuminate/support`, `illuminate/http`, `illuminate/routing` from `^11.0` to `^11.0|^12.0` in `require` block in `composer.json`
- [X] T002 Update `composer.json` — change `pestphp/pest` from `^2.0` to `^3.0` in `require-dev` block in `composer.json`
- [X] T003 Update `composer.json` — change `orchestra/testbench` from `^9.0` to `^9.0|^10.0` in `require-dev` block in `composer.json`

**Checkpoint**: `composer.json` reflects all required constraint changes. Run `composer validate` to confirm the file is syntactically valid before proceeding.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Verify the updated `composer.json` actually resolves correctly for both Laravel versions before touching the CI pipeline or README. This phase **blocks all user-story work**.

**⚠️ CRITICAL**: Both installs below must succeed with zero conflicts before continuing.

- [X] T004 Run `composer update --prefer-dist --no-interaction` locally (or in WSL) and confirm resolution succeeds for the current Laravel 11 environment — no errors expected in root directory
- [X] T005 Run `composer require "laravel/framework:12.*" --no-interaction --no-update && composer update --prefer-dist --no-interaction` locally to simulate a Laravel 12 install and confirm resolution succeeds — run from root directory, then reset with `composer require "laravel/framework:^11.0"` after verifying

**Checkpoint**: Foundation ready — Composer resolves without conflicts for both Laravel 11 and Laravel 12. CI and README updates can now proceed.

---

## Phase 3: User Story 1 — Install Package on Laravel 12 (Priority: P1) 🎯 MVP

**Goal**: A developer on Laravel 12 + any supported PHP version (8.2, 8.3, 8.4) can `composer require` the package and run generation without errors.

**Independent Test**: Run `composer require laravelgenerators/postman-generator` in a clean Laravel 12 project and execute `php artisan postman:generate` — must complete without errors. (Can be tested locally after Phase 2 checkout confirms resolution.)

### Implementation for User Story 1

- [X] T006 [US1] Update CI matrix in `.github/workflows/tests.yml` — add `8.4` to the `php` array so the matrix reads `php: [8.2, 8.3, 8.4]`
- [X] T007 [US1] Update CI matrix in `.github/workflows/tests.yml` — add `12.*` to the `laravel` array so the matrix reads `laravel: [11.*, 12.*]`

**Checkpoint**: `.github/workflows/tests.yml` now defines 6 jobs (3 PHP × 2 Laravel). Push to branch and confirm all 6 CI jobs are triggered and pass.

---

## Phase 4: User Story 2 — CI/CD Pipeline Validates Multiple PHP Versions (Priority: P2)

**Goal**: Every pull request automatically runs 6 test jobs covering the full PHP × Laravel matrix, and any single failing combo clearly identifies itself.

**Independent Test**: Open a pull request — inspect the Actions tab and confirm 6 named jobs appear (e.g., "P8.2 - L11.*", "P8.4 - L12.*"). All must show green.

### Implementation for User Story 2

> **Note**: Phase 3 (T006, T007) already implements the matrix change. This phase validates and completes the CI story end-to-end.

- [X] T008 [US2] Verify the CI job naming in `.github/workflows/tests.yml` — confirm `name: P${{ matrix.php }} - L${{ matrix.laravel }}` is present and produces distinct, readable job names for all 6 combinations
- [X] T009 [US2] Confirm `fail-fast: false` is already set in `.github/workflows/tests.yml` — if missing, add it under `strategy:` so all 6 jobs always run to completion regardless of individual failures

**Checkpoint**: Push the branch. All 6 CI jobs must appear by distinct name in the GitHub Actions UI and pass. Any failing job clearly identifies its PHP + Laravel combination in its name.

---

## Phase 5: User Story 3 — Existing Laravel 11 Projects Are Unaffected (Priority: P3)

**Goal**: A developer on an existing Laravel 11 project can upgrade to this package version without any changes to their app or tests.

**Independent Test**: Run the full existing test suite (`vendor/bin/pest`) against Laravel 11 on PHP 8.2, 8.3, and 8.4 — all tests must pass with the same count as before this change.

### Implementation for User Story 3

> **Note**: No source code changes are needed (confirmed by audit in research.md). This phase completes the story via CI evidence.

- [X] T010 [US3] Confirm all previously passing tests still pass in CI jobs for `laravel: 11.*` rows across PHP 8.2, 8.3, and 8.4 — verify in GitHub Actions after the Phase 3 push
- [X] T011 [US3] Run `vendor/bin/pest` locally against the Laravel 11 environment after the `composer.json` update (`T001–T003`) to confirm 0 test regressions — run from root directory

**Checkpoint**: Laravel 11 CI rows all green; local test run shows the same pass count as before. User Story 3 is independently verified.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Documentation accuracy and final validation across all stories.

- [X] T012 [P] Update `README.md` — update the requirements / compatibility section to state supported Laravel versions as `^11.0 | ^12.0` in `README.md`
- [X] T013 [P] Update `README.md` — update the PHP version line to state supported versions as `^8.2` (8.2, 8.3, 8.4) in `README.md`
- [X] T014 Run the local quickstart validation from `specs/001-laravel12-php-support/quickstart.md` — execute both the Laravel 11 and Laravel 12 composer install scenarios to confirm end-to-end readiness
- [X] T015 Open a pull request from `001-laravel12-php-support` → `main` and confirm all 6 CI matrix jobs pass before merging

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 complete — **blocks Phases 3, 4, 5**
- **User Stories (Phases 3–5)**: All depend on Phase 2 completion
  - Phase 3 (US1) → must complete before Phase 4 validation tasks
  - Phase 4 (US2) → depends on Phase 3 (CI matrix must exist to validate)
  - Phase 5 (US3) → can run in parallel with Phases 3–4 (different validation angle)
- **Polish (Phase 6)**: Depends on Phases 3–5 all complete

### User Story Dependencies

- **US1 (P1)**: After Foundational — no dependency on US2 or US3
- **US2 (P2)**: After US1 (the matrix must exist from T006/T007 before US2 can be fully validated)
- **US3 (P3)**: After Foundational — fully independent of US1 and US2 (tests against L11 only)

### Within Each Phase

- T001, T002, T003 — all edit `composer.json`; execute **sequentially** (same file)
- T004, T005 — sequential; T005 requires T004 to pass first
- T006, T007 — both edit `tests.yml`; execute **sequentially** (same file)
- T008, T009 — read-only checks on `tests.yml`; can run in **parallel**
- T012, T013 — both edit `README.md`; execute **sequentially** (same file)

### Parallel Opportunities

- **Phase 5 (US3)** can start in parallel with **Phase 3 (US1)** after Foundational completes
- **T008** and **T009** (Phase 4) can run in parallel — both are read-verify tasks
- **T012** and **T013** are marked [P] but edit the same file; do them in one sitting

---

## Parallel Example: Phases 3 & 5 After Foundation

```
After Phase 2 completes:

  Thread A (US1):
    T006 → T007 (CI matrix update, sequential, same file)

  Thread B (US3 local validation):
    T011 (run pest locally against L11, independent)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete **Phase 1**: Update `composer.json` (T001–T003)
2. Complete **Phase 2**: Verify local Composer resolution (T004–T005)
3. Complete **Phase 3**: Update CI matrix (T006–T007)
4. **STOP and VALIDATE**: Push branch — confirm all 6 CI jobs appear and pass
5. If CI is green → US1 is delivered ✅

### Incremental Delivery

1. Phase 1 + 2 → Composer constraints correct and locally verified
2. Phase 3 → CI matrix expanded → push → confirm 6 green jobs (US1 ✅ MVP)
3. Phase 4 → CI naming/fail-fast verified → US2 ✅
4. Phase 5 → L11 regression confirmed clean → US3 ✅
5. Phase 6 → README updated, PR opened, all jobs green → merge ✅

### Solo Developer Strategy

Work sequentially T001 → T015. Each task is sub-5-minute with the research already done. Total estimated effort: **~30–45 minutes**.

---

## Notes

- [P] tasks = different files, no dependencies between them
- [Story] label maps each task to a specific user story for traceability
- **No source code changes** to `src/` or `tests/` are required for this feature
- All 3 `illuminate/*` constraint updates in T001 can be done in one editor session — they are listed as one task
- Commit after T003 (before Phase 2 verification), and again after T007 (before CI validation)
- After T005 always **reset** back to Laravel 11: `composer require "laravel/framework:^11.0" --no-update && composer update`
