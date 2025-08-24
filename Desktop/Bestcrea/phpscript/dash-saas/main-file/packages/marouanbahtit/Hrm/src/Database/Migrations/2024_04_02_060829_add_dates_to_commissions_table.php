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
        if (Schema::hasTable('commissions') && !Schema::hasColumn('commissions', 'start_date')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->date('start_date')->after('amount')->nullable();
            });
        }

        if (Schema::hasTable('commissions') && !Schema::hasColumn('commissions', 'end_date')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->date('end_date')->after('start_date')->nullable();
            });
        }

        if (Schema::hasTable('commissions') && !Schema::hasColumn('commissions', 'status')) {
            Schema::table('commissions', function (Blueprint $table) {
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
        Schema::table('commissions', function (Blueprint $table) {});
    }
};
