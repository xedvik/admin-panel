<?php

namespace App\Services\Forms;

use App\Models\ProductAttribute;
use App\Services\Business\AttributeTypeService;
use Filament\Forms;
use Illuminate\Support\Str;

class ProductAttributeFormFieldFactory
{
    public function __construct(
        private AttributeTypeService $attributeTypeService
    ) {}

    /**
     * Создать основные поля атрибута (название, slug, описание)
     */
    public function createBaseFields(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                    if ($context === 'create') {
                        $set('slug', Str::slug($state));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(ProductAttribute::class, 'slug', ignoreRecord: true)
                ->rules(['alpha_dash']),

            Forms\Components\Textarea::make('description')
                ->label('Описание')
                ->maxLength(65535)
                ->columnSpanFull(),
        ];
    }

    /**
     * Создать поля типа и настроек
     */
    public function createTypeAndSettingsFields(): array
    {
        return [
            $this->createTypeField(),
            $this->createOptionsField(),
            $this->createRequiredField(),
            $this->createFilterableField(),
            $this->createActiveField(),
            $this->createSortOrderField(),
        ];
    }

    /**
     * Создать поле типа атрибута
     */
    private function createTypeField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('type')
            ->label('Тип атрибута')
            ->required()
            ->options(fn () => $this->attributeTypeService->getTypeOptions())
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set) {
                if (!$this->attributeTypeService->shouldShowOptionsField($state)) {
                    $set('options', null);
                }
            });
    }

    /**
     * Создать поле вариантов выбора
     */
    private function createOptionsField(): Forms\Components\TagsInput
    {
        return Forms\Components\TagsInput::make('options')
            ->label('Варианты (для типа "Выбор из списка")')
            ->visible(fn (Forms\Get $get): bool => $this->attributeTypeService->shouldShowOptionsField($get('type')))
            ->helperText('Введите варианты для выбора. Нажмите Enter после каждого варианта.');
    }

    /**
     * Создать поле обязательности
     */
    private function createRequiredField(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_required')
            ->label('Обязательный атрибут')
            ->default(false);
    }

    /**
     * Создать поле фильтрации
     */
    private function createFilterableField(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_filterable')
            ->label('Использовать в фильтрах')
            ->default(true);
    }

    /**
     * Создать поле активности
     */
    private function createActiveField(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_active')
            ->label('Активный')
            ->default(true);
    }

    /**
     * Создать поле порядка сортировки
     */
    private function createSortOrderField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('sort_order')
            ->label('Порядок сортировки')
            ->numeric()
            ->default(0)
            ->helperText('Меньшее число = выше в списке');
    }

    /**
     * Создать основной макет формы
     */
    public function createMainLayout(): array
    {
        return [
            Forms\Components\Section::make('Основные данные')
                ->schema($this->createBaseFields())
                ->columns(2),

            Forms\Components\Section::make('Тип и настройки')
                ->schema($this->createTypeAndSettingsFields())
                ->columns(2),
        ];
    }
}
