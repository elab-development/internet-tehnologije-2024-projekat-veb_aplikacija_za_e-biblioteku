<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan')->default('basic');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamps();
            
            //indexi za bolje performanse
            $table->index(['user_id', 'ends_at']);
            
            //TODO: dodati unique constraint za aktivne pretplate?
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
