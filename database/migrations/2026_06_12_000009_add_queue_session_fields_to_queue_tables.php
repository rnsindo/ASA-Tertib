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
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->foreignId('queue_session_id')
                ->nullable()
                ->after('applicant_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['queue_session_id', 'queue_service_id'], 'queue_tickets_session_service_index');
        });

        Schema::table('queue_service_dependencies', function (Blueprint $table) {
            $table->dropUnique('qsd_service_required_unique');

            $table->foreignId('queue_session_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['queue_session_id', 'queue_service_id'], 'queue_service_dependencies_session_service_index');
            $table->unique(['queue_session_id', 'queue_service_id', 'required_queue_service_id'], 'qsd_session_service_required_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropIndex('queue_tickets_session_service_index');
            $table->dropConstrainedForeignId('queue_session_id');
        });

        Schema::table('queue_service_dependencies', function (Blueprint $table) {
            $table->dropUnique('qsd_session_service_required_unique');
            $table->dropIndex('queue_service_dependencies_session_service_index');
            $table->dropConstrainedForeignId('queue_session_id');
            $table->unique(['queue_service_id', 'required_queue_service_id'], 'qsd_service_required_unique');
        });
    }
};
