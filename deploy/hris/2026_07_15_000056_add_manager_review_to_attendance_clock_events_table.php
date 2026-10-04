<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_clock_events', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_clock_events', 'review_status')) {
                $table->string('review_status', 40)->default('pending')->after('status');
            }
            if (! Schema::hasColumn('attendance_clock_events', 'review_decision')) {
                $table->string('review_decision', 40)->nullable()->after('review_status');
            }
            if (! Schema::hasColumn('attendance_clock_events', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('review_decision');
            }
            if (! Schema::hasColumn('attendance_clock_events', 'reviewed_by')) {
                $table->string('reviewed_by')->nullable()->after('reviewed_at');
            }
            if (! Schema::hasColumn('attendance_clock_events', 'review_notes')) {
                $table->text('review_notes')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_clock_events', function (Blueprint $table) {
            foreach (['review_notes', 'reviewed_by', 'reviewed_at', 'review_decision', 'review_status'] as $column) {
                if (Schema::hasColumn('attendance_clock_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
