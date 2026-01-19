<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->integer('current_innings')->default(1)->after('status');
            $table->decimal('current_over', 5, 1)->default(0)->after('current_innings');
            $table->unsignedBigInteger('current_batsman_1')->nullable()->after('current_over');
            $table->unsignedBigInteger('current_batsman_2')->nullable()->after('current_batsman_1');
            $table->unsignedBigInteger('current_bowler')->nullable()->after('current_batsman_2');
            $table->integer('target_score')->nullable()->after('current_bowler');
            $table->string('toss_winner', 100)->nullable()->after('target_score');
            $table->string('toss_decision', 20)->nullable()->after('toss_winner');
            $table->text('match_summary')->nullable()->after('toss_decision');
            $table->integer('viewers_count')->default(0)->after('match_summary');
            $table->timestamp('started_at')->nullable()->after('viewers_count');
            $table->timestamp('ended_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'current_innings',
                'current_over',
                'current_batsman_1',
                'current_batsman_2',
                'current_bowler',
                'target_score',
                'toss_winner',
                'toss_decision',
                'match_summary',
                'viewers_count',
                'started_at',
                'ended_at'
            ]);
        });
    }
};
