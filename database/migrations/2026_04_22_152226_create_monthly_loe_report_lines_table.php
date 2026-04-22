<?php

use App\MonthlyLoeReportLineType;
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
        Schema::create('monthly_loe_report_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_loe_report_id')->constrained()->cascadeOnDelete();
            $table->enum('line_type', array_map(static fn (MonthlyLoeReportLineType $type): string => $type->value, MonthlyLoeReportLineType::cases()));
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('entered_hours', 8, 2)->default(0);
            $table->decimal('calculated_days', 8, 2)->default(0);
            $table->decimal('calculated_percentage', 6, 2)->default(0);
            $table->decimal('expected_percentage', 6, 2)->nullable();
            $table->text('line_notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('line_type');
            $table->index('project_id');
            $table->index(['monthly_loe_report_id', 'line_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_loe_report_lines');
    }
};
