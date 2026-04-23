<?php

namespace App\Services\Loe;

use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\MonthlyLoeReportActivityAction;

class MonthlyLoeReportActivityLogger
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function log(
        MonthlyLoeReport $report,
        MonthlyLoeReportActivityAction $action,
        ?User $user = null,
        ?string $description = null,
        array $meta = [],
    ): void {
        $report->activities()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'meta' => $meta,
        ]);
    }
}
