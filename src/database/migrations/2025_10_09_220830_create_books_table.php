<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->integer('year');
            $table->string('genre')->nullable();
            $table->string('isbn')->nullable()->unique();
            $table->timestamps();
            
            //TODO: dodati index na title i author za pretragu
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
