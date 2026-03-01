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
        Schema::create('help_article_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_article_id')->constrained()->cascadeOnDelete();
            $table->string('section_key');
            $table->string('title');
            $table->json('paragraphs');
            $table->string('callout_type')->nullable();
            $table->string('callout_title')->nullable();
            $table->text('callout_content')->nullable();
            $table->string('code_language')->nullable();
            $table->longText('code')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_article_sections');
    }
};
