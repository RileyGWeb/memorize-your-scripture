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
        Schema::table('memorize_later', function (Blueprint $table) {
            $table->text('note')->nullable()->after('verses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memorize_later', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
