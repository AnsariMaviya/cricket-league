<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_match_stats', function (Blueprint $table) {
            $table->id('stat_id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('team_id');
            $table->integer('runs_scored')->default(0);
            $table->integer('balls_faced')->default(0);
            $table->integer('fours')->default(0);
            $table->integer('sixes')->default(0);
            $table->decimal('strike_rate', 6, 2)->default(0);
            $table->integer('wickets_taken')->default(0);
            $table->integer('overs_bowled')->default(0);
            $table->integer('balls_bowled')->default(0);
            $table->integer('runs_conceded')->default(0);
            $table->integer('maidens')->default(0);
            $table->decimal('economy', 5, 2)->default(0);
            $table->integer('catches')->default(0);
            $table->integer('stumpings')->default(0);
            $table->integer('run_outs')->default(0);
            $table->timestamps();

            $table->foreign('match_id')->references('match_id')->on('matches')->onDelete('cascade');
            $table->foreign('player_id')->references('player_id')->on('players')->onDelete('cascade');
            $table->foreign('team_id')->references('team_id')->on('teams')->onDelete('cascade');
            
            $table->unique(['match_id', 'player_id']);
            $table->index(['player_id', 'match_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_match_stats');
    }
};
