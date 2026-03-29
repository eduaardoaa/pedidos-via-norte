<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->decimal('request_latitude', 10, 7)->nullable()->after('approved_at');
            $table->decimal('request_longitude', 10, 7)->nullable()->after('request_latitude');
            $table->decimal('request_location_accuracy', 8, 2)->nullable()->after('request_longitude');

            $table->string('request_street')->nullable()->after('request_location_accuracy');
            $table->string('request_number')->nullable()->after('request_street');
            $table->string('request_neighborhood')->nullable()->after('request_number');
            $table->string('request_city')->nullable()->after('request_neighborhood');
            $table->string('request_state', 100)->nullable()->after('request_city');
            $table->string('request_zipcode', 20)->nullable()->after('request_state');
            $table->string('request_full_address')->nullable()->after('request_zipcode');

            $table->timestamp('request_location_captured_at')->nullable()->after('request_full_address');
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn([
                'request_latitude',
                'request_longitude',
                'request_location_accuracy',
                'request_street',
                'request_number',
                'request_neighborhood',
                'request_city',
                'request_state',
                'request_zipcode',
                'request_full_address',
                'request_location_captured_at',
            ]);
        });
    }
};