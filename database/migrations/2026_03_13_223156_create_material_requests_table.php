<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('route_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();

            $table->string('requester_role', 30); // cabo_turma | supervisor
            $table->string('scope', 30); // rota | almoxarifado
            $table->string('status', 30)->default('pending'); // pending | approved | rejected

            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'scope']);
            $table->index(['user_id', 'created_at']);
            $table->index('route_id');
            $table->index('location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};