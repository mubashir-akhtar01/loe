<?php

use App\MonthlyLoeReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_loe_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('report_year');
            $table->unsignedTinyInteger('report_month');
            $table->enum('status', array_map(static fn (MonthlyLoeReportStatus $status): string => $status->value, MonthlyLoeReportStatus::cases()))
                ->default(MonthlyLoeReportStatus::Draft->value);
            $table->text('report_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('total_days', 8, 2)->default(0);
            $table->decimal('total_percentage', 6, 2)->default(0);
            $table->decimal('time_off_hours', 8, 2)->default(0);
            $table->decimal('time_off_percentage', 6, 2)->default(0);
            $table->decimal('open_to_new_projects_hours', 8, 2)->default(0);
            $table->decimal('open_to_new_projects_percentage', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'report_year', 'report_month']);
            $table->index(['report_year', 'report_month']);
            $table->index(['department_id', 'report_year', 'report_month']);
            $table->index(['status', 'report_year', 'report_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_loe_reports');
    }
};
