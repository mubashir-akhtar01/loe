<?php

use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\MonthlyLoeReportStatus;
use App\Notifications\Loe\AdminLoeReportSubmittedNotification;
use App\Notifications\Loe\EmployeeLoeReminderNotification;

test('admin loe submitted notification produces a filament database payload', function () {
    $employee = User::factory()->make([
        'name' => 'Mubashir Akhtar',
    ]);

    $report = new MonthlyLoeReport([
        'id' => 123,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'total_percentage' => 100,
    ]);
    $report->setRelation('user', $employee);

    $payload = (new AdminLoeReportSubmittedNotification($report))->toDatabase($employee);

    expect($payload['format'])->toBe('filament')
        ->and($payload['title'])->toContain('LoE report submitted by Mubashir Akhtar')
        ->and($payload['body'])->toContain('April 2026')
        ->and($payload['actions'])->toHaveCount(1);
});

test('employee loe reminder notification produces a filament database payload', function () {
    $employee = User::factory()->make();

    $payload = (new EmployeeLoeReminderNotification('April 2026'))->toDatabase($employee);

    expect($payload['format'])->toBe('filament')
        ->and($payload['title'])->toBe('LoE reminder for April 2026')
        ->and($payload['body'])->toContain('Please submit your Level of Effort report')
        ->and($payload['actions'])->toHaveCount(1);
});
