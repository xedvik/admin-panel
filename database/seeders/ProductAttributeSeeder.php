<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Цвет',
                'slug' => 'color',
                'type' => 'select',
                'description' => 'Цвет товара',
                'options' => ['Красный', 'Синий', 'Зеленый', 'Черный', 'Белый', 'Желтый', 'Розовый'],
                'is_required' => false,
                'is_active' => true,
                'is_filterable' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Размер',
                'slug' => 'size',
                'type' => 'select',
                'description' => 'Размер товара',
                'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'is_required' => false,
                'is_active' => true,
                'is_filterable' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Материал',
                'slug' => 'material',
                'type' => 'text',
                'description' => 'Материал изготовления',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
                'is_filterable' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Вес (г)',
                'slug' => 'weight_grams',
                'type' => 'number',
                'description' => 'Вес товара в граммах',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
                'is_filterable' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Водостойкий',
                'slug' => 'waterproof',
                'type' => 'boolean',
                'description' => 'Является ли товар водостойким',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
                'is_filterable' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Дата производства',
                'slug' => 'production_date',
                'type' => 'date',
                'description' => 'Дата производства товара',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
                'is_filterable' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($attributes as $attribute) {
            ProductAttribute::create($attribute);
        }
    }
}
