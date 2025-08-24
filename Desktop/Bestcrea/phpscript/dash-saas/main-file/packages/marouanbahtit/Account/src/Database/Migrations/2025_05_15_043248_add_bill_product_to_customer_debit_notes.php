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
        if (Schema::hasTable('customer_debit_notes') && !Schema::hasColumn('customer_debit_notes', 'bill_product')) {
            Schema::table('customer_debit_notes', function (Blueprint $table) {

                $table->integer('debit_id')->after('id')->nullable()->default(0);
                $table->integer('bill_product')->after('bill')->nullable()->default(0);
                $table->string('type')->after('bill_product')->nullable()->default('bill');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_debit_notes', function (Blueprint $table) {
            //
        });
    }
};
