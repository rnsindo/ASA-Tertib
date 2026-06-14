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
        Schema::create('counter_daily_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('queue_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_counter_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('target_quota');
            $table->boolean('manual_overflow_allowed')->default(true);
            $table->timestamps();

            $table->unique(['queue_session_id', 'service_counter_id'], 'counter_daily_allocations_session_counter_unique');
            $table->index(['queue_session_id', 'queue_service_id'], 'counter_daily_allocations_session_service_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counter_daily_allocations');
    }
};
