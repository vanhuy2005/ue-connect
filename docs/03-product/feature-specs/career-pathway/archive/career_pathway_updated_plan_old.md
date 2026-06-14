# Career Pathway Updated Plan

## Product definition

Career Pathway is a community-powered academic and career roadmap platform for HCMUE students.

Formula:

```text
Official curriculum data
+ Community course knowledge
+ User-generated career positions
+ Senior/alumni pathway sharing
= Career Pathway
```

## Data gate first

Do not ship Career Pathway as if every markdown file is equally reliable.

Current audit:

```text
Generated roadmaps: 319
Layer 1 import-ready: 278
Clean with no missing descriptions: 126
Need recovery before publishing as official worktree: 41
P0 unresolved semester structure: 16
P0 empty markdown: 2
P0 missing curriculum PDF: 3
P1 partial semester extraction: 23
P2 missing course descriptions: 172 programs / 3287 missing descriptions
```

## Phase 0: Data Quality Gate

Build:
- `import_runs`
- `source_documents`
- `data_quality_issues`
- `program_status`
- Admin Known Issues page

Statuses:
- ready
- ready_with_missing_descriptions
- partial_semester_extraction
- unresolved_semester_structure
- empty_extraction
- missing_curriculum_pdf
- excluded_non_program_document

## Phase 1: Official Academic Roadmap

Use only:
- `ready`
- `ready_with_missing_descriptions`

Features:
- selector: Khóa -> Khoa -> Ngành -> Chương trình
- visual worktree by semester
- course detail drawer
- source PDF traceability
- data quality badge

## Phase 2: Community Knowledge Layer

Features:
- contribution creation
- contribution types: skill, experience, project, resource, difficulty, prerequisite suggestion, career relevance, exam note, portfolio advice
- votes
- reports
- moderation status
- verified community summary

## Phase 3: Career Position Builder

Features:
- user-created positions
- attach official courses
- attach skills/projects/resources
- publish role pathway
- duplicate detection
- moderation

## Phase 4: Senior/Alumni Pathways

Features:
- personal pathway stories
- semester-based advice
- attach courses/projects/skills
- save/follow pathway

## Phase 5: AI-assisted Advisor

Only after source grounding exists:
- summarize notes
- suggest skill tags
- detect duplicates
- draft contribution text
- never publish official facts silently
