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
        if (Schema::hasTable('bank_transfer_payments') && !Schema::hasColumn('bank_transfer_payments', 'payment_type')) {
            Schema::table('bank_transfer_payments', function (Blueprint $table) {
                    $table->string('payment_type')->after('type')->default('Bank Transfer');
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
