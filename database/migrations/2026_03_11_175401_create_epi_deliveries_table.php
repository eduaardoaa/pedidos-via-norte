<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('epi_deliveries')) {
            Schema::create('epi_deliveries', function (Blueprint $table) {
                $table->id();

                $table->foreignId('employee_id')
                    ->constrained('employees')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->date('delivery_date');

                $table->text('notes')->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('epi_deliveries');
    }
};