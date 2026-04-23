<?php

namespace App\Filament\Employee\Pages;

use App\Models\MonthlyLoeReport;
use Filament\Pages\Page;

class ViewReport extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'history/{report}';

    protected string $view = 'filament.employee.pages.view-report';

    public int $reportId;

    public function mount(MonthlyLoeReport $report): void
    {
        abort_unless($report->user_id === auth()->id(), 403);

        $this->reportId = $report->id;
    }

    public function getTitle(): string
    {
        return 'Report Detail';
    }

    public function report(): MonthlyLoeReport
    {
        return MonthlyLoeReport::query()
            ->with(['activities.user', 'lines.project'])
            ->findOrFail($this->reportId);
    }
}
