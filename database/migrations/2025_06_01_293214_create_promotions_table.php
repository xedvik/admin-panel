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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->enum('discount_type', ['percentage', 'fixed']); // тип скидки: процент или фиксированная сумма
            $table->integer('discount_value'); // значение скидки
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
