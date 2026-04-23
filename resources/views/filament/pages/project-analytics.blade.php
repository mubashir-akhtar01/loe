<x-filament-panels::page>
    @php($summary = $this->summary())
    @php($contributionLines = $this->contributionLines())
    @php($contributorBreakdown = $this->contributorBreakdown())
    @php($projectOverview = $this->projectOverview())
    @php($selectedDepartmentNames = $summary['department_names'] !== [] ? implode(' + ', $summary['department_names']) : 'All departments')
    @php($selectedProjectName = $summary['project_name'] ?? 'All projects')
    @php($summaryExportUrl = route('admin.loe-exports', ['export' => 'dashboard-summary', 'department_ids' => $this->departmentIds, 'project_id' => $this->projectId, 'report_month' => $this->reportMonth, 'report_year' => $this->reportYear]))
    @php($allocationsExportUrl = route('admin.loe-exports', ['export' => 'project-allocations', 'department_ids' => $this->departmentIds, 'project_id' => $this->projectId, 'report_month' => $this->reportMonth, 'report_year' => $this->reportYear]))
    @php($maxContributorHours = max(1, (float) $contributorBreakdown->max('total_hours')))
    @php($maxProjectHours = max(1, (float) $projectOverview->max(fn ($project) => (float) ($project->allocation_hours_sum ?? 0))))

    <div style="display: grid; gap: 24px;">
        <section
            style="
                overflow: hidden;
                border-radius: 28px;
                border: 1px solid rgba(56, 189, 248, 0.22);
                background:
                    radial-gradient(circle at top right, rgba(56, 189, 248, 0.18), transparent 32%),
                    linear-gradient(135deg, #0f172a 0%, #142337 48%, #1e293b 100%);
                color: #f8fafc;
                box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
            "
        >
            <div
                style="
                    display: flex;
                    flex-wrap: wrap;
                    gap: 24px;
                    align-items: stretch;
                    justify-content: space-between;
                    padding: 28px;
                "
            >
                <div style="flex: 1 1 560px; min-width: 320px;">
                    <div
                        style="
                            display: inline-flex;
                            align-items: center;
                            gap: 10px;
                            padding: 8px 14px;
                            border-radius: 999px;
                            background: rgba(255, 255, 255, 0.1);
                            border: 1px solid rgba(255, 255, 255, 0.1);
                            color: #bae6fd;
                            font-size: 11px;
                            font-weight: 700;
                            letter-spacing: 0.18em;
                            text-transform: uppercase;
                        "
                    >
                        Project intelligence
                    </div>

                    <div style="margin-top: 18px; max-width: 760px;">
                        <h1
                            style="
                                margin: 0;
                                font-size: 2rem;
                                line-height: 1.1;
                                font-weight: 700;
                                letter-spacing: -0.03em;
                                color: #ffffff;
                            "
                        >
                            {{ $selectedProjectName }}
                        </h1>

                        <p
                            style="
                                margin: 12px 0 0;
                                font-size: 0.98rem;
                                line-height: 1.7;
                                color: rgba(243, 244, 246, 0.88);
                                max-width: 720px;
                            "
                        >
                            Explore project contribution, expected-versus-actual pressure, and contributor distribution for {{ $this->monthLabel() }} across {{ $selectedDepartmentNames }}.
                        </p>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 22px;">
                        <div
                            style="
                                display: inline-flex;
                                align-items: center;
                                gap: 10px;
                                padding: 12px 16px;
                                border-radius: 16px;
                                background: rgba(255, 255, 255, 0.08);
                                border: 1px solid rgba(255, 255, 255, 0.1);
                                color: #f3f4f6;
                            "
                        >
                            <span style="width: 10px; height: 10px; border-radius: 999px; background: #38bdf8;"></span>
                            <span style="font-size: 0.95rem;">{{ number_format((float) $summary['total_hours'], 2) }}h total contribution</span>
                        </div>

                        <div
                            style="
                                display: inline-flex;
                                align-items: center;
                                gap: 10px;
                                padding: 12px 16px;
                                border-radius: 16px;
                                background: rgba(255, 255, 255, 0.08);
                                border: 1px solid rgba(255, 255, 255, 0.1);
                                color: #f3f4f6;
                            "
                        >
                            <span style="width: 10px; height: 10px; border-radius: 999px; background: #22c55e;"></span>
                            <span style="font-size: 0.95rem;">{{ number_format((float) $summary['average_allocation'], 2) }}% average allocation</span>
                        </div>

                        <div
                            style="
                                display: inline-flex;
                                align-items: center;
                                gap: 10px;
                                padding: 12px 16px;
                                border-radius: 16px;
                                background: rgba(255, 255, 255, 0.08);
                                border: 1px solid rgba(255, 255, 255, 0.1);
                                color: #f3f4f6;
                            "
                        >
                            <span style="width: 10px; height: 10px; border-radius: 999px; background: #f59e0b;"></span>
                            <span style="font-size: 0.95rem;">{{ $summary['variance_count'] }} variance flags</span>
                        </div>
                    </div>
                </div>

                <div style="flex: 1 1 460px; min-width: 320px; max-width: 600px;">
                    <div
                        style="
                            display: grid;
                            gap: 14px;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            margin-bottom: 14px;
                        "
                    >
                        <a
                            href="{{ $summaryExportUrl }}"
                            target="_blank"
                            rel="noreferrer"
                            style="
                                display: block;
                                min-height: 112px;
                                padding: 18px;
                                border-radius: 20px;
                                text-decoration: none;
                                color: #ffffff;
                                background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.08));
                                border: 1px solid rgba(255, 255, 255, 0.12);
                            "
                        >
                            <div style="font-size: 0.95rem; font-weight: 700;">Export summary</div>
                            <div style="margin-top: 8px; font-size: 0.88rem; line-height: 1.6; color: rgba(229, 231, 235, 0.85);">
                                Download the scoped KPI snapshot for the selected project view.
                            </div>
                        </a>

                        <a
                            href="{{ $allocationsExportUrl }}"
                            target="_blank"
                            rel="noreferrer"
                            style="
                                display: block;
                                min-height: 112px;
                                padding: 18px;
                                border-radius: 20px;
                                text-decoration: none;
                                color: #ffffff;
                                background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.08));
                                border: 1px solid rgba(255, 255, 255, 0.12);
                            "
                        >
                            <div style="font-size: 0.95rem; font-weight: 700;">Export allocations</div>
                            <div style="margin-top: 8px; font-size: 0.88rem; line-height: 1.6; color: rgba(229, 231, 235, 0.85);">
                                Pull the underlying project allocation rows for deeper analysis.
                            </div>
                        </a>
                    </div>

                    <form
                        method="GET"
                        style="
                            display: grid;
                            gap: 14px;
                            padding: 20px;
                            border-radius: 22px;
                            background: rgba(255, 255, 255, 0.09);
                            border: 1px solid rgba(255, 255, 255, 0.1);
                            backdrop-filter: blur(12px);
                        "
                    >
                        <div style="display: grid; gap: 6px;">
                            <div style="font-size: 0.88rem; font-weight: 600; color: #ffffff;">Departments</div>
                            <div
                                style="
                                    display: grid;
                                    gap: 10px;
                                    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                                "
                            >
                                @foreach ($this->departmentOptions() as $departmentId => $departmentName)
                                    <label
                                        for="project_department_{{ $departmentId }}"
                                        style="
                                            display: flex;
                                            align-items: center;
                                            gap: 12px;
                                            padding: 12px 14px;
                                            border-radius: 14px;
                                            border: 1px solid rgba(255, 255, 255, 0.12);
                                            background: rgba(17, 24, 39, 0.28);
                                            color: #ffffff;
                                            cursor: pointer;
                                        "
                                    >
                                        <input
                                            id="project_department_{{ $departmentId }}"
                                            type="checkbox"
                                            name="department_ids[]"
                                            value="{{ $departmentId }}"
                                            @checked(in_array((int) $departmentId, $this->departmentIds, true))
                                            style="
                                                width: 16px;
                                                height: 16px;
                                                accent-color: #38bdf8;
                                                flex-shrink: 0;
                                            "
                                        />
                                        <span style="font-size: 0.92rem; font-weight: 600;">{{ $departmentName }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div style="font-size: 0.78rem; color: rgba(229, 231, 235, 0.72);">Select one department or both together for a combined project view.</div>
                        </div>

                        <div style="display: grid; gap: 6px;">
                            <label for="project_id" style="font-size: 0.88rem; font-weight: 600; color: #ffffff;">Project</label>
                            <select
                                id="project_id"
                                name="project_id"
                                style="
                                    width: 100%;
                                    border-radius: 14px;
                                    border: 1px solid rgba(255, 255, 255, 0.12);
                                    background: rgba(17, 24, 39, 0.28);
                                    color: #ffffff;
                                    padding: 12px 14px;
                                "
                            >
                                <option value="">All projects</option>
                                @foreach ($this->projectOptions() as $projectId => $projectName)
                                    <option value="{{ $projectId }}" @selected($this->projectId === (int) $projectId)>{{ $projectName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display: grid; gap: 14px; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
                            <div style="display: grid; gap: 6px;">
                                <label for="report_month" style="font-size: 0.88rem; font-weight: 600; color: #ffffff;">Month</label>
                                <select
                                    id="report_month"
                                    name="report_month"
                                    style="
                                        width: 100%;
                                        border-radius: 14px;
                                        border: 1px solid rgba(255, 255, 255, 0.12);
                                        background: rgba(17, 24, 39, 0.28);
                                        color: #ffffff;
                                        padding: 12px 14px;
                                    "
                                >
                                    @foreach (collect(range(1, 12))->mapWithKeys(fn (int $month): array => [$month => now()->setMonth($month)->startOfMonth()->format('F')])->all() as $monthValue => $monthLabel)
                                        <option value="{{ $monthValue }}" @selected($this->reportMonth === (int) $monthValue)>{{ $monthLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="display: grid; gap: 6px;">
                                <label for="report_year" style="font-size: 0.88rem; font-weight: 600; color: #ffffff;">Year</label>
                                <input
                                    id="report_year"
                                    type="number"
                                    name="report_year"
                                    value="{{ $this->reportYear }}"
                                    style="
                                        width: 100%;
                                        border-radius: 14px;
                                        border: 1px solid rgba(255, 255, 255, 0.12);
                                        background: rgba(17, 24, 39, 0.28);
                                        color: #ffffff;
                                        padding: 12px 14px;
                                    "
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            style="
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                width: 100%;
                                padding: 12px 16px;
                                border-radius: 14px;
                                border: 0;
                                background: linear-gradient(135deg, #0ea5e9, #06b6d4);
                                color: #ffffff;
                                font-weight: 700;
                                cursor: pointer;
                            "
                        >
                            Apply filters
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <div
            style="
                display: grid;
                gap: 16px;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            "
        >
            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Selected project</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $selectedProjectName }}</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">Current project scope for this analytics view.</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Selected departments</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $selectedDepartmentNames }}</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">The current department scope for project reporting.</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Current month</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $this->monthLabel() }}</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">The month driving all contribution and variance numbers.</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Total contributed hours</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ number_format((float) $summary['total_hours'], 2) }}h</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">All reported project hours in the selected scope.</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Variance flags</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $summary['variance_count'] }}</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">Actual allocation lines above the expected project allocation.</div>
            </div>
        </div>

        <div
            style="
                display: grid;
                gap: 24px;
                grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.95fr);
            "
        >
            <section style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                @if ($this->projectId)
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                        <div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Top contributors</div>
                            <div style="margin-top: 6px; font-size: 0.94rem; color: #64748b;">Who contributed the most hours to {{ $selectedProjectName }} in {{ $this->monthLabel() }}.</div>
                        </div>
                        <div style="border-radius: 999px; background: #f1f5f9; padding: 10px 14px; font-size: 0.9rem; font-weight: 600; color: #334155;">
                            {{ $summary['contributors'] }} contributors
                        </div>
                    </div>

                    <div style="display: grid; gap: 14px; margin-top: 24px;">
                        @forelse ($contributorBreakdown as $line)
                            <div wire:key="project-contributor-{{ $line->user_id }}" style="border-radius: 20px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                    <div style="min-width: 0;">
                                        <div style="font-size: 0.98rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $line->user_name ?? 'Unknown employee' }}</div>
                                        <div style="margin-top: 4px; font-size: 0.84rem; color: #64748b;">{{ $line->department_name ?? 'Unassigned' }} · {{ number_format((float) $line->average_percentage, 2) }}% average allocation</div>
                                    </div>
                                    <span style="display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; background: #e0f2fe; padding: 8px 12px; font-size: 0.78rem; font-weight: 700; color: #075985;">
                                        {{ number_format((float) $line->total_hours, 2) }}h
                                    </span>
                                </div>

                                <div style="height: 10px; overflow: hidden; border-radius: 999px; background: #e2e8f0; margin-top: 14px;">
                                    <div style="height: 100%; width: {{ min(100, ((float) $line->total_hours / $maxContributorHours) * 100) }}%; border-radius: 999px; background: linear-gradient(90deg, #38bdf8 0%, #06b6d4 100%);"></div>
                                </div>
                            </div>
                        @empty
                            <div style="border-radius: 20px; border: 1px dashed #cbd5e1; background: #f8fafc; padding: 20px; font-size: 0.92rem; color: #64748b;">
                                No project contribution data is available for the selected scope.
                            </div>
                        @endforelse
                    </div>
                @else
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                        <div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Project overview</div>
                            <div style="margin-top: 6px; font-size: 0.94rem; color: #64748b;">The projects drawing the most reported hours in {{ $this->monthLabel() }}.</div>
                        </div>
                    </div>

                    <div style="display: grid; gap: 14px; margin-top: 24px;">
                        @forelse ($projectOverview as $project)
                            <div wire:key="project-overview-card-{{ $project->id }}" style="border-radius: 20px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                    <div style="min-width: 0;">
                                        <div style="font-size: 0.98rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $project->name }}</div>
                                        <div style="margin-top: 4px; font-size: 0.84rem; color: #64748b;">{{ $project->project_assignments_count }} assignments</div>
                                    </div>
                                    <span style="display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; background: #e0f2fe; padding: 8px 12px; font-size: 0.78rem; font-weight: 700; color: #075985;">
                                        {{ number_format((float) ($project->allocation_hours_sum ?? 0), 2) }}h
                                    </span>
                                </div>

                                <div style="height: 10px; overflow: hidden; border-radius: 999px; background: #e2e8f0; margin-top: 14px;">
                                    <div style="height: 100%; width: {{ min(100, (((float) ($project->allocation_hours_sum ?? 0)) / $maxProjectHours) * 100) }}%; border-radius: 999px; background: linear-gradient(90deg, #38bdf8 0%, #06b6d4 100%);"></div>
                                </div>
                            </div>
                        @empty
                            <div style="border-radius: 20px; border: 1px dashed #cbd5e1; background: #f8fafc; padding: 20px; font-size: 0.92rem; color: #64748b;">
                                No projects are available yet.
                            </div>
                        @endforelse
                    </div>
                @endif
            </section>

            <section style="display: grid; gap: 24px;">
                <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                    <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Scope notes</div>
                    <div style="margin-top: 8px; font-size: 0.92rem; line-height: 1.7; color: #64748b;">
                        This page now reflects only the selected month and departments. Use it to inspect a single project deeply or leave the project filter open to compare where effort is flowing right now.
                    </div>
                </div>

                <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                    <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Expected allocation signal</div>
                    <div style="margin-top: 8px; font-size: 0.92rem; line-height: 1.7; color: #64748b;">
                        Average expected allocation for the current scope is <strong style="color: #0f172a;">{{ number_format((float) $summary['expected_allocation'], 2) }}%</strong>. Variance flags count any line where actual reported allocation exceeds the expected value.
                    </div>
                </div>
            </section>
        </div>

        <section style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05); overflow: hidden;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                <div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Current month contribution lines</div>
                    <div style="margin-top: 6px; font-size: 0.94rem; color: #64748b;">Detailed project contribution rows for {{ $this->monthLabel() }}.</div>
                </div>
                <div style="border-radius: 999px; background: #f1f5f9; padding: 10px 14px; font-size: 0.9rem; font-weight: 600; color: #334155;">
                    {{ $this->monthLabel() }}
                </div>
            </div>

            <div style="overflow-x: auto; margin-top: 24px;">
                <table style="width: 100%; min-width: 960px; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Project</th>
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Employee</th>
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Department</th>
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Hours</th>
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Allocation</th>
                            <th style="padding: 14px 0 14px 0; font-weight: 600;">Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contributionLines as $line)
                            <tr wire:key="project-line-row-{{ $line->id }}" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 16px 16px 16px 0; font-weight: 600; color: #0f172a;">{{ $line->project?->name ?? 'Unknown project' }}</td>
                                <td style="padding: 16px 16px 16px 0; color: #475569;">{{ $line->monthlyLoeReport?->user?->name }}</td>
                                <td style="padding: 16px 16px 16px 0; color: #475569;">{{ $line->monthlyLoeReport?->department?->name ?? 'Unassigned' }}</td>
                                <td style="padding: 16px 16px 16px 0; color: #475569;">{{ number_format((float) $line->entered_hours, 2) }}h</td>
                                <td style="padding: 16px 16px 16px 0; color: #475569;">{{ number_format((float) $line->calculated_percentage, 2) }}%</td>
                                <td style="padding: 16px 0 16px 0; color: #475569;">{{ $line->expected_percentage !== null ? number_format((float) $line->expected_percentage, 2) . '%' : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 28px 0; text-align: center; font-size: 0.92rem; color: #64748b;">
                                    No project allocation lines matched the selected month, departments, and project scope.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
