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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('commission_type')->default('percentage');
            $table->unsignedInteger('commission_value')->default(10);
            $table->unsignedSmallInteger('cookie_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('total_earnings')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
