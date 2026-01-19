<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_match_stats', function (Blueprint $table) {
            $table->string('dismissal_text')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('player_match_stats', function (Blueprint $table) {
            $table->dropColumn('dismissal_text');
        });
    }
};
