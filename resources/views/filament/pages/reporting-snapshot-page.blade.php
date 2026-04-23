<x-filament-panels::page>
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
                        Reporting drilldown
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
                            {{ $this->getTitle() }}
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
                            {{ $this->heroDescription() }}
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
                            <span style="font-size: 0.95rem;">{{ $this->scopePill() }}</span>
                        </div>

                        <a
                            href="{{ $this->backToDashboardUrl() }}"
                            style="
                                display: inline-flex;
                                align-items: center;
                                gap: 10px;
                                padding: 12px 16px;
                                border-radius: 16px;
                                background: rgba(255, 255, 255, 0.08);
                                border: 1px solid rgba(255, 255, 255, 0.1);
                                color: #f3f4f6;
                                text-decoration: none;
                            "
                        >
                            Back to command center
                        </a>
                    </div>
                </div>

                <div style="flex: 1 1 420px; min-width: 320px; max-width: 560px;">
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

                        <div style="display: grid; gap: 6px;">
                            <label for="department_id" style="font-size: 0.88rem; font-weight: 600; color: #ffffff;">Department</label>
                            <select
                                id="department_id"
                                name="department_id"
                                style="
                                    width: 100%;
                                    border-radius: 14px;
                                    border: 1px solid rgba(255, 255, 255, 0.12);
                                    background: rgba(17, 24, 39, 0.28);
                                    color: #ffffff;
                                    padding: 12px 14px;
                                "
                            >
                                <option value="">All departments</option>
                                @foreach ($this->departmentOptions() as $departmentId => $departmentName)
                                    <option value="{{ $departmentId }}" @selected($this->departmentId === (int) $departmentId)>{{ $departmentName }}</option>
                                @endforeach
                            </select>
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
            @foreach ($this->summaryCards() as $card)
                <div style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 20px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                    <div style="font-size: 0.9rem; font-weight: 600; color: #64748b;">{{ $card['label'] }}</div>
                    <div style="margin-top: 10px; font-size: 1.65rem; font-weight: 700; color: #0f172a;">{{ $card['value'] }}</div>
                    <div style="margin-top: 10px; font-size: 0.92rem; line-height: 1.6; color: #64748b;">{{ $card['description'] }}</div>
                </div>
            @endforeach
        </div>

        <section style="border-radius: 24px; border: 1px solid #e2e8f0; background: #ffffff; padding: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05); overflow: hidden;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                <div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">{{ $this->tableHeading() }}</div>
                    <div style="margin-top: 6px; font-size: 0.94rem; color: #64748b;">{{ $this->tableSubheading() }}</div>
                </div>
                <div style="border-radius: 999px; background: #f1f5f9; padding: 10px 14px; font-size: 0.9rem; font-weight: 600; color: #334155;">
                    {{ $this->monthLabel() }}
                </div>
            </div>

            <div style="overflow-x: auto; margin-top: 24px;">
                <table style="width: 100%; min-width: 860px; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">
                            @foreach ($this->tableColumns() as $column)
                                <th style="padding: 14px 16px 14px 0; font-weight: 600;">{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->tableRows() as $row)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                @foreach ($row as $cell)
                                    <td style="padding: 16px 16px 16px 0; color: #475569;">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($this->tableColumns()) }}" style="padding: 28px 0; text-align: center; font-size: 0.92rem; color: #64748b;">
                                    {{ $this->tableEmptyState() }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
