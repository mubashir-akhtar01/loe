<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Loe\LoeCsvExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoeExportController extends Controller
{
    public function __invoke(Request $request, string $export, LoeCsvExportService $csvExportService): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return $csvExportService->stream($export, $this->filters($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $departmentIds = collect((array) $request->query('department_ids', []))
            ->map(fn (mixed $departmentId): int => (int) $departmentId)
            ->filter(fn (int $departmentId): bool => $departmentId > 0)
            ->values()
            ->all();

        return array_filter([
            'department_id' => $this->filterValue($request, 'department_id'),
            'department_ids' => $departmentIds !== [] ? $departmentIds : null,
            'project_id' => $this->filterValue($request, 'project_id'),
            'report_month' => $this->filterValue($request, 'report_month'),
            'report_year' => $this->filterValue($request, 'report_year'),
            'status' => $this->filterValue($request, 'status'),
            'user_id' => $this->filterValue($request, 'user_id') ?? $this->filterValue($request, 'employee_id'),
        ], fn ($value): bool => filled($value));
    }

    private function filterValue(Request $request, string $key): mixed
    {
        $value = $request->query($key);

        if (filled($value)) {
            return $value;
        }

        $tableFilterValue = data_get($request->query('tableFilters', []), "{$key}.value");

        return filled($tableFilterValue) ? $tableFilterValue : null;
    }
}
