<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id('player_id');
            $table->string('name', 100);
            $table->unsignedBigInteger('team_id');
            $table->date('dob')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('role', 50)->nullable();
            $table->string('batting_style', 50)->nullable();
            $table->string('bowling_style', 50)->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('team_id')->on('teams')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
