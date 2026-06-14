# Data Contract & Schema

This document defines the Layer 1 (Official Academic Roadmap) data schema.

## 1. Core Entities

### `cp_cohorts` (Khóa)
- `id` (PK)
- `name` (e.g., "2018 - Khoá 44")
- `slug`
- `start_year`
- `timestamps`

### `cp_faculties` (Khoa)
- `id` (PK)
- `name` (e.g., "Giáo dục Thể chất")
- `slug`
- `timestamps`

### `cp_majors` (Ngành)
- `id` (PK)
- `name` (e.g., "Sư phạm Toán học")
- `slug`
- `faculty_id` (FK)
- `timestamps`

### `cp_programs` (Chương trình đào tạo)
- `id` (PK)
- `cohort_id` (FK)
- `major_id` (FK)
- `name` 
- `slug`
- `status` (Enum: `ready`, `ready_with_missing_descriptions`, `partial_semester_extraction`, `unresolved_semester_structure`, `empty_extraction`, `missing_curriculum_pdf`)
- `total_credits`
- `total_semesters`
- `original_dir` (relative path from source)
- `timestamps`

### `cp_semesters` (Học kỳ)
- `id` (PK)
- `program_id` (FK)
- `semester_number` (1, 2, 3...)
- `title` (e.g., "Học kỳ 1")
- `timestamps`

### `cp_courses` (Học phần)
- `id` (PK)
- `program_id` (FK)
- `semester_id` (FK)
- `code` (Mã học phần)
- `name` (Tên học phần)
- `credits` (Số tín chỉ)
- `is_mandatory` (Bắt buộc / Tự chọn)
- `knowledge_block` (Nhóm kiến thức)
- `description` (Mô tả học phần - nullable)
- `timestamps`

## 2. Enums & Statuses

**Program Status Enum:**
- `ready`: All data cleanly extracted.
- `ready_with_missing_descriptions`: Course list is fine, but descriptions are missing.
- `partial_semester_extraction`: Too few semesters extracted.
- `unresolved_semester_structure`: Collapsed into Học kỳ 0.
- `empty_extraction`: Markdown is completely empty.
- `missing_curriculum_pdf`: Source PDF not found.
- `excluded_non_program_document`: Ignored document.

## 3. Layer 2: Community Knowledge

### `career_contributions`
- `id` (PK)
- `user_id` (FK)
- `target_type`
- `target_id`
- `contribution_type`
- `title`
- `content`
- `status`
- `visibility`
- `source_type`
- `upvotes_count`
- `downvotes_count`
- `reports_count`
- `verified_at`
- `verified_by`
- `metadata_json`
- `timestamps`
- `deleted_at`

### `career_skills`
- `id` (PK)
- `name`
- `normalized_name`
- `category`
- `description`
- `created_by`
- `is_active`
- `metadata_json`
- `timestamps`

### `career_course_skill_edges`
- `id` (PK)
- `career_course_id` (FK)
- `career_skill_id` (FK)
- `career_contribution_id` (FK, nullable)
- `source_type`
- `relevance_level`
- `is_active`
- `created_by`
- `verified_by`
- `verified_at`
- `metadata_json`
- `timestamps`
