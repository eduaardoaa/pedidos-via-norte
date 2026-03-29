<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('stock_location_id')
                ->nullable()
                ->constrained('stock_locations')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->date('movement_date')->index();
            $table->string('type', 30); // initial, entry, exit, adjustment

            $table->decimal('quantity', 15, 3);
            $table->decimal('balance_before', 15, 3)->default(0);
            $table->decimal('balance_after', 15, 3)->default(0);

            $table->string('document_number', 100)->nullable();
            $table->string('source_name', 150)->nullable(); // fornecedor/origem

            $table->string('reference_type', 100)->nullable(); // pedido, entrada_manual...
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'product_variant_id'], 'idx_product_variant');
            $table->index(['movement_date', 'type'], 'idx_movement_date_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};