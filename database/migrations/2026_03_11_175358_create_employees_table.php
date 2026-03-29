<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('cpf', 14)->unique();
                $table->string('registration')->unique();
                $table->date('hired_at');

                $table->foreignId('cargo_id')
                    ->constrained('cargos')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->boolean('active')->default(true);

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};