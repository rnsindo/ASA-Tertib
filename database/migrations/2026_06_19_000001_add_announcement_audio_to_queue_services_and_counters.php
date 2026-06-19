<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_services', function (Blueprint $table): void {
            $table->string('announcement_audio_path')->nullable()->after('description');
        });

        Schema::table('service_counters', function (Blueprint $table): void {
            $table->string('announcement_audio_path')->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('service_counters', function (Blueprint $table): void {
            $table->dropColumn('announcement_audio_path');
        });

        Schema::table('queue_services', function (Blueprint $table): void {
            $table->dropColumn('announcement_audio_path');
        });
    }
};
