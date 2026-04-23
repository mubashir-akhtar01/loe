<x-filament-panels::page>
    @php($currentReport = $this->currentReport())
    @php($previousReport = $this->previousReport())
    @php($recentReports = $this->recentReports())

    <div class="grid gap-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">
                            Employee workspace
                        </span>
                        <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ $this->currentMonthLabel() }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                            Your reporting cockpit for {{ $this->currentMonthLabel() }}
                        </h1>
                        <p class="max-w-2xl text-sm leading-6 text-zinc-500">
                            Keep your monthly LoE current, understand your available capacity, and make it easy for admins to staff new work with confidence.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            Status: {{ $this->reportStatusLabel() }}
                        </span>
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            Deadline: {{ $this->closingDeadline()->format('M j, Y') }}
                        </span>
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            Available capacity: {{ number_format((float) ($currentReport?->open_to_new_projects_percentage ?? 0), 2) }}%
                        </span>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:w-auto xl:min-w-[34rem]">
                    <a
                        href="{{ \App\Filament\Employee\Pages\MyReport::getUrl(panel: 'employee') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-400"
                    >
                        {{ $this->primaryActionLabel() }}
                    </a>
                    <a
                        href="{{ \App\Filament\Employee\Pages\MyAnalytics::getUrl(panel: 'employee') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900"
                    >
                        Review analytics
                    </a>
                    <a
                        href="{{ \App\Filament\Employee\Pages\ReportHistory::getUrl(panel: 'employee') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900"
                    >
                        Browse history
                    </a>
                </div>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Reporting status</p>
                <h2 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">{{ $this->reportStatusLabel() }}</h2>
                <p class="mt-3 text-sm leading-6 text-zinc-500">{{ $this->reportStatusSummary() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Allocated effort</p>
                <h2 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) ($currentReport?->total_percentage ?? 0), 2) }}%</h2>
                <p class="mt-3 text-sm text-zinc-500">{{ number_format((float) ($currentReport?->total_hours ?? 0), 2) }} hours recorded</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Available for new work</p>
                <h2 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) ($currentReport?->open_to_new_projects_percentage ?? 0), 2) }}%</h2>
                <p class="mt-3 text-sm text-zinc-500">{{ number_format((float) ($currentReport?->open_to_new_projects_hours ?? 0), 2) }} hours available</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Previous snapshot</p>
                <h2 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">
                    {{ $previousReport ? \Carbon\CarbonImmutable::create($previousReport->report_year, $previousReport->report_month, 1)->format('M Y') : 'N/A' }}
                </h2>
                <p class="mt-3 text-sm text-zinc-500">
                    {{ number_format((float) ($currentReport?->total_percentage ?? 0) - (float) ($previousReport?->total_percentage ?? 0), 2) }}% versus previous month
                </p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">What needs your attention</h2>
                            <p class="text-sm leading-6 text-zinc-500">{{ $this->reportStatusSummary() }}</p>
                        </div>
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            <div class="font-medium text-zinc-950 dark:text-white">Grace window ends</div>
                            <div class="mt-1">{{ $this->closingDeadline()->format('l, M j') }}</div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <a href="{{ \App\Filament\Employee\Pages\MyReport::getUrl(panel: 'employee') }}" class="rounded-xl border border-zinc-200 p-4 transition hover:border-amber-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                            <p class="text-sm text-zinc-500">Step 1</p>
                            <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">Review this month</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">Open the live report, update hours, and check whether your numbers still reflect reality.</p>
                        </a>
                        <a href="{{ \App\Filament\Employee\Pages\MyAnalytics::getUrl(panel: 'employee') }}" class="rounded-xl border border-zinc-200 p-4 transition hover:border-amber-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                            <p class="text-sm text-zinc-500">Step 2</p>
                            <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">Check your trend</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">Compare capacity against previous months before you submit or reopen your report.</p>
                        </a>
                        <a href="{{ \App\Filament\Employee\Pages\ReportHistory::getUrl(panel: 'employee') }}" class="rounded-xl border border-zinc-200 p-4 transition hover:border-amber-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                            <p class="text-sm text-zinc-500">Step 3</p>
                            <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">Use past context</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">Open recent snapshots when you need to sanity-check shifts in allocation or time off.</p>
                        </a>
                    </div>
                </section>

                <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="space-y-2">
                            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Current month report</h2>
                            <p class="text-sm leading-6 text-zinc-500">{{ $this->reportStatusSummary() }}</p>
                        </div>
                        <a
                            href="{{ \App\Filament\Employee\Pages\MyReport::getUrl(panel: 'employee') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-400"
                        >
                            {{ $this->primaryActionLabel() }}
                        </a>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-sm text-zinc-500">Current period</p>
                            <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $this->currentMonthLabel() }}</h3>
                        </div>
                        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-sm text-zinc-500">Submission state</p>
                            <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $this->reportStatusLabel() }}</h3>
                        </div>
                        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-sm text-zinc-500">Lock date</p>
                            <h3 class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $this->closingDeadline()->format('M j, Y') }}</h3>
                        </div>
                    </div>
                </section>
            </div>

            <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Recent months</h2>
                        <p class="text-sm text-zinc-500">Your latest six LoE snapshots, ordered most recent first.</p>
                    </div>
                    <a href="{{ \App\Filament\Employee\Pages\ReportHistory::getUrl(panel: 'employee') }}" class="text-sm font-medium text-amber-600 hover:text-amber-500">
                        View all
                    </a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($recentReports as $report)
                        <a
                            href="{{ \App\Filament\Employee\Pages\ViewReport::getUrl(['report' => $report], panel: 'employee') }}"
                            class="block rounded-xl border border-zinc-200 px-4 py-3 transition hover:border-amber-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-zinc-950 dark:text-white">
                                        {{ \Carbon\CarbonImmutable::create($report->report_year, $report->report_month, 1)->format('F Y') }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-zinc-500">
                                        <span>{{ number_format((float) $report->total_percentage, 2) }}% allocated</span>
                                        <span>{{ number_format((float) $report->open_to_new_projects_percentage, 2) }}% open</span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        {{ ucfirst($report->status->value) }}
                                    </span>
                                    <span class="text-xs text-zinc-400">{{ $report->submitted_at?->format('M j') ?? 'Draft' }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 p-5 text-sm text-zinc-500 dark:border-zinc-700">
                            No reports are available yet. Once you save your first monthly LoE, it will appear here for quick access.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
