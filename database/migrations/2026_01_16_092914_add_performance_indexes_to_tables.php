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
        // Add indexes to teams table
        Schema::table('teams', function (Blueprint $table) {
            $table->index('country_id');
            $table->index('team_name');
        });

        // Add indexes to players table
        Schema::table('players', function (Blueprint $table) {
            $table->index('team_id');
            $table->index('name');
            $table->index(['team_id', 'role']);
        });

        // Add indexes to matches table
        Schema::table('matches', function (Blueprint $table) {
            $table->index('venue_id');
            $table->index('first_team_id');
            $table->index('second_team_id');
            $table->index('status');
            $table->index('match_date');
        });

        // Add indexes to venues table
        Schema::table('venues', function (Blueprint $table) {
            $table->index('city');
            $table->index('name');
        });

        // Add indexes to countries table
        Schema::table('countries', function (Blueprint $table) {
            $table->index('name');
            $table->index('short_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex(['country_id']);
            $table->dropIndex(['team_name']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
            $table->dropIndex(['name']);
            $table->dropIndex(['team_id', 'role']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex(['venue_id']);
            $table->dropIndex(['first_team_id']);
            $table->dropIndex(['second_team_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['match_date']);
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropIndex(['name']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['short_name']);
        });
    }
};
