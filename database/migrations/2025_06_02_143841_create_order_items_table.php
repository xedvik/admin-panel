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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete(); // заказ
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // товар
            $table->string('product_name'); // название товара
            $table->string('product_sku'); // артикул товара в момент заказа
            $table->foreign('product_sku')->references('sku')->on('products')->cascadeOnDelete();
            $table->integer('product_price'); // цена товара в момент заказа
            $table->integer('quantity'); // количество товара
            $table->integer('total_price'); // сумма товара

            $table->jsonb('product_variant')->nullable(); // для хранения размера, цвета и т.д.

            $table->timestamps();

            $table->index(['order_id', 'product_id']);
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
