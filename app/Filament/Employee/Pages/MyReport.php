<?php

namespace App\Filament\Employee\Pages;

use App\Models\MonthlyLoeReport;
use App\Models\ProjectAssignment;
use App\Services\Loe\LoeValueCalculator;
use App\Services\Loe\MonthlyLoeMonthLockService;
use App\Services\Loe\MonthlyLoeReportManager;
use App\Services\Loe\WorkdayCalculator;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use UnitEnum;

class MyReport extends Page
{
    protected static ?string $title = 'My Report';

    protected static ?string $navigationLabel = 'My Report';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Reporting';

    protected static ?int $navigationSort = -1;

    protected static ?string $slug = 'report';

    protected string $view = 'filament.employee.pages.my-report';

    public int $currentMonth;

    public int $currentYear;

    /** @var array<int, float|int|string|null> */
    public array $projectHours = [];

    /** @var array<int, string> */
    public array $projectNotes = [];

    public int $reportId;

    public string $reportNotes = '';

    public string $timeOffHours = '0';

    public string $timeOffNotes = '';

    public int $workingDays = 0;

    public function mount(MonthlyLoeReportManager $manager, WorkdayCalculator $workdayCalculator): void
    {
        $today = now();

        $this->currentMonth = $today->month;
        $this->currentYear = $today->year;

        $report = $manager->getOrCreateForMonth(auth()->user(), $this->currentYear, $this->currentMonth);

        $holidayDates = DB::table('public_holidays')
            ->whereYear('holiday_date', $this->currentYear)
            ->whereMonth('holiday_date', $this->currentMonth)
            ->pluck('holiday_date');

        $this->workingDays = $workdayCalculator->workingDaysInMonth(
            $this->currentYear,
            $this->currentMonth,
            $holidayDates,
            auth()->user()->joining_date,
        );

        $this->fillFromReport($report);
    }

    public function activeAssignments(): Collection
    {
        $monthStart = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth()->toDateString();
        $monthEnd = Carbon::create($this->currentYear, $this->currentMonth, 1)->endOfMonth()->toDateString();

        return auth()->user()->projectAssignments()
            ->with('project')
            ->where('is_active', true)
            ->where(function ($query) use ($monthEnd): void {
                $query
                    ->whereNull('starts_on')
                    ->orWhere('starts_on', '<=', $monthEnd);
            })
            ->where(function ($query) use ($monthStart): void {
                $query
                    ->whereNull('ends_on')
                    ->orWhere('ends_on', '>=', $monthStart);
            })
            ->whereHas('project', fn ($query) => $query->where('status', 'active'))
            ->orderBy(ProjectAssignment::query()->getModel()->qualifyColumn('id'))
            ->get();
    }

    public function estimatedOpenHours(): float
    {
        return app(LoeValueCalculator::class)->hoursFromPercentage($this->estimatedOpenPercentage(), $this->workingDays);
    }

    public function estimatedOpenPercentage(): float
    {
        return $this->estimatedTotalPercentage() < 95
            ? round(100 - $this->estimatedTotalPercentage(), 2)
            : 0;
    }

    public function estimatedTotalHours(): float
    {
        return round(
            collect($this->projectHours)->map(fn (mixed $value): float => (float) $value)->sum() + (float) $this->timeOffHours,
            2,
        );
    }

    public function estimatedTotalPercentage(): float
    {
        return app(LoeValueCalculator::class)->percentageFromHours($this->estimatedTotalHours(), $this->workingDays);
    }

    public function isLocked(): bool
    {
        return app(MonthlyLoeMonthLockService::class)->isLocked($this->currentYear, $this->currentMonth);
    }

    public function report(): MonthlyLoeReport
    {
        return MonthlyLoeReport::query()
            ->with(['lines.project', 'lines.projectAssignment'])
            ->findOrFail($this->reportId);
    }

    public function saveDraft(): void
    {
        $this->persist(false);
    }

    public function submitReport(): void
    {
        $this->persist(true);
    }

    protected function fillFromReport(MonthlyLoeReport $report): void
    {
        $report->loadMissing('lines.projectAssignment');

        $this->reportId = $report->id;
        $this->reportNotes = $report->report_notes ?? '';
        $this->projectHours = [];
        $this->projectNotes = [];

        foreach ($this->activeAssignments() as $assignment) {
            $line = $report->lines->firstWhere('project_assignment_id', $assignment->id);

            $this->projectHours[$assignment->id] = $line?->entered_hours ?? 0;
            $this->projectNotes[$assignment->id] = $line?->line_notes ?? '';
        }

        $timeOffLine = $report->lines->firstWhere('line_type', \App\MonthlyLoeReportLineType::TimeOff);

        $this->timeOffHours = (string) ($timeOffLine?->entered_hours ?? 0);
        $this->timeOffNotes = $timeOffLine?->line_notes ?? '';
    }

    protected function persist(bool $submit): void
    {
        if ($this->isLocked()) {
            abort(403);
        }

        $validated = Validator::make(
            [
                'projectHours' => $this->projectHours,
                'projectNotes' => $this->projectNotes,
                'reportNotes' => $this->reportNotes,
                'timeOffHours' => $this->timeOffHours,
                'timeOffNotes' => $this->timeOffNotes,
            ],
            [
                'projectHours.*' => ['nullable', 'numeric', 'min:0'],
                'projectNotes.*' => ['nullable', 'string', 'max:1000'],
                'reportNotes' => ['nullable', 'string', 'max:5000'],
                'timeOffHours' => ['nullable', 'numeric', 'min:0'],
                'timeOffNotes' => ['nullable', 'string', 'max:1000'],
            ],
        )->validate();

        $report = app(MonthlyLoeReportManager::class)->saveReport(
            report: $this->report(),
            user: auth()->user(),
            projectHours: $validated['projectHours'] ?? [],
            projectNotes: $validated['projectNotes'] ?? [],
            timeOffHours: (float) ($validated['timeOffHours'] ?? 0),
            timeOffNotes: $validated['timeOffNotes'] ?? null,
            reportNotes: $validated['reportNotes'] ?? null,
            submit: $submit,
        );

        $this->fillFromReport($report);

        Notification::make()
            ->title($submit ? 'Monthly LoE report submitted.' : 'Monthly LoE draft saved.')
            ->success()
            ->send();
    }
}
