<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('bank_accounts') && !Schema::hasColumn('bank_accounts', 'bank_branch')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->string('bank_branch')->after('bank_address')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {

        });
    }
};
