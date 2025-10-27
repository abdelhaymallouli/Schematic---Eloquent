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
        Schema::create('articles', function (Blueprint $table) {
           $table->id(); // PK
    $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // FK to users
    $table->string('title', 180); // Title (max 180 chars)
    $table->string('slug', 200)->unique(); // Unique URL-friendly slug
    $table->text('excerpt')->nullable(); // Short summary
    $table->longText('content')->nullable(); // Full article content
    $table->timestamps(); // created_at, updated_at
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
