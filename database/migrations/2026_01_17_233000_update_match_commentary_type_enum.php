<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `match_commentary` MODIFY COLUMN `type` ENUM('ball', 'wicket', 'boundary', 'milestone', 'toss', 'over_summary') DEFAULT 'ball'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `match_commentary` MODIFY COLUMN `type` ENUM('ball', 'wicket', 'boundary', 'milestone') DEFAULT 'ball'");
    }
};
