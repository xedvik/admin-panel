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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // номер заказа
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete(); // клиент

            // Используем строки вместо enum с ограничениями для PostgreSQL
            $table->string('status')->default('pending'); // статус заказа
            $table->string('payment_status')->default('pending'); // статус оплаты

            $table->integer('subtotal'); // сумма заказа без скидок и доставки
            $table->integer('tax_amount')->default(0); // сумма налога
            $table->integer('shipping_amount')->default(0); // сумма доставки
            $table->integer('discount_amount')->default(0); // сумма скидки
            $table->integer('total_amount'); // сумма заказа
            $table->string('currency', 3)->default('RUB'); // валюта заказа
            $table->string('payment_method')->nullable(); // метод оплаты

            $table->jsonb('billing_address'); // адрес выставления счета
            $table->jsonb('shipping_address'); // адрес доставки

            $table->text('notes')->nullable(); // примечания к заказу
            $table->timestamp('shipped_at')->nullable(); // дата отправки заказа
            $table->timestamp('delivered_at')->nullable(); // дата доставки заказа
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['client_id', 'status']);
            $table->index('order_number');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
