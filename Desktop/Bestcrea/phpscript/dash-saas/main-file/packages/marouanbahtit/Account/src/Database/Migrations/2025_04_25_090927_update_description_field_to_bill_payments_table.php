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
        if (Schema::hasColumn('bill_payments', 'description')) {
            Schema::table('bill_payments', function (Blueprint $table) {
                $table->longText('description')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bill_payments', 'description')) {
            Schema::table('bill_payments', function (Blueprint $table) {
                $table->longText('description')->change();
            });
        }
    }
};
