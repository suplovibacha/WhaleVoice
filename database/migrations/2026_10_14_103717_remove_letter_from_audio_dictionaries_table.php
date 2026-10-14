<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audio_dictionaries', function (Blueprint $table) {
            $table->dropColumn('letter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audio_dictionaries', function (Blueprint $table) {
            $table->string('letter')->nullable()->after('id');
        });
    }
};