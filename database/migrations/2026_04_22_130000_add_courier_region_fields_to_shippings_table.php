<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->string('courier')->nullable()->after('id');
            $table->string('region')->nullable()->after('type');
            $table->decimal('region_fee', 10, 2)->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->dropColumn(['courier', 'region', 'region_fee']);
        });
    }
};
