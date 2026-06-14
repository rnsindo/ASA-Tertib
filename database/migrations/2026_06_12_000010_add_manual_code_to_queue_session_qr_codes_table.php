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
        Schema::table('queue_session_qr_codes', function (Blueprint $table) {
            $table->string('manual_code', 16)->nullable()->after('token_hash');
            $table->index('manual_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_session_qr_codes', function (Blueprint $table) {
            $table->dropIndex(['manual_code']);
            $table->dropColumn('manual_code');
        });
    }
};
