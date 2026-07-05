<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            Schema::create('orders_temp', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->float('sub_total');
                $table->foreignId('shipping_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('shipping_destination_id')->nullable();
                $table->string('shipping_destination_label')->nullable();
                $table->string('shipping_courier')->nullable();
                $table->string('shipping_service')->nullable();
                $table->string('shipping_description')->nullable();
                $table->string('shipping_etd')->nullable();
                $table->decimal('shipping_cost', 12, 2)->nullable();
                $table->float('coupon')->nullable();
                $table->float('total_amount');
                $table->integer('quantity');
                $table->string('payment_method')->default('cod');
                $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
                $table->enum('status', ['new', 'process', 'delivered', 'cancel'])->default('new');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email');
                $table->string('phone');
                $table->string('country');
                $table->string('post_code')->nullable();
                $table->text('address1');
                $table->text('address2')->nullable();
                $table->timestamps();
            });

            DB::statement('
                INSERT INTO orders_temp (
                    id, order_number, user_id, sub_total, shipping_id, shipping_destination_id,
                    shipping_destination_label, shipping_courier, shipping_service, shipping_description,
                    shipping_etd, shipping_cost, coupon, total_amount, quantity, payment_method,
                    payment_status, status, first_name, last_name, email, phone, country,
                    post_code, address1, address2, created_at, updated_at
                )
                SELECT
                    id, order_number, user_id, sub_total, shipping_id, shipping_destination_id,
                    shipping_destination_label, shipping_courier, shipping_service, shipping_description,
                    shipping_etd, shipping_cost, coupon, total_amount, quantity, payment_method,
                    payment_status, status, first_name, last_name, email, phone, country,
                    post_code, address1, address2, created_at, updated_at
                FROM orders
            ');

            Schema::drop('orders');
            Schema::rename('orders_temp', 'orders');

            DB::statement('PRAGMA foreign_keys=ON');

            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method VARCHAR(255) NOT NULL DEFAULT 'cod'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method VARCHAR(255) NOT NULL DEFAULT 'cod'");
    }
};
