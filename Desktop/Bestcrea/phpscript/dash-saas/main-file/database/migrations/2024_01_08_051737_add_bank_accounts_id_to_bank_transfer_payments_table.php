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

        if (Schema::hasTable('bank_transfer_payments') && !Schema::hasColumn('bank_transfer_payments', 'bank_accounts_id')) {
            Schema::table('bank_transfer_payments', function (Blueprint $table) {
                $table->string('bank_accounts_id')->after('payment_type')->default('0');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_transfer_payments', function (Blueprint $table) {
            //
        });
    }
};
