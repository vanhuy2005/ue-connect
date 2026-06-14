# Prompt for AI Agent: Create PRD for UE-Connect Career Pathway

You are a senior product manager and technical product owner working on UE-Connect, a student community platform for Ho Chi Minh City University of Education.

Your task is to create a complete PRD named:

`PRD_Career_Pathway.md`

This PRD will become the source of truth for the Career Pathway feature.

## Context

Career Pathway is the flagship feature of UE-Connect. It is not merely a static curriculum viewer. It is a community-powered academic and career roadmap platform.

The system has 3 layers:

### Layer 1: Official Academic Roadmap

Students select:

```text
Khóa -> Khoa -> Ngành -> Chương trình đào tạo
```

The system displays a visual worktree grouped by semester:

```text
Học kỳ 1 -> Học kỳ 2 -> ... -> Học kỳ 8
```

Each course node contains official data:

```text
Mã học phần
Tên học phần
Số tín chỉ
Bắt buộc / Tự chọn
Nhóm kiến thức, if available
Source PDF
Import status
```

This layer is official, source-backed, and read-only for normal users.

### Layer 2: Community Knowledge Layer

Users can contribute extra knowledge to courses, programs, semesters, skills, and career positions.

Contribution types include:

```text
skill
learning_experience
project_idea
resource
difficulty_note
prerequisite_suggestion
career_relevance
exam_note
portfolio_advice
```

Community data must never overwrite official curriculum data.

### Layer 3: Career Position Builder

Users can create career positions / role pathways, for example:

```text
Frontend Developer Intern
Backend Developer Intern
Giáo viên Tin học THPT
Chuyên viên tư vấn tâm lý học đường
Biên dịch viên tiếng Trung
Giáo viên Mầm non
Chuyên viên EdTech
```

A position is built from:

```text
official courses from Layer 1
skills/projects/resources from Layer 2
senior/alumni advice
external learning resources
portfolio suggestions
```

## Data audit facts

Use these audit facts in the PRD. Do not ignore them, because pretending dirty data does not exist is how software becomes archaeology with buttons.

Source data:

```text
PDF source files: 968
Program directories in PDF source: 322
Chuandaura PDFs: 321
Chuongtrinhkhung PDFs: 319
Quyetdinh PDFs: 320
Root-level student handbook PDFs: 8
Generated roadmap.md files: 319
_summary.json rows: 319
```

Layer 1 readiness:

```text
Programs ready for Layer 1 import, total_courses > 0 and semesters >= 6: 278
Clean programs with no missing course descriptions: 126
Programs needing recovery before official worktree publish: 41
```

Known issue categories:

```text
unresolved_semester_structure: 16
empty_markdown: 2
missing_curriculum_pdf: 3
partial_semester_extraction: 23
missing_course_descriptions: 172 programs / 3287 missing course descriptions
```

Important P0 known issues:

### 16 unresolved semester structure files

These files exist and have extracted content, but all courses are collapsed into `Học kỳ 0`. They must not be published as clean official semester worktrees until recovered.

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

### 2 empty markdown files

| Khóa | Khoa | Ngành | relative_dir | source_pdf |
| --- | --- | --- | --- | --- |
| 2025 - Khóa 51 | Công nghệ thông tin | Công nghệ giáo dục | 2025 - Khóa 51/Khoa/Công nghệ thông tin/Ngành/Công nghệ giáo dục | /content/HCMUE-db/2025 - Khóa 51/Khoa/Công nghệ thông tin/Ngành/Công nghệ giáo dục/Chuongtrinhkhung/02_Chuong_trinh_khung.pdf |
| 2025 - Khóa 51 | Toán - Tin | Toán ứng dụng | 2025 - Khóa 51/Khoa/Toán - Tin/Ngành/Toán ứng dụng | /content/HCMUE-db/2025 - Khóa 51/Khoa/Toán - Tin/Ngành/Toán ứng dụng/Chuongtrinhkhung/02_Chuong_trinh_khung.pdf |

### 3 program directories missing Chuongtrinhkhung

| Khóa | Khoa | Ngành | Doc hiện có | relative_dir |
| --- | --- | --- | --- | --- |
| 2021 - Khoá 47 | Giáo dục Mầm non | Giáo dục Mầm non | Chuandaura, Quyetdinh | 2021 - Khoá 47/Khoa/Giáo dục Mầm non/Ngành/Giáo dục Mầm non |
| 2021 - Khoá 47 | Ngữ văn | Sư phạm Ngữ văn | Chuandaura, Quyetdinh | 2021 - Khoá 47/Khoa/Ngữ văn/Ngành/Sư phạm Ngữ văn |
| 2023 - Khóa 49 | Tiếng Anh | Sư phạm tiếng Anh tiểu học | Quyetdinh | 2023 - Khóa 49/Khoa/Tiếng Anh/Ngành/Sư phạm tiếng Anh tiểu học |

## Required PRD structure

Write the PRD in Vietnamese.

The PRD must include these sections:

1. Product summary
   - What Career Pathway is
   - Why it is a flagship feature
   - One-paragraph positioning statement

2. Problem statement
   - Students cannot easily understand official curricula
   - PDF/program pages are hard to navigate
   - Students do not know what each course is useful for
   - Career mapping across all majors cannot be manually maintained by the developer

3. Goals
   - Official curriculum visualization
   - Community-powered course knowledge
   - User-generated career positions
   - Senior/alumni pathway sharing
   - Data quality transparency

4. Non-goals
   - Do not manually build full career mapping for every HCMUE major
   - Do not let AI hallucinate official facts
   - Do not replace official academic advising
   - Do not allow community edits to overwrite official curriculum data

5. User personas
   - First-year student
   - Current student
   - Senior student
   - Alumni
   - Moderator/admin
   - Optional: lecturer/faculty expert

6. Data model concept
   Include official data tables:
   ```text
   cohorts
   faculties
   majors
   programs
   semesters
   courses
   program_courses
   source_documents
   import_runs
   data_quality_issues
   ```

   Include community data tables:
   ```text
   contributions
   skills
   course_skill_edges
   career_positions
   position_requirements
   user_pathways
   user_pathway_items
   votes
   reports
   moderation_logs
   ```

7. Data quality statuses
   Must include:
   ```text
   ready
   ready_with_missing_descriptions
   partial_semester_extraction
   unresolved_semester_structure
   empty_extraction
   missing_curriculum_pdf
   excluded_non_program_document
   ```

8. Functional requirements

   Layer 1:
   - Program selector
   - Official worktree visualization
   - Course detail drawer
   - Source PDF reference
   - Import status badge
   - Known issue handling

   Layer 2:
   - Create contribution
   - Contribution list
   - Upvote/downvote
   - Report
   - Moderation status
   - Verified community summaries

   Layer 3:
   - Create career position
   - Attach courses
   - Attach skills
   - Attach project ideas
   - Attach resources
   - Publish pathway

   Senior pathway:
   - Create personal pathway
   - Attach to program and position
   - Add semester-based advice
   - Save/follow pathway

9. UX requirements
   - Worktree style
   - Official-only mode
   - Community overlay toggles
   - Course drawer
   - Data quality warnings
   - Empty/partial program states
   - Mobile-first PWA-friendly layout

10. API requirements
    Propose REST endpoints, for example:
    ```text
    GET /api/v1/career-pathway/filters
    GET /api/v1/career-pathway/programs
    GET /api/v1/career-pathway/programs/<built-in function id>
    GET /api/v1/career-pathway/programs/<built-in function id>/worktree
    GET /api/v1/career-pathway/courses/<built-in function id>
    POST /api/v1/career-pathway/contributions
    POST /api/v1/career-pathway/contributions/<built-in function id>/vote
    POST /api/v1/career-pathway/contributions/<built-in function id>/report
    GET /api/v1/career-pathway/positions
    POST /api/v1/career-pathway/positions
    GET /api/v1/career-pathway/positions/<built-in function id>
    POST /api/v1/career-pathway/user-pathways
    ```

11. Moderation and trust
    - Contribution lifecycle
    - Report flow
    - Moderator review
    - Verified contribution rules
    - Reputation/badges
    - Abuse prevention

12. AI usage policy
    AI may:
    - suggest skills
    - normalize duplicate terms
    - summarize community notes
    - detect duplicate positions
    - help draft contributions

    AI must not:
    - silently create official facts
    - mark prerequisites as official without source
    - overwrite PDF-derived data
    - publish career mappings without status label

13. Analytics
    Define product events:
    ```text
    career_pathway_viewed
    program_selected
    course_opened
    contribution_created
    contribution_upvoted
    position_created
    pathway_saved
    known_issue_viewed
    ```

14. Rollout plan
    - Phase 0: Data quality gate
    - Phase 1: Official roadmap
    - Phase 2: Course contributions
    - Phase 3: Position builder
    - Phase 4: Senior/alumni pathways
    - Phase 5: AI-assisted advisor, only after RAG/source grounding exists

15. Acceptance criteria
    Include testable statements for:
    - Official data separation
    - Worktree rendering
    - Known issue display
    - Contribution creation
    - Moderation
    - Position creation
    - Source traceability

16. Open questions
    Include questions about:
    - Who can moderate?
    - Whether alumni verification exists
    - Whether source PDFs are public links or locally stored
    - Whether data should support multi-version programs
    - Whether course equivalence across cohorts should be normalized

17. Appendix
    - Known issue table
    - Glossary
    - Example worktree
    - Example contribution
    - Example career position

## Product decisions that must be reflected

- Career Pathway is a platform, not a manually curated mega-roadmap.
- Layer 1 is official and read-only.
- Layer 2 and Layer 3 are community-powered.
- Bad data must be visible to admin and safely hidden or warned in public UI.
- The MVP should launch with ready programs only, not blocked by dirty edge cases.
- The PRD must be specific enough for designers, backend developers, frontend developers, moderators, and AI agents to use as source of truth.

## Output requirement

Create only the PRD markdown file:

`PRD_Career_Pathway.md`

Do not implement code in this task.
Do not invent additional data not present in the audit.
Where details are unknown, add them to Open Questions instead of guessing.
