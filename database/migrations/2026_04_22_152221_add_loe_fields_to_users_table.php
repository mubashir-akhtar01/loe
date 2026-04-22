<?php

use App\UserRole;
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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('email')
                ->constrained()
                ->nullOnDelete();
            $table->enum('role', array_map(static fn (UserRole $role): string => $role->value, UserRole::cases()))
                ->default(UserRole::Employee->value)
                ->after('password');
            $table->date('joining_date')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('joining_date');

            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['department_id', 'role', 'joining_date', 'is_active']);
        });
    }
};
