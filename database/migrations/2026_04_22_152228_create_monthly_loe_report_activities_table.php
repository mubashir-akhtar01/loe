<?php

use App\MonthlyLoeReportActivityAction;
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
        Schema::create('monthly_loe_report_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_loe_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('action', array_map(static fn (MonthlyLoeReportActivityAction $action): string => $action->value, MonthlyLoeReportActivityAction::cases()));
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_loe_report_activities');
    }
};
