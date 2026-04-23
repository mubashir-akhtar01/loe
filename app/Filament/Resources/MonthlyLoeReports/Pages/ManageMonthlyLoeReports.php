<?php

namespace App\Filament\Resources\MonthlyLoeReports\Pages;

use App\Filament\Resources\MonthlyLoeReports\MonthlyLoeReportResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Arr;

class ManageMonthlyLoeReports extends ManageRecords
{
    protected static string $resource = MonthlyLoeReportResource::class;

    protected function getHeaderActions(): array
    {
        $query = Arr::except(request()->query(), ['page']);

        return [
            Action::make('exportMonthlyReports')
                ->label('Export Reports CSV')
                ->url(route('admin.loe-exports', ['export' => 'monthly-reports', ...$query]))
                ->openUrlInNewTab(),
            Action::make('exportDashboardSummary')
                ->label('Export Summary CSV')
                ->url(route('admin.loe-exports', ['export' => 'dashboard-summary', ...$query]))
                ->openUrlInNewTab(),
            Action::make('exportProjectAllocations')
                ->label('Export Project Allocations CSV')
                ->url(route('admin.loe-exports', ['export' => 'project-allocations', ...$query]))
                ->openUrlInNewTab(),
        ];
    }
}
