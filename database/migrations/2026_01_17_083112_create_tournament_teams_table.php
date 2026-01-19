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
        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id('tournament_team_id');
            $table->unsignedBigInteger('tournament_id');
            $table->unsignedBigInteger('team_id');
            $table->string('group_name')->nullable(); // A, B, C for group stages
            $table->integer('points')->default(0);
            $table->integer('matches_played')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('ties')->default(0);
            $table->integer('no_results')->default(0);
            $table->decimal('net_run_rate', 8, 3)->default(0);
            $table->integer('runs_scored')->default(0);
            $table->integer('runs_conceded')->default(0);
            $table->decimal('overs_faced', 8, 1)->default(0);
            $table->decimal('overs_bowled', 8, 1)->default(0);
            $table->integer('position')->nullable();
            $table->boolean('qualified')->default(false);
            $table->timestamps();
            
            $table->foreign('tournament_id')->references('tournament_id')->on('tournaments')->onDelete('cascade');
            $table->foreign('team_id')->references('team_id')->on('teams')->onDelete('cascade');
            
            $table->unique(['tournament_id', 'team_id']);
            $table->index(['tournament_id', 'group_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_teams');
    }
};
