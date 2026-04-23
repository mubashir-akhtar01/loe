<x-filament-panels::page>
    @php($report = $this->report())

    <div class="grid gap-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-violet-700 dark:bg-violet-500/10 dark:text-violet-200">
                            Report detail
                        </span>
                        <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ \Carbon\CarbonImmutable::create($report->report_year, $report->report_month, 1)->format('F Y') }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                            {{ \Carbon\CarbonImmutable::create($report->report_year, $report->report_month, 1)->format('F Y') }}
                        </h1>
                        <p class="max-w-2xl text-sm leading-6 text-zinc-500">
                            Review the submitted line items, totals, report notes, and activity trail for this month.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ \App\Filament\Employee\Pages\ReportHistory::getUrl(panel: 'employee') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900"
                >
                    Back to history
                </a>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Status</p>
                <h2 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">{{ ucfirst($report->status->value) }}</h2>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Total allocation</p>
                <h2 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $report->total_percentage, 2) }}%</h2>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Time off</p>
                <h2 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $report->time_off_percentage, 2) }}%</h2>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Open to new projects</p>
                <h2 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $report->open_to_new_projects_percentage, 2) }}%</h2>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Line items</h2>

                    <div class="mt-5 space-y-4">
                        @foreach ($report->lines->sortBy('sort_order') as $line)
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-semibold text-zinc-950 dark:text-white">{{ $line->project?->name ?? str($line->line_type->value)->replace('_', ' ')->title() }}</h3>
                                            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                                {{ str($line->line_type->value)->replace('_', ' ')->title() }}
                                            </span>
                                        </div>

                                        @if ($line->line_notes)
                                            <p class="text-sm leading-6 text-zinc-500">{{ $line->line_notes }}</p>
                                        @endif
                                    </div>

                                    <div class="grid gap-3 text-sm text-zinc-500 md:grid-cols-3">
                                        <div>{{ number_format((float) $line->entered_hours, 2) }} hours</div>
                                        <div>{{ number_format((float) $line->calculated_days, 2) }} days</div>
                                        <div>{{ number_format((float) $line->calculated_percentage, 2) }}%</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Report note</h2>
                    <p class="mt-4 text-sm leading-6 text-zinc-500">
                        {{ $report->report_notes ?: 'No report note was added for this month.' }}
                    </p>
                </section>
            </div>

            <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Activity</h2>

                <div class="mt-5 space-y-4">
                    @forelse ($report->activities->sortByDesc('created_at') as $activity)
                        <div class="border-b border-zinc-200 pb-4 last:border-b-0 last:pb-0 dark:border-zinc-800">
                            <p class="font-medium text-zinc-950 dark:text-white">
                                {{ $activity->description ?? str($activity->action->value)->replace('_', ' ')->title() }}
                            </p>
                            <p class="mt-1 text-sm text-zinc-500">
                                {{ $activity->created_at?->format('M j, Y g:i A') }}
                                @if ($activity->user)
                                    · {{ $activity->user->name }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">No activity recorded yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
