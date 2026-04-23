<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MonthlyLoeReports\MonthlyLoeReportResource;
use App\Models\MonthlyLoeReport;
use App\MonthlyLoeReportStatus;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingLoeReportsTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = [
        'xl' => 4,
    ];

    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        $today = CarbonImmutable::create(
            year: (int) ($this->pageFilters['report_year'] ?? now()->year),
            month: (int) ($this->pageFilters['report_month'] ?? now()->month),
            day: 1,
        );
        $departmentId = $this->pageFilters['department_id'] ?? null;

        return $table
            ->query(
                fn (): Builder => MonthlyLoeReport::query()
                    ->with(['department', 'user'])
                    ->when($departmentId, fn (Builder $query, $departmentId): Builder => $query->where('department_id', $departmentId))
                    ->where('report_year', $today->year)
                    ->where('report_month', $today->month)
                    ->where('status', '!=', MonthlyLoeReportStatus::Submitted)
                    ->latest('updated_at'),
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('Unassigned'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn (MonthlyLoeReportStatus|string $state): string => ucfirst(
                            $state instanceof MonthlyLoeReportStatus ? $state->value : $state,
                        ),
                    ),
                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->since(),
            ])
            ->heading('Pending reports')
            ->description('Employees who still need attention in the selected reporting scope.')
            ->emptyStateHeading('No pending reports in this scope')
            ->emptyStateDescription('Every employee in the selected reporting window is either fully up to date or no draft work exists yet.')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->recordActions([
                Action::make('view')
                    ->label('Open reports')
                    ->url(fn (MonthlyLoeReport $record): string => MonthlyLoeReportResource::getUrl('index'))
                    ->icon('heroicon-m-arrow-top-right-on-square'),
            ]);
    }
}
