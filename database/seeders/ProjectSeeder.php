<?php

namespace Database\Seeders;

use App\Models\Project;
use App\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['BDC', 'LoanEdge'] as $projectName) {
            Project::query()->updateOrCreate(
                ['name' => $projectName],
                ['status' => ProjectStatus::Active],
            );
        }
    }
}
