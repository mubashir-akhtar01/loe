<?php

namespace App\Filament\Resources\ProjectAssignments\Pages;

use App\Filament\Resources\ProjectAssignments\ProjectAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProjectAssignments extends ManageRecords
{
    protected static string $resource = ProjectAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
