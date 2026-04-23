<?php

use App\Models\MonthlyLoeClosure;
use App\Services\Loe\MonthlyLoeMonthLockService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it keeps a month editable through the grace period and locks it after the deadline', function () {
    $service = new MonthlyLoeMonthLockService();

    expect($service->isEditable(2026, 4, CarbonImmutable::parse('2026-05-03 23:59:59')))->toBeTrue()
        ->and($service->isLocked(2026, 4, CarbonImmutable::parse('2026-05-04 00:00:00')))->toBeTrue();
});

test('it locks a month immediately when a closure record exists', function () {
    MonthlyLoeClosure::query()->create([
        'closure_year' => 2026,
        'closure_month' => 4,
        'closure_type' => \App\MonthlyLoeClosureType::Manual,
        'closed_at' => CarbonImmutable::parse('2026-05-02 10:00:00'),
    ]);

    $service = new MonthlyLoeMonthLockService();

    expect($service->isLocked(2026, 4, CarbonImmutable::parse('2026-05-02 12:00:00')))->toBeTrue();
});
