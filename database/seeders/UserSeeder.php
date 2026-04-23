<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $engineeringDepartment = Department::query()
            ->where('name', 'Engineering')
            ->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'department_id' => $engineeringDepartment->id,
                'is_active' => true,
                'joining_date' => now()->toDateString(),
                'name' => 'Admin User',
                'password' => 'password',
                'role' => UserRole::Admin,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'mubashir.akhtar@pixeledge.io'],
            [
                'department_id' => $engineeringDepartment->id,
                'is_active' => true,
                'joining_date' => now()->toDateString(),
                'name' => 'Mubashir Akhtar',
                'password' => 'password',
                'role' => UserRole::Employee,
            ],
        );
    }
}
