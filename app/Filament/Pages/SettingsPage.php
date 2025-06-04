<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use App\Contracts\Repositories\SettingRepositoryInterface;
use Filament\Notifications\Notification;

class SettingsPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings-page';
    protected static ?string $title = 'Настройки сайта';
    protected static ?string $navigationLabel = 'Настройки';
    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillFormWithSettings();
    }

    protected function fillFormWithSettings(): void
    {
        $settingRepository = app(SettingRepositoryInterface::class);

        // Загружаем только статичные настройки
        $settings = [
            // Основные
            'site_name' => $settingRepository->getValue('site_name', ''),
            'site_description' => $settingRepository->getValue('site_description', ''),
            'contact_email' => $settingRepository->getValue('contact_email', ''),
            'contact_phone' => $settingRepository->getValue('contact_phone', ''),
            'site_logo' => $settingRepository->getValue('site_logo', ''),

            // Доставка
            'free_shipping_threshold' => $settingRepository->getValue('free_shipping_threshold', null),
            'shipping_cost' => $settingRepository->getValue('shipping_cost', 0),
            'delivery_time' => $settingRepository->getValue('delivery_time', ''),

            // Магазин
            'store_status' => $settingRepository->getValue('store_status', true),
            'maintenance_message' => $settingRepository->getValue('maintenance_message', ''),
            'min_order_amount' => $settingRepository->getValue('min_order_amount', 0),

            // SEO
            'meta_keywords' => $settingRepository->getValue('meta_keywords', ''),
            'google_analytics_id' => $settingRepository->getValue('google_analytics_id', ''),
            'yandex_metrika_id' => $settingRepository->getValue('yandex_metrika_id', ''),

            // Соцсети
            'social_vk' => '',
            'social_telegram' => '',
            'social_instagram' => '',
            'social_youtube' => '',

            // Уведомления
            'admin_email_notifications' => $settingRepository->getValue('admin_email_notifications', true),
            'notification_emails' => $settingRepository->getValue('notification_emails', ''),

            // Валюта
            'currency_symbol' => $settingRepository->getValue('currency_symbol', '₽'),
            'currency_code' => $settingRepository->getValue('currency_code', 'RUB'),
        ];

        // Обрабатываем социальные сети
        $socialLinks = $settingRepository->getValue('social_links', []);
        if (is_array($socialLinks)) {
            $settings['social_vk'] = $socialLinks['vk'] ?? '';
            $settings['social_telegram'] = $socialLinks['telegram'] ?? '';
            $settings['social_instagram'] = $socialLinks['instagram'] ?? '';
            $settings['social_youtube'] = $socialLinks['youtube'] ?? '';
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
                                    ->helperText('Необязательно. Минимальная сумма заказа для бесплатной доставки'),

                                Forms\Components\TextInput::make('shipping_cost')
                                    ->label('Стоимость доставки')
                                    ->numeric()
                                    ->suffix('₽')
                                    ->required(),

                                Forms\Components\TextInput::make('delivery_time')
                                    ->label('Время доставки')
                                    ->helperText('Необязательно. Например: "1-3 рабочих дня"')
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

                                Forms\Components\TextInput::make('currency_symbol')
                                    ->label('Символ валюты')
                                    ->required()
                                    ->maxLength(5),

                                Forms\Components\TextInput::make('currency_code')
                                    ->label('Код валюты')
                                    ->required()
                                    ->maxLength(3)
                                    ->placeholder('RUB'),
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
            $settingRepository = app(SettingRepositoryInterface::class);
            $data = $this->form->getState();

            // Обрабатываем социальные сети
            $socialLinks = [
                'vk' => $data['social_vk'] ?? '',
                'telegram' => $data['social_telegram'] ?? '',
                'instagram' => $data['social_instagram'] ?? '',
                'youtube' => $data['social_youtube'] ?? '',
            ];

            // Удаляем социальные сети из основных данных
            unset($data['social_vk'], $data['social_telegram'], $data['social_instagram'], $data['social_youtube']);

            // Сохраняем только значения существующих настроек
            foreach ($data as $key => $value) {
                $setting = $settingRepository->findByKey($key);
                if ($setting) {
                    // Обрабатываем boolean значения
                    if ($setting->type === 'boolean') {
                        $value = $value ? '1' : '0';
                    }
                    $settingRepository->setValue($key, $value, $setting->type);
                }
            }

            // Сохраняем социальные сети
            $settingRepository->setValue('social_links', $socialLinks, 'json');

            // Очищаем кеш
            $settingRepository->clearCache();

            Notification::make()
                ->title('Настройки сохранены')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при сохранении')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
