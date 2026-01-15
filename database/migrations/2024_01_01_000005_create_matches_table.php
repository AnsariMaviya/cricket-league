<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id('match_id');
            $table->unsignedBigInteger('venue_id');
            $table->unsignedBigInteger('first_team_id');
            $table->unsignedBigInteger('second_team_id');
            $table->string('match_type', 50)->default('T20');
            $table->integer('overs')->default(20);
            $table->string('first_team_score', 20)->nullable();
            $table->string('second_team_score', 20)->nullable();
            $table->string('outcome', 255)->nullable();
            $table->date('match_date')->nullable();
            $table->enum('status', ['scheduled', 'live', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();

            $table->foreign('venue_id')->references('venue_id')->on('venues')->onDelete('cascade');
            $table->foreign('first_team_id')->references('team_id')->on('teams')->onDelete('cascade');
            $table->foreign('second_team_id')->references('team_id')->on('teams')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
