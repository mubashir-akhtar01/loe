<?php

use App\Models\Department;
use App\Models\MonthlyLoeClosure;
use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\Notifications\Loe\EmployeeLoeOverdueReminderNotification;
use App\Notifications\Loe\EmployeeLoeReminderNotification;
use App\MonthlyLoeReportStatus;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('monthly reminders are sent during the last three days of the month', function () {
    CarbonImmutable::setTestNow('2026-04-29 09:00:00');
    Notification::fake();

    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create(['department_id' => $department->id]);

    $this->artisan('loe:send-reminders')
        ->assertSuccessful();

    Notification::assertSentTo($user, EmployeeLoeReminderNotification::class);

    CarbonImmutable::setTestNow();
});

test('overdue reminders are sent during the grace period for the previous month', function () {
    CarbonImmutable::setTestNow('2026-05-02 09:15:00');
    Notification::fake();

    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create(['department_id' => $department->id]);

    $this->artisan('loe:send-overdue-reminders')
        ->assertSuccessful();

    Notification::assertSentTo($user, EmployeeLoeOverdueReminderNotification::class);

    CarbonImmutable::setTestNow();
});

test('months are automatically closed after the grace period ends', function () {
    CarbonImmutable::setTestNow('2026-05-04 00:30:00');

    $department = Department::query()->create(['name' => 'Engineering']);
    $user = User::factory()->create(['department_id' => $department->id]);

    MonthlyLoeReport::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Draft,
    ]);

    $this->artisan('loe:auto-close-months')
        ->assertSuccessful();

    expect(MonthlyLoeClosure::query()
        ->where('closure_year', 2026)
        ->where('closure_month', 4)
        ->exists())->toBeTrue();

    CarbonImmutable::setTestNow();
});
