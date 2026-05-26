---
title: "Career Pathway"
module: "03-product/feature-specs"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P1"
feature_id: "CAREER-PATHWAY"
last_updated: "2026-05-26"
owner: "Product / BA / UX / Frontend / Backend / QA / Security"
depends_on:
  - "../product-overview.md"
  - "../feature-list.md"
  - "../feature-priority.md"
  - "../sitemap.md"
  - "authentication.md"
  - "verification-identity.md"
  - "profile-management.md"
  - "mentor-matching.md"
  - "community-club.md"
  - "home-feed.md"
  - "settings-privacy.md"
  - "safety-reporting.md"
  - "moderation.md"
  - "notification.md"
  - "../../02-requirements/functional-requirements.md"
  - "../../02-requirements/non-functional-requirements.md"
  - "../../02-requirements/role-permission-matrix.md"
  - "../../02-requirements/acceptance-criteria.md"
  - "../../02-requirements/edge-cases.md"
  - "../../02-requirements/traceability-matrix.md"
related:
  - "mentor-matching.md"
  - "community-club.md"
  - "resource-library.md"
  - "analytics-events.md"
  - "admin-operations.md"
  - "../user-flow/module-level/career-pathway-flow.md"
  - "../state-machines/career-pathway-status-machine.md"
  - "../state-machines/mentor-request-status-machine.md"
  - "../../04-design/page-specs/career-pathways.md"
  - "../../04-design/page-specs/career-pathway-detail.md"
  - "../../06-database"
  - "../../07-security"
  - "../../08-testing"
---

# Career Pathway

## 1. Feature Summary

### 1.1. One-line Description

Career Pathway giúp UEers khám phá định hướng học tập và nghề nghiệp dựa trên ngành học/chương trình đào tạo HCMUE, học phần liên quan, kỹ năng cần phát triển, mentor phù hợp, cộng đồng liên quan và tài nguyên học tập được quản lý.

### 1.2. Feature Goal

Feature này tồn tại để:

- Giúp sinh viên hiểu “ngành mình học có thể đi theo hướng nào”.
- Mapping chương trình đào tạo thực tế của HCMUE sang các career pathway dễ hiểu.
- Gợi ý kỹ năng, học phần, mentor, cộng đồng và tài nguyên liên quan cho từng pathway.
- Hỗ trợ sinh viên năm 1, 2, 3, 4 định hướng theo từng giai đoạn.
- Giúp alumni/advisor/mentor chia sẻ insight theo ngành và pathway.
- Tạo entry point cho Mentor Matching.
- Tạo nền cho curated resource library và opportunity sharing.
- Không biến UEConnect thành job board/recruiter portal.
- Không tạo pathway chung chung kiểu “Full-stack Developer: học HTML xong thành triệu phú”, vì nhân loại đã đủ bị lừa bởi bootcamp poster rồi.

### 1.3. Product Value

| Value | Description |
|---|---|
| Student value | Sinh viên hiểu rõ hơn ngành học, hướng phát triển, kỹ năng và bước tiếp theo |
| Mentor value | Mentor có context rõ để hỗ trợ sinh viên theo topic/pathway |
| Product value | UEConnect có lớp định hướng học tập/nghề nghiệp khác biệt với social app thường |
| Business value | Feature này rất mạnh cho portfolio vì kết nối domain giáo dục + social platform + mentor |
| Trust value | Pathway dựa trên CTĐT HCMUE và nội dung curated, không phải content trôi nổi |
| Safety value | Tài nguyên/opportunity được kiểm duyệt, tránh scam/recruiter trá hình |
| UX value | Sinh viên có thể đi từ “tôi đang mông lung” sang “tôi nên học gì, hỏi ai, tham gia đâu” |

### 1.4. Non-goals

Feature này không làm:

- Không tạo recruiter portal.
- Không tạo employer account.
- Không cho công ty đăng tin tuyển dụng trực tiếp trong MVP.
- Không thay thế website tuyển sinh/chương trình đào tạo chính thức của HCMUE.
- Không cam kết lộ trình nghề nghiệp tuyệt đối đúng cho mọi sinh viên.
- Không tự động tư vấn học vụ thay cố vấn học tập.
- Không hiển thị học phần như dữ liệu chính thức nếu chưa được admin kiểm duyệt.
- Không public tài liệu vi phạm bản quyền.
- Không biến pathway thành ranking ngành nào “hot” hơn ngành nào.
- Không làm AI career counselor tự phán đời người, nghe đã thấy thảm họa.

---

## 2. HCMUE Research Notes

### 2.1. Source Basis

Career Pathway phải được thiết kế để bám vào nguồn dữ liệu thật của HCMUE:

```txt
- Trang chương trình đào tạo chính thức của HCMUE cho phép tra cứu theo năm tuyển sinh/khóa và tên chương trình.
- Khoa CNTT HCMUE công khai các chương trình như Công nghệ Thông tin, Sư phạm Tin học, Công nghệ Giáo dục.
- Khoa CNTT HCMUE nêu các lĩnh vực/bộ môn gồm Khoa học máy tính, Phương pháp Giảng dạy Tin học, Hệ thống Thông tin và Mạng truyền thông.
- Công nghệ Giáo dục được giới thiệu như chương trình liên ngành Công nghệ Thông tin - Giáo dục học.
````

### 2.2. Important Design Implication

UEConnect không nên hardcode career pathway một cách tùy tiện.

Thay vào đó:

```txt
Official training program data
→ normalized program/major/course structure
→ skill mapping
→ career pathway mapping
→ mentor/topic/resource/community recommendation
```

### 2.3. HCMUE Program-driven Direction

Ví dụ với nhóm CNTT/FIT, Career Pathway nên hỗ trợ tối thiểu:

```txt
- Công nghệ Thông tin
- Sư phạm Tin học
- Công nghệ Giáo dục
```

Và các pathway có thể mapping từ domain học thuật/thực tế:

```txt
- Software Engineering
- Web Development
- Backend Development
- Frontend Development
- Full-stack Development
- Database / Information Systems
- Network & System Administration
- AI / Data Fundamentals
- Educational Technology
- Informatics Teaching
- UX/UI for Education
- Research / Graduate Study
```

### 2.4. Reliability Rule

```txt
Career Pathway content must show whether it is:
- official HCMUE-derived
- admin-curated
- mentor/alumni insight
- community-contributed
```

Không được trộn tất cả vào một nồi rồi gọi là “chuẩn”. Đó là cách tài liệu sống thành truyền thuyết đô thị.

---

## 3. Scope

### 3.1. In Scope

```txt
- Career Pathway listing page
- Career Pathway detail page
- Program/major-based pathway mapping
- Course-to-skill mapping
- Skill roadmap per pathway
- Suggested mentors per pathway
- Suggested communities per pathway
- Suggested resources per pathway
- Alumni/advisor insight cards
- Year-based guidance: year 1, 2, 3, 4, alumni
- Student save/follow pathway
- Pathway progress checklist
- Admin curated pathway content
- Opportunity sharing as curated content
- Safety/moderation for submitted resources/insights
- Analytics events
```

### 3.2. Out of Scope

```txt
- Recruiter portal
- Employer account
- Job application system
- Paid career service
- CV parsing AI
- Automated official academic advising
- Official degree audit
- Official graduation eligibility checker
- Full LMS replacement
- Public SEO career blog
- Native mobile career app
```

### 3.3. Future-ready

```txt
- Personalized pathway recommendation
- AI-assisted course/pathway explanation
- Student self-assessment
- Skill gap analysis
- Portfolio checklist
- Internship readiness score
- Alumni AMA sessions
- Career event calendar
- Opportunity board with admin approval
- Bilingual Vietnamese/English pathway content
- Import official curriculum from structured source
```

---

## 4. Priority

| Capability                 | Priority | Reason                              |
| -------------------------- | -------- | ----------------------------------- |
| Career Pathway list        | P1       | Core discovery of pathways          |
| Career Pathway detail      | P1       | Main learning/decision surface      |
| Program/major mapping      | P1       | HCMUE relevance                     |
| Course-to-skill mapping    | P1       | Connect CTĐT to practical direction |
| Suggested mentors          | P1       | Mentor integration                  |
| Suggested resources        | P1       | Learning support                    |
| Suggested communities      | P1       | Community activation                |
| Year-based guidance        | P1/P2    | Student-first guidance              |
| Save/follow pathway        | P1/P2    | Personalization                     |
| Progress checklist         | P2       | Self-guidance                       |
| Admin content management   | P1       | Trust/content quality               |
| Opportunity sharing        | P2       | Alumni/advisor insight              |
| AI recommendation          | P3       | Future                              |
| Official curriculum import | P2/P3    | Data maturity                       |

---

## 5. Actors & Permissions

### 5.1. Actors

| Actor                 |   Can Use? | Notes                                                    |
| --------------------- | ---------: | -------------------------------------------------------- |
| Guest                 | Limited/No | Public landing may show marketing only, not full pathway |
| Registered user       |         No | Must verify                                              |
| Pending user          |         No | Redirect verification                                    |
| Verified Student      |        Yes | Main user                                                |
| Verified Alumni       |        Yes | Can view and contribute insight if allowed               |
| Verified Advisor      |        Yes | Can view and contribute curated insight if allowed       |
| Mentor                |        Yes | Can be linked to pathways                                |
| Club Manager          |        Yes | Can suggest related community/resource                   |
| Admin                 |        Yes | Manage pathway content                                   |
| Moderator             |        Yes | Review reported pathway content/resource                 |
| Suspended/Banned user |         No | Restricted                                               |

### 5.2. Required Permissions

| Action                     | Required Actor        | Permission                                     | Scope            |
| -------------------------- | --------------------- | ---------------------------------------------- | ---------------- |
| View career pathways       | verified active user  | none                                           | app              |
| View pathway detail        | verified active user  | none                                           | app              |
| Save/follow pathway        | verified active user  | none                                           | own              |
| View suggested mentors     | verified active user  | none                                           | privacy-filtered |
| Submit pathway insight     | mentor/alumni/advisor | `submit_pathway_insight` or allowed capability | own              |
| Submit resource suggestion | verified user         | none/P1                                        | review required  |
| Approve pathway content    | admin                 | `manage_career_pathways`                       | global           |
| Create/edit pathway        | admin                 | `manage_career_pathways`                       | global           |
| Publish/unpublish pathway  | admin                 | `manage_career_pathways`                       | global           |
| Moderate insight/resource  | moderator/admin       | `moderate_content`                             | global/scoped    |

### 5.3. Access Rules

```txt
- Only verified active users can access full Career Pathway.
- Pathway content visible only if status = published.
- Draft/archived pathways visible only to admin.
- Suggested mentors must respect mentor visibility, block, account status and moderation.
- Suggested communities must respect community visibility/status.
- Suggested resources must be approved and copyright-safe.
- User-submitted insight/resource must enter review before public display.
- Admin content actions require reason and audit for publish/unpublish/delete.
```

---

## 6. Related Requirements

| Requirement ID | Requirement Name           | Notes                 |
| -------------- | -------------------------- | --------------------- |
| REQ-CAREER-001 | Career Pathway list        | P1                    |
| REQ-CAREER-002 | Career Pathway detail      | P1                    |
| REQ-CAREER-003 | Program-to-pathway mapping | P1                    |
| REQ-CAREER-004 | Course-to-skill mapping    | P1                    |
| REQ-CAREER-005 | Suggested mentors          | Mentor integration    |
| REQ-CAREER-006 | Suggested communities      | Community integration |
| REQ-CAREER-007 | Suggested resources        | Resource integration  |
| REQ-CAREER-008 | Save/follow pathway        | Personalization       |
| REQ-CAREER-009 | Year-based guidance        | Student-first         |
| REQ-CAREER-010 | Alumni/advisor insight     | Social proof          |
| REQ-CAREER-011 | Admin pathway management   | Trust                 |
| REQ-CAREER-012 | Resource copyright safety  | Policy                |
| REQ-MENTOR-015 | Career pathway entry       | Mentor matching       |
| REQ-SAFE-001   | Report content             | Safety                |
| REQ-MOD-004    | Hide content               | Moderation            |

### Related Business Rules

| Business Rule | Description                                          |
| ------------- | ---------------------------------------------------- |
| BR-CAREER-001 | Pathway content must be curated or source-tagged     |
| BR-CAREER-002 | Official program data must not be misrepresented     |
| BR-CAREER-003 | Pathway is guidance, not guarantee                   |
| BR-CAREER-004 | Suggested mentors must respect privacy/safety        |
| BR-CAREER-005 | Opportunity sharing is curated, not recruiter portal |
| BR-CAREER-006 | Copyright-violating resources are forbidden          |
| BR-CAREER-007 | Student progress is private                          |
| BR-CAREER-008 | Admin publish/unpublish requires audit               |
| BR-J003       | Copyright resources not allowed                      |
| BR-P003       | Analytics avoids sensitive content                   |

---

## 7. User Stories

| ID            | User Story                                                                                                       | Priority |
| ------------- | ---------------------------------------------------------------------------------------------------------------- | -------- |
| US-CAREER-001 | As a student, I want to browse career pathways related to my major so that I can understand possible directions. | P1       |
| US-CAREER-002 | As a student, I want to see related courses and skills so that I know why a pathway fits my program.             | P1       |
| US-CAREER-003 | As a student year 1, I want beginner-friendly guidance so that I do not feel lost.                               | P1       |
| US-CAREER-004 | As a student year 3/4, I want internship/portfolio guidance so that I can prepare for work or graduation.        | P1       |
| US-CAREER-005 | As a student, I want to find mentors related to a pathway so that I can ask for advice.                          | P1       |
| US-CAREER-006 | As a mentor/alumni, I want to share insight for a pathway so that students can learn from real experience.       | P1/P2    |
| US-CAREER-007 | As an admin, I want to create and publish pathway content so that information stays reliable.                    | P1       |
| US-CAREER-008 | As a user, I want to save a pathway so that I can revisit it later.                                              | P1/P2    |
| US-CAREER-009 | As a moderator, I want reported resources/insights to enter review so that unsafe content is handled.            | P1       |
| US-CAREER-010 | As a product owner, I want analytics on pathway views and mentor conversions so that I can measure usefulness.   | P1       |

---

## 8. Career Pathway Content Model

### 8.1. Pathway Definition

A Career Pathway is a curated learning/career direction connected to:

```txt
- HCMUE program / major
- course groups
- skills
- mentors
- communities
- resources
- alumni/advisor insights
- possible roles or learning outcomes
```

### 8.2. Pathway Types

| Type                 | Description                                    |
| -------------------- | ---------------------------------------------- |
| `career_role`        | Career-oriented path, e.g. Backend Developer   |
| `academic_direction` | Academic/research path                         |
| `teaching_direction` | Teaching/pedagogical path                      |
| `skill_track`        | Skill-focused path, e.g. Database, UX/UI       |
| `interdisciplinary`  | Cross-domain path, e.g. Educational Technology |
| `graduate_study`     | Postgraduate/research direction                |

### 8.3. Pathway Status

| Status                 | Meaning                            |
| ---------------------- | ---------------------------------- |
| `draft`                | Admin editing                      |
| `reviewing`            | Awaiting approval                  |
| `published`            | Visible to users                   |
| `archived`             | Hidden from main list but retained |
| `hidden_by_moderation` | Temporarily hidden                 |
| `deleted`              | Soft deleted                       |

### 8.4. Source Type

Each content item should indicate source:

| Source Type             | Meaning                                             |
| ----------------------- | --------------------------------------------------- |
| `official_hcmue`        | Derived from official HCMUE training program/source |
| `admin_curated`         | Curated by UEConnect admin                          |
| `mentor_insight`        | Mentor/alumni/advisor contributed                   |
| `community_contributed` | Suggested by community/user                         |
| `external_reference`    | External resource reference                         |

### 8.5. Pathway Detail Sections

A pathway detail should include:

```txt
01. Overview
02. Who this is for
03. Related HCMUE programs/majors
04. Related courses/course groups
05. Skills to build
06. Suggested year-by-year roadmap
07. Suggested mentors
08. Related communities
09. Curated resources
10. Alumni/advisor insights
11. Portfolio/project suggestions
12. Common misconceptions
13. Next actions
```

---

## 9. Initial HCMUE Pathway Taxonomy

### 9.1. FIT / CNTT-related Seed Pathways

Based on public HCMUE/FIT direction, initial seed pathways can include:

| Pathway                         | Related Program           | Type                 | Notes                                          |
| ------------------------------- | ------------------------- | -------------------- | ---------------------------------------------- |
| Software Engineering            | Công nghệ Thông tin       | `career_role`        | Software development foundation                |
| Web Development                 | Công nghệ Thông tin       | `career_role`        | Frontend/backend/full-stack                    |
| Backend Development             | Công nghệ Thông tin       | `career_role`        | API, database, system design                   |
| Frontend Development            | Công nghệ Thông tin       | `career_role`        | UI, web app, UX implementation                 |
| Database / Information Systems  | Công nghệ Thông tin       | `skill_track`        | DB, IS, data modeling                          |
| Network & System Administration | Công nghệ Thông tin       | `skill_track`        | Network, Linux, deployment                     |
| AI / Data Fundamentals          | Công nghệ Thông tin       | `skill_track`        | AI/data foundation, not hype-driven            |
| Informatics Teaching            | Sư phạm Tin học           | `teaching_direction` | Teaching informatics                           |
| Educational Technology          | Công nghệ Giáo dục        | `interdisciplinary`  | EdTech, instructional design, digital learning |
| UX/UI for Education             | Công nghệ Giáo dục / CNTT | `interdisciplinary`  | Learning product design                        |
| Research / Graduate Study       | CNTT / Sư phạm Tin học    | `academic_direction` | Research, graduate study                       |

### 9.2. Non-IT Expansion Direction

Career Pathway architecture must support all HCMUE majors later:

```txt
- Sư phạm Toán
- Sư phạm Ngữ văn
- Sư phạm Tiếng Anh
- Ngôn ngữ Anh
- Tâm lý học
- Tâm lý học giáo dục
- Giáo dục Tiểu học
- Giáo dục Mầm non
- Công tác xã hội
- Du lịch
- Quốc tế học
- Việt Nam học
- Các ngành khoa học tự nhiên/xã hội khác
```

But MVP can start with:

```txt
- FIT/CNTT cluster
- Mentor-related career clusters
- Admin-curated common pathways
```

### 9.3. Pathway Naming Rules

Good:

```txt
- Backend Development
- Thiết kế trải nghiệm học tập số
- Giảng dạy Tin học
- Quản trị hệ thống và mạng
- Nghiên cứu Khoa học máy tính
```

Bad:

```txt
- Nghề hot lương nghìn đô
- AI Engineer trong 30 ngày
- Full-stack thần tốc
- Top 1% mentor pathway
```

Không bán giấc mơ bằng title rẻ tiền. Sinh viên đã đủ áp lực, đừng thêm mắm quảng cáo.

---

## 10. Program / Major Mapping

### 10.1. Program Entity

UEConnect should model HCMUE programs independently from pathways.

Example fields:

```txt
program_name
program_code
faculty
degree_level
training_type
admission_year
total_credits
duration_years
source_url
source_status
```

### 10.2. Why Admission Year Matters

HCMUE chương trình đào tạo có thể khác nhau theo khóa/năm tuyển sinh.

Therefore:

```txt
Pathway mapping must support admission_year/cohort.
```

Example:

```txt
Công nghệ Thông tin - Khóa 2024
Công nghệ Thông tin - Khóa 2025
Sư phạm Tin học - Khóa 2025
Công nghệ Giáo dục - Khóa 2025
```

### 10.3. Mapping Rule

```txt
A career pathway can map to:
- one program
- multiple programs
- one faculty
- multiple faculties
- specific course groups
- specific admission years
```

### 10.4. Program Mapping UI

Pathway detail should show:

```txt
Liên quan đến:
- Công nghệ Thông tin
- Sư phạm Tin học
- Công nghệ Giáo dục
```

If available:

```txt
Áp dụng tốt cho:
- Sinh viên năm 1-2 đang tìm hướng học
- Sinh viên năm 3-4 chuẩn bị portfolio/thực tập
```

### 10.5. Data Reliability Labels

| Label                     | Meaning                          |
| ------------------------- | -------------------------------- |
| `Đối chiếu từ CTĐT HCMUE` | Derived from official curriculum |
| `Admin curated`           | Curated by UEConnect admin       |
| `Mentor insight`          | Shared by mentor/alumni/advisor  |
| `Community suggestion`    | Suggested, not official          |
| `Needs review`            | Not public or pending review     |

---

## 11. Course-to-Skill Mapping

### 11.1. Purpose

Course-to-skill mapping giúp user hiểu:

```txt
- Học phần này giúp mình làm được gì?
- Pathway này cần những kỹ năng nào?
- Học phần trong CTĐT liên quan đến kỹ năng đó ra sao?
```

### 11.2. Course Groups

Recommended course group model:

| Group                  | Meaning                     |
| ---------------------- | --------------------------- |
| `foundation`           | Kiến thức nền               |
| `programming`          | Lập trình                   |
| `database`             | Cơ sở dữ liệu               |
| `network_system`       | Mạng/hệ thống               |
| `software_engineering` | Công nghệ phần mềm          |
| `ai_data`              | AI/data                     |
| `pedagogy`             | Sư phạm/phương pháp dạy học |
| `education_technology` | Công nghệ giáo dục          |
| `research`             | Nghiên cứu                  |
| `internship_project`   | Thực tập/đồ án              |

### 11.3. Skill Types

| Skill Type      | Examples                          |
| --------------- | --------------------------------- |
| Technical skill | Laravel, SQL, Linux, API, testing |
| Academic skill  | research method, reading papers   |
| Teaching skill  | lesson planning, assessment       |
| Product skill   | UX thinking, user research        |
| Soft skill      | teamwork, communication           |
| Career skill    | CV, portfolio, interview          |

### 11.4. Mapping Strength

| Strength     | Meaning                 |
| ------------ | ----------------------- |
| `core`       | Essential for pathway   |
| `supporting` | Helpful but not central |
| `optional`   | Good extension          |
| `advanced`   | Later-stage skill       |

### 11.5. Example Mapping

```txt
Pathway: Backend Development
Related course groups:
- programming: core
- database: core
- software_engineering: core
- network_system: supporting
- testing: supporting
```

```txt
Pathway: Informatics Teaching
Related course groups:
- pedagogy: core
- programming: supporting/core
- education technology: supporting
- assessment: core
```

```txt
Pathway: Educational Technology
Related course groups:
- education technology: core
- UX/UI: core/supporting
- pedagogy: core
- programming: supporting
- research: supporting
```

---

## 12. Year-based Guidance

### 12.1. Why It Matters

Sinh viên năm 1 và sinh viên năm 4 cần guidance khác nhau. Nếu show cùng một checklist “build portfolio, apply internship, publish research” cho sinh viên mới nhập học thì chúc mừng, bạn vừa tạo panic UX.

### 12.2. Year 1 Guidance

Focus:

```txt
- Hiểu ngành học
- Xây nền kỹ năng
- Làm quen cộng đồng
- Tìm bạn cùng học
- Đọc pathway overview
- Theo dõi mentor/community phù hợp
```

### 12.3. Year 2 Guidance

Focus:

```txt
- Chọn hướng kỹ năng
- Làm project nhỏ
- Tham gia community/CLB
- Bắt đầu hỏi mentor
- Xây thói quen portfolio
```

### 12.4. Year 3 Guidance

Focus:

```txt
- Làm project thực tế hơn
- Chuẩn bị thực tập/nghiên cứu
- Nhận mentor review
- Tìm tài nguyên chuyên sâu
- Tham gia opportunity sharing curated
```

### 12.5. Year 4 Guidance

Focus:

```txt
- Hoàn thiện portfolio/đồ án/khóa luận
- Chuẩn bị phỏng vấn hoặc hướng học tiếp
- Hỏi alumni/advisor
- Chia sẻ lại insight cho khóa dưới
```

### 12.6. Alumni Guidance

Focus:

```txt
- Share insight
- Mentor students
- Curate resources
- Participate in community
```

---

## 13. Main User Flows

### 13.1. Browse Pathways

```txt
User opens Career Pathway
→ System checks verified active status
→ Shows recommended pathways by program/profile
→ User filters by program/topic/year
→ Opens pathway detail
```

### 13.2. Pathway Detail to Mentor Request

```txt
User opens pathway detail
→ Reviews skills/courses/resources
→ Sees suggested mentors
→ Opens mentor profile
→ Sends mentor request with pathway prefilled
```

### 13.3. Pathway Detail to Community

```txt
User opens pathway detail
→ Sees related communities
→ Opens community
→ Requests join or views community posts/resources
```

### 13.4. Save Pathway

```txt
User opens pathway detail
→ Clicks Save/Follow pathway
→ System saves to user's profile
→ Pathway appears in saved list/dashboard
```

### 13.5. Admin Creates Pathway

```txt
Admin opens Career Pathway admin
→ Creates draft pathway
→ Adds related programs/course groups/skills/resources/mentors
→ Reviews source labels
→ Publishes pathway
→ Audit created
```

### 13.6. Mentor/Alumni Submits Insight

```txt
Mentor opens pathway detail
→ Submits insight
→ Insight enters review
→ Admin/moderator approves
→ Insight appears on pathway
```

### 13.7. Report Resource/Insight

```txt
User opens pathway content
→ Reports resource/insight
→ Report enters moderation queue
→ Content can be hidden/deleted/restored
```

---

## 14. Page / Route Mapping

| Page / Action         | Route                                            | Layout           | Access                                        | Priority |
| --------------------- | ------------------------------------------------ | ---------------- | --------------------------------------------- | -------- |
| Career Pathway List   | `/app/career-pathways`                           | `AppShellLayout` | Verified active                               | P1       |
| Career Pathway Detail | `/app/career-pathways/{pathwayId}`               | `AppShellLayout` | Verified active                               | P1       |
| Saved Pathways        | `/app/career-pathways/saved`                     | `AppShellLayout` | Verified active                               | P1/P2    |
| Follow/Save Pathway   | `/app/career-pathways/{pathwayId}/save` POST     | Action           | Verified active                               | P1/P2    |
| Unsave Pathway        | `/app/career-pathways/{pathwayId}/save` DELETE   | Action           | Verified active                               | P1/P2    |
| Submit Insight        | `/app/career-pathways/{pathwayId}/insights` POST | Action           | Mentor/alumni/advisor                         | P1/P2    |
| Report Insight        | `/app/career-pathway-insights/{id}/report` POST  | Action           | Verified active                               | P1       |
| Report Resource       | `/app/career-pathway-resources/{id}/report` POST | Action           | Verified active                               | P1       |
| Admin Pathway List    | `/admin/career-pathways`                         | `AdminLayout`    | `manage_career_pathways`                      | P1       |
| Admin Pathway Create  | `/admin/career-pathways/create`                  | `AdminLayout`    | `manage_career_pathways`                      | P1       |
| Admin Pathway Edit    | `/admin/career-pathways/{id}/edit`               | `AdminLayout`    | `manage_career_pathways`                      | P1       |
| Admin Publish Pathway | `/admin/career-pathways/{id}/publish` POST       | Action           | `manage_career_pathways`                      | P1       |
| Admin Review Insight  | `/admin/career-pathway-insights`                 | `AdminLayout`    | `manage_career_pathways` / `moderate_content` | P1/P2    |

---

## 15. UI States

### 15.1. Pathway List States

| State             | Required | Description             |
| ----------------- | -------: | ----------------------- |
| Loading           |      Yes | Fetching pathways       |
| Loaded            |      Yes | Pathways visible        |
| Empty             |      Yes | No pathways             |
| Filtered empty    |      Yes | No result               |
| Error             |      Yes | Load failed             |
| Permission denied |      Yes | Not verified/restricted |
| Saved state       |    P1/P2 | Pathway saved           |

### 15.2. Pathway Detail States

| State                    |   Required | Description              |
| ------------------------ | ---------: | ------------------------ |
| Loading                  |        Yes | Fetching detail          |
| Loaded                   |        Yes | Detail shown             |
| Draft/unpublished        | Admin only | Not public               |
| Archived                 |        Yes | Not active               |
| Hidden by moderation     |        Yes | Unavailable              |
| Missing official mapping |        Yes | Show source warning      |
| No mentors               |        Yes | Suggest follow/community |
| No resources             |        Yes | Empty resources state    |
| Save processing          |      P1/P2 | Saving                   |
| Report submitted         |        Yes | Safety confirmation      |

### 15.3. Admin States

| State            | Required | Description               |
| ---------------- | -------: | ------------------------- |
| Draft editing    |      Yes | Admin edits               |
| Reviewing        |      Yes | Content review            |
| Publishing       |      Yes | Publish action            |
| Published        |      Yes | Public                    |
| Unpublishing     |      Yes | Hide                      |
| Audit failed     |      Yes | Rollback sensitive action |
| Validation error |      Yes | Missing required fields   |

---

## 16. Components

### 16.1. User-facing Components

| Component                     | Purpose                         |
| ----------------------------- | ------------------------------- |
| `CareerPathwayHome`           | Entry page                      |
| `CareerPathwayCard`           | Pathway summary                 |
| `PathwayFilterBar`            | Program/topic/year filters      |
| `PathwayDetailHeader`         | Title, source, badges           |
| `RelatedProgramChips`         | Programs/majors                 |
| `CourseGroupSection`          | Related course groups           |
| `SkillRoadmapSection`         | Skills by stage                 |
| `YearGuidanceTabs`            | Year 1/2/3/4 guidance           |
| `SuggestedMentorList`         | Mentor integration              |
| `RelatedCommunityList`        | Community integration           |
| `CuratedResourceList`         | Resource list                   |
| `AlumniInsightCard`           | Mentor/alumni/advisor insight   |
| `PortfolioProjectSuggestions` | Suggested projects              |
| `PathwaySaveButton`           | Save/follow                     |
| `PathwaySourceBadge`          | Official/admin/mentor/community |
| `ReportContentButton`         | Safety                          |

### 16.2. Admin Components

| Component                   | Purpose                  |
| --------------------------- | ------------------------ |
| `AdminPathwayTable`         | List/manage pathways     |
| `PathwayEditorForm`         | Create/edit pathway      |
| `ProgramMappingEditor`      | Map programs             |
| `CourseSkillMappingEditor`  | Map courses/skills       |
| `ResourceCurationPanel`     | Add resources            |
| `MentorMappingPanel`        | Map mentors/topics       |
| `InsightReviewQueue`        | Review submitted insight |
| `PathwayPublishPanel`       | Publish/archive actions  |
| `SourceReliabilitySelector` | Source labels            |
| `AuditTimeline`             | Admin changes            |

### 16.3. Design System Notes

```txt
- Friendly, clean, student-first.
- Use HCMUE blue for primary CTA.
- Avoid over-premium style.
- Avoid too many colors; pathway tags should be controlled.
- Use cards, chips, tabs, progress checklists.
- UI should feel like guidance, not corporate career portal.
```

---

## 17. Data Model Impact

### 17.1. Entities / Tables

| Entity                         | Purpose                       | Required |
| ------------------------------ | ----------------------------- | -------- |
| `academic_programs`            | HCMUE programs/majors by year | Yes      |
| `academic_courses`             | Courses/học phần              | P1/P2    |
| `course_groups`                | Course category groups        | P1       |
| `career_pathways`              | Main pathway                  | Yes      |
| `career_pathway_programs`      | Program mapping               | Yes      |
| `career_pathway_course_groups` | Course group mapping          | P1       |
| `career_pathway_skills`        | Skill mapping                 | Yes      |
| `career_pathway_resources`     | Curated resources             | P1       |
| `career_pathway_mentors`       | Mentor mapping                | P1       |
| `career_pathway_communities`   | Community mapping             | P1       |
| `career_pathway_insights`      | Alumni/advisor/mentor insight | P1       |
| `user_saved_pathways`          | User saves/follows            | P1/P2    |
| `user_pathway_progress`        | Private checklist progress    | P2       |
| `reports`                      | Report content                | Yes      |
| `audit_logs`                   | Admin publish/edit actions    | Yes      |
| `analytics_events`             | Tracking                      | Yes      |

### 17.2. `academic_programs`

| Field                | Type              | Notes                        |
| -------------------- | ----------------- | ---------------------------- |
| `id`                 | bigint / ulid     | Primary key                  |
| `program_code`       | string nullable   | e.g. 7480201, 7140103        |
| `program_name`       | string            | Program name                 |
| `faculty`            | string nullable   | Faculty                      |
| `degree_level`       | string enum       | bachelor/master/etc          |
| `training_type`      | string enum       | chính quy/VLVH/etc           |
| `admission_year`     | int nullable      | Cohort year                  |
| `duration_years`     | decimal nullable  | e.g. 4                       |
| `total_credits`      | int nullable      | If known                     |
| `source_type`        | string enum       | official_hcmue/admin_curated |
| `source_url`         | string nullable   | Official source              |
| `source_verified_at` | datetime nullable | Admin verified               |
| `status`             | string enum       | active/archived/draft        |
| `created_at`         | datetime          |                              |
| `updated_at`         | datetime          |                              |
| `deleted_at`         | datetime nullable | Soft delete                  |

### 17.3. `career_pathways`

| Field              | Type                 | Notes                                                                           |
| ------------------ | -------------------- | ------------------------------------------------------------------------------- |
| `id`               | bigint / ulid        | Primary key                                                                     |
| `slug`             | string unique        | URL slug                                                                        |
| `title`            | string               | Pathway title                                                                   |
| `type`             | string enum          | career_role/academic_direction/teaching_direction/skill_track/interdisciplinary |
| `summary`          | text                 | Short description                                                               |
| `description`      | text                 | Detail                                                                          |
| `who_is_this_for`  | text nullable        | Target users                                                                    |
| `difficulty_level` | string enum nullable | beginner/intermediate/advanced                                                  |
| `source_type`      | string enum          | official_hcmue/admin_curated/mentor_insight                                     |
| `status`           | string enum          | draft/reviewing/published/archived/hidden_by_moderation                         |
| `created_by`       | FK users nullable    | Admin                                                                           |
| `published_by`     | FK users nullable    | Admin                                                                           |
| `published_at`     | datetime nullable    |                                                                                 |
| `created_at`       | datetime             |                                                                                 |
| `updated_at`       | datetime             |                                                                                 |
| `deleted_at`       | datetime nullable    | Soft delete                                                                     |

### 17.4. `career_pathway_skills`

| Field               | Type          | Notes                                           |
| ------------------- | ------------- | ----------------------------------------------- |
| `id`                | bigint / ulid | Primary key                                     |
| `career_pathway_id` | FK            | Pathway                                         |
| `skill_name`        | string        | Skill                                           |
| `skill_type`        | string enum   | technical/academic/teaching/product/soft/career |
| `stage`             | string enum   | foundation/build/apply/advanced                 |
| `importance`        | string enum   | core/supporting/optional/advanced               |
| `description`       | text nullable |                                                 |
| `order_index`       | int           | Display order                                   |
| `created_at`        | datetime      |                                                 |

### 17.5. `career_pathway_resources`

| Field               | Type              | Notes                                                 |
| ------------------- | ----------------- | ----------------------------------------------------- |
| `id`                | bigint / ulid     | Primary key                                           |
| `career_pathway_id` | FK                | Pathway                                               |
| `title`             | string            | Resource title                                        |
| `resource_type`     | string enum       | article/video/document/course/book/community_resource |
| `url`               | string nullable   | External or internal                                  |
| `media_file_id`     | FK nullable       | Internal file                                         |
| `source_type`       | string enum       | admin_curated/mentor_insight/community_contributed    |
| `copyright_status`  | string enum       | approved/needs_review/rejected                        |
| `status`            | string enum       | draft/pending_review/published/hidden/rejected        |
| `submitted_by`      | FK users nullable | User                                                  |
| `approved_by`       | FK users nullable | Admin                                                 |
| `created_at`        | datetime          |                                                       |
| `updated_at`        | datetime          |                                                       |
| `deleted_at`        | datetime nullable | Soft delete                                           |

### 17.6. `career_pathway_insights`

| Field               | Type              | Notes                                         |
| ------------------- | ----------------- | --------------------------------------------- |
| `id`                | bigint / ulid     | Primary key                                   |
| `career_pathway_id` | FK                | Pathway                                       |
| `author_id`         | FK users          | Mentor/alumni/advisor                         |
| `title`             | string            | Insight title                                 |
| `body`              | text              | Insight body                                  |
| `insight_type`      | string enum       | advice/story/mistake/resource_tip/project_tip |
| `status`            | string enum       | pending_review/published/hidden/rejected      |
| `reviewed_by`       | FK nullable       | Admin/moderator                               |
| `reviewed_at`       | datetime nullable |                                               |
| `created_at`        | datetime          |                                               |
| `updated_at`        | datetime          |                                               |
| `deleted_at`        | datetime nullable | Soft delete                                   |

### 17.7. `user_saved_pathways`

| Field               | Type              | Notes         |
| ------------------- | ----------------- | ------------- |
| `id`                | bigint / ulid     | Primary key   |
| `user_id`           | FK users          | Owner         |
| `career_pathway_id` | FK                | Saved pathway |
| `created_at`        | datetime          |               |
| `deleted_at`        | datetime nullable | Soft delete   |

### 17.8. Constraints

```txt
- published pathway requires title, summary, description, type.
- pathway slug unique.
- pathway can map to multiple programs.
- resource must be approved before public display.
- insight must be reviewed before public display.
- user can save a pathway once.
- official_hcmue source requires source_url or admin verified note.
- all content uses soft delete.
```

---

## 18. API / Controller / Service Direction

### 18.1. Controllers

| Controller                                   | Purpose                     |
| -------------------------------------------- | --------------------------- |
| `CareerPathwayController`                    | User pathway list/detail    |
| `SavedCareerPathwayController`               | Save/unsave                 |
| `CareerPathwayInsightController`             | Submit/list insights        |
| `CareerPathwayResourceController`            | Resources                   |
| `AdminCareerPathwayController`               | Admin CRUD                  |
| `AdminCareerPathwayPublishController`        | Publish/archive             |
| `AdminCareerPathwayInsightReviewController`  | Insight review              |
| `AdminCareerPathwayResourceReviewController` | Resource review             |
| `AcademicProgramController`                  | Program data admin/internal |
| `CareerPathwayReportController`              | Report insight/resource     |

### 18.2. Services / Actions

| Service / Action                          | Responsibility                  |
| ----------------------------------------- | ------------------------------- |
| `BuildCareerPathwayListAction`            | Load/filter pathways            |
| `BuildCareerPathwayDetailAction`          | Load detail                     |
| `RecommendPathwaysForUserAction`          | Suggest by profile/program/year |
| `MapProgramToPathwaysAction`              | Program mapping                 |
| `MapCourseGroupsToSkillsAction`           | Course/skill mapping            |
| `BuildSuggestedMentorsForPathwayAction`   | Mentor suggestions              |
| `BuildRelatedCommunitiesForPathwayAction` | Community suggestions           |
| `SaveCareerPathwayAction`                 | Save/follow                     |
| `SubmitPathwayInsightAction`              | User insight                    |
| `ReviewPathwayInsightAction`              | Admin review                    |
| `CuratePathwayResourceAction`             | Resource curation               |
| `PublishCareerPathwayAction`              | Admin publish                   |
| `ArchiveCareerPathwayAction`              | Admin archive                   |
| `TrackCareerPathwayEventAction`           | Analytics                       |

### 18.3. Form Requests

| Form Request                   | Purpose            |
| ------------------------------ | ------------------ |
| `CareerPathwayFilterRequest`   | Filters            |
| `SaveCareerPathwayRequest`     | Save               |
| `SubmitPathwayInsightRequest`  | Insight validation |
| `CreateCareerPathwayRequest`   | Admin create       |
| `UpdateCareerPathwayRequest`   | Admin update       |
| `PublishCareerPathwayRequest`  | Publish            |
| `ReviewPathwayInsightRequest`  | Review             |
| `ReviewPathwayResourceRequest` | Review resource    |

### 18.4. Policies / Gates

| Policy / Gate                  | Purpose                      |
| ------------------------------ | ---------------------------- |
| `CareerPathwayPolicy@viewAny`  | User can view list           |
| `CareerPathwayPolicy@view`     | User can view detail         |
| `CareerPathwayPolicy@save`     | User can save                |
| `CareerPathwayPolicy@manage`   | Admin manage                 |
| `CareerPathwayPolicy@publish`  | Admin publish                |
| `PathwayInsightPolicy@create`  | Mentor/alumni/advisor submit |
| `PathwayInsightPolicy@review`  | Admin/moderator review       |
| `PathwayResourcePolicy@review` | Admin/moderator review       |

---

## 19. Validation Rules

### 19.1. Career Pathway Create/Update

| Field             | Rule                         |
| ----------------- | ---------------------------- |
| `title`           | required, string, max:160    |
| `slug`            | required, unique, alpha_dash |
| `type`            | required, valid enum         |
| `summary`         | required, string, max:500    |
| `description`     | required, string, max:5000   |
| `who_is_this_for` | nullable, string, max:2000   |
| `program_ids`     | nullable, array              |
| `skills`          | nullable, array              |
| `source_type`     | required, valid enum         |
| `status`          | required, valid enum         |

### 19.2. Pathway Insight

| Field               | Rule                        |
| ------------------- | --------------------------- |
| `title`             | required, string, max:160   |
| `body`              | required, string, max:3000  |
| `insight_type`      | required, valid enum        |
| `career_pathway_id` | required, exists, published |

### 19.3. Resource

| Field              | Rule                      |
| ------------------ | ------------------------- |
| `title`            | required, string, max:200 |
| `resource_type`    | required, valid enum      |
| `url`              | nullable, url             |
| `media_file_id`    | nullable, exists          |
| `copyright_status` | required, enum            |
| `description`      | nullable, max:1000        |

### 19.4. Save Pathway

| Field               | Rule                        |
| ------------------- | --------------------------- |
| `career_pathway_id` | required, exists, published |

---

## 20. Security & Privacy

### 20.1. Security Rules

```txt
- Only verified active users can access full pathway.
- Admin pathway CRUD requires manage_career_pathways.
- Publish/archive requires audit.
- Resource links/files must be reviewed for copyright/safety.
- User-submitted insight must be reviewed before public display.
- Suggested mentors must respect privacy/block/account status.
- Suggested communities must respect membership/visibility.
- User saved pathways are private.
- Analytics must not expose private pathway progress.
```

### 20.2. Privacy Rules

```txt
- Do not expose student progress publicly.
- Do not expose private mentor profile fields.
- Do not expose private community resources.
- Do not expose admin notes.
- Do not expose raw report details.
- Do not imply official HCMUE endorsement unless source is verified.
```

### 20.3. Sensitive Data Matrix

| Data                    |   Sensitive? | Handling                      |
| ----------------------- | -----------: | ----------------------------- |
| Saved pathways          |     Moderate | User only                     |
| Pathway progress        |     Moderate | User only                     |
| Mentor suggestions      |     Moderate | Privacy-filtered              |
| Admin notes             |    Sensitive | Admin only                    |
| Submitted resources     |     Moderate | Review before publish         |
| Insight drafts          |     Moderate | Author/admin only             |
| Official source mapping | Low/Moderate | Show verified label carefully |

---

## 21. Audit Requirements

### 21.1. Audit Needed?

| Action                             |          Audit Required | Reason Required |
| ---------------------------------- | ----------------------: | --------------: |
| User views pathway                 |                      No |              No |
| User saves pathway                 |                      No |              No |
| User submits insight               | No, content record only |              No |
| Admin creates pathway              |                     Yes |        Optional |
| Admin edits published pathway      |                     Yes |             Yes |
| Admin publishes pathway            |                     Yes |             Yes |
| Admin archives/unpublishes pathway |                     Yes |             Yes |
| Admin approves/rejects insight     |                     Yes |             Yes |
| Admin approves/rejects resource    |                     Yes |             Yes |
| Moderator hides insight/resource   |                     Yes |             Yes |

### 21.2. Audit Payload

```txt
actor_id
action
target_type = career_pathway / pathway_insight / pathway_resource
target_id
previous_status
new_status
reason
before_values
after_values
created_at
```

---

## 22. Notification Requirements

### 22.1. Notification Types

| Trigger                             | Receiver    | Type                            | Priority |
| ----------------------------------- | ----------- | ------------------------------- | -------- |
| Insight approved                    | Author      | `pathway_insight_approved`      | P2       |
| Insight rejected                    | Author      | `pathway_insight_rejected`      | P2       |
| Resource approved                   | Submitter   | `pathway_resource_approved`     | P2       |
| Resource rejected                   | Submitter   | `pathway_resource_rejected`     | P2       |
| New mentor related to saved pathway | User        | `saved_pathway_mentor_update`   | P2/P3    |
| New resource for saved pathway      | User        | `saved_pathway_resource_update` | P2/P3    |
| Pathway archived                    | Saved users | optional                        | P2       |

### 22.2. Notification Rule

```txt
Career Pathway notification is not P0 unless connected to mentor request or moderation.
```

Tránh notification spam. Không ai cần app báo “có thêm một paragraph mới về Backend” lúc 11 giờ đêm như thể thế giới sắp kết thúc.

---

## 23. Analytics / Event Tracking

### 23.1. Events

| Event Name                            | Trigger                     | Payload                                 | Sensitive?  |
| ------------------------------------- | --------------------------- | --------------------------------------- | ----------- |
| `career_pathways_opened`              | User opens list             | user_id                                 | No          |
| `career_pathway_viewed`               | User opens detail           | user_id, pathway_id                     | Moderate    |
| `career_pathway_saved`                | User saves                  | user_id, pathway_id                     | Moderate    |
| `career_pathway_unsaved`              | User unsaves                | user_id, pathway_id                     | Moderate    |
| `career_pathway_mentor_clicked`       | User opens mentor           | user_id, pathway_id, mentor_id          | Moderate    |
| `career_pathway_resource_clicked`     | User opens resource         | user_id, pathway_id, resource_id        | Moderate    |
| `career_pathway_community_clicked`    | User opens community        | user_id, pathway_id, community_id       | Moderate    |
| `pathway_insight_submitted`           | Insight submitted           | author_id, pathway_id, insight_type     | No raw body |
| `pathway_resource_submitted`          | Resource submitted          | submitter_id, pathway_id, resource_type | No raw URL? |
| `admin_pathway_published`             | Admin publishes             | admin_id, pathway_id                    | Moderate    |
| `mentor_request_started_from_pathway` | Mentor request from pathway | user_id, pathway_id, mentor_id          | Moderate    |

### 23.2. Payload Rules

```txt
- Do not store private progress details in analytics.
- Do not store insight body.
- Do not store raw report description.
- Avoid storing external URLs in analytics unless needed.
- Use IDs and categories.
```

---

## 24. Edge Cases

| Edge Case                               | Expected Handling                          |
| --------------------------------------- | ------------------------------------------ |
| Pathway has no mapped program           | Show admin curated label                   |
| Official source becomes outdated        | Admin marks needs review                   |
| User's major has no pathway             | Show general pathways + request suggestion |
| Related mentor becomes unavailable      | Hide from suggestions                      |
| Mentor blocked user                     | Hide mentor                                |
| Community suspended                     | Hide or show unavailable                   |
| Resource removed                        | Show unavailable                           |
| Resource violates copyright             | Hide and send to moderation                |
| Insight author suspended                | Hide insight if policy says                |
| Admin publishes without required fields | Validation error                           |
| Two admins edit same pathway            | Stale state warning                        |
| User saves pathway twice                | Idempotent                                 |
| Pathway archived after saved            | Saved list shows archived/unavailable      |
| External source link dead               | Show broken link state/admin review        |
| Analytics fails                         | User action still succeeds                 |
| Reported resource already hidden        | Attach report to existing case             |

---

## 25. Accessibility Requirements

```txt
- Pathway cards must be keyboard navigable.
- Filters must have labels.
- Chips must have readable text.
- Skill roadmap must not rely only on color.
- Tabs must be keyboard accessible.
- Save button must expose saved state.
- Resource links must have clear labels.
- Admin editor fields must have validation messages.
- Report modals must trap focus.
```

---

## 26. Responsive / PWA Requirements

### 26.1. Mobile

```txt
- Pathway list uses card layout.
- Filters use bottom sheet.
- Detail page uses stacked sections.
- Mentor/resource/community lists use horizontal cards only if accessible.
- Save CTA remains easy to reach.
```

### 26.2. Tablet

```txt
- Pathway detail can use sticky section navigation.
- Mentor/resource sections can use 2-column cards.
```

### 26.3. Desktop

```txt
- Pathway list can use filters sidebar + card grid.
- Detail page can use left content + right action/context rail.
- Admin editor uses multi-section form.
```

### 26.4. PWA Notes

```txt
- Pathway content can be cached read-only if implemented.
- Save/report/submit actions require network.
- Do not fake successful save/report offline.
```

---

## 27. Performance Requirements

```txt
- Pathway list must be paginated or efficiently loaded.
- Detail page should eager load mapped programs/skills/resources/mentors.
- Avoid N+1 queries for mentors/communities/resources.
- Resource click tracking should be async.
- Admin editor should not load all programs/courses without search.
```

Recommended indexes:

```txt
career_pathways(status, type)
career_pathways(slug)
career_pathway_programs(career_pathway_id, academic_program_id)
career_pathway_skills(career_pathway_id, skill_type)
career_pathway_resources(career_pathway_id, status)
career_pathway_mentors(career_pathway_id, mentor_profile_id)
career_pathway_communities(career_pathway_id, community_id)
career_pathway_insights(career_pathway_id, status)
user_saved_pathways(user_id, career_pathway_id)
academic_programs(program_code, admission_year)
academic_programs(program_name)
```

---

## 28. Content / Copy Rules

### 28.1. Tone

Career Pathway copy should be:

```txt
clear
student-first
supportive
realistic
growth-oriented
not corporate-heavy
not hype-driven
```

### 28.2. Forbidden Copy

```txt
ngành hot
lương nghìn đô
đảm bảo có việc
top mentor
ứng tuyển ngay
recruiter
việc làm đảm bảo
AI thần tốc
crush/match/dating language
```

### 28.3. Preferred Copy

```txt
Lộ trình định hướng
Ngành học liên quan
Học phần liên quan
Kỹ năng nên xây dựng
Mentor phù hợp
Cộng đồng liên quan
Tài nguyên gợi ý
Góc nhìn từ alumni
Lưu lộ trình
Bắt đầu hỏi mentor
```

### 28.4. Example UI Copy

Pathway list title:

```txt
Lộ trình định hướng
```

Pathway detail CTA:

```txt
Tìm mentor cho lộ trình này
```

Related courses:

```txt
Học phần / nhóm học phần liên quan
```

Skills:

```txt
Kỹ năng nên xây dựng
```

Source badge:

```txt
Đối chiếu từ CTĐT HCMUE
```

Save:

```txt
Lưu lộ trình
```

Empty state:

```txt
Chưa có lộ trình phù hợp. Bạn có thể thử bộ lọc khác hoặc quay lại sau khi nội dung được cập nhật.
```

Disclaimer:

```txt
Lộ trình này mang tính định hướng học tập và nghề nghiệp, không thay thế tư vấn học vụ chính thức.
```

---

## 29. Backend Package / Implementation Direction

### 29.1. Laravel Direction

Recommended:

```txt
Laravel Policies/Gates
Laravel Form Requests
Laravel Eloquent Scopes
Laravel Soft Deletes
Laravel Transactions
Laravel Queues
Laravel Notifications
Laravel Scheduler
Blade Components
Livewire for admin editor/filter tables if useful
Alpine.js for filters/modals
```

### 29.2. Content Management Direction

MVP should use admin-curated CRUD:

```txt
- manually create pathways
- manually map programs
- manually curate resources
- manually review insights
```

Future:

```txt
- import official program/course data
- version content by admission_year/cohort
- source verification workflow
```

### 29.3. Transaction Direction

Use transaction for:

```txt
- publish pathway + audit
- archive pathway + audit
- approve insight/resource + audit
- reject insight/resource + audit
```

### 29.4. Scheduler Jobs

```txt
CheckBrokenPathwayResourceLinksJob
ExpireArchivedPathwayNotificationsJob
RecalculatePathwayMentorCountsJob
```

---

## 30. QA / Test Plan

### 30.1. Test Types

| Test Type     | Required | Notes                                   |
| ------------- | -------: | --------------------------------------- |
| Unit          |      Yes | Source labels, mapping logic            |
| Feature       |      Yes | Routes/actions                          |
| Integration   |      Yes | Pathway + mentor + community + resource |
| Security      |      Yes | Admin permissions and privacy           |
| Content QA    |      Yes | Source label correctness                |
| Accessibility |      Yes | Cards/tabs/forms                        |
| Performance   |      Yes | Detail load and N+1                     |
| Manual UX QA  |      Yes | Mobile pathway reading                  |

### 30.2. Core Test Cases

| Test Case ID  | Scenario                                        | Expected Result               |
| ------------- | ----------------------------------------------- | ----------------------------- |
| TC-CAREER-001 | Verified user opens pathway list                | List loads                    |
| TC-CAREER-002 | Unverified user opens pathway list              | Redirect verification         |
| TC-CAREER-003 | User opens published pathway                    | Detail loads                  |
| TC-CAREER-004 | User opens draft pathway                        | 404/403 unless admin          |
| TC-CAREER-005 | Pathway shows related programs                  | Correct mapping               |
| TC-CAREER-006 | Pathway shows skills                            | Skills grouped correctly      |
| TC-CAREER-007 | Suggested mentor blocked user                   | Mentor hidden                 |
| TC-CAREER-008 | Suggested mentor unavailable                    | Mentor hidden/disabled        |
| TC-CAREER-009 | User saves pathway                              | Saved                         |
| TC-CAREER-010 | User saves same pathway twice                   | Idempotent                    |
| TC-CAREER-011 | User submits insight                            | Pending review                |
| TC-CAREER-012 | Admin approves insight                          | Published + audit             |
| TC-CAREER-013 | Admin publishes pathway without required fields | Validation error              |
| TC-CAREER-014 | Admin archives pathway                          | Hidden + audit                |
| TC-CAREER-015 | Resource copyright rejected                     | Not public                    |
| TC-CAREER-016 | Report resource                                 | Report created                |
| TC-CAREER-017 | Analytics payload                               | No raw sensitive content      |
| TC-CAREER-018 | Mobile detail page                              | Usable                        |
| TC-CAREER-019 | Accessibility tabs/cards                        | Keyboard/screen reader usable |
| TC-CAREER-020 | N+1 check                                       | Query count acceptable        |

---

## 31. Rollout Checklist

Before marking this feature ready:

```txt
[ ] Career Pathway list implemented.
[ ] Career Pathway detail implemented.
[ ] Program mapping implemented.
[ ] Skill mapping implemented.
[ ] Suggested mentors implemented.
[ ] Suggested communities implemented or scoped.
[ ] Suggested resources implemented.
[ ] Source label implemented.
[ ] Save/follow pathway implemented or scoped.
[ ] Year-based guidance implemented or scoped.
[ ] Admin pathway CRUD implemented.
[ ] Admin publish/archive implemented.
[ ] Admin audit implemented.
[ ] Insight submission/review implemented or scoped.
[ ] Resource copyright review implemented.
[ ] Report resource/insight integrated.
[ ] Mentor request from pathway integrated.
[ ] Privacy/safety filters implemented.
[ ] Analytics sanitized.
[ ] Mobile UI tested.
[ ] Accessibility tested.
[ ] Performance/N+1 tested.
```

---

## 32. Implementation Task Breakdown

### 32.1. Backend Tasks

```txt
- Create academic_programs migration
- Create academic_courses migration if needed
- Create course_groups migration
- Create career_pathways migration
- Create career_pathway_programs migration
- Create career_pathway_course_groups migration
- Create career_pathway_skills migration
- Create career_pathway_resources migration
- Create career_pathway_mentors migration
- Create career_pathway_communities migration
- Create career_pathway_insights migration
- Create user_saved_pathways migration
- Implement CareerPathway model
- Implement AcademicProgram model
- Implement CareerPathwayResource model
- Implement CareerPathwayInsight model
- Implement CareerPathwayPolicy
- Implement CareerPathwayController
- Implement SavedCareerPathwayController
- Implement AdminCareerPathwayController
- Implement AdminCareerPathwayPublishController
- Implement BuildCareerPathwayListAction
- Implement BuildCareerPathwayDetailAction
- Implement RecommendPathwaysForUserAction
- Implement BuildSuggestedMentorsForPathwayAction
- Implement SubmitPathwayInsightAction
- Implement ReviewPathwayInsightAction
- Implement PublishCareerPathwayAction
- Implement ArchiveCareerPathwayAction
- Implement TrackCareerPathwayEventAction
- Write unit/feature/security/content tests
```

### 32.2. Frontend Tasks

```txt
- Build CareerPathwayHome
- Build CareerPathwayCard
- Build PathwayFilterBar
- Build CareerPathwayDetail
- Build PathwayDetailHeader
- Build RelatedProgramChips
- Build CourseGroupSection
- Build SkillRoadmapSection
- Build YearGuidanceTabs
- Build SuggestedMentorList
- Build RelatedCommunityList
- Build CuratedResourceList
- Build AlumniInsightCard
- Build PortfolioProjectSuggestions
- Build PathwaySaveButton
- Build PathwaySourceBadge
- Build ReportContentButton
- Build AdminPathwayTable
- Build PathwayEditorForm
- Build ProgramMappingEditor
- Build CourseSkillMappingEditor
- Build ResourceCurationPanel
- Build InsightReviewQueue
- Add Vietnamese localization copy
- Mobile responsive layout
- Accessibility polish
```

### 32.3. QA Tasks

```txt
- Test pathway access guard
- Test published/draft/archived visibility
- Test program mapping
- Test skill mapping
- Test suggested mentors privacy filters
- Test suggested communities visibility filters
- Test resource approval/copyright state
- Test insight review flow
- Test save/follow
- Test mentor request entry from pathway
- Test admin publish/archive audit
- Test report resource/insight
- Test analytics payload
- Test mobile UI
- Test accessibility
- Test performance/N+1
```

---

## 33. Final Feature Statement

```txt
Career Pathway là guidance layer của UEConnect. Feature này giúp verified UEers khám phá định hướng học tập/nghề nghiệp dựa trên chương trình/ngành học HCMUE, học phần/nhóm học phần, kỹ năng, mentor, cộng đồng, tài nguyên và alumni/advisor insight. Nội dung phải có source label rõ ràng, tránh tự nhận là dữ liệu chính thức nếu chưa được đối chiếu. Feature này không phải recruiter portal, không phải job board, không phải công cụ hứa hẹn việc làm; nó là bản đồ định hướng giúp sinh viên biết nên học gì, hỏi ai, tham gia đâu và phát triển theo hướng nào.
```

---

## 34. Short Version

```txt
Career Pathway bám ngành học thật của HCMUE.
Không bịa pathway kiểu bootcamp quảng cáo.
Có program mapping.
Có course-to-skill mapping.
Có skill roadmap.
Có year-based guidance.
Có mentor liên quan.
Có community liên quan.
Có resource curated.
Có alumni/advisor insight.
Có source label.
Có admin review.
Có copyright safety.
Không recruiter portal.
Không job board.
Không hứa đảm bảo có việc.
Pathway mà không bám CTĐT thì chỉ là blog motivational mặc áo dashboard.
```


[1]: https://hcmue.edu.vn/vi/dao-tao/dai-hoc/chuong-trinh-dao-tao?utm_source=chatgpt.com "Chương trình đào tạo - hcmue"
