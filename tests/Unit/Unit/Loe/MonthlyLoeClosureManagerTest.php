<?php

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\MonthlyLoeClosureType;
use App\MonthlyLoeReportStatus;
use App\Services\Loe\MonthlyLoeClosureManager;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it closes a month and marks related reports as closed', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $admin = User::factory()->admin()->create(['department_id' => $department->id]);
    $employee = User::factory()->create(['department_id' => $department->id]);

    $report = MonthlyLoeReport::query()->create([
        'user_id' => $employee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => CarbonImmutable::parse('2026-04-30 18:00:00'),
    ]);

    $closure = app(MonthlyLoeClosureManager::class)->closeMonth(
        2026,
        4,
        MonthlyLoeClosureType::Manual,
        actor: $admin,
        notes: 'Closed from admin panel.',
        closedAt: CarbonImmutable::parse('2026-05-02 09:00:00'),
    );

    expect($closure->closure_type)->toBe(MonthlyLoeClosureType::Manual)
        ->and($closure->closed_by_user_id)->toBe($admin->id)
        ->and($report->fresh()->status)->toBe(MonthlyLoeReportStatus::Closed)
        ->and($report->fresh()->activities)->toHaveCount(1);
});
