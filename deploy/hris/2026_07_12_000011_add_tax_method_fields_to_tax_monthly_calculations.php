<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_monthly_calculations', function (Blueprint $table) {
            $table->string('tax_method')->default('gross')->after('ter_category');
            $table->decimal('tax_allowance_amount', 18, 2)->default(0)->after('pph21_amount');
            $table->decimal('company_tax_borne_amount', 18, 2)->default(0)->after('tax_allowance_amount');
            $table->decimal('take_home_pay', 18, 2)->default(0)->after('company_tax_borne_amount');
        });
    }

    public function down(): void
    {
        Schema::table('tax_monthly_calculations', function (Blueprint $table) {
            $table->dropColumn([
                'tax_method',
                'tax_allowance_amount',
                'company_tax_borne_amount',
                'take_home_pay',
            ]);
        });
    }
};
