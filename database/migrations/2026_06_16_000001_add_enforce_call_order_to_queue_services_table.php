<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_services', function (Blueprint $table): void {
            $table->boolean('enforce_call_order')->default(true)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('queue_services', function (Blueprint $table): void {
            $table->dropColumn('enforce_call_order');
        });
    }
};
