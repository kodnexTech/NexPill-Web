<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table): void {
            $table->decimal('dose_amount', 10, 3)->nullable()->after('form');
            $table->string('dose_unit', 32)->nullable()->after('dose_amount');
            $table->string('food_instruction', 16)->default('none')->after('dose_unit');
            $table->text('doctor_notes')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table): void {
            $table->dropColumn(['dose_amount', 'dose_unit', 'food_instruction', 'doctor_notes']);
        });
    }
};
