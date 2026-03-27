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
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_pinned_to_top')->default(false)->after('cover_image_path');
            $table->boolean('is_featured_in_homepage')->default(false)->after('is_pinned_to_top');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_pinned_to_top', 'is_featured_in_homepage']);
        });
    }
};
