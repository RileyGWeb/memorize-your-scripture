<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('memory_bank', function (Blueprint $table) {
            $table->text('user_text')->nullable()->after('accuracy_score');
        });
    }

    public function down()
    {
        Schema::table('memory_bank', function (Blueprint $table) {
            if (Schema::hasColumn('memory_bank', 'user_text')) {
                $table->dropColumn('user_text');
            }
        });
    }
};
