<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_predictions', function (Blueprint $table) {
            $table->id('prediction_id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('predicted_winner_id')->nullable();
            $table->integer('predicted_margin')->nullable();
            $table->string('margin_type', 20)->nullable();
            $table->integer('confidence_score')->default(50);
            $table->json('factors')->nullable();
            $table->boolean('is_ai_generated')->default(true);
            $table->timestamps();

            $table->foreign('match_id')->references('match_id')->on('matches')->onDelete('cascade');
            $table->foreign('predicted_winner_id')->references('team_id')->on('teams')->onDelete('set null');
        });

        Schema::create('user_predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('predicted_winner_id');
            $table->boolean('is_correct')->nullable();
            $table->integer('points_earned')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('match_id')->references('match_id')->on('matches')->onDelete('cascade');
            $table->foreign('predicted_winner_id')->references('team_id')->on('teams')->onDelete('cascade');
            
            $table->unique(['user_id', 'match_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_predictions');
        Schema::dropIfExists('match_predictions');
    }
};
