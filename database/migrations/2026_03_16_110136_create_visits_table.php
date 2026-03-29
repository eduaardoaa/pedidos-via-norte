<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('visited_at');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->string('address')->nullable();
            $table->string('display_name')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'visited_at']);
            $table->index('visited_at');
            $table->index('location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};