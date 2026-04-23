<?php

use App\MonthlyLoeReportLineType;
use App\MonthlyLoeReportStatus;
use App\Filament\Employee\Pages\MyAnalytics;
use App\Filament\Employee\Pages\MyReport;
use App\Filament\Employee\Pages\ReportHistory;
use App\Filament\Employee\Pages\ViewReport;
use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\MonthlyLoeReportLine;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('authenticated employees can open the dashboard and monthly loe page', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $this->actingAs($user)
        ->followingRedirects()
        ->get(route('dashboard'))
        ->assertOk();

    $this->actingAs($user)
        ->followingRedirects()
        ->get(route('loe.report'))
        ->assertOk();

    $this->actingAs($user)
        ->followingRedirects()
        ->get(route('loe.history'))
        ->assertOk();

    $this->actingAs($user)
        ->followingRedirects()
        ->get(route('loe.analytics'))
        ->assertOk();

    $this->actingAs($user)
        ->get(MyReport::getUrl(panel: 'employee'))
        ->assertOk();

    $this->actingAs($user)
        ->get(ReportHistory::getUrl(panel: 'employee'))
        ->assertOk();

    $this->actingAs($user)
        ->get(MyAnalytics::getUrl(panel: 'employee'))
        ->assertOk();
});

test('employees can save their monthly loe report from the employee filament page', function () {
    CarbonImmutable::setTestNow('2026-05-10 10:00:00');

    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create([
        'department_id' => $department->id,
        'joining_date' => '2026-01-01',
    ]);
    $project = Project::query()->create(['name' => 'LoE Platform']);
    $assignment = ProjectAssignment::query()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'expected_percentage' => 35,
        'is_active' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(MyReport::class)
        ->set("projectHours.{$assignment->id}", 40)
        ->set("projectNotes.{$assignment->id}", 'Implementation work')
        ->set('timeOffHours', 8)
        ->set('reportNotes', 'Draft entry')
        ->call('saveDraft')
        ->assertHasNoErrors();

    $report = $user->monthlyLoeReports()->first();

    expect($report)->not->toBeNull()
        ->and($report->total_hours)->not->toEqual('0.00')
        ->and($report->lines)->toHaveCount(3);

    CarbonImmutable::setTestNow();
});

test('employees can view their own historical loe report detail page', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $report = MonthlyLoeReport::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
    ]);

    $this->actingAs($user)
        ->followingRedirects()
        ->get(route('loe.show', $report))
        ->assertOk();

    $this->actingAs($user)
        ->get(ViewReport::getUrl(['report' => $report], panel: 'employee'))
        ->assertOk();
});

test('employee analytics month filter shows only the selected month project details', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $aprilProject = Project::query()->create(['name' => 'Apollo']);
    $mayProject = Project::query()->create(['name' => 'Beacon']);

    $aprilReport = MonthlyLoeReport::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'total_hours' => 48,
        'total_days' => 6,
        'total_percentage' => 75,
        'time_off_hours' => 8,
        'time_off_percentage' => 12.5,
        'open_to_new_projects_hours' => 8,
        'open_to_new_projects_percentage' => 12.5,
    ]);

    $mayReport = MonthlyLoeReport::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 5,
        'status' => MonthlyLoeReportStatus::Submitted,
        'total_hours' => 64,
        'total_days' => 8,
        'total_percentage' => 100,
        'time_off_hours' => 0,
        'time_off_percentage' => 0,
        'open_to_new_projects_hours' => 0,
        'open_to_new_projects_percentage' => 0,
    ]);

    MonthlyLoeReportLine::query()->create([
        'monthly_loe_report_id' => $aprilReport->id,
        'line_type' => MonthlyLoeReportLineType::Project,
        'project_id' => $aprilProject->id,
        'entered_hours' => 48,
        'calculated_days' => 6,
        'calculated_percentage' => 75,
        'expected_percentage' => 70,
        'sort_order' => 1,
    ]);

    MonthlyLoeReportLine::query()->create([
        'monthly_loe_report_id' => $mayReport->id,
        'line_type' => MonthlyLoeReportLineType::Project,
        'project_id' => $mayProject->id,
        'entered_hours' => 64,
        'calculated_days' => 8,
        'calculated_percentage' => 100,
        'expected_percentage' => 100,
        'sort_order' => 1,
    ]);

    $this->actingAs($user)
        ->get(MyAnalytics::getUrl(['report_year' => 2026, 'report_month' => 4], panel: 'employee'))
        ->assertOk()
        ->assertSee('April 2026')
        ->assertSee('Apollo')
        ->assertDontSee('Beacon')
        ->assertDontSee('May 2026');
});
