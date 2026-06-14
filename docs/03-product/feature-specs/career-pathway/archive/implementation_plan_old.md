# Tổng roadmap phát triển Career Pathway

| Phase | Tên phase                               | Mục tiêu                                           |
| ----: | --------------------------------------- | -------------------------------------------------- |
|     0 | Product & Data Lock                     | Chốt PRD, scope, data status, known issues         |
|     1 | Data Foundation                         | Thiết kế schema official + import pipeline         |
|     2 | Official Roadmap Backend                | API đọc chương trình khung theo khóa/khoa/ngành    |
|     3 | Official Worktree UI                    | UI visualize học kỳ và môn học                     |
|     4 | Data Quality Recovery                   | Admin dashboard xử lý file lỗi, partial, missing   |
|     5 | Community Knowledge Layer               | Người dùng đóng góp skill, note, project, resource |
|     6 | Career Position Builder                 | Người dùng tự tạo role/pathway nghề nghiệp         |
|     7 | Senior / Alumni Pathway                 | Người đi trước chia sẻ lộ trình cá nhân            |
|     8 | Search & Discovery                      | Search môn, chương trình, skill, position          |
|     9 | AI Assisted Enrichment                  | AI gợi ý, gom nhóm, tóm tắt, không ghi đè official |
|    10 | Analytics, Moderation, Launch Hardening | Tracking, performance, trust, production polish    |

---

# Phase 0: Product & Data Lock

## Mục tiêu

Biến toàn bộ ý tưởng Career Pathway thành **source of truth** trước khi code. Không có phase này thì AI Agent sẽ code rất chăm, rất nhanh, và rất có khả năng xây một cái lâu đài bằng bìa carton.

## Việc cần làm

Chốt rõ 3 lớp:

```text
Layer 1: Official Academic Roadmap
Layer 2: Community Knowledge Layer
Layer 3: Career Position Builder
```

Chốt trạng thái dữ liệu:

```text
ready
ready_with_missing_descriptions
partial_semester_extraction
unresolved_semester_structure
empty_extraction
missing_curriculum_pdf
excluded_non_program_document
```

Chốt scope MVP:

```text
MVP chỉ public các chương trình có status:
- ready
- ready_with_missing_descriptions
```

Không public:

```text
- unresolved_semester_structure
- partial_semester_extraction
- empty_extraction
- missing_curriculum_pdf
```

## Deliverables

```text
docs/PRD_Career_Pathway.md
docs/career_pathway_known_issues.md
docs/career_pathway_data_contract.md
docs/career_pathway_api_contract.md
```

## Definition of Done

Phase này xong khi:

```text
- Có PRD rõ ràng
- Có danh sách known issues
- Có data status chuẩn
- Có MVP scope
- Có non-goals
- Có acceptance criteria
```

---

# Phase 1: Data Foundation

## Mục tiêu

Tạo nền dữ liệu sạch cho Layer 1. Đây là phase quan trọng nhất. UI xấu còn sửa được, data model nát thì chỉ còn cách chôn cất.

## Database official layer

Nên có các bảng:

```text
import_runs
source_documents
cohorts
faculties
majors
programs
semesters
courses
program_courses
course_descriptions
data_quality_issues
```

## Schema gợi ý

### import_runs

Lưu mỗi lần import dữ liệu.

```text
id
name
source_type
started_at
finished_at
status
total_files
successful_files
failed_files
metadata_json
created_by
```

### source_documents

Lưu PDF / markdown gốc.

```text
id
import_run_id
document_type
original_file_name
normalized_file_name
source_path
markdown_path
cohort_name
faculty_name
major_name
program_name
extraction_status
checksum
metadata_json
```

`document_type`:

```text
curriculum_framework
learning_outcome
decision
unknown
```

### cohorts

```text
id
name
year
display_name
normalized_name
```

Ví dụ:

```text
Khóa 51
2025
Khóa 51 (2025)
```

### faculties

```text
id
name
normalized_name
```

### majors

```text
id
faculty_id
name
normalized_name
```

### programs

```text
id
cohort_id
faculty_id
major_id
source_document_id
name
education_level
training_type
total_credits
duration_years
status
metadata_json
```

### semesters

```text
id
program_id
semester_number
name
order_index
```

### courses

```text
id
code
name
normalized_name
description
metadata_json
```

### program_courses

```text
id
program_id
semester_id
course_id
course_code_raw
course_name_raw
credits
course_type
knowledge_group
is_required
order_index
metadata_json
```

### data_quality_issues

```text
id
source_document_id
program_id
issue_type
severity
message
raw_context
status
resolved_by
resolved_at
```

## Import pipeline

Pipeline nên đi như này:

```text
Markdown files
→ Parse folder metadata
→ Parse program metadata
→ Parse semester sections
→ Parse course rows
→ Validate result
→ Assign program status
→ Store official records
→ Store data quality issues
```

## Validation rules

```text
Program phải có cohort, faculty, major
Program phải có ít nhất 1 source document
Ready program nên có >= 6 học kỳ
Mỗi course nên có code hoặc name
Course credits nên là số hợp lệ
Semester number phải parse được
Không được silently import Học kỳ 0 thành dữ liệu official sạch
```

## Backend services

```text
CareerPathwayImportService
CurriculumMarkdownParser
CurriculumValidationService
ProgramStatusResolver
CourseNormalizer
SourceDocumentService
```

## Commands

```bash
php artisan career-pathway:import-md storage/app/hcmue-md
php artisan career-pathway:validate
php artisan career-pathway:rebuild-status
```

## Definition of Done

```text
- Import được toàn bộ markdown
- Mỗi program có status rõ ràng
- Các file lỗi vào data_quality_issues
- Không mất source path
- Không merge nhầm official và community data
```

---

# Phase 2: Official Roadmap Backend

## Mục tiêu

Tạo API ổn định để frontend đọc Layer 1.

## Public API

```http
GET /api/v1/career-pathway/filters
GET /api/v1/career-pathway/cohorts
GET /api/v1/career-pathway/faculties
GET /api/v1/career-pathway/majors
GET /api/v1/career-pathway/programs
GET /api/v1/career-pathway/programs/{program}
GET /api/v1/career-pathway/programs/{program}/worktree
GET /api/v1/career-pathway/courses/{course}
```

## Admin API

```http
GET /api/v1/admin/career-pathway/import-runs
GET /api/v1/admin/career-pathway/source-documents
GET /api/v1/admin/career-pathway/data-quality-issues
PATCH /api/v1/admin/career-pathway/programs/{program}/status
POST /api/v1/admin/career-pathway/import-runs
```

## Response mẫu cho worktree

```json
{
  "program": {
    "id": 1,
    "name": "Công nghệ thông tin",
    "cohort": "Khóa 51 (2025)",
    "faculty": "Công nghệ Thông tin",
    "major": "Công nghệ thông tin",
    "status": "ready_with_missing_descriptions"
  },
  "semesters": [
    {
      "id": 10,
      "semester_number": 1,
      "name": "Học kỳ 1",
      "courses": [
        {
          "id": 100,
          "code": "COMP1001",
          "name": "Nhập môn lập trình",
          "credits": 3,
          "course_type": "Bắt buộc",
          "knowledge_group": "Cơ sở ngành",
          "is_required": true
        }
      ]
    }
  ],
  "source_documents": [],
  "quality_warnings": []
}
```

## Logic quan trọng

Public API chỉ trả về:

```text
ready
ready_with_missing_descriptions
```

Nếu admin gọi thì có thể thấy mọi status.

## Definition of Done

```text
- Frontend có thể chọn khóa/khoa/ngành/program
- API trả worktree đúng học kỳ
- Program lỗi không bị public nhầm
- Course detail có source document
- Có cache cho worktree response
```

---

# Phase 3: Official Worktree UI

## Mục tiêu

Làm bản MVP đầu tiên dùng được.

User phải làm được:

```text
Chọn Khóa → Khoa → Ngành → Chương trình đào tạo
Xem chương trình theo học kỳ
Click môn để xem chi tiết
Xem source / trạng thái dữ liệu
```

## Pages

```text
CareerPathwayPage
ProgramExplorerPage
ProgramWorktreePage
CourseDetailPage hoặc Drawer
```

## Components

```text
ProgramSelector
CareerPathwayFilters
CurriculumWorktree
SemesterNode
CourseNode
CourseDetailDrawer
ProgramSummaryCard
DataQualityBadge
SourceDocumentCard
EmptyState
```

## UI layout đề xuất

```text
Top:
[Khóa] [Khoa] [Ngành] [Chương trình]

Summary:
Tổng tín chỉ | Số học kỳ | Số môn | Bắt buộc | Tự chọn | Status

Main:
Worktree theo học kỳ

Right Drawer:
Chi tiết môn học
```

## Worktree behavior

Không show mọi thứ cùng lúc. Làm như sau:

```text
Program
├── Học kỳ 1
│   ├── Course card
│   ├── Course card
│   └── Course card
├── Học kỳ 2
│   └── ...
```

Mỗi course card hiển thị:

```text
Mã học phần
Tên học phần
Số tín chỉ
Bắt buộc / Tự chọn
Nhóm kiến thức
```

## Drawer course detail

```text
Official Info
- Tên học phần
- Mã học phần
- Số tín chỉ
- Loại học phần
- Nhóm kiến thức
- Học kỳ đề xuất
- Thuộc chương trình nào
- Source document

Community Info
- Coming soon ở phase 5
```

## Mobile behavior

Mobile không nên render graph kiểu desktop rồi bắt người dùng pinch zoom như đang đọc bản đồ kho báu.

Mobile nên dùng:

```text
Accordion theo học kỳ
Course card dạng list
Bottom sheet cho course detail
Sticky filter compact
```

## Definition of Done

```text
- User xem được official roadmap
- UI responsive
- Worktree không lag với program nhiều môn
- Course detail mở nhanh
- Có warning nếu program thiếu mô tả môn
```

Đây là **MVP launchable**. Làm tới phase này là bạn đã có feature đem demo được.

---

# Phase 4: Data Quality Recovery

## Mục tiêu

Xử lý nhóm dữ liệu lỗi mà không làm bẩn public product.

## Known issue cần support

```text
unresolved_semester_structure
empty_extraction
missing_curriculum_pdf
partial_semester_extraction
missing_course_descriptions
```

## Admin dashboard

Cần có trang:

```text
AdminCareerPathwayDashboard
ImportRunDetailPage
DataQualityIssueList
ProgramRecoveryPage
SourceDocumentViewer
```

## Recovery workflow

### Với unresolved_semester_structure

```text
1. Admin mở file roadmap.md
2. Xem raw markdown
3. Chọn rule recovery
4. Split lại semester
5. Preview worktree
6. Approve reimport
```

### Với empty_extraction

```text
1. Đánh dấu cần re-extract
2. Upload markdown mới hoặc chạy lại parser
3. Validate
4. Update status
```

### Với missing_curriculum_pdf

```text
1. Program vẫn tồn tại dưới dạng source incomplete
2. Không public worktree
3. Admin gắn curriculum PDF / markdown khi có
```

### Với missing_course_descriptions

```text
1. Không block public
2. Hiển thị warning nhẹ
3. Cho phép bổ sung sau từ chuẩn đầu ra hoặc community
```

## Definition of Done

```text
- Admin biết chính xác program nào lỗi
- Không cần mò folder thủ công
- Có thể reimport một program riêng lẻ
- Có audit log cho thay đổi status
```

---

# Phase 5: Community Knowledge Layer

## Mục tiêu

Biến mỗi môn học thành một knowledge page sống.

Người dùng có thể đóng góp:

```text
Skill
Kinh nghiệm học
Project gợi ý
Tài liệu
Mức độ khó
Môn nên học trước
Liên quan nghề nghiệp
Ghi chú thi / đồ án
Portfolio advice
```

## Database

```text
contributions
contribution_votes
contribution_reports
contribution_moderation_logs
skills
course_skill_edges
resources
project_ideas
```

Có thể làm gọn bằng `contributions` polymorphic trước, sau này tách bảng chuyên biệt.

### contributions

```text
id
user_id
target_type
target_id
contribution_type
title
content
status
visibility
source_type
upvotes_count
downvotes_count
reports_count
verified_at
verified_by
metadata_json
```

`target_type`:

```text
course
program
semester
career_position
```

`contribution_type`:

```text
skill
experience
project_idea
resource
difficulty_note
prerequisite_suggestion
career_relevance
exam_note
portfolio_advice
```

## API

```http
GET /api/v1/career-pathway/courses/{course}/contributions
POST /api/v1/career-pathway/courses/{course}/contributions
POST /api/v1/career-pathway/contributions/{contribution}/vote
POST /api/v1/career-pathway/contributions/{contribution}/report
PATCH /api/v1/admin/career-pathway/contributions/{contribution}/moderate
```

## UI

Trong Course Detail Drawer thêm tabs:

```text
Official
Skills
Projects
Resources
Experiences
Career Links
```

## Moderation rule

Ban đầu nên làm đơn giản:

```text
Personal notes: publish ngay nhưng gắn nhãn community
Summary / featured knowledge: chỉ lấy approved hoặc verified
```

Nghĩa là người dùng có nội dung ngay, nhưng phần “tri thức chính” không bị biến thành bãi đáp của mọi ý kiến random.

## Definition of Done

```text
- User thêm contribution được
- User vote/report được
- Admin duyệt/ẩn được
- Official data không bị ghi đè
- Course detail hiển thị official + community tách biệt
```

---

# Phase 6: Career Position Builder

## Mục tiêu

Cho cộng đồng tự tạo các position/pathway nghề nghiệp thay vì bạn phải tự mapping toàn trường. Đây là quyết định đúng, vì nếu không bạn sẽ dành phần đời còn lại để trả lời “ngành Giáo dục Quốc phòng thì mapping nghề gì?”. Một câu hỏi đáng kính, nhưng không nên để một mình bạn gánh.

## Database

```text
career_positions
position_requirements
position_sections
position_items
position_saves
```

### career_positions

```text
id
created_by
title
slug
description
industry
target_audience
related_faculty_id
related_major_id
related_program_id
status
visibility
published_at
metadata_json
```

### position_requirements

```text
id
position_id
requirement_type
target_type
target_id
importance_level
explanation
order_index
```

`requirement_type`:

```text
course
skill
project
resource
certificate
experience
advice
```

## API

```http
GET /api/v1/career-pathway/positions
POST /api/v1/career-pathway/positions
GET /api/v1/career-pathway/positions/{position}
PATCH /api/v1/career-pathway/positions/{position}
POST /api/v1/career-pathway/positions/{position}/publish
POST /api/v1/career-pathway/positions/{position}/items
DELETE /api/v1/career-pathway/positions/{position}/items/{item}
```

## UI

```text
PositionListPage
PositionDetailPage
PositionBuilderPage
PositionCoursePicker
PositionSkillPicker
PositionProjectPicker
PositionResourceEditor
```

## Builder flow

```text
1. Tạo position
2. Chọn ngành/khoa liên quan
3. Chọn các môn từ Layer 1
4. Chọn skill từ Layer 2
5. Thêm project/resource/advice
6. Preview pathway
7. Publish
```

## Ví dụ output

```text
Frontend Developer Intern
├── Môn trong trường nên chú ý
│   ├── Nhập môn lập trình
│   ├── Cấu trúc dữ liệu và giải thuật
│   ├── Cơ sở dữ liệu
│   └── Lập trình Web
├── Skill cần có
│   ├── HTML/CSS
│   ├── JavaScript
│   ├── React
│   └── Git
├── Project nên làm
│   ├── Portfolio cá nhân
│   ├── Dashboard quản lý dữ liệu
│   └── App realtime notification
└── Advice từ người đi trước
```

## Definition of Done

```text
- User tạo position được
- User gắn course/skill/project/resource được
- Position có public page
- Người khác save/follow được
- Có report/moderation cơ bản
```

---

# Phase 7: Senior / Alumni Pathway Sharing

## Mục tiêu

Cho người đi trước kể lại hành trình thật.

Khác với Position Builder:

```text
Position Builder = lộ trình theo vai trò
Senior Pathway = câu chuyện cá nhân theo chương trình học
```

## Database

```text
user_pathways
user_pathway_items
user_pathway_saves
user_pathway_comments
```

### user_pathways

```text
id
user_id
title
program_id
career_position_id
story
status
visibility
published_at
```

### user_pathway_items

```text
id
pathway_id
item_type
target_type
target_id
semester_number
title
note
order_index
metadata_json
```

## UI

```text
SeniorPathwayList
SeniorPathwayDetail
CreateSeniorPathwayWizard
PathwayTimelineEditor
```

## User flow

```text
1. Chọn chương trình đã học
2. Chọn career position hiện tại hoặc mục tiêu
3. Kể story tổng quan
4. Thêm note theo từng học kỳ
5. Gắn môn/project/resource
6. Publish
```

## Definition of Done

```text
- User tạo lộ trình cá nhân
- Người khác đọc/save được
- Lộ trình gắn với program và position
- Có moderation/report
```

---

# Phase 8: Search & Discovery

## Mục tiêu

Khi dữ liệu lớn lên, user phải tìm được thứ cần tìm.

## Search targets

```text
Programs
Courses
Skills
Contributions
Career Positions
Senior Pathways
Resources
Projects
```

## Tech gợi ý

Nếu đang Laravel:

```text
Laravel Scout + Meilisearch
```

Không thì PostgreSQL full-text cũng đủ cho bản đầu. Đừng vội dựng Elasticsearch nếu team chưa cần, vì Elasticsearch là một con thú cưng ăn RAM và lòng tự trọng.

## Search UI

```text
Global search bar
Filter by cohort
Filter by faculty
Filter by major
Filter by content type
Filter by status
```

## API

```http
GET /api/v1/career-pathway/search?q=
GET /api/v1/career-pathway/courses/search?q=
GET /api/v1/career-pathway/positions/search?q=
```

## Definition of Done

```text
- Search được course theo mã và tên
- Search được program theo khóa/khoa/ngành
- Search được position
- Có filter rõ ràng
- Không trả dữ liệu unreleased/bad-status ra public
```

---

# Phase 9: AI Assisted Enrichment

## Mục tiêu

Dùng AI để hỗ trợ cộng đồng và admin, không dùng AI làm nguồn sự thật. Nhắc lại vì đây là chỗ sản phẩm rất dễ biến thành máy phát minh dữ liệu.

## AI use cases hợp lý

```text
Gợi ý skill từ tên môn và mô tả
Gom nhóm contribution trùng
Tóm tắt nhiều experience notes
Gợi ý project idea
Gợi ý career relevance dạng draft
Phát hiện duplicate skill/position
Viết lại contribution cho rõ ràng hơn
```

## Không được làm

```text
Không tự tạo official course
Không tự sửa số tín chỉ
Không tự đoán học kỳ
Không tự tạo prerequisite như sự thật
Không overwrite dữ liệu từ PDF
```

## Database bổ sung

```text
ai_suggestions
ai_suggestion_reviews
```

### ai_suggestions

```text
id
target_type
target_id
suggestion_type
content_json
model_name
prompt_version
confidence_score
status
created_by
reviewed_by
reviewed_at
```

## AI workflow

```text
AI suggests
→ Admin/User reviews
→ Approved contribution hoặc edge được tạo
```

## Definition of Done

```text
- AI suggestion luôn có status
- Không có AI suggestion nào thành official fact tự động
- Có review queue
- Có prompt version
```

---

# Phase 10: Analytics, Moderation, Launch Hardening

## Mục tiêu

Đưa feature từ “chạy được trên máy tôi” sang “chịu được người dùng thật”. Một bước tiến vĩ đại mà nhân loại phần mềm thường đánh giá thấp.

## Analytics events

```text
career_pathway_viewed
program_filter_changed
program_worktree_viewed
course_detail_opened
source_document_clicked
contribution_created
contribution_voted
contribution_reported
position_created
position_published
position_saved
senior_pathway_published
search_performed
```

## Moderation

Cần có:

```text
Report content
Hide contribution
Approve / reject
Verify
Audit log
User reputation basic
Rate limit contribution
```

## Performance

Cần:

```text
Cache program worktree
Cache filter options
Lazy load course contributions
Paginate contribution list
Index search fields
Precompute summary stats
```

## Security

```text
Policy cho create/update/delete contribution
Policy cho position editing
Admin-only import/recovery
Rate limit write APIs
Sanitize markdown/user content
Prevent XSS trong resource links
```

## Testing

```text
Parser unit tests
Import service tests
Program status tests
API feature tests
Policy tests
Contribution moderation tests
Position builder tests
Frontend component tests
E2E happy path
```

## Definition of Done

```text
- Có analytics cơ bản
- Có moderation queue
- Có cache
- Có test coverage cho flow chính
- Public UI không lộ program lỗi
- Feature đủ ổn để launch beta
```

---

# Release plan thực tế

## Release 1: Official Roadmap MVP

Gồm:

```text
Phase 0
Phase 1
Phase 2
Phase 3
```

Thành phẩm:

```text
Sinh viên chọn Khóa → Khoa → Ngành → Chương trình
Xem được worktree học kỳ
Click môn xem chi tiết
Có source document
Có warning nếu thiếu dữ liệu
```

Đây là bản nên launch đầu tiên.

---

## Release 2: Data Recovery + Admin QA

Gồm:

```text
Phase 4
```

Thành phẩm:

```text
Admin dashboard kiểm tra dữ liệu
Biết program nào lỗi
Reimport/recovery từng program
Theo dõi known issues
```

---

## Release 3: Community Course Knowledge

Gồm:

```text
Phase 5
```

Thành phẩm:

```text
Người dùng đóng góp skill/note/project/resource vào môn học
Có vote/report/moderation
Course detail bắt đầu có tri thức cộng đồng
```

---

## Release 4: Career Position Builder

Gồm:

```text
Phase 6
```

Thành phẩm:

```text
Người dùng tự tạo lộ trình nghề nghiệp
Gắn môn học + skill + project + resource
Public position page
```

---

## Release 5: Senior Pathway + Search

Gồm:

```text
Phase 7
Phase 8
```

Thành phẩm:

```text
Người đi trước chia sẻ lộ trình
Sinh viên search được môn, ngành, position, pathway
```

---

## Release 6: AI + Production Hardening

Gồm:

```text
Phase 9
Phase 10
```

Thành phẩm:

```text
AI hỗ trợ enrich data
Có analytics, moderation, cache, test, launch beta
```

---

# Thứ tự implementation cụ thể cho AI Agent

## Sprint 1: PRD + Schema

```text
1. Tạo PRD_Career_Pathway.md
2. Tạo migrations official layer
3. Tạo models và relationships
4. Tạo enum/status constants
5. Tạo data quality issue system
```

## Sprint 2: Import Pipeline

```text
1. Tạo Markdown parser
2. Tạo import command
3. Parse cohort/faculty/major từ folder
4. Parse semester/course từ markdown
5. Validate data
6. Save import run và source documents
7. Save data quality issues
```

## Sprint 3: Official API

```text
1. API filters
2. API programs
3. API worktree
4. API course detail
5. Admin API import runs
6. Admin API data issues
7. Resource/Transformer classes
```

## Sprint 4: Official UI

```text
1. CareerPathwayPage
2. ProgramSelector
3. CurriculumWorktree
4. SemesterNode
5. CourseNode
6. CourseDetailDrawer
7. SourceDocumentCard
8. Responsive mobile layout
```

## Sprint 5: Admin Data QA

```text
1. Admin dashboard
2. Import run detail
3. Data quality issue table
4. Program status editor
5. Reimport single program
6. Recovery preview
```

## Sprint 6: Community Contribution

```text
1. Contribution migrations
2. Contribution CRUD API
3. Vote API
4. Report API
5. Moderation API
6. ContributionList UI
7. ContributionForm UI
8. Add contribution tabs in course drawer
```

## Sprint 7: Career Position Builder

```text
1. Position migrations
2. Position CRUD API
3. Position requirement API
4. PositionBuilder UI
5. Course/skill picker
6. Position detail public page
7. Publish/save/report logic
```

## Sprint 8: Senior Pathway

```text
1. User pathway migrations
2. Pathway CRUD API
3. Timeline editor
4. Public pathway page
5. Save/follow pathway
```

## Sprint 9: Search

```text
1. Add searchable indexes
2. Implement search service
3. Global search UI
4. Career Pathway search page
5. Filters by type/cohort/faculty/major
```

## Sprint 10: AI + Hardening

```text
1. AI suggestion table
2. AI suggestion service
3. Review queue
4. Contribution summarizer
5. Analytics events
6. Cache worktree
7. Rate limit write APIs
8. E2E tests
```

---

# Ưu tiên nếu bạn làm một mình

Nếu bạn solo hoặc dùng AI Agent hỗ trợ, làm theo thứ tự này:

```text
1. Official schema
2. Import pipeline
3. Worktree API
4. Worktree UI
5. Course detail drawer
6. Admin data quality dashboard
7. Contribution system
8. Position builder
9. Search
10. AI suggestion
```

Tuyệt đối không làm AI trước. AI là gia vị, không phải cơm. Đổ cả hũ gia vị vào nồi chưa có gạo thì chỉ tạo ra một sản phẩm thơm mùi thất bại.

---

# Bản MVP nên chốt

MVP đầu tiên chỉ cần:

```text
Official Academic Roadmap
Data status
Worktree UI
Course detail
Source document
Admin data quality list
```

Không cần:

```text
AI
Position builder
Senior pathway
Advanced recommendation
Graduation risk detector
Full career heatmap
```

MVP Done Criteria:

```text
- Import được ít nhất 278 ready/usable programs
- Không public 41 programs cần recovery
- User lọc được Khóa/Khoa/Ngành/Chương trình
- User xem được worktree theo học kỳ
- Course card hiển thị mã môn, tên môn, tín chỉ, loại môn, nhóm kiến thức nếu có
- Course detail hiển thị source và warning nếu thiếu mô tả
- Admin thấy được known issues
```

---

# Kết luận

Để hoàn thiện thật sự: **10 phase**.

Để có bản launch có giá trị: **4 phase đầu**.

Để nó thành signature feature đúng nghĩa: cần tới **Phase 6**, vì lúc đó nó không còn là “xem chương trình khung” nữa, mà thành:

```text
Official curriculum
+ Community knowledge
+ User-generated career positions
```
    