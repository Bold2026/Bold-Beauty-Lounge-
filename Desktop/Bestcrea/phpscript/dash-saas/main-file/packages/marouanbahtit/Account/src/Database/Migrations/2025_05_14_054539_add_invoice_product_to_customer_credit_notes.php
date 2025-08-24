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
        if (Schema::hasTable('customer_credit_notes') && !Schema::hasColumn('customer_credit_notes', 'invoice_product')) {
            Schema::table('customer_credit_notes', function (Blueprint $table) {

                $table->integer('credit_id')->after('id')->nullable()->default(0);
                $table->integer('invoice_product')->after('invoice')->nullable()->default(0);
            });
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_credit_notes', function (Blueprint $table) {
            //
        });
    }
};
