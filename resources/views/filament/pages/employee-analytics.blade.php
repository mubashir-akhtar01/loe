<x-filament-panels::page>
    @php($summary = $this->summary())
    @php($reports = $this->reports())
    @php($employeeBreakdown = $this->employeeBreakdown())
    @php($projectBreakdown = $this->projectBreakdown())
    @php($selectedDepartmentNames = $summary['department_names'] !== [] ? implode(' + ', $summary['department_names']) : 'All departments')
    @php($selectedEmployeeName = $summary['employee_name'] ?? 'All employees')
    @php($summaryExportUrl = route('admin.loe-exports', ['export' => 'dashboard-summary', 'department_ids' => $this->departmentIds, 'employee_id' => $this->employeeId, 'report_month' => $this->reportMonth, 'report_year' => $this->reportYear]))
    @php($reportsExportUrl = route('admin.loe-exports', ['export' => 'monthly-reports', 'department_ids' => $this->departmentIds, 'employee_id' => $this->employeeId, 'report_month' => $this->reportMonth, 'report_year' => $this->reportYear]))
    @php($allocationTone = (float) $summary['average_allocation'] > 100 ? '#ef4444' : ((float) $summary['average_allocation'] >= 85 ? '#f59e0b' : '#10b981'))
    @php($maxAllocation = max(1, (float) $employeeBreakdown->max('total_percentage')))
    @php($maxProjectHours = max(1, (float) $projectBreakdown->max('total_hours')))

    <div style="display: grid; gap: 24px;">
        <section
            style="
                overflow: hidden;
                border-radius: 28px;
                border: 1px solid rgba(251, 191, 36, 0.22);
                background:
                    radial-gradient(circle at top left, rgba(245, 158, 11, 0.2), transparent 34%),
                    linear-gradient(135deg, #111827 0%, #172033 48%, #1f2937 100%);
                color: #f8fafc;
                box-shadow: 0 18px 40px rgba(17, 24, 39, 0.14);
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
                            color: #fde68a;
                            font-size: 11px;
                            font-weight: 700;
                            letter-spacing: 0.18em;
                            text-transform: uppercase;
                        "
                    >
                        Department workforce view
                    </div>

                    <div style="margin-top: 18px; max-width: 740px;">
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
                            {{ $selectedDepartmentNames }}
                        </h1>

                        <p
                            style="
                                margin: 12px 0 0;
                                font-size: 0.98rem;
                                line-height: 1.7;
                                color: rgba(243, 244, 246, 0.88);
                                max-width: 700px;
                            "
                        >
                            Monitor employee load, remaining bandwidth, and project contribution for {{ $this->monthLabel() }} with a department-first view.
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
                            <span style="width: 10px; height: 10px; border-radius: 999px; background: {{ $allocationTone }};"></span>
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
                            <span style="width: 10px; height: 10px; border-radius: 999px; background: #38bdf8;"></span>
                            <span style="font-size: 0.95rem;">{{ number_format((float) $summary['average_open_capacity'], 2) }}% average open capacity</span>
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
                            <span style="font-size: 0.95rem;">{{ $summary['submitted_reports'] }} submitted / {{ $summary['total_reports'] }} total reports</span>
                        </div>
                    </div>
                </div>

                <div style="flex: 1 1 440px; min-width: 320px; max-width: 580px;">
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
                                Download the current month KPI snapshot for the selected departments.
                            </div>
                        </a>

                        <a
                            href="{{ $reportsExportUrl }}"
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
                            <div style="font-size: 0.95rem; font-weight: 700;">Export reports</div>
                            <div style="margin-top: 8px; font-size: 0.88rem; line-height: 1.6; color: rgba(229, 231, 235, 0.85);">
                                Pull the underlying employee report rows for the selected month.
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
                                        for="department_{{ $departmentId }}"
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
                                            id="department_{{ $departmentId }}"
                                            type="checkbox"
                                            name="department_ids[]"
                                            value="{{ $departmentId }}"
                                            @checked(in_array((int) $departmentId, $this->departmentIds, true))
                                            style="
                                                width: 16px;
                                                height: 16px;
                                                accent-color: #f59e0b;
                                                flex-shrink: 0;
                                            "
                                        />
                                        <span style="font-size: 0.92rem; font-weight: 600;">{{ $departmentName }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div style="font-size: 0.78rem; color: rgba(229, 231, 235, 0.72);">Select one department or both together for a combined view.</div>
                        </div>

                        <div style="display: grid; gap: 6px;">
                            <label for="employee_id" style="font-size: 0.88rem; font-weight: 600; color: #ffffff;">Employee</label>
                            <select
                                id="employee_id"
                                name="employee_id"
                                style="
                                    width: 100%;
                                    border-radius: 14px;
                                    border: 1px solid rgba(255, 255, 255, 0.12);
                                    background: rgba(17, 24, 39, 0.28);
                                    color: #ffffff;
                                    padding: 12px 14px;
                                "
                            >
                                <option value="">All employees</option>
                                @foreach ($this->employeeOptions() as $employeeId => $employeeName)
                                    <option value="{{ $employeeId }}" @selected($this->employeeId === (int) $employeeId)>{{ $employeeName }}</option>
                                @endforeach
                            </select>
                            <div style="font-size: 0.78rem; color: rgba(229, 231, 235, 0.72);">Narrow the view to one employee inside the selected departments.</div>
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
                                background: linear-gradient(135deg, #f59e0b, #f97316);
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
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Selected employee</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $selectedEmployeeName }}</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">The current employee scope inside the selected departments.</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Selected departments</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $selectedDepartmentNames }}</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">The current department scope for this view.</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Current month</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $this->monthLabel() }}</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">The reporting window driving every metric on this page.</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">{{ $this->employeeId ? 'Employee allocation' : 'Average allocation' }}</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ number_format((float) ($this->employeeId ? $summary['selected_employee_allocation'] : $summary['average_allocation']), 2) }}%</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">{{ $this->employeeId ? 'Selected employee LoE allocation for this month.' : 'Average utilized effort across the selected departments.' }}</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">{{ $this->employeeId ? 'Employee spare capacity' : 'Average open capacity' }}</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ number_format((float) ($this->employeeId ? $summary['selected_employee_open_capacity'] : $summary['average_open_capacity']), 2) }}%</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">{{ $this->employeeId ? 'Selected employee remaining open capacity this month.' : 'Typical available bandwidth left after reporting.' }}</div>
            </div>

            <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">{{ $this->employeeId ? 'Employee time off' : 'Overallocated employees' }}</div>
                <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $this->employeeId ? number_format((float) $summary['selected_employee_time_off'], 2) . '%' : $summary['overallocated_employees'] }}</div>
                <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">{{ $this->employeeId ? 'Selected employee reported time off for this month.' : 'Employees reporting above 100% allocation this month.' }}</div>
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
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                    <div>
                        <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Employee load snapshot</div>
                        <div style="margin-top: 6px; font-size: 0.94rem; color: #64748b;">Current month allocation, time off, and bandwidth by employee.</div>
                    </div>
                    <div style="border-radius: 999px; background: #f1f5f9; padding: 10px 14px; font-size: 0.9rem; font-weight: 600; color: #334155;">
                        {{ $summary['submitted_reports'] }} submitted / {{ $summary['total_reports'] }} total
                    </div>
                </div>

                <div style="display: grid; gap: 14px; margin-top: 24px;">
                    @forelse ($employeeBreakdown as $report)
                        <div wire:key="employee-report-card-{{ $report->id }}" style="border-radius: 20px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 16px;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                                <div>
                                    <div style="font-size: 0.98rem; font-weight: 700; color: #0f172a;">{{ $report->user?->name }}</div>
                                    <div style="margin-top: 4px; font-size: 0.84rem; color: #64748b;">{{ $report->department?->name ?? 'Unassigned' }}</div>
                                </div>
                                <div style="font-size: 0.76rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: #94a3b8;">
                                    {{ ucfirst($report->status->value) }}
                                </div>
                            </div>

                            <div style="height: 12px; overflow: hidden; border-radius: 999px; background: #e2e8f0; margin-top: 14px;">
                                <div style="height: 100%; width: {{ min(100, (((float) $report->total_percentage) / $maxAllocation) * 100) }}%; border-radius: 999px; background: linear-gradient(90deg, #fbbf24 0%, #fb923c 52%, #f43f5e 100%);"></div>
                            </div>

                            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 10px; font-size: 0.82rem; color: #64748b;">
                                <span>{{ number_format((float) $report->total_percentage, 2) }}% allocation</span>
                                <span>{{ number_format((float) $report->time_off_percentage, 2) }}% time off</span>
                                <span>{{ number_format((float) $report->open_to_new_projects_percentage, 2) }}% open</span>
                            </div>
                        </div>
                    @empty
                        <div style="border-radius: 20px; border: 1px dashed #cbd5e1; background: #f8fafc; padding: 20px; font-size: 0.92rem; color: #64748b;">
                            No employee reports are available for the selected departments in {{ $this->monthLabel() }}.
                        </div>
                    @endforelse
                </div>
            </section>

            <section style="display: grid; gap: 24px;">
                <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                    <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Project contribution</div>
                    <div style="margin-top: 6px; font-size: 0.94rem; color: #64748b;">The projects receiving the most reported hours this month.</div>

                    <div style="display: grid; gap: 14px; margin-top: 24px;">
                        @forelse ($projectBreakdown as $line)
                            <div wire:key="employee-project-breakdown-{{ $line->project_id }}" style="border-radius: 20px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                    <div style="min-width: 0;">
                                        <div style="font-size: 0.98rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $line->project?->name ?? 'Unknown project' }}</div>
                                        <div style="margin-top: 4px; font-size: 0.86rem; color: #64748b;">{{ number_format((float) $line->average_percentage, 2) }}% average allocation</div>
                                    </div>
                                    <span style="display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; background: #fef3c7; padding: 8px 12px; font-size: 0.78rem; font-weight: 700; color: #92400e;">
                                        {{ number_format((float) $line->total_hours, 2) }}h
                                    </span>
                                </div>

                                <div style="height: 10px; overflow: hidden; border-radius: 999px; background: #e2e8f0; margin-top: 14px;">
                                    <div style="height: 100%; width: {{ min(100, ((float) $line->total_hours / $maxProjectHours) * 100) }}%; border-radius: 999px; background: linear-gradient(90deg, #38bdf8 0%, #06b6d4 100%);"></div>
                                </div>
                            </div>
                        @empty
                            <div style="border-radius: 20px; border: 1px dashed #cbd5e1; background: #f8fafc; padding: 20px; font-size: 0.92rem; color: #64748b;">
                                No project allocations are available yet for the selected scope.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                    <div style="display: flex; align-items: flex-start; gap: 16px;">
                        <div style="display: flex; width: 48px; height: 48px; align-items: center; justify-content: center; border-radius: 18px; background: #fef3c7; color: #b45309; font-size: 1.2rem; font-weight: 700;">
                            i
                        </div>
                        <div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">Reading the signal</div>
                            <div style="margin-top: 8px; font-size: 0.92rem; line-height: 1.7; color: #64748b;">
                                This page now reflects only the selected month. Use it to compare employee load inside Engineering, Experience, or both together without blending historical months into the same view.
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05); overflow: hidden;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                <div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Current month reports</div>
                    <div style="margin-top: 6px; font-size: 0.94rem; color: #64748b;">Detailed employee reporting rows for {{ $this->monthLabel() }}.</div>
                </div>
                <div style="border-radius: 999px; background: #f1f5f9; padding: 10px 14px; font-size: 0.9rem; font-weight: 600; color: #334155;">
                    {{ $this->monthLabel() }}
                </div>
            </div>

            <div style="overflow-x: auto; margin-top: 24px;">
                <table style="width: 100%; min-width: 860px; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Employee</th>
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Department</th>
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Status</th>
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Allocation</th>
                            <th style="padding: 14px 16px 14px 0; font-weight: 600;">Time off</th>
                            <th style="padding: 14px 0 14px 0; font-weight: 600;">Open capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr wire:key="employee-report-row-{{ $report->id }}" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 16px 16px 16px 0; font-weight: 600; color: #0f172a;">{{ $report->user?->name }}</td>
                                <td style="padding: 16px 16px 16px 0; color: #475569;">{{ $report->department?->name ?? 'Unassigned' }}</td>
                                <td style="padding: 16px 16px 16px 0;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; background: #f1f5f9; padding: 7px 12px; font-size: 0.76rem; font-weight: 700; color: #334155;">
                                        {{ ucfirst($report->status->value) }}
                                    </span>
                                </td>
                                <td style="padding: 16px 16px 16px 0; color: #475569;">{{ number_format((float) $report->total_percentage, 2) }}%</td>
                                <td style="padding: 16px 16px 16px 0; color: #475569;">{{ number_format((float) $report->time_off_percentage, 2) }}%</td>
                                <td style="padding: 16px 0 16px 0; color: #475569;">{{ number_format((float) $report->open_to_new_projects_percentage, 2) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 28px 0; text-align: center; font-size: 0.92rem; color: #64748b;">
                                    No employee reporting data is available for the selected month and departments.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
