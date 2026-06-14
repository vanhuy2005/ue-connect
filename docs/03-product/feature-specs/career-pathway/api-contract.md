# API Contract

REST API definitions for Layer 1 Official Academic Roadmap.

## 1. Filters & Navigation

### `GET /career-pathway/cohorts`
Returns cohorts that have at least one public-ready program.
**Response:** `{"data": [{ "id": 1, "name": "2024" }]}`

### `GET /career-pathway/faculties`
Returns faculties that have at least one public-ready program.
**Response:** `{"data": [{ "id": 1, "name": "Công nghệ thông tin" }]}`

### `GET /career-pathway/majors`
Returns majors that have at least one public-ready program.
**Response:** `{"data": [{ "id": 1, "name": "Kỹ thuật phần mềm" }]}`

### `GET /career-pathway/programs`
List programs matching filter criteria. Only returns programs with `ready` or `ready_with_missing_descriptions` status.
**Query Params:** `?cohort_id=1&faculty_id=2&major_id=3`
**Response:** `{"data": [{ "id": 1, "name": "CTĐT Kỹ thuật phần mềm 2024", "status": "ready" }]}`

## 2. Worktree & Courses

### `GET /career-pathway/programs/{program_id}/worktree`
Get the full semester and course tree for visualizing the roadmap. The response is heavily cached.
**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Kỹ thuật phần mềm",
    "quality_warnings": [],
    "semesters": [
      {
        "id": 10,
        "semester_number": 1,
        "title": "Học kỳ 1",
        "courses": [
          {
            "id": 101,
            "course_code": "COMP101",
            "is_mandatory": true,
            "knowledge_block": "Kiến thức cơ sở ngành",
            "course": {
              "id": 50,
              "code": "COMP101",
              "name": "Lập trình cơ bản",
              "credits": 3,
              "description": "..."
            }
          }
        ]
      }
    ]
  }
}
```

### `GET /career-pathway/courses/{course_id}`
Get detailed course info (including the long description).

## 3. Admin APIs (Requires `admin` role/permission)

### `GET /admin/career-pathway/import-runs`
List all import runs.

### `GET /admin/career-pathway/source-documents`
List all source documents.

### `GET /admin/career-pathway/data-quality-issues`
List all data quality issues (includes `raw_context` for debugging).

### `POST /admin/career-pathway/import-runs`
Start a new manual import run (requires `path`).

### `PATCH /admin/career-pathway/programs/{program_id}/status`
Update a program's status (requires `status` and `reason` if changing to a public status). Also invalidates cache.

## 4. Phase 5: Community Knowledge Layer

### Public APIs
- `GET /career-pathway/courses/{course}/contributions`
- `POST /career-pathway/courses/{course}/contributions`
- `GET /career-pathway/contributions/{contribution}`
- `PATCH /career-pathway/contributions/{contribution}`
- `DELETE /career-pathway/contributions/{contribution}`
- `POST /career-pathway/contributions/{contribution}/vote`
- `DELETE /career-pathway/contributions/{contribution}/vote`
- `POST /career-pathway/contributions/{contribution}/report`
- `GET /career-pathway/skills`
- `GET /career-pathway/courses/{course}/skills`

### Admin APIs
- `GET /career-pathway/contributions`
- `PATCH /career-pathway/contributions/{contribution}/moderate`
- `PATCH /career-pathway/contributions/{contribution}/verify`
- `GET /career-pathway/contribution-reports`
- `PATCH /career-pathway/contribution-reports/{report}/resolve`
