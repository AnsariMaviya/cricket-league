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
        Schema::create('fall_of_wickets', function (Blueprint $table) {
            $table->id('fow_id');
            $table->unsignedBigInteger('match_id');
            $table->integer('innings_number');
            $table->unsignedBigInteger('player_id');
            $table->string('dismissal_type');
            $table->integer('runs_at_dismissal');
            $table->integer('wicket_number'); // 1st wicket, 2nd wicket, etc.
            $table->decimal('over_number', 5, 1);
            $table->timestamps();
            
            $table->foreign('match_id')->references('match_id')->on('matches')->onDelete('cascade');
            $table->foreign('player_id')->references('player_id')->on('players')->onDelete('cascade');
            
            $table->index(['match_id', 'innings_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fall_of_wickets');
    }
};
