<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_simulations', function (Blueprint $table) {
            if (! Schema::hasColumn('tax_simulations', 'scenario_type')) {
                $table->string('scenario_type', 60)->default('monthly')->after('scenario_name');
            }
            if (! Schema::hasColumn('tax_simulations', 'irregular_income')) {
                $table->decimal('irregular_income', 18, 2)->default(0)->after('taxable_allowance');
            }
            if (! Schema::hasColumn('tax_simulations', 'final_tax_amount')) {
                $table->decimal('final_tax_amount', 18, 2)->default(0)->after('company_tax_borne_amount');
            }
            if (! Schema::hasColumn('tax_simulations', 'gross_up_iterations')) {
                $table->unsignedTinyInteger('gross_up_iterations')->default(0)->after('final_tax_amount');
            }
            if (! Schema::hasColumn('tax_simulations', 'visible_to_employee')) {
                $table->boolean('visible_to_employee')->default(false)->after('scenario_status');
            }
            if (! Schema::hasColumn('tax_simulations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('visible_to_employee');
            }
            if (! Schema::hasColumn('tax_simulations', 'approved_by')) {
                $table->string('approved_by')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tax_simulations', function (Blueprint $table) {
            foreach ([
                'approved_by',
                'approved_at',
                'visible_to_employee',
                'gross_up_iterations',
                'final_tax_amount',
                'irregular_income',
                'scenario_type',
            ] as $column) {
                if (Schema::hasColumn('tax_simulations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
