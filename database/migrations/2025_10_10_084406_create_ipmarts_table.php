<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::table('ipmarts')->insert([
            [
                'user_id' => 1,
                'ipmart_id' => '68e77560d247fca264c188ab',
                'ipmart_email' => 'user222@email.com',
                'plan_balance' => 0,
                'proxyName' => '0Yd6Tl3Ov9Lj',
                'proxyPwd' => '3Km1Jv2Lc1Ls2Nl6Wt',
                'login_name' => '0Yd6Tl3Ov9Lj',
                'passwd' => 'password183929922UGHGG',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipmarts');
    }
};
