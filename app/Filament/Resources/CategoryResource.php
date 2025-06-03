<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Категории';

    protected static ?string $modelLabel = 'Категория';

    protected static ?string $pluralModelLabel = 'Категории';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Основная информация')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->label('URL (slug)')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Category::class, 'slug', ignoreRecord: true)
                                    ->rules(['alpha_dash'])
                                    ->helperText('Используется в URL адресе'),

                                Forms\Components\Textarea::make('description')
                                    ->label('Описание')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('image')
                                    ->label('Изображение')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('categories')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Настройки')
                            ->schema([
                                Forms\Components\Select::make('parent_id')
                                    ->label('Родительская категория')
                                    ->relationship('parent', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Оставьте пустым для корневой категории'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Порядок сортировки')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Меньшее число = выше в списке'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Активна')
                                    ->default(true)
                                    ->helperText('Показывать категорию на сайте'),
                            ])
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('SEO')
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
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Изображение')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Category $record): string => $record->slug),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Родительская')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Товаров')
                    ->counts('products')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->boolean()
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === true) {
                            $categoryRepository = app(CategoryRepositoryInterface::class);
                            $categoryIds = $categoryRepository->getActive()->pluck('id');
                            return $query->whereIn('id', $categoryIds);
                        } elseif ($data['value'] === false) {
                            $categoryRepository = app(CategoryRepositoryInterface::class);
                            $activeIds = $categoryRepository->getActive()->pluck('id');
                            return $query->whereNotIn('id', $activeIds);
                        }
                        return $query;
                    })
                    ->native(false),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Родительская категория')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('root_categories')
                    ->label('Корневые категории')
                    ->query(function (Builder $query): Builder {
                        $categoryRepository = app(CategoryRepositoryInterface::class);
                        $rootIds = $categoryRepository->getRoot()->pluck('id');
                        return $query->whereIn('id', $rootIds);
                    }),

                Tables\Filters\Filter::make('with_products')
                    ->label('С товарами')
                    ->query(function (Builder $query): Builder {
                        $categoryRepository = app(CategoryRepositoryInterface::class);
                        $categoriesWithProducts = $categoryRepository->getWithProductsCount()->filter(function($category) {
                            return $category->products_count > 0;
                        });
                        $categoryIds = $categoriesWithProducts->pluck('id');
                        return $query->whereIn('id', $categoryIds);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),

                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),

                Tables\Actions\Action::make('viewProducts')
                    ->label('Товары категории')
                    ->icon('heroicon-o-cube')
                    ->color('info')
                    ->url(fn (Category $record): string => ProductResource::getUrl('index', ['tableFilters' => ['category_id' => ['value' => $record->id]]])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить'),

                    Tables\Actions\BulkAction::make('activateCategories')
                        ->label('Активировать категории')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $categoryRepository = app(CategoryRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                $categoryRepository->update($record->id, ['is_active' => true]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Активировано категорий: {$count}")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivateCategories')
                        ->label('Деактивировать категории')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $categoryRepository = app(CategoryRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                $categoryRepository->update($record->id, ['is_active' => false]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Деактивировано категорий: {$count}")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
