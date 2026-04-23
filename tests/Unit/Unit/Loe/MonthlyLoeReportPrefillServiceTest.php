<?php

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\User;
use App\MonthlyLoeReportLineType;
use App\Services\Loe\MonthlyLoeReportPrefillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it prefills the new month from the previous report without copying open lines', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create(['department_id' => $department->id]);
    $project = Project::query()->create(['name' => 'LoE Platform']);
    $assignment = ProjectAssignment::query()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'expected_percentage' => 35,
        'is_active' => true,
    ]);

    $previousReport = MonthlyLoeReport::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
    ]);

    $previousReport->lines()->create([
        'line_type' => MonthlyLoeReportLineType::Project,
        'project_id' => $project->id,
        'project_assignment_id' => $assignment->id,
        'entered_hours' => 56,
        'line_notes' => 'Client delivery work',
        'sort_order' => 1,
    ]);

    $previousReport->lines()->create([
        'line_type' => MonthlyLoeReportLineType::OpenToNewProjects,
        'entered_hours' => 24,
        'sort_order' => 99,
    ]);

    $currentReport = MonthlyLoeReport::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 5,
    ]);

    $prefilledReport = (new MonthlyLoeReportPrefillService())->prefill($currentReport);

    expect($prefilledReport->lines)->toHaveCount(1)
        ->and($prefilledReport->lines->first()?->line_type)->toBe(MonthlyLoeReportLineType::Project)
        ->and($prefilledReport->lines->first()?->entered_hours)->toEqual('56.00')
        ->and($prefilledReport->lines->first()?->line_notes)->toBe('Client delivery work');
});
