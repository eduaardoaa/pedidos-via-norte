<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('sku', 80)->nullable()->unique();
            $table->text('description')->nullable();

            $table->foreignId('product_unit_id')
                ->constrained('product_units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->boolean('uses_variants')->default(false);
            $table->decimal('current_stock', 15, 3)->default(0); // só sem variação
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};