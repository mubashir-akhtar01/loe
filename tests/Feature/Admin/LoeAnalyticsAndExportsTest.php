<?php

use App\Filament\Pages\EmployeeAnalytics;
use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\MonthlyLoeReportLine;
use App\Models\Project;
use App\Models\User;
use App\MonthlyLoeReportLineType;
use App\MonthlyLoeReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admins can download the monthly reports csv export', function () {
    [$admin, $report, $project] = seedLoeAnalyticsExportData();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.loe-exports', ['export' => 'monthly-reports', 'report_year' => 2026]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())
        ->toContain('Employee')
        ->toContain($report->user->name);
});

test('admins can download the dashboard summary csv export', function () {
    [$admin] = seedLoeAnalyticsExportData();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.loe-exports', ['export' => 'dashboard-summary', 'report_year' => 2026]));

    $response->assertOk();
    expect($response->streamedContent())
        ->toContain('Average Allocation %')
        ->toContain('2026');
});

test('admins can download the project allocations csv export', function () {
    [$admin, $report, $project] = seedLoeAnalyticsExportData();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.loe-exports', ['export' => 'project-allocations', 'project_id' => $project->id, 'report_year' => 2026]));

    $response->assertOk();
    expect($response->streamedContent())
        ->toContain('Project')
        ->toContain($project->name)
        ->toContain($report->user->name);
});

test('admins can download monthly reports filtered by multiple departments', function () {
    $engineering = Department::query()->create(['name' => 'Engineering']);
    $experience = Department::query()->create(['name' => 'Experience']);

    $admin = User::factory()->admin()->create(['department_id' => $engineering->id]);
    $engineeringEmployee = User::factory()->create(['department_id' => $engineering->id]);
    $experienceEmployee = User::factory()->create(['department_id' => $experience->id]);

    MonthlyLoeReport::query()->create([
        'user_id' => $engineeringEmployee->id,
        'department_id' => $engineering->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 120,
        'total_days' => 15,
        'total_percentage' => 88.5,
        'time_off_hours' => 8,
        'time_off_percentage' => 5.9,
        'open_to_new_projects_hours' => 12,
        'open_to_new_projects_percentage' => 6.5,
    ]);

    MonthlyLoeReport::query()->create([
        'user_id' => $experienceEmployee->id,
        'department_id' => $experience->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Draft,
        'total_hours' => 100,
        'total_days' => 12.5,
        'total_percentage' => 80,
        'time_off_hours' => 0,
        'time_off_percentage' => 0,
        'open_to_new_projects_hours' => 20,
        'open_to_new_projects_percentage' => 20,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.loe-exports', [
            'export' => 'monthly-reports',
            'department_ids' => [$engineering->id, $experience->id],
            'report_month' => 4,
            'report_year' => 2026,
        ]));

    $response->assertOk();
    expect($response->streamedContent())
        ->toContain($engineeringEmployee->name)
        ->toContain($experienceEmployee->name);
});

test('employees cannot download admin loe exports', function () {
    $employee = User::factory()->create();

    $this->actingAs($employee)
        ->get(route('admin.loe-exports', ['export' => 'monthly-reports']))
        ->assertForbidden();
});

test('employee analytics project contribution honors the selected employee filter', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $selectedEmployee = User::factory()->create(['department_id' => $department->id]);
    $otherEmployee = User::factory()->create(['department_id' => $department->id]);

    $selectedProject = Project::query()->create(['name' => 'Selected Employee Project']);
    $otherProject = Project::query()->create(['name' => 'Other Employee Project']);

    $selectedReport = MonthlyLoeReport::query()->create([
        'user_id' => $selectedEmployee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 120,
        'total_days' => 15,
        'total_percentage' => 100,
        'time_off_hours' => 0,
        'time_off_percentage' => 0,
        'open_to_new_projects_hours' => 0,
        'open_to_new_projects_percentage' => 0,
    ]);

    $otherReport = MonthlyLoeReport::query()->create([
        'user_id' => $otherEmployee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 80,
        'total_days' => 10,
        'total_percentage' => 66.67,
        'time_off_hours' => 0,
        'time_off_percentage' => 0,
        'open_to_new_projects_hours' => 40,
        'open_to_new_projects_percentage' => 33.33,
    ]);

    MonthlyLoeReportLine::query()->create([
        'monthly_loe_report_id' => $selectedReport->id,
        'line_type' => MonthlyLoeReportLineType::Project,
        'project_id' => $selectedProject->id,
        'entered_hours' => 120,
        'calculated_days' => 15,
        'calculated_percentage' => 100,
        'expected_percentage' => 100,
    ]);

    MonthlyLoeReportLine::query()->create([
        'monthly_loe_report_id' => $otherReport->id,
        'line_type' => MonthlyLoeReportLineType::Project,
        'project_id' => $otherProject->id,
        'entered_hours' => 80,
        'calculated_days' => 10,
        'calculated_percentage' => 66.67,
        'expected_percentage' => 66.67,
    ]);

    /** @var EmployeeAnalytics $page */
    $page = app(EmployeeAnalytics::class);
    $page->departmentIds = [$department->id];
    $page->employeeId = $selectedEmployee->id;
    $page->reportMonth = 4;
    $page->reportYear = 2026;

    $projects = $page->projectBreakdown()->pluck('project.name')->all();

    expect($projects)
        ->toContain('Selected Employee Project')
        ->not->toContain('Other Employee Project');
});

/**
 * @return array{0: User, 1: MonthlyLoeReport, 2: Project}
 */
function seedLoeAnalyticsExportData(): array
{
    $department = Department::query()->create(['name' => 'Engineering']);
    $admin = User::factory()->admin()->create(['department_id' => $department->id]);
    $employee = User::factory()->create(['department_id' => $department->id]);
    $project = Project::query()->create(['name' => 'LoE Platform']);

    $report = MonthlyLoeReport::query()->create([
        'user_id' => $employee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 120,
        'total_days' => 15,
        'total_percentage' => 88.5,
        'time_off_hours' => 8,
        'time_off_percentage' => 5.9,
        'open_to_new_projects_hours' => 12,
        'open_to_new_projects_percentage' => 6.5,
    ]);

    MonthlyLoeReportLine::query()->create([
        'monthly_loe_report_id' => $report->id,
        'line_type' => MonthlyLoeReportLineType::Project,
        'project_id' => $project->id,
        'entered_hours' => 112,
        'calculated_days' => 14,
        'calculated_percentage' => 82.6,
        'expected_percentage' => 75,
        'line_notes' => 'Core implementation',
    ]);

    return [$admin, $report->load('user'), $project];
}
