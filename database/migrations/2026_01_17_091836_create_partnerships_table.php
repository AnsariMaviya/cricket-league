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
        Schema::create('partnerships', function (Blueprint $table) {
            $table->id('partnership_id');
            $table->unsignedBigInteger('match_id');
            $table->integer('innings_number');
            $table->unsignedBigInteger('batsman1_id');
            $table->unsignedBigInteger('batsman2_id');
            $table->integer('runs')->default(0);
            $table->integer('balls')->default(0);
            $table->integer('wicket_number'); // 1st wicket, 2nd wicket, etc.
            $table->decimal('start_over', 5, 1)->default(0);
            $table->decimal('end_over', 5, 1)->nullable();
            $table->timestamps();
            
            $table->foreign('match_id')->references('match_id')->on('matches')->onDelete('cascade');
            $table->foreign('batsman1_id')->references('player_id')->on('players')->onDelete('cascade');
            $table->foreign('batsman2_id')->references('player_id')->on('players')->onDelete('cascade');
            
            $table->index(['match_id', 'innings_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partnerships');
    }
};
