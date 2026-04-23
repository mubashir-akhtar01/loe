<?php

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\User;
use App\MonthlyLoeReportLineType;
use App\Services\Loe\LoeValueCalculator;
use App\Services\Loe\MonthlyLoeReportCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it recalculates report totals and auto-adds the open to new projects line', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create(['department_id' => $department->id]);
    $project = Project::query()->create(['name' => 'LoE Platform']);
    $assignment = ProjectAssignment::query()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'expected_percentage' => 35,
        'is_active' => true,
    ]);

    $report = MonthlyLoeReport::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
    ]);

    $report->lines()->create([
        'line_type' => MonthlyLoeReportLineType::Project,
        'project_id' => $project->id,
        'project_assignment_id' => $assignment->id,
        'entered_hours' => 40,
        'sort_order' => 1,
    ]);

    $report->lines()->create([
        'line_type' => MonthlyLoeReportLineType::TimeOff,
        'entered_hours' => 8,
        'sort_order' => 2,
    ]);

    $calculator = new MonthlyLoeReportCalculator(new LoeValueCalculator());
    $recalculatedReport = $calculator->recalculate($report, workingDays: 20);

    $openLine = $recalculatedReport->lines
        ->firstWhere('line_type', MonthlyLoeReportLineType::OpenToNewProjects);

    expect($recalculatedReport->total_hours)->toEqual('160.00')
        ->and($recalculatedReport->total_percentage)->toEqual('100.00')
        ->and($recalculatedReport->time_off_percentage)->toEqual('5.00')
        ->and($recalculatedReport->open_to_new_projects_percentage)->toEqual('70.00')
        ->and($openLine)->not->toBeNull()
        ->and($openLine->entered_hours)->toEqual('112.00')
        ->and($recalculatedReport->lines->firstWhere('line_type', MonthlyLoeReportLineType::Project)?->expected_percentage)->toEqual('35.00');
});
