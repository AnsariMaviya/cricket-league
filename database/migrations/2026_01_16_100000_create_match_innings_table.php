<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_innings', function (Blueprint $table) {
            $table->id('innings_id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('batting_team_id');
            $table->unsignedBigInteger('bowling_team_id');
            $table->integer('innings_number')->default(1);
            $table->integer('total_runs')->default(0);
            $table->integer('wickets')->default(0);
            $table->decimal('overs', 5, 1)->default(0);
            $table->integer('extras')->default(0);
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamps();

            $table->foreign('match_id')->references('match_id')->on('matches')->onDelete('cascade');
            $table->foreign('batting_team_id')->references('team_id')->on('teams')->onDelete('cascade');
            $table->foreign('bowling_team_id')->references('team_id')->on('teams')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_innings');
    }
};
