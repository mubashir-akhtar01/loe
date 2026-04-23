<?php

use App\Models\Department;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates a new admin user from the command', function () {
    $department = Department::query()->create(['name' => 'Engineering']);

    $this->artisan('loe:create-admin', [
        'email' => 'admin@example.com',
        '--department' => (string) $department->id,
        '--name' => 'Admin User',
        '--password' => 'password123',
    ])
        ->expectsOutputToContain('Created admin admin@example.com.')
        ->assertSuccessful();

    $user = User::query()->where('email', 'admin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Admin)
        ->and($user->is_active)->toBeTrue()
        ->and($user->department_id)->toBe($department->id);
});

test('it promotes an existing user to admin', function () {
    $department = Department::query()->create(['name' => 'Experience']);
    $user = User::factory()->create([
        'department_id' => null,
        'email' => 'employee@example.com',
        'is_active' => false,
    ]);

    $this->artisan('loe:create-admin', [
        'email' => $user->email,
        '--department' => 'Experience',
    ])
        ->expectsOutputToContain('Promoted employee@example.com to admin.')
        ->assertSuccessful();

    $user->refresh();

    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->is_active)->toBeTrue()
        ->and($user->department_id)->toBe($department->id);
});
