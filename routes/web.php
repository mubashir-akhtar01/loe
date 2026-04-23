<?php

use App\Filament\Employee\Pages\Dashboard as EmployeeDashboard;
use App\Filament\Employee\Pages\MyAnalytics;
use App\Filament\Employee\Pages\MyReport;
use App\Filament\Employee\Pages\ReportHistory;
use App\Filament\Employee\Pages\ViewReport;
use App\Filament\Pages\Dashboard as AdminDashboard;
use App\Http\Controllers\Admin\LoeExportController;
use App\Models\MonthlyLoeReport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('filament.employee.auth.login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        abort_unless($user !== null, 403);

        return redirect()->to(
            $user->isAdmin()
                ? AdminDashboard::getUrl(panel: 'admin')
                : EmployeeDashboard::getUrl(panel: 'employee'),
        );
    })->name('dashboard');
    Route::get('admin/exports/{export}', LoeExportController::class)->name('admin.loe-exports');
    Route::get('loe/analytics', fn () => redirect()->to(MyAnalytics::getUrl(panel: 'employee')))->name('loe.analytics');
    Route::get('loe/report', fn () => redirect()->to(MyReport::getUrl(panel: 'employee')))->name('loe.report');
    Route::get('loe/history', fn () => redirect()->to(ReportHistory::getUrl(panel: 'employee')))->name('loe.history');
    Route::get('loe/history/{report}', fn (MonthlyLoeReport $report) => redirect()->to(
        ViewReport::getUrl(['report' => $report], panel: 'employee')
    ))->name('loe.show');
});

require __DIR__.'/settings.php';
