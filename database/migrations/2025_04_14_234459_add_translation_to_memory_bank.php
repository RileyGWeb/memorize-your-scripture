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
        Schema::table('memory_bank', function (Blueprint $table) {
            // Add a new column for the verse text
            $table->text('bible_translation')->nullable()->after('memorized_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memory_bank', function (Blueprint $table) {
            // Drop the column if it exists
            if (Schema::hasColumn('memory_bank', 'bible_translation')) {
                $table->dropColumn('bible_translation');
            }
        });
    }
};
