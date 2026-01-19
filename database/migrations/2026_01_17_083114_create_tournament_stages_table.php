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
        Schema::create('tournament_stages', function (Blueprint $table) {
            $table->id('stage_id');
            $table->unsignedBigInteger('tournament_id');
            $table->string('stage_name'); // Group Stage, Quarter Finals, Semi Finals, Final
            $table->integer('stage_order');
            $table->enum('stage_format', ['round_robin', 'knockout', 'league'])->default('knockout');
            $table->enum('status', ['pending', 'ongoing', 'completed'])->default('pending');
            $table->integer('teams_qualify')->nullable(); // How many teams move to next stage
            $table->timestamps();
            
            $table->foreign('tournament_id')->references('tournament_id')->on('tournaments')->onDelete('cascade');
            
            $table->index(['tournament_id', 'stage_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_stages');
    }
};
