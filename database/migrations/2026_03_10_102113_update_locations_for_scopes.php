<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('scope')->default('rota')->after('route_id');
        });

        DB::statement('ALTER TABLE locations DROP FOREIGN KEY locations_route_id_foreign');
        DB::statement('ALTER TABLE locations MODIFY route_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE locations ADD CONSTRAINT locations_route_id_foreign FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE locations DROP FOREIGN KEY locations_route_id_foreign');
        DB::statement('ALTER TABLE locations MODIFY route_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE locations ADD CONSTRAINT locations_route_id_foreign FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE');

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};