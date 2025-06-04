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
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // Основная информация адреса
            $table->enum('type', ['shipping', 'billing'])->default('shipping');
            $table->string('label')->nullable(); // Название адреса (Дом, Офис, Дача)
            $table->boolean('is_default')->default(false);

            // Информация о получателе
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();

            // Адресная информация
            $table->string('street');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Russia');
            $table->string('phone')->nullable();

            $table->timestamps();

            // Индексы для производительности
            $table->index(['client_id', 'type']);
            $table->index(['client_id', 'is_default']);
            $table->index(['type', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
    }
};
