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
        Schema::create('attendance_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('queue_session_qr_code_id')->nullable()->constrained()->nullOnDelete();
            $table->string('presence_status', 32)->default('checked_in');
            $table->timestamp('presence_confirmed_at');
            $table->foreignId('presence_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('presence_method', 32)->default('qr');
            $table->string('presence_location_code')->nullable();
            $table->text('presence_notes')->nullable();
            $table->timestamps();

            $table->unique(['queue_session_id', 'applicant_id'], 'attendance_checkins_session_applicant_unique');
            $table->index(['queue_session_id', 'presence_status'], 'attendance_checkins_session_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_checkins');
    }
};
