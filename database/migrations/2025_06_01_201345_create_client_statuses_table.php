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
        Schema::create('client_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // machine name: new, regular, loyal, vip
            $table->string('label'); // display name: Новый, Обычный, Постоянный, VIP
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_statuses');
    }
};
