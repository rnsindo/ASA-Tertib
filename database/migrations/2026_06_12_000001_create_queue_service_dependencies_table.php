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
        Schema::create('queue_service_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('required_queue_service_id')->constrained('queue_services')->cascadeOnDelete();
            $table->string('required_status_mode', 32)->default('queued');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['queue_service_id', 'required_queue_service_id'], 'qsd_service_required_unique');
            $table->index(['queue_service_id', 'is_active'], 'qsd_service_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_service_dependencies');
    }
};
