<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->decimal('call_sequence', 20, 6)->nullable()->index();
            $table->unsignedSmallInteger('no_show_count')->default(0);
            $table->timestamp('no_show_at')->nullable();
            $table->timestamp('requeued_at')->nullable();
        });

        DB::table('queue_tickets')
            ->select(['id', 'queue_number'])
            ->orderBy('id')
            ->chunkById(100, function ($tickets): void {
                foreach ($tickets as $ticket) {
                    DB::table('queue_tickets')
                        ->where('id', $ticket->id)
                        ->update([
                            'call_sequence' => ((int) $ticket->queue_number) * 1000,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'call_sequence',
                'no_show_count',
                'no_show_at',
                'requeued_at',
            ]);
        });
    }
};
