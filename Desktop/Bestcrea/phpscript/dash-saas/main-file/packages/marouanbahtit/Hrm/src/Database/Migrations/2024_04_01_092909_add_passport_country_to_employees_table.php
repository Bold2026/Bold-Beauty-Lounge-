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
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'passport_country')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('passport_country')->after('account_type')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'passport')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('passport')->after('passport_country')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'location_type')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('location_type')->after('passport')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'country')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('country')->after('location_type')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'state')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('state')->after('country')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'city')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('city')->after('state')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'zipcode')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('zipcode')->after('city')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'hours_per_day')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->float('hours_per_day', 30, 2)->after('zipcode')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'annual_salary')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->integer('annual_salary')->after('hours_per_day')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'days_per_week')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->integer('days_per_week')->after('annual_salary')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'fixed_salary')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->integer('fixed_salary')->after('days_per_week')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'hours_per_month')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->float('hours_per_month', 30, 2)->after('fixed_salary')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'rate_per_day')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->integer('rate_per_day')->after('hours_per_month')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'days_per_month')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->integer('days_per_month')->after('rate_per_day')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'rate_per_hour')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->integer('rate_per_hour')->after('days_per_month')->nullable();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'payment_requires_work_advice')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('payment_requires_work_advice')->after('rate_per_hour')->default('off');
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
        Schema::table('employees', function (Blueprint $table) {});
    }
};
