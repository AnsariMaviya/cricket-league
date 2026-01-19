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
        Schema::create('player_career_stats', function (Blueprint $table) {
            $table->id('career_stat_id');
            $table->unsignedBigInteger('player_id')->unique();
            
            // Batting stats
            $table->integer('total_matches')->default(0);
            $table->integer('total_innings_batted')->default(0);
            $table->integer('total_runs')->default(0);
            $table->integer('total_balls_faced')->default(0);
            $table->integer('total_fours')->default(0);
            $table->integer('total_sixes')->default(0);
            $table->integer('highest_score')->default(0);
            $table->decimal('batting_average', 8, 2)->default(0);
            $table->decimal('batting_strike_rate', 8, 2)->default(0);
            $table->integer('fifties')->default(0);
            $table->integer('centuries')->default(0);
            $table->integer('ducks')->default(0);
            $table->integer('not_outs')->default(0);
            
            // Bowling stats
            $table->integer('total_innings_bowled')->default(0);
            $table->integer('total_wickets')->default(0);
            $table->integer('total_balls_bowled')->default(0);
            $table->integer('total_runs_conceded')->default(0);
            $table->integer('total_maidens')->default(0);
            $table->decimal('bowling_average', 8, 2)->default(0);
            $table->decimal('bowling_economy', 8, 2)->default(0);
            $table->decimal('bowling_strike_rate', 8, 2)->default(0);
            $table->string('best_bowling_figures')->nullable(); // e.g., "5/24"
            $table->integer('five_wicket_hauls')->default(0);
            $table->integer('ten_wicket_hauls')->default(0);
            
            // Fielding stats
            $table->integer('total_catches')->default(0);
            $table->integer('total_run_outs')->default(0);
            $table->integer('total_stumpings')->default(0);
            
            $table->timestamps();
            
            $table->foreign('player_id')->references('player_id')->on('players')->onDelete('cascade');
            
            $table->index('total_runs');
            $table->index('total_wickets');
            $table->index('batting_average');
            $table->index('bowling_average');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_career_stats');
    }
};
