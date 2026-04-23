<?php

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\User;
use App\Notifications\Loe\AdminLoeReportAlertNotification;
use App\Notifications\Loe\AdminLoeReportSubmittedNotification;
use App\Notifications\Loe\AdminLoeReportUpdatedNotification;
use App\MonthlyLoeReportLineType;
use App\MonthlyLoeReportStatus;
use App\Services\Loe\MonthlyLoeReportManager;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it creates prefills and submits a monthly report through the manager', function () {
    CarbonImmutable::setTestNow('2026-05-10 10:00:00');
    Notification::fake();

    $department = Department::query()->create(['name' => 'Engineering']);
    $admin = User::factory()->admin()->create(['department_id' => $department->id]);
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
        'entered_hours' => 32,
        'sort_order' => 1,
    ]);

    $manager = app(MonthlyLoeReportManager::class);

    $report = $manager->getOrCreateForMonth($user, 2026, 5);

    expect($report->lines->firstWhere('project_assignment_id', $assignment->id))->not->toBeNull();

    $savedReport = $manager->saveReport(
        report: $report,
        user: $user,
        projectHours: [$assignment->id => 80],
        projectNotes: [$assignment->id => 'Delivery sprint'],
        timeOffHours: 8,
        timeOffNotes: 'Public holiday coverage',
        reportNotes: 'May submission',
        submit: true,
    );

    expect($savedReport->status)->toBe(MonthlyLoeReportStatus::Submitted)
        ->and($savedReport->submitted_at)->not->toBeNull()
        ->and($savedReport->lines->firstWhere('line_type', MonthlyLoeReportLineType::TimeOff))->not->toBeNull()
        ->and($savedReport->lines->firstWhere('line_type', MonthlyLoeReportLineType::OpenToNewProjects))->not->toBeNull()
        ->and($savedReport->activities)->toHaveCount(5);

    Notification::assertSentTo($admin, AdminLoeReportSubmittedNotification::class);
    Notification::assertSentTo($admin, AdminLoeReportAlertNotification::class);

    CarbonImmutable::setTestNow();
});

test('it notifies admins when a submitted report is updated and returned to draft', function () {
    CarbonImmutable::setTestNow('2026-05-15 10:00:00');
    Notification::fake();

    $department = Department::query()->create(['name' => 'Engineering']);
    $admin = User::factory()->admin()->create(['department_id' => $department->id]);
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

    $report = MonthlyLoeReport::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 5,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now()->subDay(),
    ]);

    $report->lines()->create([
        'line_type' => MonthlyLoeReportLineType::Project,
        'project_id' => $project->id,
        'project_assignment_id' => $assignment->id,
        'entered_hours' => 40,
        'sort_order' => 1,
    ]);

    $savedReport = app(MonthlyLoeReportManager::class)->saveReport(
        report: $report,
        user: $user,
        projectHours: [$assignment->id => 80],
        projectNotes: [$assignment->id => 'Adjusted after review'],
        timeOffHours: 0,
        timeOffNotes: null,
        reportNotes: 'Reopened for correction',
        submit: false,
    );

    expect($savedReport->status)->toBe(MonthlyLoeReportStatus::Draft);

    Notification::assertSentTo($admin, AdminLoeReportUpdatedNotification::class);
    Notification::assertSentTo($admin, AdminLoeReportAlertNotification::class);

    CarbonImmutable::setTestNow();
});
