<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_destination_id')->nullable()->after('shipping_id');
            $table->string('shipping_destination_label')->nullable()->after('shipping_destination_id');
            $table->string('shipping_courier')->nullable()->after('shipping_destination_label');
            $table->string('shipping_service')->nullable()->after('shipping_courier');
            $table->string('shipping_description')->nullable()->after('shipping_service');
            $table->string('shipping_etd')->nullable()->after('shipping_description');
            $table->decimal('shipping_cost', 12, 2)->nullable()->after('shipping_etd');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_destination_id',
                'shipping_destination_label',
                'shipping_courier',
                'shipping_service',
                'shipping_description',
                'shipping_etd',
                'shipping_cost',
            ]);
        });
    }
};
