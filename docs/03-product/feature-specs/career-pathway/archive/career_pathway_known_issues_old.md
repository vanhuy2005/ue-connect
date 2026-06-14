# Career Pathway Data Audit & Known Issues

Generated from:
- `HCMUE-db.zip`
- `HCMUE-db_md.zip`

## 1. Audit summary

| Metric | Value |
|---|---:|
| PDF source files | 968 |
| Program directories in PDF source | 322 |
| Chuandaura PDFs | 321 |
| Chuongtrinhkhung PDFs | 319 |
| Quyetdinh PDFs | 320 |
| Root-level student handbook PDFs | 8 |
| Generated roadmap.md files | 319 |
| `_summary.json` rows | 319 |
| Layer 1 import-ready programs, total_courses > 0 and semesters >= 6 | 278 |
| Clean programs, no missing course descriptions | 126 |
| Programs needing recovery before official worktree publish | 41 |

## 2. Known issue taxonomy

| Issue type | Severity | Count | Meaning |
|---|---:|---:|---|
| unresolved_semester_structure | P0 | 16 | MD has content, but all courses are collapsed into `Học kỳ 0`; not usable for semester worktree yet. |
| empty_markdown | P0 | 2 | Generated `roadmap.md` is empty although source PDF exists. |
| missing_curriculum_pdf | P0 | 3 | Program exists in source tree but has no `Chuongtrinhkhung` PDF, so no official roadmap can be generated. |
| partial_semester_extraction | P1 | 23 | Parser detected too few semesters, usually < 6, so output is likely incomplete or malformed. |
| missing_course_descriptions | P2 | 172 programs / 3287 course descriptions | Course list may exist, but detail drawer will be incomplete. |

## 3. P0: 16 files with unresolved semester structure

These are probably the “~16 files còn sót” you mentioned. They are not missing as files. They exist, but their roadmap is collapsed into `Học kỳ 0`, so they cannot become a semester worktree without recovery.

| Khóa | Khoa | Ngành | Học kỳ detect | Course headings trong MD | relative_dir |
| --- | --- | --- | --- | --- | --- |
| 2018 - Khoá 44 | Giáo dục Thể chất | Giáo dục Thể chất | Học kỳ 0 | 70.0 | 2018 - Khoá 44/Khoa/Giáo dục Thể chất/Ngành/Giáo dục Thể chất |
| 2018 - Khoá 44 | Tiếng Nga | Sư phạm tiếng Nga | Học kỳ 0 | 22.0 | 2018 - Khoá 44/Khoa/Tiếng Nga/Ngành/Sư phạm tiếng Nga |
| 2018 - Khoá 44 | Tiếng Nga | Ngôn ngữ Nga | Học kỳ 0 | 46.0 | 2018 - Khoá 44/Khoa/Tiếng Nga/Ngành/Ngôn ngữ Nga |
| 2018 - Khoá 44 | Tiếng Nhật | Ngôn ngữ Nhật | Học kỳ 0 | 79.0 | 2018 - Khoá 44/Khoa/Tiếng Nhật/Ngành/Ngôn ngữ Nhật |
| 2018 - Khoá 44 | Lịch sử | Sư phạm Lịch sử | Học kỳ 0 | 40.0 | 2018 - Khoá 44/Khoa/Lịch sử/Ngành/Sư phạm Lịch sử |
| 2018 - Khoá 44 | Tâm lý học | Tâm lý học | Học kỳ 0 | 82.0 | 2018 - Khoá 44/Khoa/Tâm lý học/Ngành/Tâm lý học |
| 2019 - Khoá 45 | Giáo dục Thể chất | Giáo dục Thể chất | Học kỳ 0 | 70.0 | 2019 - Khoá 45/Khoa/Giáo dục Thể chất/Ngành/Giáo dục Thể chất |
| 2019 - Khoá 45 | Tiếng Nga | Sư phạm Tiếng Nga | Học kỳ 0 | 22.0 | 2019 - Khoá 45/Khoa/Tiếng Nga/Ngành/Sư phạm Tiếng Nga |
| 2019 - Khoá 45 | Tiếng Nga | Ngôn ngữ Nga | Học kỳ 0 | 46.0 | 2019 - Khoá 45/Khoa/Tiếng Nga/Ngành/Ngôn ngữ Nga |
| 2019 - Khoá 45 | Tiếng Nhật | Ngôn ngữ Nhật | Học kỳ 0 | 79.0 | 2019 - Khoá 45/Khoa/Tiếng Nhật/Ngành/Ngôn ngữ Nhật |
| 2019 - Khoá 45 | Lịch sử | Sư phạm Lịch sử | Học kỳ 0 | 40.0 | 2019 - Khoá 45/Khoa/Lịch sử/Ngành/Sư phạm Lịch sử |
| 2019 - Khoá 45 | Tâm lý học | Tâm lý học | Học kỳ 0 | 82.0 | 2019 - Khoá 45/Khoa/Tâm lý học/Ngành/Tâm lý học |
| 2020 - Khoá 46 | Giáo dục Thể chất | Giáo dục Thể chất | Học kỳ 0 | 70.0 | 2020 - Khoá 46/Khoa/Giáo dục Thể chất/Ngành/Giáo dục Thể chất |
| 2020 - Khoá 46 | Giáo dục Chính trị | Giáo dục Chính trị | Học kỳ 0 | 17.0 | 2020 - Khoá 46/Khoa/Giáo dục Chính trị/Ngành/Giáo dục Chính trị |
| 2020 - Khoá 46 | Lịch sử | Sư phạm Lịch sử | Học kỳ 0 | 40.0 | 2020 - Khoá 46/Khoa/Lịch sử/Ngành/Sư phạm Lịch sử |
| 2021 - Khoá 47 | Giáo dục Chính trị | Giáo dục Chính trị | Học kỳ 0 | 17.0 | 2021 - Khoá 47/Khoa/Giáo dục Chính trị/Ngành/Giáo dục Chính trị |

## 4. P0: 2 empty markdown roadmaps

| Khóa | Khoa | Ngành | relative_dir | source_pdf |
| --- | --- | --- | --- | --- |
| 2025 - Khóa 51 | Công nghệ thông tin | Công nghệ giáo dục | 2025 - Khóa 51/Khoa/Công nghệ thông tin/Ngành/Công nghệ giáo dục | /content/HCMUE-db/2025 - Khóa 51/Khoa/Công nghệ thông tin/Ngành/Công nghệ giáo dục/Chuongtrinhkhung/02_Chuong_trinh_khung.pdf |
| 2025 - Khóa 51 | Toán - Tin | Toán ứng dụng | 2025 - Khóa 51/Khoa/Toán - Tin/Ngành/Toán ứng dụng | /content/HCMUE-db/2025 - Khóa 51/Khoa/Toán - Tin/Ngành/Toán ứng dụng/Chuongtrinhkhung/02_Chuong_trinh_khung.pdf |

## 5. P0: 3 program directories missing Chuongtrinhkhung

| Khóa | Khoa | Ngành | Doc hiện có | relative_dir |
| --- | --- | --- | --- | --- |
| 2021 - Khoá 47 | Giáo dục Mầm non | Giáo dục Mầm non | Chuandaura, Quyetdinh | 2021 - Khoá 47/Khoa/Giáo dục Mầm non/Ngành/Giáo dục Mầm non |
| 2021 - Khoá 47 | Ngữ văn | Sư phạm Ngữ văn | Chuandaura, Quyetdinh | 2021 - Khoá 47/Khoa/Ngữ văn/Ngành/Sư phạm Ngữ văn |
| 2023 - Khóa 49 | Tiếng Anh | Sư phạm tiếng Anh tiểu học | Quyetdinh | 2023 - Khóa 49/Khoa/Tiếng Anh/Ngành/Sư phạm tiếng Anh tiểu học |

## 6. P1: partial semester extraction

These files have courses, but the detected semester count is suspiciously low. They may still be usable as draft data, but should not be published as clean official roadmap.

| Khóa | Khoa | Ngành | Số HK detect | Môn detect | Thiếu mô tả | relative_dir |
| --- | --- | --- | --- | --- | --- | --- |
| 2018 - Khoá 44 | Tiếng Pháp | Ngôn ngữ Pháp - Biên phiên dịch | 1 | 27 | 14 | 2018 - Khoá 44/Khoa/Tiếng Pháp/Ngành/Ngôn ngữ Pháp - Biên phiên dịch |
| 2019 - Khoá 45 | Hóa học | Sư phạm khoa học tự nhiên | 2 | 79 | 11 | 2019 - Khoá 45/Khoa/Hóa học/Ngành/Sư phạm khoa học tự nhiên |
| 2019 - Khoá 45 | Giáo dục Quốc phòng | Giáo dục Quốc phòng - An ninh | 1 | 2 | 1 | 2019 - Khoá 45/Khoa/Giáo dục Quốc phòng/Ngành/Giáo dục Quốc phòng - An ninh |
| 2019 - Khoá 45 | Tiếng Pháp | Ngôn ngữ Pháp - Biên phiên dịch | 1 | 27 | 14 | 2019 - Khoá 45/Khoa/Tiếng Pháp/Ngành/Ngôn ngữ Pháp - Biên phiên dịch |
| 2020 - Khoá 46 | Công nghệ thông tin | Công nghệ thông tin | 1 | 75 | 2 | 2020 - Khoá 46/Khoa/Công nghệ thông tin/Ngành/Công nghệ thông tin |
| 2020 - Khoá 46 | Hóa học | Hoá học | 1 | 47 | 6 | 2020 - Khoá 46/Khoa/Hóa học/Ngành/Hoá học |
| 2020 - Khoá 46 | Hóa học | Sư phạm Khoa học tự nhiên | 2 | 79 | 11 | 2020 - Khoá 46/Khoa/Hóa học/Ngành/Sư phạm Khoa học tự nhiên |
| 2020 - Khoá 46 | Giáo dục Quốc phòng | Giáo dục Quốc phòng - An ninh | 3 | 8 | 4 | 2020 - Khoá 46/Khoa/Giáo dục Quốc phòng/Ngành/Giáo dục Quốc phòng - An ninh |
| 2020 - Khoá 46 | Tiếng Nga | Sư phạm Tiếng Nga | 5 | 11 | 1 | 2020 - Khoá 46/Khoa/Tiếng Nga/Ngành/Sư phạm Tiếng Nga |
| 2020 - Khoá 46 | Tiếng Nga | Ngôn ngữ Nga | 5 | 6 | 2 | 2020 - Khoá 46/Khoa/Tiếng Nga/Ngành/Ngôn ngữ Nga |
| 2020 - Khoá 46 | Toán - Tin | Sư phạm Toán học | 5 | 8 | 0 | 2020 - Khoá 46/Khoa/Toán - Tin/Ngành/Sư phạm Toán học |
| 2020 - Khoá 46 | Tiếng Pháp | Ngôn ngữ Pháp - Du lịch | 5 | 6 | 6 | 2020 - Khoá 46/Khoa/Tiếng Pháp/Ngành/Ngôn ngữ Pháp - Du lịch |
| 2020 - Khoá 46 | Tiếng Pháp | Ngôn ngữ Pháp - Biên phiên dịch | 5 | 6 | 6 | 2020 - Khoá 46/Khoa/Tiếng Pháp/Ngành/Ngôn ngữ Pháp - Biên phiên dịch |
| 2020 - Khoá 46 | Tiếng Pháp | Sư phạm Tiếng Pháp | 5 | 6 | 6 | 2020 - Khoá 46/Khoa/Tiếng Pháp/Ngành/Sư phạm Tiếng Pháp |
| 2021 - Khoá 47 | Công nghệ thông tin | Công nghệ thông tin | 1 | 75 | 2 | 2021 - Khoá 47/Khoa/Công nghệ thông tin/Ngành/Công nghệ thông tin |
| 2021 - Khoá 47 | Hóa học | Hoá học | 1 | 47 | 6 | 2021 - Khoá 47/Khoa/Hóa học/Ngành/Hoá học |
| 2021 - Khoá 47 | Hóa học | Sư phạm Khoa học tự nhiên | 2 | 79 | 11 | 2021 - Khoá 47/Khoa/Hóa học/Ngành/Sư phạm Khoa học tự nhiên |
| 2021 - Khoá 47 | Giáo dục Quốc phòng | Giáo dục Quốc phòng - An ninh | 3 | 8 | 4 | 2021 - Khoá 47/Khoa/Giáo dục Quốc phòng/Ngành/Giáo dục Quốc phòng - An ninh |
| 2021 - Khoá 47 | Tiếng Nga | Ngôn ngữ Nga | 5 | 6 | 2 | 2021 - Khoá 47/Khoa/Tiếng Nga/Ngành/Ngôn ngữ Nga |
| 2021 - Khoá 47 | Tiếng Nhật | Ngôn ngữ Nhật | 5 | 8 | 0 | 2021 - Khoá 47/Khoa/Tiếng Nhật/Ngành/Ngôn ngữ Nhật |
| 2021 - Khoá 47 | Toán - Tin | Sư phạm Toán học | 5 | 8 | 0 | 2021 - Khoá 47/Khoa/Toán - Tin/Ngành/Sư phạm Toán học |
| 2021 - Khoá 47 | Tiếng Pháp | Ngôn ngữ Pháp - Du lịch | 5 | 6 | 6 | 2021 - Khoá 47/Khoa/Tiếng Pháp/Ngành/Ngôn ngữ Pháp - Du lịch |
| 2021 - Khoá 47 | Tiếng Pháp | Ngôn ngữ Pháp - Biên phiên dịch | 5 | 6 | 6 | 2021 - Khoá 47/Khoa/Tiếng Pháp/Ngành/Ngôn ngữ Pháp - Biên phiên dịch |

## 7. Product decision

Career Pathway must treat data quality as a first-class product concept. Do not import all generated MD as equally trustworthy.

Recommended program status:

```text
ready
ready_with_missing_descriptions
partial_semester_extraction
unresolved_semester_structure
empty_extraction
missing_curriculum_pdf
excluded_non_program_document
```

Recommended UI behavior:

```text
ready                           -> publish normally
ready_with_missing_descriptions -> publish official course tree, badge “Thiếu mô tả môn”
partial_semester_extraction     -> hide from default listing or show warning
unresolved_semester_structure   -> hide from default listing until semester recovery
empty_extraction                -> hide; queue for re-extraction
missing_curriculum_pdf          -> show placeholder only if needed, with source issue
```

## 8. Updated feature plan

### Phase 0: Data Quality Gate

- Add `import_runs`, `source_documents`, and `data_quality_issues`.
- Import `_summary.json`.
- Classify every program by `program_status`.
- Build admin-only Known Issues page.
- Block P0 programs from public official worktree.

### Phase 1: Official Academic Roadmap

- Use only programs with `ready` or `ready_with_missing_descriptions`.
- Student selects `Khóa -> Khoa -> Ngành -> Chương trình`.
- Display visual worktree by semester.
- Course node shows official fields only.
- Course detail drawer must always display source PDF and import status.

### Phase 2: Community Knowledge Layer

- Users add skill, experience, project, resource, difficulty note, prerequisite suggestion, exam note, portfolio advice.
- Store contributions separately from official data.
- Add vote, report, moderation, and verified status.
- Never overwrite official curriculum fields.

### Phase 3: Career Position Builder

- Users create positions and role pathways.
- Position is built from Layer 1 official courses plus Layer 2 skills/projects/resources.
- Allow community-created positions, moderation, versioning, and duplication checks.

### Phase 4: Senior/Alumni Pathways

- Users publish personal pathway stories.
- Pathways attach to program, courses, skills, projects, and positions.
- Other students can save/follow pathways.

## 9. Non-negotiable rules

- Official PDF-derived data and community data must be physically separated in the database.
- Every official record must have source document reference.
- Every community edge must have author, status, votes, and moderation trail.
- AI can suggest, summarize, normalize, and detect duplicates, but cannot silently create official facts.
- Any prerequisite/career mapping that is not from official source must be labeled as community-suggested or AI-suggested.
