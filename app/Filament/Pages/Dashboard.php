<?php

namespace App\Filament\Pages;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Support\Enums\Width;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected static ?int $navigationSort = -2;

    protected static ?string $title = 'Workforce Command Center';

    protected bool $persistsFiltersInSession = true;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make('filters')
                ->label('Filter dashboard')
                ->icon('heroicon-o-funnel')
                ->slideOver()
                ->schema([
                    Select::make('report_month')
                        ->label('Month')
                        ->options(collect(range(1, 12))->mapWithKeys(
                            fn (int $month): array => [$month => now()->setMonth($month)->startOfMonth()->format('F')],
                        )->all())
                        ->default(now()->month)
                        ->native(false),
                    Select::make('report_year')
                        ->label('Year')
                        ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(
                            fn (int $year): array => [$year => (string) $year],
                        )->all())
                        ->default(now()->year)
                        ->native(false),
                    Select::make('department_id')
                        ->label('Department')
                        ->options(Department::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->placeholder('All departments')
                        ->native(false),
                ])
                ->modalHeading('Refine dashboard scope')
                ->modalDescription('Choose the reporting period and optional department filter for every widget on this page.')
                ->modalSubmitActionLabel('Apply filters'),
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 1,
            'xl' => 12,
        ];
    }

    public function getHeading(): string
    {
        return 'Workforce Command Center';
    }

    public function getSubheading(): ?string
    {
        $month = now()->setMonth((int) ($this->filters['report_month'] ?? now()->month))->startOfMonth()->format('F');
        $year = (int) ($this->filters['report_year'] ?? now()->year);
        $departmentName = Department::query()->find($this->filters['department_id'] ?? null)?->name;

        return $departmentName
            ? "Live LOE reporting for {$month} {$year} · {$departmentName}"
            : "Live LOE reporting for {$month} {$year} across the full organization";
    }
}
