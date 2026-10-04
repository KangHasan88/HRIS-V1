<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometric_connectors', function (Blueprint $table) {
            $table->string('agent_pairing_code', 120)->nullable()->unique()->after('auth_mode');
            $table->string('agent_token_hash')->nullable()->after('agent_pairing_code');
            $table->timestamp('agent_last_seen_at')->nullable()->after('next_sync_at');
            $table->string('agent_version', 80)->nullable()->after('agent_last_seen_at');
            $table->string('agent_status', 40)->default('unpaired')->after('agent_version');
            $table->json('agent_config')->nullable()->after('mapping_config');
        });
    }

    public function down(): void
    {
        Schema::table('biometric_connectors', function (Blueprint $table) {
            $table->dropColumn([
                'agent_pairing_code',
                'agent_token_hash',
                'agent_last_seen_at',
                'agent_version',
                'agent_status',
                'agent_config',
            ]);
        });
    }
};
