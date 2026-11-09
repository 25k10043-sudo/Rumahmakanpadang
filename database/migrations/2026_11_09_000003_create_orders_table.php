<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('tracking_token', 64)->unique();
            $table->string('customer_name');
            $table->string('table_number')->nullable();
            $table->string('order_type')->default('dine_in');
            $table->string('dining_mode')->default('pilih_lauk');
            $table->string('payment_method')->default('kasir');
            $table->string('payment_status')->default('unpaid');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedInteger('total');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
