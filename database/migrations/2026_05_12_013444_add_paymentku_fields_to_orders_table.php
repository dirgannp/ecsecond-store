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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('paymentku_transaction_id')->nullable()->after('qris_expiry_time');
            $table->text('paymentku_qr_string')->nullable()->after('paymentku_transaction_id');
            $table->decimal('paymentku_original_amount', 12, 2)->nullable()->after('paymentku_qr_string');
            $table->decimal('paymentku_final_amount', 12, 2)->nullable()->after('paymentku_original_amount');
            $table->timestamp('paymentku_expired_at')->nullable()->after('paymentku_final_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'paymentku_transaction_id',
                'paymentku_qr_string',
                'paymentku_original_amount',
                'paymentku_final_amount',
                'paymentku_expired_at',
            ]);
        });
    }
};
