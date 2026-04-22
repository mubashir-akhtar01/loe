<?php

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\MonthlyLoeReportStatus;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an employee can only have one loe report per month', function () {
    $department = Department::query()->create([
        'name' => 'Engineering',
    ]);

    $employee = User::factory()->create([
        'department_id' => $department->id,
    ]);

    MonthlyLoeReport::query()->create([
        'user_id' => $employee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Draft,
    ]);

    expect(fn () => MonthlyLoeReport::query()->create([
        'user_id' => $employee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Draft,
    ]))->toThrow(QueryException::class);
});
