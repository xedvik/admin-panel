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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // название атрибута (Цвет, Размер, Материал)
            $table->string('slug')->unique(); // slug для программного использования
            $table->string('type')->default('text'); // тип атрибута: text, number, select, boolean, string, date
            $table->text('description')->nullable(); // описание атрибута
            $table->jsonb('options')->nullable(); // опции для select (массив значений)
            $table->boolean('is_required')->default(false); // обязательный ли атрибут
            $table->boolean('is_active')->default(true); // активный ли атрибут
            $table->boolean('is_filterable')->default(true); // фильтруемый ли атрибут
            $table->integer('sort_order')->default(0); // порядок сортировки

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
