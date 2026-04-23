<?php

namespace App\Filament\Employee\Pages;

use App\Models\MonthlyLoeReport;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class ReportHistory extends Page
{
    protected static ?string $title = 'History';

    protected static ?string $navigationLabel = 'History';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Reporting';

    protected static ?string $slug = 'history';

    protected string $view = 'filament.employee.pages.report-history';

    public function reports(): Collection
    {
        return MonthlyLoeReport::query()
            ->where('user_id', auth()->id())
            ->latest('report_year')
            ->latest('report_month')
            ->get();
    }
}
