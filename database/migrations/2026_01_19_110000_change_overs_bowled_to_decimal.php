<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_match_stats', function (Blueprint $table) {
            $table->decimal('overs_bowled', 5, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('player_match_stats', function (Blueprint $table) {
            $table->integer('overs_bowled')->default(0)->change();
        });
    }
};
