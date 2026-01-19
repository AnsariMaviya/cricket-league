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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id('tournament_id');
            $table->string('name');
            $table->enum('tournament_type', ['league', 'knockout', 'round_robin', 'hybrid'])->default('league');
            $table->enum('format', ['T20', 'ODI', 'Test'])->default('T20');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming');
            $table->integer('max_teams')->default(8);
            $table->integer('current_teams')->default(0);
            $table->decimal('prize_pool', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('rules')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
