<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometric_connectors', function (Blueprint $table) {
            $table->json('database_adapter_config')->nullable()->after('agent_last_payload');
            $table->string('adapter_status', 40)->default('not_configured')->after('database_adapter_config');
            $table->timestamp('adapter_last_test_at')->nullable()->after('adapter_status');
            $table->json('adapter_last_preview')->nullable()->after('adapter_last_test_at');
        });
    }

    public function down(): void
    {
        Schema::table('biometric_connectors', function (Blueprint $table) {
            $table->dropColumn([
                'database_adapter_config',
                'adapter_status',
                'adapter_last_test_at',
                'adapter_last_preview',
            ]);
        });
    }
};
