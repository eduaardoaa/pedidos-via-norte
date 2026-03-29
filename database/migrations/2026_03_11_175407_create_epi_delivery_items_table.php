<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('epi_delivery_items')) {
            Schema::create('epi_delivery_items', function (Blueprint $table) {
                $table->id();

                $table->foreignId('epi_delivery_id')
                    ->constrained('epi_deliveries')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('product_id')
                    ->constrained('products')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->constrained('product_variants')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->unsignedInteger('quantity');

                $table->date('next_expected_date')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('epi_delivery_items');
    }
};