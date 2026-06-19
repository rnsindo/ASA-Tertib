<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_room_assignments', function (Blueprint $table): void {
            $table->id();
            $table->string('nisn', 20)->unique();
            $table->string('name');
            $table->string('junior_school');
            $table->date('birth_date')->nullable();
            $table->string('room');
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['room', 'name']);
            $table->index('junior_school');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_room_assignments');
    }
};
