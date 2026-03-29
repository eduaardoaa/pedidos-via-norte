<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 120);
            $table->string('sku', 80)->nullable()->unique();
            $table->decimal('current_stock', 15, 3)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(['product_id', 'name'], 'uk_product_variant_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};