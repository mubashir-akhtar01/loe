<x-filament-panels::page>
    @php($report = $this->report())
    @php($activeAssignments = $this->activeAssignments())

    <div class="grid gap-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">
                            Current month reporting
                        </span>
                        <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ now()->startOfMonth()->format('F Y') }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                            Translate your month into staffing visibility
                        </h1>
                        <p class="max-w-2xl text-sm leading-6 text-zinc-500">
                            Log project hours, capture time off, and let the system calculate percentages and remaining bandwidth automatically.
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[32rem]">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="text-sm text-zinc-500">Working days</p>
                        <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ $workingDays }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="text-sm text-zinc-500">Status</p>
                        <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ ucfirst($report->status->value) }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="text-sm text-zinc-500">Open capacity</p>
                        <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $report->open_to_new_projects_percentage, 2) }}%</p>
                    </div>
                </div>
            </div>
        </section>

        @if ($this->isLocked())
            <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100">
                This month is locked. You can review your report, but edits are no longer allowed.
            </div>
        @elseif ($this->estimatedTotalPercentage() !== 100.0)
            <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100">
                Your current total is {{ number_format($this->estimatedTotalPercentage(), 2) }}%. Submission is still allowed, but admins will be notified when the total is above or below 100%.
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Assigned projects</h2>
                            <p class="mt-1 text-sm text-zinc-500">Only your active assignments are available for reporting.</p>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ ucfirst($report->status->value) }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($activeAssignments as $assignment)
                            @php($estimatedPercentage = app(\App\Services\Loe\LoeValueCalculator::class)->percentageFromHours((float) ($projectHours[$assignment->id] ?? 0), $workingDays))

                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start">
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <h3 class="font-semibold text-zinc-950 dark:text-white">{{ $assignment->project->name }}</h3>
                                        <div class="flex flex-wrap gap-2 text-xs">
                                            <span class="rounded-full bg-sky-50 px-3 py-1 font-medium text-sky-700 dark:bg-sky-500/10 dark:text-sky-200">
                                                Expected {{ number_format((float) ($assignment->expected_percentage ?? 0), 2) }}%
                                            </span>
                                            <span class="rounded-full bg-zinc-100 px-3 py-1 font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                                Current {{ number_format($estimatedPercentage, 2) }}%
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid flex-1 gap-3 md:grid-cols-2">
                                        <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                            <span>Hours</span>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.25"
                                                wire:model.live.debounce.300ms="projectHours.{{ $assignment->id }}"
                                                @disabled($this->isLocked())
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                            />
                                            @error("projectHours.{$assignment->id}") <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                        </label>

                                        <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                            <span>Line note</span>
                                            <textarea
                                                rows="1"
                                                wire:model.blur="projectNotes.{{ $assignment->id }}"
                                                @disabled($this->isLocked())
                                                class="h-[50px] w-full resize-none rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                            ></textarea>
                                            @error("projectNotes.{$assignment->id}") <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-300 p-5 text-sm text-zinc-500 dark:border-zinc-700">
                                No active project assignments are available yet. Ask an admin to assign you to a project before filling your LoE.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Time off and report notes</h2>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            <span>Time off hours</span>
                            <input
                                type="number"
                                min="0"
                                step="0.25"
                                wire:model.live.debounce.300ms="timeOffHours"
                                @disabled($this->isLocked())
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                            />
                            @error('timeOffHours') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>

                        <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            <span>Time off note</span>
                            <textarea
                                rows="1"
                                wire:model.blur="timeOffNotes"
                                @disabled($this->isLocked())
                                class="h-[50px] w-full resize-none rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                            ></textarea>
                            @error('timeOffNotes') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <label class="mt-4 grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        <span>Monthly report note</span>
                        <textarea
                            rows="5"
                            wire:model.blur="reportNotes"
                            @disabled($this->isLocked())
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                        ></textarea>
                        @error('reportNotes') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>
                </section>
            </div>

            <div class="space-y-4">
                <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Live summary</h2>
                    <div class="mt-4 space-y-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Working days</span>
                            <span class="font-medium text-zinc-950 dark:text-white">{{ $workingDays }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Total hours</span>
                            <span class="font-medium text-zinc-950 dark:text-white">{{ number_format($this->estimatedTotalHours(), 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Total allocation</span>
                            <span class="font-medium text-zinc-950 dark:text-white">{{ number_format($this->estimatedTotalPercentage(), 2) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Open to new projects</span>
                            <span class="font-medium text-zinc-950 dark:text-white">{{ number_format($this->estimatedOpenPercentage(), 2) }}% / {{ number_format($this->estimatedOpenHours(), 2) }}h</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Actions</h2>
                    <div class="mt-4 space-y-3">
                        <button
                            type="button"
                            wire:click="saveDraft"
                            @disabled($this->isLocked())
                            class="inline-flex w-full items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900"
                        >
                            Save draft
                        </button>
                        <button
                            type="button"
                            wire:click="submitReport"
                            @disabled($this->isLocked())
                            class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Submit report
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
