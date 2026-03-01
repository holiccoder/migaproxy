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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('skype_profile')->nullable()->after('avatar_path');
            $table->string('telegram_profile')->nullable()->after('skype_profile');
            $table->string('facebook_profile')->nullable()->after('telegram_profile');
            $table->string('x_profile')->nullable()->after('facebook_profile');
            $table->string('youtube_profile')->nullable()->after('x_profile');
            $table->string('instagram_profile')->nullable()->after('youtube_profile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'skype_profile',
                'telegram_profile',
                'facebook_profile',
                'x_profile',
                'youtube_profile',
                'instagram_profile',
            ]);
        });
    }
};
