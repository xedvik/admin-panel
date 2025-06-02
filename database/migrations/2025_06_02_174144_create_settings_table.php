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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index(); // ключ настройки
            $table->text('value')->nullable(); // значение настройки
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('group')->nullable(); // для группировки настроек
            $table->string('label')->nullable(); // человекочитаемое название
            $table->string('description')->nullable(); // описание настройки
            $table->boolean('is_public')->default(false); // доступна ли для публичного API
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
