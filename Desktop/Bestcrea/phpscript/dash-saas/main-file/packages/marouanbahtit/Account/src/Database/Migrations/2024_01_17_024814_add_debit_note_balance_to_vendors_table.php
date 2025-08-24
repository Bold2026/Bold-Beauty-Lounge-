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

        if (Schema::hasTable('vendors') && !Schema::hasColumn('vendors', 'debit_note_balance')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->string('debit_note_balance')->after('balance')->default('0.00');
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
        Schema::table('vendors', function (Blueprint $table) {

        });
    }
};
