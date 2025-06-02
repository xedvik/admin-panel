<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;

class SettingsPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings-page';
    protected static ?string $title = 'Настройки сайта';
    protected static ?string $navigationLabel = 'Настройки';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillFormWithSettings();
    }

    protected function fillFormWithSettings(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Обрабатываем социальные сети
        if (isset($settings['social_links'])) {
            $socialLinks = $settings['social_links'];
            if (is_array($socialLinks)) {
                $settings['social_vk'] = $socialLinks['vk'] ?? '';
                $settings['social_telegram'] = $socialLinks['telegram'] ?? '';
                $settings['social_instagram'] = $socialLinks['instagram'] ?? '';
                $settings['social_youtube'] = $socialLinks['youtube'] ?? '';
            }
            unset($settings['social_links']);
        }

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Настройки')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Основные')
                            ->schema([
                                Forms\Components\TextInput::make('site_name')
                                    ->label('Название сайта')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('site_description')
                                    ->label('Описание сайта')
                                    ->rows(3)
                                    ->maxLength(500),

                                Forms\Components\TextInput::make('contact_email')
                                    ->label('Email для связи')
                                    ->email()
                                    ->required(),

                                Forms\Components\TextInput::make('contact_phone')
                                    ->label('Телефон для связи')
                                    ->required()
                                    ->tel(),

                                Forms\Components\FileUpload::make('site_logo')
                                    ->label('Логотип сайта')
                                    ->image()
                                    ->directory('logos')
                                    ->visibility('public'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Доставка')
                            ->schema([
                                Forms\Components\TextInput::make('free_shipping_threshold')
                                    ->label('Сумма бесплатной доставки')
                                    ->numeric()
                                    ->suffix('₽')
                                    ->step(100)
                                    ->helperText('Минимальная сумма заказа для бесплатной доставки'),

                                Forms\Components\TextInput::make('shipping_cost')
                                    ->label('Стоимость доставки')
                                    ->numeric()
                                    ->suffix('₽')
                                    ->step(50)
                                    ->required(),

                                Forms\Components\TextInput::make('delivery_time')
                                    ->label('Время доставки')
                                    ->required()
                                    ->placeholder('1-3 рабочих дня'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Магазин')
                            ->schema([
                                Forms\Components\Toggle::make('store_status')
                                    ->label('Магазин открыт')
                                    ->helperText('Включить/выключить возможность оформления заказов')
                                    ->inline(false),

                                Forms\Components\Textarea::make('maintenance_message')
                                    ->label('Сообщение при закрытии')
                                    ->rows(3)
                                    ->helperText('Отображается когда магазин закрыт'),

                                Forms\Components\TextInput::make('min_order_amount')
                                    ->label('Минимальная сумма заказа')
                                    ->numeric()
                                    ->suffix('₽')
                                    ->step(100)
                                    ->required(),
                            ]),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                Forms\Components\Textarea::make('meta_keywords')
                                    ->label('Ключевые слова')
                                    ->rows(2)
                                    ->helperText('Через запятую'),

                                Forms\Components\TextInput::make('google_analytics_id')
                                    ->label('Google Analytics ID')
                                    ->placeholder('G-XXXXXXXXXX')
                                    ->helperText('Измерение GA4'),

                                Forms\Components\TextInput::make('yandex_metrika_id')
                                    ->label('Яндекс.Метрика ID')
                                    ->placeholder('12345678')
                                    ->helperText('Номер счетчика'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Соцсети')
                            ->schema([
                                Forms\Components\TextInput::make('social_vk')
                                    ->label('ВКонтакте')
                                    ->url()
                                    ->placeholder('https://vk.com/myshop'),

                                Forms\Components\TextInput::make('social_telegram')
                                    ->label('Telegram')
                                    ->placeholder('@myshop'),

                                Forms\Components\TextInput::make('social_instagram')
                                    ->label('Instagram')
                                    ->url()
                                    ->placeholder('https://instagram.com/myshop'),

                                Forms\Components\TextInput::make('social_youtube')
                                    ->label('YouTube')
                                    ->url()
                                    ->placeholder('https://youtube.com/myshop'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Уведомления')
                            ->schema([
                                Forms\Components\Toggle::make('admin_email_notifications')
                                    ->label('Email уведомления админу')
                                    ->helperText('Отправлять уведомления о новых заказах')
                                    ->inline(false),

                                Forms\Components\Textarea::make('notification_emails')
                                    ->label('Email для уведомлений')
                                    ->rows(2)
                                    ->helperText('Список email через запятую'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить настройки')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        try {
            // Валидируем данные формы
            $data = $this->form->getState();

            // Дополнительная валидация
            $this->validateSettings($data);

            // Обрабатываем социальные сети
            $socialLinks = [
                'vk' => $data['social_vk'] ?? '',
                'telegram' => $data['social_telegram'] ?? '',
                'instagram' => $data['social_instagram'] ?? '',
                'youtube' => $data['social_youtube'] ?? '',
            ];

            // Удаляем социальные сети из основных данных
            unset($data['social_vk'], $data['social_telegram'], $data['social_instagram'], $data['social_youtube']);

            // Сохраняем настройки
            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $setting = Setting::where('key', $key)->first();
                    if ($setting) {
                        $type = $setting->type;

                        // Обрабатываем boolean значения
                        if ($type === 'boolean') {
                            $value = $value ? '1' : '0';
                        }

                        $setting->update(['value' => $value]);
                    }
                }
            }

            // Сохраняем социальные сети как JSON
            Setting::set('social_links', $socialLinks, 'json', [
                'group' => 'social',
                'label' => 'Ссылки на соцсети',
                'description' => 'JSON с ссылками на социальные сети',
                'is_public' => true,
            ]);

            // Очищаем кеш настроек
            Setting::clearCache();

            Notification::make()
                ->title('Настройки сохранены')
                ->success()
                ->send();

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Обрабатываем ошибки валидации
            $errors = $e->validator->errors()->all();
            Notification::make()
                ->title('Ошибка валидации')
                ->body(implode(', ', $errors))
                ->danger()
                ->send();

            throw $e; // Перебрасываем исключение для показа ошибок в форме
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при сохранении')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Дополнительная валидация настроек
     */
    protected function validateSettings(array $data): void
    {
        $rules = [
            'site_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:50',
            'free_shipping_threshold' => 'nullable|integer|min:0',
            'shipping_cost' => 'nullable|integer|min:0',
            'min_order_amount' => 'nullable|integer|min:0',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }
}
