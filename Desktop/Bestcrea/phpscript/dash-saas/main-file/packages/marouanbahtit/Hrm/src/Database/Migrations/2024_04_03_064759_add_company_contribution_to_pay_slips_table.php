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
        if (Schema::hasTable('pay_slips') && !Schema::hasColumn('pay_slips', 'company_contribution')) {
            Schema::table('pay_slips', function (Blueprint $table) {
                $table->text('company_contribution')->after('overtime')->nullable();
            });
        }

        if (Schema::hasTable('pay_slips') && !Schema::hasColumn('pay_slips', 'tax_bracket')) {
            Schema::table('pay_slips', function (Blueprint $table) {
                $table->float('tax_bracket', 30, 2)->after('company_contribution')->nullable();
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
        Schema::table('pay_slips', function (Blueprint $table) {});
    }
};
