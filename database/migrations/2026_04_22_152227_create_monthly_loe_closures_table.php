<?php

use App\MonthlyLoeClosureType;
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
        Schema::create('monthly_loe_closures', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('closure_year');
            $table->unsignedTinyInteger('closure_month');
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('closure_type', array_map(static fn (MonthlyLoeClosureType $type): string => $type->value, MonthlyLoeClosureType::cases()));
            $table->timestamp('closed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['closure_year', 'closure_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_loe_closures');
    }
};
