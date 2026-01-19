<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_commentary', function (Blueprint $table) {
            $table->id('commentary_id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('ball_id')->nullable();
            $table->text('commentary_text');
            $table->decimal('over_number', 5, 1);
            $table->enum('type', ['ball', 'milestone', 'wicket', 'boundary', 'over_summary', 'innings_break', 'match_update'])->default('ball');
            $table->timestamps();

            $table->foreign('match_id')->references('match_id')->on('matches')->onDelete('cascade');
            $table->foreign('ball_id')->references('ball_id')->on('ball_by_ball')->onDelete('cascade');
            
            $table->index(['match_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_commentary');
    }
};
