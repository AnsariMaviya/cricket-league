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
        Schema::table('matches', function (Blueprint $table) {
            $table->unsignedBigInteger('tournament_id')->nullable()->after('match_id');
            $table->unsignedBigInteger('stage_id')->nullable()->after('tournament_id');
            $table->integer('match_number')->nullable()->after('stage_id');
            $table->boolean('is_knockout')->default(false)->after('match_number');
            
            $table->foreign('tournament_id')->references('tournament_id')->on('tournaments')->onDelete('set null');
            $table->foreign('stage_id')->references('stage_id')->on('tournament_stages')->onDelete('set null');
            
            $table->index('tournament_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['tournament_id']);
            $table->dropForeign(['stage_id']);
            $table->dropColumn(['tournament_id', 'stage_id', 'match_number', 'is_knockout']);
        });
    }
};
