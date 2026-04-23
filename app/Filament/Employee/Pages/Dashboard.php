<?php

namespace App\Filament\Employee\Pages;

use App\Models\MonthlyLoeReport;
use App\Services\Loe\MonthlyLoeMonthLockService;
use Carbon\CarbonImmutable;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Collection;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'My Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.employee.pages.dashboard';

    public function closingDeadline(): CarbonImmutable
    {
        return app(MonthlyLoeMonthLockService::class)->closingDeadline(now()->year, now()->month);
    }

    public function currentMonthLabel(): string
    {
        return now()->startOfMonth()->format('F Y');
    }

    public function currentReport(): ?MonthlyLoeReport
    {
        return MonthlyLoeReport::query()
            ->where('user_id', auth()->id())
            ->where('report_year', now()->year)
            ->where('report_month', now()->month)
            ->first();
    }

    public function isLocked(): bool
    {
        return app(MonthlyLoeMonthLockService::class)->isLocked(now()->year, now()->month);
    }

    public function previousReport(): ?MonthlyLoeReport
    {
        $previousMonth = CarbonImmutable::now()->startOfMonth()->subMonth();

        return MonthlyLoeReport::query()
            ->where('user_id', auth()->id())
            ->where('report_year', $previousMonth->year)
            ->where('report_month', $previousMonth->month)
            ->first();
    }

    public function recentReports(): Collection
    {
        return MonthlyLoeReport::query()
            ->where('user_id', auth()->id())
            ->latest('report_year')
            ->latest('report_month')
            ->limit(6)
            ->get();
    }

    public function reportStatusLabel(): string
    {
        return match ($this->currentReport()?->status?->value) {
            'submitted' => 'Submitted',
            'closed' => 'Closed',
            'draft' => 'Draft in progress',
            default => 'Not started',
        };
    }

    public function reportStatusSummary(): string
    {
        if ($this->isLocked()) {
            return 'This reporting window is closed. You can still review the final numbers for reference.';
        }

        return match ($this->currentReport()?->status?->value) {
            'submitted' => 'Your report is in. You can review it or reopen it before the grace window ends.',
            'draft' => 'You have a saved draft. Finish the details and submit before the reporting window closes.',
            default => 'You have not started this month yet. Open your report to record project effort and time off.',
        };
    }

    public function primaryActionLabel(): string
    {
        return $this->currentReport() === null
            ? 'Start this month\'s report'
            : 'Open my LoE report';
    }
}
