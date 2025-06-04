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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->unique(); // артикул товара
            $table->integer('price');
            $table->integer('compare_price')->nullable(); // цена до скдики (зачеркнутая цена)
            $table->integer('stock_quantity')->default(0); // количество товара на складе
            $table->boolean('track_quantity')->default(true); // отслеживать количество товара на складе
            $table->boolean('continue_selling_when_out_of_stock')->default(false); // продолжать продавать товар когда на складе нет
            $table->decimal('weight', 8, 2)->nullable(); // вес товара
            $table->string('weight_unit')->default('kg'); // единица измерения веса


            $table->jsonb('images')->nullable(); // массив ссылок на изображения

            $table->string('meta_title')->nullable(); // seo
            $table->text('meta_description')->nullable(); // seo
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete(); // категория товара
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // рекомендуемый товар
            $table->timestamp('published_at')->nullable(); // дата публикации товара
            $table->timestamps();

            $table->index(['is_active', 'published_at']);
            $table->index(['category_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
