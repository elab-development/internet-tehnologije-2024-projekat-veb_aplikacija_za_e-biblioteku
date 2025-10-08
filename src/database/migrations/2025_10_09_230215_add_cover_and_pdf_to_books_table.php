<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('cover_path')->nullable()->after('isbn');
            $table->string('pdf_path')->nullable()->after('cover_path');
            
            //TODO: validacija za file paths?
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['cover_path', 'pdf_path']);
        });
    }
};
