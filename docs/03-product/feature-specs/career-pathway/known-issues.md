# Career Pathway Data Audit & Known Issues

## 1. Audit Summary

| Metric | Value |
|---|---:|
| PDF source files | 968 |
| Program directories in PDF source | 322 |
| Chuandaura PDFs | 321 |
| Chuongtrinhkhung PDFs | 319 |
| Quyetdinh PDFs | 320 |
| Generated roadmap.md files | 319 |
| Layer 1 import-ready programs | 278 |
| Clean programs, no missing course descriptions | 126 |
| Programs needing recovery before official worktree publish | 41 |

## 2. Known Issue Taxonomy

| Issue type | Count | Meaning |
|---|---:|---|
| `unresolved_semester_structure` | 16 | MD has content, but courses are collapsed into `Học kỳ 0`. |
| `empty_markdown` | 2 | Generated `roadmap.md` is empty although source PDF exists. |
| `missing_curriculum_pdf` | 3 | Program exists in source tree but has no `Chuongtrinhkhung` PDF. |
| `partial_semester_extraction` | 23 | Parser detected too few semesters, usually < 6. |
| `missing_course_descriptions` | 172 programs (3287 courses) | Course list exists, but detail drawer will be incomplete. |

## 3. Data Quality Gate Policy
Do not ship Career Pathway as if every markdown file is equally reliable.
- **P0 Blockers:** `unresolved_semester_structure`, `empty_markdown`, `missing_curriculum_pdf`. These must not be published to end-users until recovered.
- **P1 Issues:** `partial_semester_extraction`. Should be hidden or flagged.

*Note: You can verify these rules programmatically by running `php artisan career-pathway:audit-import` with the `--fail-on-mismatch` flag to ensure that no bad status programs are accidentally exposed as public.*
- **P2 Issues:** `missing_course_descriptions`. Can be published (with status `ready_with_missing_descriptions`), showing "Đang cập nhật" on the frontend.
