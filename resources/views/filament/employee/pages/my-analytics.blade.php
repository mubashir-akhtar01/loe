<x-filament-panels::page>
    @php($summary = $this->summary())
    @php($monthlyTrend = collect($this->monthlyTrend()))
    @php($projectBreakdown = $this->projectBreakdown())
    @php($maxAllocation = max(1, (float) $monthlyTrend->max('allocation')))
    @php($maxProjectHours = max(1, (float) $projectBreakdown->max('total_hours')))
    @php($selectedPeriodLabel = $this->selectedPeriodLabel())
    @php($projectSegmentColors = ['#0ea5e9', '#10b981', '#8b5cf6', '#f43f5e', '#06b6d4', '#6366f1'])

    <div class="grid gap-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">
                            Personal workload intelligence
                        </span>
                        <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ $selectedPeriodLabel }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Your effort story, month by month</h1>
                        <p class="max-w-2xl text-sm leading-6 text-zinc-500">
                            Filter by year or month to inspect your allocation, time off, spare bandwidth, and project mix for the exact period you want to review.
                        </p>
                    </div>
                </div>

                <form method="GET" class="flex flex-wrap gap-3">
                    <input
                        type="number"
                        name="report_year"
                        value="{{ $reportYear }}"
                        class="w-32 rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                    />
                    <select
                        name="report_month"
                        class="w-44 rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                    >
                        <option value="">All months</option>
                        @foreach ($this->monthOptions() as $monthNumber => $monthLabel)
                            <option value="{{ $monthNumber }}" @selected($reportMonth === $monthNumber)>{{ $monthLabel }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-400">
                        Refresh view
                    </button>
                </form>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <p class="text-sm text-zinc-500">Average allocation</p>
                    <h2 class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $summary['average_allocation'], 2) }}%</h2>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <p class="text-sm text-zinc-500">Average open capacity</p>
                    <h2 class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $summary['average_open'], 2) }}%</h2>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <p class="text-sm text-zinc-500">Highest allocation month</p>
                    <h2 class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $summary['highest_allocation'], 2) }}%</h2>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <p class="text-sm text-zinc-500">Submitted reports</p>
                    <h2 class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $summary['reports_submitted'] }}/{{ $summary['tracked_months'] }}</h2>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,0.85fr)]">
            <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Allocation rhythm</h2>
                        <p class="text-sm text-zinc-500">A project-aware view of how your allocation and spare bandwidth moved through the selected period.</p>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ $selectedPeriodLabel }}</span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($monthlyTrend as $month)
                        <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                            @php($projectTotal = max(1, (float) collect($month['projects'])->sum('percentage')))
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-20 text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $month['label'] }}</div>
                                    <div class="text-sm text-zinc-500">{{ number_format($month['allocation'], 2) }}% allocated</div>
                                </div>
                                <div class="text-xs text-zinc-400">
                                    {{ number_format($month['open'], 2) }}% open
                                </div>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="flex h-full overflow-hidden rounded-full transition-all" style="width: {{ min(100, ($month['allocation'] / $maxAllocation) * 100) }}%">
                                    @forelse ($month['projects'] as $index => $project)
                                        <div
                                            class="h-full"
                                            style="background-color: {{ $projectSegmentColors[$index % count($projectSegmentColors)] }}; width: {{ min(100, (((float) $project['percentage']) / $projectTotal) * 100) }}%"
                                            title="{{ $project['project_name'] }}: {{ number_format((float) $project['percentage'], 2) }}% / {{ number_format((float) $project['hours'], 2) }}h"
                                        ></div>
                                    @empty
                                        <div class="h-full w-full rounded-full bg-amber-500"></div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <span>{{ number_format($month['time_off'], 2) }}% time off</span>
                                <span>{{ number_format($month['open'], 2) }}% bandwidth</span>
                            </div>

                            <div class="space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">Project details</p>
                                    <span class="text-xs text-zinc-400">{{ $month['month_label'] }}</span>
                                </div>

                                @if (count($month['projects']))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($month['projects'] as $project)
                                            <div class="inline-flex items-center gap-2 rounded-full bg-zinc-50 px-3 py-2 text-xs dark:bg-zinc-950/60">
                                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $projectSegmentColors[$loop->index % count($projectSegmentColors)] }}"></span>
                                                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $project['project_name'] }}</span>
                                                <span class="text-zinc-500">{{ number_format((float) $project['percentage'], 2) }}%</span>
                                                <span class="text-zinc-400">{{ number_format((float) $project['hours'], 2) }}h</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-500">No project allocations were submitted for {{ strtolower($month['month_label']) }}.</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center text-zinc-500 dark:border-zinc-700">
                            No reports are available for the selected period yet.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="space-y-6">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="space-y-1">
                        <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Top project mix</h2>
                        <p class="text-sm text-zinc-500">Which assignments absorbed most of your time in {{ strtolower($selectedPeriodLabel) }}.</p>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse ($projectBreakdown as $line)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-medium text-zinc-950 dark:text-white">{{ $line->project?->name ?? 'Unknown project' }}</p>
                                        <p class="text-sm text-zinc-500">{{ number_format((float) $line->average_percentage, 2) }}% average allocation</p>
                                    </div>
                                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        {{ number_format((float) $line->total_hours, 2) }}h
                                    </span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div
                                        class="h-full rounded-full bg-sky-500"
                                        style="width: {{ min(100, ((float) $line->total_hours / $maxProjectHours) * 100) }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No project allocations are available yet for this year.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Latest snapshot</h2>
                            <p class="text-sm text-zinc-500">Your most recent LoE report at a glance.</p>
                        </div>
                        @if ($this->latestReport())
                            <a href="{{ \App\Filament\Employee\Pages\ViewReport::getUrl(['report' => $this->latestReport()], panel: 'employee') }}" class="text-sm font-medium text-amber-600 hover:text-amber-500">
                                Open report
                            </a>
                        @endif
                    </div>

                    @if ($this->latestReport())
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <p class="text-sm text-zinc-500">Period</p>
                                <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ \Carbon\CarbonImmutable::create($this->latestReport()->report_year, $this->latestReport()->report_month, 1)->format('F Y') }}</h3>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <p class="text-sm text-zinc-500">Status</p>
                                <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ ucfirst($this->latestReport()->status->value) }}</h3>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <p class="text-sm text-zinc-500">Allocation</p>
                                <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $this->latestReport()->total_percentage, 2) }}%</h3>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <p class="text-sm text-zinc-500">Open capacity</p>
                                <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $this->latestReport()->open_to_new_projects_percentage, 2) }}%</h3>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-zinc-500">Once you submit your first report, your latest snapshot will appear here.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
