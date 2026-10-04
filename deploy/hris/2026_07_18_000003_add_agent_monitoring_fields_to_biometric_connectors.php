<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometric_connectors', function (Blueprint $table) {
            if (! Schema::hasColumn('biometric_connectors', 'agent_last_ip')) {
                $table->string('agent_last_ip', 80)->nullable()->after('agent_last_seen_at');
            }

            if (! Schema::hasColumn('biometric_connectors', 'agent_heartbeat_count')) {
                $table->unsignedInteger('agent_heartbeat_count')->default(0)->after('agent_status');
            }

            if (! Schema::hasColumn('biometric_connectors', 'agent_last_payload')) {
                $table->json('agent_last_payload')->nullable()->after('agent_config');
            }
        });
    }

    public function down(): void
    {
        Schema::table('biometric_connectors', function (Blueprint $table) {
            foreach (['agent_last_ip', 'agent_heartbeat_count', 'agent_last_payload'] as $column) {
                if (Schema::hasColumn('biometric_connectors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
