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
        Schema::table('player_match_stats', function (Blueprint $table) {
            $table->index('team_id');
            $table->index(['match_id', 'team_id']);
            $table->index(['match_id', 'balls_faced']);
            $table->index(['match_id', 'balls_bowled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_match_stats', function (Blueprint $table) {
            $table->dropIndex(['player_match_stats_team_id_index']);
            $table->dropIndex(['player_match_stats_match_id_team_id_index']);
            $table->dropIndex(['player_match_stats_match_id_balls_faced_index']);
            $table->dropIndex(['player_match_stats_match_id_balls_bowled_index']);
        });
    }
};
