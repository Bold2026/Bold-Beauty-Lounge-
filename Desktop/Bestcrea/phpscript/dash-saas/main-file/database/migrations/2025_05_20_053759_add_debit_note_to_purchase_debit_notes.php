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
        if (Schema::hasTable('purchase_debit_notes') && !Schema::hasColumn('purchase_debit_notes', 'debit_note')) {
            Schema::table('purchase_debit_notes', function (Blueprint $table) {

                $table->integer('debit_note')->after('purchase')->nullable()->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_debit_notes', function (Blueprint $table) {
            //
        });
    }
};
