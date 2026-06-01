<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('document_number', 30)->nullable()->after('full_name');
            $table->index('document_number');
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->uuid('device_identifier')->nullable()->after('duplicate_flag');
            $table->index('device_identifier');
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropIndex(['device_identifier']);
            $table->dropColumn('device_identifier');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['document_number']);
            $table->dropColumn('document_number');
        });
    }
};
