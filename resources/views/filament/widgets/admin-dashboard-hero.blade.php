<x-filament-widgets::widget>
    <div
        style="
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid rgba(251, 191, 36, 0.22);
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, 0.22), transparent 32%),
                linear-gradient(135deg, #111827 0%, #172033 48%, #1f2937 100%);
            color: #f9fafb;
            box-shadow: 0 18px 40px rgba(17, 24, 39, 0.16);
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
            <div style="flex: 1 1 520px; min-width: 320px;">
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
                    LoE intelligence
                </div>

                <div style="margin-top: 18px; max-width: 760px;">
                    <h2
                        style="
                            margin: 0;
                            font-size: 2rem;
                            line-height: 1.1;
                            font-weight: 700;
                            letter-spacing: -0.03em;
                            color: #ffffff;
                        "
                    >
                        Workforce visibility for {{ $monthLabel }}
                    </h2>

                    <p
                        style="
                            margin: 12px 0 0;
                            font-size: 0.98rem;
                            line-height: 1.7;
                            color: rgba(243, 244, 246, 0.88);
                            max-width: 720px;
                        "
                    >
                        {{ $departmentName ? "Focused on {$departmentName}" : 'Focused across every department' }}
                        with live reporting, staffing pressure, overdue follow-up, and capacity signals in one place.
                    </p>
                </div>

                <div
                    style="
                        display: flex;
                        flex-wrap: wrap;
                        gap: 12px;
                        margin-top: 22px;
                    "
                >
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
                        <span style="width: 10px; height: 10px; border-radius: 999px; background: #34d399;"></span>
                        <span style="font-size: 0.95rem;">{{ $reportsCount }} reports in scope</span>
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
                        <span style="font-size: 0.95rem;">{{ $employeesCount }} active employees</span>
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
                        <span style="font-size: 0.95rem;">{{ $projectsCount }} active projects</span>
                    </div>
                </div>
            </div>

            <div style="flex: 1 1 420px; min-width: 320px; max-width: 560px;">
                <div
                    style="
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                        gap: 14px;
                        height: 100%;
                    "
                >
                    <a
                        href="{{ \App\Filament\Resources\MonthlyLoeReports\MonthlyLoeReportResource::getUrl('index', ['tableFilters' => ['report_month' => ['value' => $reportMonth], 'report_year' => ['value' => $reportYear], 'department_id' => ['value' => $departmentId]]]) }}"
                        style="
                            display: block;
                            min-height: 124px;
                            padding: 18px;
                            border-radius: 20px;
                            text-decoration: none;
                            color: #ffffff;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.08));
                            border: 1px solid rgba(255, 255, 255, 0.12);
                        "
                    >
                        <div style="font-size: 1rem; font-weight: 700;">Open reports</div>
                        <div style="margin-top: 8px; font-size: 0.92rem; line-height: 1.6; color: rgba(229, 231, 235, 0.85);">
                            Inspect monthly submissions, exceptions, and alert activity for this period.
                        </div>
                    </a>

                    <a
                        href="{{ \App\Filament\Pages\EmployeeAnalytics::getUrl(['report_month' => $reportMonth, 'report_year' => $reportYear, 'department_ids' => $departmentId ? [$departmentId] : []]) }}"
                        style="
                            display: block;
                            min-height: 124px;
                            padding: 18px;
                            border-radius: 20px;
                            text-decoration: none;
                            color: #ffffff;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.08));
                            border: 1px solid rgba(255, 255, 255, 0.12);
                        "
                    >
                        <div style="font-size: 1rem; font-weight: 700;">Employee analytics</div>
                        <div style="margin-top: 8px; font-size: 0.92rem; line-height: 1.6; color: rgba(229, 231, 235, 0.85);">
                            Track utilization, bandwidth, and staffing pressure patterns by person.
                        </div>
                    </a>

                    <a
                        href="{{ \App\Filament\Pages\ProjectAnalytics::getUrl(['report_month' => $reportMonth, 'report_year' => $reportYear, 'department_ids' => $departmentId ? [$departmentId] : []]) }}"
                        style="
                            display: block;
                            min-height: 124px;
                            padding: 18px;
                            border-radius: 20px;
                            text-decoration: none;
                            color: #ffffff;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.08));
                            border: 1px solid rgba(255, 255, 255, 0.12);
                        "
                    >
                        <div style="font-size: 1rem; font-weight: 700;">Project analytics</div>
                        <div style="margin-top: 8px; font-size: 0.92rem; line-height: 1.6; color: rgba(229, 231, 235, 0.85);">
                            See contribution flow, load balance, and project-level effort patterns.
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
