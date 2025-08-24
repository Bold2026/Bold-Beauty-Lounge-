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
        if (Schema::hasTable('allowances') && Schema::hasColumn('allowances', 'amount')) {
            Schema::table('allowances', function (Blueprint $table) {
                $table->float('amount', 30, 2)->nullable()->change();
            });
        }

        if (Schema::hasTable('commissions') && Schema::hasColumn('commissions', 'amount')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->float('amount', 30, 2)->nullable()->change();
            });
        }

        if (Schema::hasTable('loans') && Schema::hasColumn('loans', 'amount')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->float('amount', 30, 2)->nullable()->change();
            });
        }

        if (Schema::hasTable('saturation_deductions') && Schema::hasColumn('saturation_deductions', 'amount')) {
            Schema::table('saturation_deductions', function (Blueprint $table) {
                $table->float('amount', 30, 2)->nullable()->change();
            });
        }

        if (Schema::hasTable('other_payments') && Schema::hasColumn('other_payments', 'amount')) {
            Schema::table('other_payments', function (Blueprint $table) {
                $table->float('amount', 30, 2)->nullable()->change();
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
        Schema::table('', function (Blueprint $table) {

        });
    }
};
