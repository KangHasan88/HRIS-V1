<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_profiles', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('full_name');
            }
            if (! Schema::hasColumn('employee_profiles', 'gender')) {
                $table->string('gender')->nullable()->after('birth_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('employee_profiles', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('employee_profiles', 'birth_date')) {
                $table->dropColumn('birth_date');
            }
        });
    }
};
