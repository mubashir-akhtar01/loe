<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $employee = User::query()
            ->where('email', 'mubashir.akhtar@pixeledge.io')
            ->firstOrFail();

        $projects = Project::query()
            ->whereIn('name', ['BDC', 'LoanEdge'])
            ->get();

        foreach ($projects as $project) {
            ProjectAssignment::query()->updateOrCreate(
                [
                    'project_id' => $project->id,
                    'user_id' => $employee->id,
                ],
                [
                    'expected_percentage' => 50,
                    'is_active' => true,
                ],
            );
        }
    }
}
