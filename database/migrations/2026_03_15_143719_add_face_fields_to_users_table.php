<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('face_photo_path')->nullable()->after('password');
            $table->longText('face_descriptor')->nullable()->after('face_photo_path');
            $table->timestamp('face_registered_at')->nullable()->after('face_descriptor');
            $table->boolean('must_register_face')->default(false)->after('face_registered_at');
            $table->timestamp('last_activity_at')->nullable()->after('must_register_face');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'face_photo_path',
                'face_descriptor',
                'face_registered_at',
                'must_register_face',
                'last_activity_at',
            ]);
        });
    }
};