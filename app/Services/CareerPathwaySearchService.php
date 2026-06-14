<?php

namespace App\Services;

use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionVisibility;
use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use App\Enums\ProgramStatus;
use App\Models\CareerContribution;
use App\Models\CareerCourse;
use App\Models\CareerPosition;
use App\Models\CareerProgram;
use App\Models\CareerSkill;
use App\Models\CareerUserPathway;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CareerPathwaySearchService
{
    /**
     * Unified search across multiple entities.
     * Maps results to a common structure.
     */
    public function search(string $query, array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $allResults = collect();

        $type = $filters['type'] ?? null;

        if (! $type || $type === 'course') {
            $allResults = $allResults->concat($this->searchCourses($query, $filters));
        }

        if (! $type || $type === 'program') {
            $allResults = $allResults->concat($this->searchPrograms($query, $filters));
        }

        if (! $type || $type === 'position') {
            $allResults = $allResults->concat($this->searchPositions($query, $filters));
        }

        if (! $type || $type === 'senior_pathway') {
            $allResults = $allResults->concat($this->searchSeniorPathways($query, $filters));
        }

        if (! $type || $type === 'skill') {
            $allResults = $allResults->concat($this->searchSkills($query, $filters));
        }

        if (! $type || $type === 'contribution') {
            $allResults = $allResults->concat($this->searchContributions($query, $filters));
        }

        // Manual pagination over the merged collection
        // Not ideal for massive DBs, but fits the MVP DB fallback request perfectly
        $total = $allResults->count();
        $items = $allResults->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    public function searchCourses(string $query, array $filters = []): Collection
    {
        $q = CareerCourse::query()
            ->whereHas('programCourses.program', function ($p) {
                $p->whereIn('status', [
                    ProgramStatus::READY->value,
                    ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS->value,
                ]);
            });

        if ($query) {
            $q->where(function ($w) use ($query) {
                $w->where('code', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%");
            });
        }

        return $q->take(50)->get()->map(fn ($c) => [
            'type' => 'course',
            'id' => $c->id,
            'title' => $c->name,
            'subtitle' => $c->code,
            'description' => '',
            'url' => route('app.career-pathway.courses.show', $c),
            'badges' => [],
            'metadata' => ['code' => $c->code],
        ]);
    }

    public function searchPrograms(string $query, array $filters = []): Collection
    {
        $q = CareerProgram::query()
            ->with(['faculty', 'major', 'cohort'])
            ->whereIn('status', [
                ProgramStatus::READY->value,
                ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS->value,
            ]);

        if ($query) {
            $q->where(function ($w) use ($query) {
                $w->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            });
        }

        if (! empty($filters['faculty_id'])) {
            $q->where('faculty_id', $filters['faculty_id']);
        }
        if (! empty($filters['major_id'])) {
            $q->where('major_id', $filters['major_id']);
        }
        if (! empty($filters['cohort_id'])) {
            $q->where('cohort_id', $filters['cohort_id']);
        }

        return $q->take(50)->get()->map(fn ($p) => [
            'type' => 'program',
            'id' => $p->id,
            'title' => $p->name,
            'subtitle' => ($p->cohort->name ?? '').' · '.($p->major->name ?? ''),
            'description' => '',
            'url' => route('app.career-pathway.programs', ['cohortId' => $p->cohort_id, 'facultyId' => $p->faculty_id, 'majorId' => $p->major_id]),
            'badges' => [$p->status->value],
            'metadata' => [],
        ]);
    }

    public function searchPositions(string $query, array $filters = []): Collection
    {
        $q = CareerPosition::query()
            ->with(['faculty', 'major', 'program'])
            ->where('status', CareerPositionStatus::PUBLISHED->value)
            ->where('visibility', CareerPositionVisibility::PUBLIC->value);

        if ($query) {
            $q->where('title', 'like', "%{$query}%");
        }

        return $q->take(50)->get()->map(fn ($p) => [
            'type' => 'position',
            'id' => $p->id,
            'title' => $p->title,
            'subtitle' => collect([$p->faculty?->name, $p->major?->name, $p->program?->name])->filter()->join(' · ') ?: 'Vị trí nghề nghiệp cộng đồng',
            'description' => str()->limit($p->description, 100),
            'url' => route('app.career-pathway.positions.show', ['position' => $p->slug]),
            'badges' => [],
            'metadata' => [],
        ]);
    }

    public function searchSeniorPathways(string $query, array $filters = []): Collection
    {
        $q = CareerUserPathway::query()
            ->with(['program', 'position'])
            ->where('status', CareerUserPathwayStatus::PUBLISHED->value)
            ->where('visibility', CareerUserPathwayVisibility::PUBLIC->value);

        if ($query) {
            $q->where(function ($w) use ($query) {
                $w->where('title', 'like', "%{$query}%")
                    ->orWhere('story', 'like', "%{$query}%");
            });
        }

        return $q->take(50)->get()->map(fn ($p) => [
            'type' => 'senior_pathway',
            'id' => $p->id,
            'title' => $p->title,
            'subtitle' => $p->program?->name ?? 'Hành trình công khai từ cộng đồng',
            'description' => str()->limit($p->story, 100),
            'url' => route('app.career-pathway.senior-pathways.show', ['pathway' => $p->slug]),
            'badges' => [],
            'metadata' => [],
        ]);
    }

    public function searchSkills(string $query, array $filters = []): Collection
    {
        $q = CareerSkill::query();
        // Assuming all skills in DB are active/approved for now,
        // or filter by some status if it exists.

        if ($query) {
            $q->where(function ($w) use ($query) {
                $w->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        return $q->take(50)->get()->map(fn ($s) => [
            'type' => 'skill',
            'id' => $s->id,
            'title' => $s->name,
            'subtitle' => 'Skill',
            'description' => str()->limit($s->description, 100),
            'url' => '#', // Usually modal or filter
            'badges' => [$s->category ?? ''],
            'metadata' => [],
        ]);
    }

    public function searchContributions(string $query, array $filters = []): Collection
    {
        $q = CareerContribution::query()
            ->whereIn('status', [
                CareerContributionStatus::APPROVED->value,
                CareerContributionStatus::VERIFIED->value,
            ])
            ->where('visibility', CareerContributionVisibility::PUBLIC->value);

        if ($query) {
            $q->where(function ($w) use ($query) {
                $w->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            });
        }

        return $q->take(50)->get()->map(fn ($c) => [
            'type' => 'contribution',
            'id' => $c->id,
            'title' => $c->title,
            'subtitle' => $c->contribution_type->value,
            'description' => str()->limit($c->content, 100),
            'url' => '#',
            'badges' => [],
            'metadata' => [],
        ]);
    }
}
