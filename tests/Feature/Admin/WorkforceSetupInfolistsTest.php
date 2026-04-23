<?php

use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\ProjectAssignments\ProjectAssignmentResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\PublicHolidays\PublicHolidayResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Schemas\Schema;

test('workforce setup resources expose populated infolists for view modals', function () {
    $resources = [
        DepartmentResource::class,
        UserResource::class,
        ProjectResource::class,
        ProjectAssignmentResource::class,
        PublicHolidayResource::class,
    ];

    foreach ($resources as $resource) {
        $components = $resource::infolist(Schema::make())->getComponents();

        expect($components)
            ->not->toBeEmpty("Expected {$resource} to define infolist components for the view modal.");
    }
});
