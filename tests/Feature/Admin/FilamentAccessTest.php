<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admins can access the filament panel dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->get('/admin');

    $response->assertOk();
});

test('employees cannot access the filament panel dashboard', function () {
    $employee = User::factory()->create();

    $response = $this
        ->actingAs($employee)
        ->get('/admin');

    $response->assertForbidden();
});
