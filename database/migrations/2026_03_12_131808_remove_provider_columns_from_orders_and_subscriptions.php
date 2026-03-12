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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_reference']);
            $table->dropColumn(['provider', 'provider_reference', 'checkout_url']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_subscription_id']);
            $table->dropColumn(['provider', 'provider_subscription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('provider')->after('subscription_id');
            $table->string('provider_reference')->nullable()->after('provider');
            $table->text('checkout_url')->nullable()->after('currency');
            $table->index(['provider', 'provider_reference']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('provider')->after('order_id');
            $table->string('provider_subscription_id')->nullable()->after('provider');
            $table->index(['provider', 'provider_subscription_id']);
        });
    }
};
