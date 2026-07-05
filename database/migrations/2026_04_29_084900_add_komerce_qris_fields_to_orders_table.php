<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('qris_history_id')->nullable()->after('shipping_cost');
            $table->text('qris_string')->nullable()->after('qris_history_id');
            $table->decimal('qris_original_amount', 12, 2)->nullable()->after('qris_string');
            $table->decimal('qris_final_amount', 12, 2)->nullable()->after('qris_original_amount');
            $table->timestamp('qris_expiry_time')->nullable()->after('qris_final_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'qris_history_id',
                'qris_string',
                'qris_original_amount',
                'qris_final_amount',
                'qris_expiry_time',
            ]);
        });
    }
};
