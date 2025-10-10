<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->timestamp('borrowed_at');
            $table->timestamp('due_at');
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
            
            //indexi za bolje performanse
            $table->index(['user_id', 'returned_at']);
            $table->index(['book_id', 'returned_at']);
            
            //TODO:  unique constraint za aktivne pozajmice?
            //TODO:  validacija za due_at da bude posle borrowed_at?
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
