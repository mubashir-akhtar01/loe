<?php

use App\Filament\Employee\Pages\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('employees can access the employee filament panel dashboard', function () {
    $employee = User::factory()->create();

    $this->actingAs($employee)
        ->get(Dashboard::getUrl(panel: 'employee'))
        ->assertOk()
        ->assertSeeText('Your reporting cockpit for');
});

test('admins cannot access the employee filament panel dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(Dashboard::getUrl(panel: 'employee'))
        ->assertForbidden();
});
