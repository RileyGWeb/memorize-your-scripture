<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('memory_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('book');
            $table->unsignedInteger('chapter');
            $table->json('verses'); // Store as JSON array of verse numbers
            $table->enum('difficulty', ['easy', 'normal', 'strict'])->default('easy');
            $table->float('accuracy_score')->nullable();
            $table->dateTime('memorized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('memory_bank');
    }
};
