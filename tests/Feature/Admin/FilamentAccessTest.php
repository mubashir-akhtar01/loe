<?php

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\MonthlyLoeReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admins can access the filament panel dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->followingRedirects()
        ->get('/admin');

    $response->assertOk();
});

test('admins can access the dashboard when pending reports are present', function () {
    $department = Department::query()->create([
        'name' => 'Engineering',
    ]);

    $admin = User::factory()->admin()->create([
        'department_id' => $department->id,
    ]);

    $employee = User::factory()->create([
        'department_id' => $department->id,
    ]);

    MonthlyLoeReport::query()->create([
        'user_id' => $employee->id,
        'department_id' => $department->id,
        'report_year' => now()->year,
        'report_month' => now()->month,
        'status' => MonthlyLoeReportStatus::Draft,
        'total_hours' => 12,
        'total_days' => 1.5,
        'total_percentage' => 18.75,
        'time_off_hours' => 0,
        'time_off_percentage' => 0,
        'open_to_new_projects_hours' => 52,
        'open_to_new_projects_percentage' => 81.25,
    ]);

    $this->actingAs($admin)
        ->followingRedirects()
        ->get('/admin')
        ->assertOk();
});

test('admin panel does not expose employee reporting pages', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('My Analytics')
        ->assertDontSee('My Report')
        ->assertDontSee('Report History')
        ->assertDontSee('View Report');

    $this->actingAs($admin)
        ->get('/admin/my-analytics')
        ->assertNotFound();
});

test('admins can access the monthly loe reports resource', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->followingRedirects()
        ->get('/admin/monthly-loe-reports');

    $response->assertOk();
});

test('admins can access the monthly loe reports resource when report enums are present', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $admin = User::factory()->admin()->create(['department_id' => $department->id]);
    $employee = User::factory()->create(['department_id' => $department->id]);

    MonthlyLoeReport::query()->create([
        'user_id' => $employee->id,
        'department_id' => $department->id,
        'report_year' => now()->year,
        'report_month' => now()->month,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 120,
        'total_days' => 15,
        'total_percentage' => 88.5,
        'time_off_hours' => 8,
        'time_off_percentage' => 5.9,
        'open_to_new_projects_hours' => 12,
        'open_to_new_projects_percentage' => 6.5,
    ]);

    $this->actingAs($admin)
        ->get('/admin/monthly-loe-reports')
        ->assertOk();
});

test('monthly loe reports employee analytics action includes the employee filter', function () {
    $department = Department::query()->create(['name' => 'Engineering']);
    $admin = User::factory()->admin()->create(['department_id' => $department->id]);
    $employee = User::factory()->create(['department_id' => $department->id]);

    MonthlyLoeReport::query()->create([
        'user_id' => $employee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 120,
        'total_days' => 15,
        'total_percentage' => 88.5,
        'time_off_hours' => 8,
        'time_off_percentage' => 5.9,
        'open_to_new_projects_hours' => 12,
        'open_to_new_projects_percentage' => 6.5,
    ]);

    $response = $this->actingAs($admin)
        ->followingRedirects()
        ->get('/admin/monthly-loe-reports');

    $response->assertOk()
        ->assertSee('employee_id=' . $employee->id, false);
});

test('admins can access the users resource', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->get('/admin/users');

    $response->assertOk();
});

test('admins can access the projects resource', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->get('/admin/projects');

    $response->assertOk();
});

test('admins can access the employee analytics page', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->get('/admin/employee-analytics?report_year=2026');

    $response->assertOk();
});

test('employee analytics defaults to the engineering department when available', function () {
    $engineering = Department::query()->create(['name' => 'Engineering']);
    Department::query()->create(['name' => 'Experience']);

    $admin = User::factory()->admin()->create([
        'department_id' => $engineering->id,
    ]);

    $this->actingAs($admin)
        ->get('/admin/employee-analytics')
        ->assertOk()
        ->assertSee('Engineering');
});

test('employee analytics can be filtered to a specific employee', function () {
    $engineering = Department::query()->create(['name' => 'Engineering']);
    $admin = User::factory()->admin()->create(['department_id' => $engineering->id]);
    $employee = User::factory()->create(['department_id' => $engineering->id]);

    MonthlyLoeReport::query()->create([
        'user_id' => $employee->id,
        'department_id' => $engineering->id,
        'report_year' => now()->year,
        'report_month' => now()->month,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 120,
        'total_days' => 15,
        'total_percentage' => 88.5,
        'time_off_hours' => 8,
        'time_off_percentage' => 5.9,
        'open_to_new_projects_hours' => 12,
        'open_to_new_projects_percentage' => 6.5,
    ]);

    $this->actingAs($admin)
        ->get('/admin/employee-analytics?department_ids[]=' . $engineering->id . '&employee_id=' . $employee->id . '&report_month=' . now()->month . '&report_year=' . now()->year)
        ->assertOk()
        ->assertSee($employee->name);
});

test('admins can access the project analytics page', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->get('/admin/project-analytics?report_year=2026');

    $response->assertOk();
});

test('project analytics defaults to the engineering department when available', function () {
    $engineering = Department::query()->create(['name' => 'Engineering']);
    Department::query()->create(['name' => 'Experience']);

    $admin = User::factory()->admin()->create([
        'department_id' => $engineering->id,
    ]);

    $this->actingAs($admin)
        ->get('/admin/project-analytics')
        ->assertOk()
        ->assertSee('Engineering');
});

test('employees cannot access the filament panel dashboard', function () {
    $employee = User::factory()->create();

    $response = $this
        ->actingAs($employee)
        ->get('/admin');

    $response->assertForbidden();
});

test('employees cannot access admin analytics pages', function () {
    $employee = User::factory()->create();

    $this->actingAs($employee)
        ->get('/admin/employee-analytics')
        ->assertForbidden();

    $this->actingAs($employee)
        ->get('/admin/project-analytics')
        ->assertForbidden();
});

test('inactive admins cannot access the filament panel dashboard', function () {
    $admin = User::factory()->admin()->create([
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertForbidden();
});
