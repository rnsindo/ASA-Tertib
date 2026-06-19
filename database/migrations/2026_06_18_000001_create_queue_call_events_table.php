<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_call_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('queue_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('queue_ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('queue_service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_counter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('called_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ticket_code', 32);
            $table->string('service_name');
            $table->string('counter_name');
            $table->string('applicant_name')->nullable();
            $table->text('announcement_text');
            $table->timestamp('called_at')->nullable();
            $table->timestamps();

            $table->index(['queue_session_id', 'id'], 'queue_call_events_session_id_index');
            $table->index(['service_counter_id', 'called_at'], 'queue_call_events_counter_called_index');
            $table->index('called_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_call_events');
    }
};
