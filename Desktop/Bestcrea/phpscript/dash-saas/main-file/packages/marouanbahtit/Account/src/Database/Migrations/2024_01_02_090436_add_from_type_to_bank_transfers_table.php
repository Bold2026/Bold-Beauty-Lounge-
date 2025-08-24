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

        if (Schema::hasTable('bank_transfers') && !Schema::hasColumn('bank_transfers', 'from_type')) {
            Schema::table('bank_transfers', function (Blueprint $table) {
                $table->string('from_type')->after('to_account');
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
        Schema::table('bank_transfers', function (Blueprint $table) {

        });
    }
};
