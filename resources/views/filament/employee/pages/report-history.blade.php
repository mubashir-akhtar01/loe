<x-filament-panels::page>
    <div class="grid gap-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                            Submission archive
                        </span>
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Your monthly reporting history in one place</h1>
                        <p class="max-w-2xl text-sm leading-6 text-zinc-500">
                            Review previous submissions, compare capacity month to month, and reopen the exact report details you need for context.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ \App\Filament\Employee\Pages\MyAnalytics::getUrl(panel: 'employee') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900"
                >
                    Open My Analytics
                </a>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-950/50">
                        <tr class="text-left text-zinc-500">
                            <th class="px-4 py-3 font-medium">Period</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Open</th>
                            <th class="px-4 py-3 font-medium">Submitted</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->reports() as $report)
                            <tr class="align-top">
                                <td class="px-4 py-4 font-medium text-zinc-950 dark:text-white">
                                    {{ \Carbon\CarbonImmutable::create($report->report_year, $report->report_month, 1)->format('F Y') }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        {{ ucfirst($report->status->value) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-zinc-600 dark:text-zinc-300">{{ number_format((float) $report->total_percentage, 2) }}%</td>
                                <td class="px-4 py-4 text-zinc-600 dark:text-zinc-300">{{ number_format((float) $report->open_to_new_projects_percentage, 2) }}%</td>
                                <td class="px-4 py-4 text-zinc-600 dark:text-zinc-300">{{ $report->submitted_at?->format('M j, Y') ?? 'Draft' }}</td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ \App\Filament\Employee\Pages\ViewReport::getUrl(['report' => $report], panel: 'employee') }}" class="text-sm font-medium text-amber-600 hover:text-amber-500">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-zinc-500">
                                    No monthly reports have been created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
