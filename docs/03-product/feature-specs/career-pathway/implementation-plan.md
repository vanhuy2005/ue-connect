# Implementation Plan

## Phase 0: Product & Data Lock (Current)
- Consolidate documentation.
- Define canonical PRD, Data Contract, API Contract, Import Pipeline.
- Archive outdated notes.

## Phase 1: Data Foundation (COMPLETED)
1. **Migrations & Models:** Create tables for `cp_cohorts`, `cp_faculties`, `cp_majors`, `cp_programs`, `cp_semesters`, `cp_courses` based on `data-contract.md`.
2. **Enum Definitions:** Implement `ProgramStatus` enum.
3. **Import Command:** Build `CareerPathwayImportCommand` and `MarkdownParserService` based on `import-pipeline.md`.
4. **Data Sync:** Run the import command locally to populate the DB and test the Quality Gate logic.
5. **Data Audit:** Build `CareerPathwayAuditImportCommand` to ensure data completeness and integrity.

## Phase 2: Foundation Backend (COMPLETED)
### Step 1: Worktree Service & Cache
- [x] Create `CareerPathwayWorktreeService`.
- [x] Implement program visibility scope (`scopePublicReady`).
- [x] Cache worktree responses by program id.

### Step 2: Public APIs
- [x] Add public routes to `routes/web.php` (no `api/v1` prefix to match existing app conventions).
- [x] Create `CareerPathwayController`.
- [x] Implement JSON resources (`WorktreeResource`, etc.).
- [x] Test that non-public programs return 404.

### Step 3: Admin APIs
- [x] Create `CareerPathwayAdminController`.
- [x] Add admin routes using existing admin middleware.
- [x] Implement status update and import run trigger endpoints.

### Step 4: Feature Tests
- [x] Create factories for all Career Pathway models.
- [x] Write `PublicApiTest`.
- [x] Write `AdminApiTest`.

## Phase 3: Official Worktree UI
1. **Page Layout:** Build `/app/career-pathways` index page with filter UI.
2. **Worktree Visualization:** Build the visual tree component for a selected program.
3. **Course Detail Drawer:** Build a slide-out drawer to display course metadata and description.
4. **Integration:** Connect UI with Phase 2 APIs.

## Phase 4+: Future Scope
- Admin Dashboard for recovery (Phase 4).
- Community Knowledge Layer (Phase 5).
- Career Position Builder (Phase 6).
