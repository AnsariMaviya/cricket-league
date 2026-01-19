<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ball_by_ball', function (Blueprint $table) {
            $table->id('ball_id');
            $table->unsignedBigInteger('innings_id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('batsman_id');
            $table->unsignedBigInteger('bowler_id');
            $table->integer('over_number');
            $table->integer('ball_number');
            $table->integer('runs_scored')->default(0);
            $table->boolean('is_wicket')->default(false);
            $table->string('wicket_type', 50)->nullable();
            $table->unsignedBigInteger('fielder_id')->nullable();
            $table->enum('extra_type', ['none', 'wide', 'no_ball', 'bye', 'leg_bye'])->default('none');
            $table->integer('extra_runs')->default(0);
            $table->boolean('is_four')->default(false);
            $table->boolean('is_six')->default(false);
            $table->text('commentary')->nullable();
            $table->timestamps();

            $table->foreign('innings_id')->references('innings_id')->on('match_innings')->onDelete('cascade');
            $table->foreign('match_id')->references('match_id')->on('matches')->onDelete('cascade');
            $table->foreign('batsman_id')->references('player_id')->on('players')->onDelete('cascade');
            $table->foreign('bowler_id')->references('player_id')->on('players')->onDelete('cascade');
            $table->foreign('fielder_id')->references('player_id')->on('players')->onDelete('cascade');
            
            $table->index(['match_id', 'innings_id', 'over_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ball_by_ball');
    }
};
