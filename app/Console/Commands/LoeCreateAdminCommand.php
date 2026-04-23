<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\User;
use App\UserRole;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

#[Signature('loe:create-admin {email? : Email address for the admin user} {--name= : Name for a new admin user} {--password= : Password for a new admin user} {--department= : Department ID or name to assign}')]
#[Description('Create a new admin user or promote an existing user to admin.')]
class LoeCreateAdminCommand extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Admin email');
        $user = User::query()->where('email', $email)->first();
        $department = $this->resolveDepartment($this->option('department'));

        if ($this->option('department') && $department === null) {
            $this->components->error('The selected department could not be found.');

            return self::FAILURE;
        }

        if ($user !== null) {
            $user->forceFill([
                'department_id' => $department?->id ?? $user->department_id,
                'is_active' => true,
                'role' => UserRole::Admin,
            ])->save();

            $this->components->info("Promoted {$user->email} to admin.");

            return self::SUCCESS;
        }

        $payload = [
            'email' => $email,
            'name' => $this->option('name') ?: $this->ask('Admin name'),
            'password' => $this->option('password') ?: $this->secret('Admin password'),
        ];

        Validator::make($payload, [
            'email' => ['required', 'email', Rule::unique(User::class, 'email')],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ])->validate();

        $user = User::query()->create([
            'department_id' => $department?->id,
            'email' => $payload['email'],
            'is_active' => true,
            'name' => $payload['name'],
            'password' => $payload['password'],
            'role' => UserRole::Admin,
        ]);

        $this->components->info("Created admin {$user->email}.");

        return self::SUCCESS;
    }

    private function resolveDepartment(?string $departmentInput): ?Department
    {
        if (blank($departmentInput)) {
            return null;
        }

        return Department::query()
            ->when(
                is_numeric($departmentInput),
                fn ($query) => $query->whereKey((int) $departmentInput),
                fn ($query) => $query->where('name', $departmentInput),
            )
            ->first();
    }
}
