<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_innings', function (Blueprint $table) {
            $table->integer('balls_in_innings')->default(0)->after('overs');
        });
    }

    public function down(): void
    {
        Schema::table('match_innings', function (Blueprint $table) {
            $table->dropColumn('balls_in_innings');
        });
    }
};
