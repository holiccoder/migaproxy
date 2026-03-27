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
        Schema::create('ipmarts', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('ipmart_id')->unique();
            $table->string('available_traffic')->default(0);
            $table->string('ipmart_email')->nullable();
            $table->string('plan_balance')->default(0);
            $table->string('proxyName');
            $table->string('proxyPwd');
            $table->string('login_name');
            $table->string('passwd');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipmarts');
    }
};
