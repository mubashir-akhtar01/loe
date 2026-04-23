<?php

use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\User;
use App\UserRole;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('database seeder creates the requested departments users projects and assignments', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Department::query()->whereIn('name', ['Engineering', 'Experience'])->count())->toBe(2);
    expect(Project::query()->whereIn('name', ['BDC', 'LoanEdge'])->count())->toBe(2);

    $admin = User::query()->where('email', 'admin@admin.com')->first();
    $employee = User::query()->where('email', 'mubashir.akhtar@pixeledge.io')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe(UserRole::Admin)
        ->and(Hash::check('password', $admin->password))->toBeTrue();

    expect($employee)->not->toBeNull()
        ->and($employee->role)->toBe(UserRole::Employee)
        ->and($employee->department?->name)->toBe('Engineering')
        ->and(Hash::check('password', $employee->password))->toBeTrue();

    expect(ProjectAssignment::query()
        ->where('user_id', $employee->id)
        ->whereHas('project', fn ($query) => $query->whereIn('name', ['BDC', 'LoanEdge']))
        ->count())->toBe(2);
});
