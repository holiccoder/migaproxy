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
        Schema::create('traffic_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('success_count');
            $table->decimal('length', 14, 6);
            $table->dateTime('request_date');
            $table->unsignedInteger('total_requests');
            $table->string('ipmart_id');
            $table->string('provider')->default('ipmart');
            $table->timestamps();

            $table->index(['ipmart_id', 'request_date']);
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traffic_histories');
    }
};
