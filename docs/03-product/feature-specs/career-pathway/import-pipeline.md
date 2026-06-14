# Import Pipeline

The import pipeline is responsible for parsing markdown files generated from HCMUE PDFs and storing them cleanly into the database.

## 1. Source Data Structure
Data originates from zip files (`HCMUE-db.zip` and `HCMUE-db_md.zip`).
The directory structure follows:
`{Khóa}/Khoa/{Tên Khoa}/Ngành/{Tên Ngành}/`
Inside this directory:
- `roadmap.md` (Generated from Chuongtrinhkhung PDF)
- `_summary.json`

## 2. Import Command
Command to trigger the import:
`php artisan career-pathway:import-md storage/app/hcmue-md`

## 3. Parsing Logic
1. **Directory Traversal:** Scan the base directory for `roadmap.md` files.
2. **Metadata Extraction:** Extract Cohort (Khóa), Faculty (Khoa), Major (Ngành) from the file path.
3. **Markdown Parsing:** 
   - Identify Semesters (`## Học kỳ X`).
   - Identify Courses under each semester (e.g., tables or list items containing course code, name, credits, and mandatory/elective status).
   - Identify Course Descriptions from the detailed sections in the markdown.
4. **Data Quality Gate Evaluation:**
   - If `roadmap.md` is empty -> status `empty_extraction`.
   - If only "Học kỳ 0" is found -> status `unresolved_semester_structure`.
   - If semesters < 6 -> status `partial_semester_extraction`.
   - If descriptions are missing -> status `ready_with_missing_descriptions`.
   - Otherwise -> status `ready`.

## 4. Upsert Strategy
- Entities (Cohorts, Faculties, Majors, Programs) use `slug` or `original_dir` as a unique identifier to upsert.
- Semesters and Courses are wiped and re-created for a program upon re-import, to avoid orphan records during markdown structure changes.

## 5. Import Audit & Data Completeness Verification
The `career-pathway:audit-import` command guarantees 100% data visibility. It traverses the source directory and compares all parsed markdown metrics against the actual inserted DB records. This ensures no document is silently skipped, corrupted, or mapped incorrectly.

Command:
```bash
php artisan career-pathway:audit-import storage/app/hcmue-md --output=storage/app/audit
```
