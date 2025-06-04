<?php

namespace App\Services\Forms;

use Filament\Forms;
use Illuminate\Support\Str;

class CategoryFormFieldFactory
{
    /**
     * Создать основные поля категории (название, slug, описание)
     */
    public function createBaseFields(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                    if ($operation === 'create') {
                        $set('slug', Str::slug($state));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->label('URL (slug)')
                ->required()
                ->maxLength(255)
                ->unique(\App\Models\Category::class, 'slug', ignoreRecord: true)
                ->rules(['alpha_dash'])
                ->helperText('Используется в URL адресе'),

            Forms\Components\Textarea::make('description')
                ->label('Описание')
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    /**
     * Создать поля SEO для категории
     */
    public function createSeoFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('SEO')
            ->schema([
                Forms\Components\TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(255)
                    ->helperText('Заголовок для поисковых систем'),

                Forms\Components\Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->rows(2)
                    ->maxLength(255)
                    ->helperText('Описание для поисковых систем (до 255 символов)'),
            ])
            ->collapsed()
            ->columnSpanFull();
    }

    /**
     * Создать поле активности категории
     */
    public function createActiveField(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_active')
            ->label('Активна')
            ->default(true)
            ->helperText('Показывать категорию на сайте');
    }

    /**
     * Создать поле порядка сортировки категории
     */
    public function createSortOrderField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('sort_order')
            ->label('Порядок сортировки')
            ->numeric()
            ->default(0)
            ->helperText('Меньшее число = выше в списке');
    }

    /**
     * Создать поле загрузки изображения категории
     */
    public function createImageField(): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make('image')
            ->label('Изображение')
            ->image()
            ->imageEditor()
            ->directory('categories')
            ->columnSpanFull();
    }

    /**
     * Создать поле выбора родительской категории
     */
    public function createParentCategoryField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('parent_id')
            ->label('Родительская категория')
            ->relationship('parent', 'name')
            ->searchable()
            ->preload()
            ->helperText('Оставьте пустым для корневой категории');
    }
}
