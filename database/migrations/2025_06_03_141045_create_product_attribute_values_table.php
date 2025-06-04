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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // товар
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete(); // атрибут
            $table->text('value'); // значение атрибута
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']); // один атрибут = одно значение для товара
            $table->index('attribute_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
