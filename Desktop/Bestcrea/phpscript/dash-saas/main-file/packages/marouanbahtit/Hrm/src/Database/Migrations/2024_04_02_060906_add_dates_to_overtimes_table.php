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
        if (Schema::hasTable('overtimes') && !Schema::hasColumn('overtimes', 'start_date')) {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->date('start_date')->after('rate')->nullable();
            });
        }

        if (Schema::hasTable('overtimes') && !Schema::hasColumn('overtimes', 'end_date')) {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->date('end_date')->after('start_date')->nullable();
            });
        }

        if (Schema::hasTable('overtimes') && !Schema::hasColumn('overtimes', 'status')) {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->string('status')->after('end_date')->nullable();
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
        Schema::table('overtimes', function (Blueprint $table) {});
    }
};
