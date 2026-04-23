<?php

use App\Filament\Pages\OpenCapacityPage;
use App\Filament\Pages\OverallocatedEmployeesPage;
use App\Filament\Pages\PendingEmployeesPage;
use App\Filament\Pages\SubmittedReportsPage;
use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\MonthlyLoeReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admins can access the reporting snapshot drilldown pages', function () {
    [$admin, $department] = seedReportingSnapshotPageData();

    $this->actingAs($admin)
        ->get(SubmittedReportsPage::getUrl(['department_id' => $department->id, 'report_month' => 4, 'report_year' => 2026]))
        ->assertSuccessful()
        ->assertSee('Submitted Reports');

    $this->actingAs($admin)
        ->get(PendingEmployeesPage::getUrl(['department_id' => $department->id, 'report_month' => 4, 'report_year' => 2026]))
        ->assertSuccessful()
        ->assertSee('Pending Employees');

    $this->actingAs($admin)
        ->get(OverallocatedEmployeesPage::getUrl(['department_id' => $department->id, 'report_month' => 4, 'report_year' => 2026]))
        ->assertSuccessful()
        ->assertSee('Overallocated Employees');

    $this->actingAs($admin)
        ->get(OpenCapacityPage::getUrl(['department_id' => $department->id, 'report_month' => 4, 'report_year' => 2026]))
        ->assertSuccessful()
        ->assertSee('Open Capacity');
});

test('dashboard reporting snapshot cards link to the dedicated drilldown pages', function () {
    [$admin, $department] = seedReportingSnapshotPageData();

    $response = $this->actingAs($admin)
        ->get('/admin?filters[department_id]='.$department->id.'&filters[report_month]=4&filters[report_year]=2026');

    $response->assertSuccessful()
        ->assertSee('/admin/submitted-reports', false)
        ->assertSee('/admin/pending-employees', false)
        ->assertSee('/admin/overallocated-employees', false)
        ->assertSee('/admin/open-capacity', false);
});

test('submitted reports page only shows submitted reports in scope', function () {
    [$admin, $department, $people] = seedReportingSnapshotPageData();

    $response = $this->actingAs($admin)
        ->get(SubmittedReportsPage::getUrl(['department_id' => $department->id, 'report_month' => 4, 'report_year' => 2026]));

    $response->assertSuccessful()
        ->assertSee($people['submitted']->name)
        ->assertSee($people['overallocated']->name)
        ->assertSee($people['capacity']->name)
        ->assertDontSee($people['draft']->name)
        ->assertDontSee($people['no_report']->name);
});

test('pending employees page shows employees without a submitted report', function () {
    [$admin, $department, $people] = seedReportingSnapshotPageData();

    $response = $this->actingAs($admin)
        ->get(PendingEmployeesPage::getUrl(['department_id' => $department->id, 'report_month' => 4, 'report_year' => 2026]));

    $response->assertSuccessful()
        ->assertSee($people['draft']->name)
        ->assertSee($people['no_report']->name)
        ->assertDontSee($people['submitted']->name)
        ->assertDontSee($people['overallocated']->name);
});

test('overallocated and capacity pages surface the correct employees', function () {
    [$admin, $department, $people] = seedReportingSnapshotPageData();

    $this->actingAs($admin)
        ->get(OverallocatedEmployeesPage::getUrl(['department_id' => $department->id, 'report_month' => 4, 'report_year' => 2026]))
        ->assertSuccessful()
        ->assertSee($people['overallocated']->name)
        ->assertDontSee($people['submitted']->name);

    $this->actingAs($admin)
        ->get(OpenCapacityPage::getUrl(['department_id' => $department->id, 'report_month' => 4, 'report_year' => 2026]))
        ->assertSuccessful()
        ->assertSee($people['capacity']->name)
        ->assertSee('40.00%');
});

/**
 * @return array{0: User, 1: Department, 2: array<string, User>}
 */
function seedReportingSnapshotPageData(): array
{
    $department = Department::query()->create(['name' => 'Engineering']);
    $admin = User::factory()->admin()->create(['department_id' => $department->id]);

    $submittedEmployee = User::factory()->create([
        'department_id' => $department->id,
        'name' => 'Submitted Employee',
    ]);
    $draftEmployee = User::factory()->create([
        'department_id' => $department->id,
        'name' => 'Draft Employee',
    ]);
    $overallocatedEmployee = User::factory()->create([
        'department_id' => $department->id,
        'name' => 'Hana Over 120',
    ]);
    $capacityEmployee = User::factory()->create([
        'department_id' => $department->id,
        'name' => 'Capacity Employee',
    ]);
    $noReportEmployee = User::factory()->create([
        'department_id' => $department->id,
        'name' => 'No Report Employee',
    ]);

    MonthlyLoeReport::query()->create([
        'user_id' => $submittedEmployee->id,
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
        'open_to_new_projects_percentage' => 10,
    ]);

    MonthlyLoeReport::query()->create([
        'user_id' => $draftEmployee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Draft,
        'total_hours' => 96,
        'total_days' => 12,
        'total_percentage' => 80,
        'time_off_hours' => 8,
        'time_off_percentage' => 6.67,
        'open_to_new_projects_hours' => 24,
        'open_to_new_projects_percentage' => 20,
    ]);

    MonthlyLoeReport::query()->create([
        'user_id' => $overallocatedEmployee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 144,
        'total_days' => 18,
        'total_percentage' => 120,
        'time_off_hours' => 0,
        'time_off_percentage' => 0,
        'open_to_new_projects_hours' => 0,
        'open_to_new_projects_percentage' => 0,
    ]);

    MonthlyLoeReport::query()->create([
        'user_id' => $capacityEmployee->id,
        'department_id' => $department->id,
        'report_year' => 2026,
        'report_month' => 4,
        'status' => MonthlyLoeReportStatus::Submitted,
        'submitted_at' => now(),
        'total_hours' => 72,
        'total_days' => 9,
        'total_percentage' => 60,
        'time_off_hours' => 0,
        'time_off_percentage' => 0,
        'open_to_new_projects_hours' => 48,
        'open_to_new_projects_percentage' => 40,
    ]);

    return [
        $admin,
        $department,
        [
            'capacity' => $capacityEmployee,
            'draft' => $draftEmployee,
            'no_report' => $noReportEmployee,
            'overallocated' => $overallocatedEmployee,
            'submitted' => $submittedEmployee,
        ],
    ];
}
