<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use App\Contracts\Repositories\SettingRepositoryInterface;
use Filament\Notifications\Notification;
use App\Contracts\Repositories\CityRepositoryInterface;

class SettingsPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings-page';
    protected static ?string $title = 'Настройки сайта';
    protected static ?string $navigationLabel = 'Настройки';
    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function __construct()
    {

    }

    public function mount(): void
    {
        $this->fillFormWithSettings();
    }

    protected function fillFormWithSettings(): void
    {
        // Загружаем только статичные настройки
        $settings = [
            // Основные
            'site_name' => $this->getSettingRepository()->getValue('site_name', ''),
            'site_description' => $this->getSettingRepository()->getValue('site_description', ''),
            'contact_email' => $this->getSettingRepository()->getValue('contact_email', ''),
            'contact_phone' => $this->getSettingRepository()->getValue('contact_phone', ''),
            'site_logo' => $this->getSettingRepository()->getValue('site_logo', ''),

            // Доставка
            'free_shipping_threshold' => $this->getSettingRepository()->getValue('free_shipping_threshold', null),
            'shipping_cost' => $this->getSettingRepository()->getValue('shipping_cost', 0),
            'delivery_time' => $this->getSettingRepository()->getValue('delivery_time', ''),

            // Магазин
            'store_status' => $this->getSettingRepository()->getValue('store_status', true),
            'maintenance_message' => $this->getSettingRepository()->getValue('maintenance_message', ''),
            'min_order_amount' => $this->getSettingRepository()->getValue('min_order_amount', 0),

            // SEO
            'meta_keywords' => $this->getSettingRepository()->getValue('meta_keywords', ''),
            'google_analytics_id' => $this->getSettingRepository()->getValue('google_analytics_id', ''),
            'yandex_metrika_id' => $this->getSettingRepository()->getValue('yandex_metrika_id', ''),

            // Соцсети
            'social_vk' => '',
            'social_telegram' => '',
            'social_instagram' => '',
            'social_youtube' => '',

            // Уведомления
            'admin_email_notifications' => $this->getSettingRepository()->getValue('admin_email_notifications', true),
            'notification_emails' => $this->getSettingRepository()->getValue('notification_emails', ''),

            // Валюта
            'currency_symbol' => $this->getSettingRepository()->getValue('currency_symbol', '₽'),
            'currency_code' => $this->getSettingRepository()->getValue('currency_code', 'RUB'),

            // Города
            'enabled_cities' => [],
        ];

        // Обрабатываем социальные сети
        $socialLinks = $this->getSettingRepository()->getValue('social_links', []);
        if (is_array($socialLinks)) {
            $settings['social_vk'] = $socialLinks['vk'] ?? '';
            $settings['social_telegram'] = $socialLinks['telegram'] ?? '';
            $settings['social_instagram'] = $socialLinks['instagram'] ?? '';
            $settings['social_youtube'] = $socialLinks['youtube'] ?? '';
        }

        // enabled_cities хранится как массив объектов [{id, name}, ...]
        $enabledCities = $this->getSettingRepository()->getValue('enabled_cities', []);
        $settings['enabled_cities'] = array_column($enabledCities, 'id');

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

                        Forms\Components\Tabs\Tab::make('Города')
                            ->schema([
                                Forms\Components\Select::make('enabled_cities')
                                    ->label('Города, в которых работает магазин')
                                    ->multiple()
                                    ->searchable()
                                    ->options(fn () => $this->getCityRepository()->getOptionsForSelect())
                                    ->preload()
                                    ->helperText('Выберите города, где доступен магазин'),
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
            $data = $this->form->getState();

            // Обрабатываем социальные сети
            $socialLinks = [
                'vk' => $data['social_vk'] ?? '',
                'telegram' => $data['social_telegram'] ?? '',
                'instagram' => $data['social_instagram'] ?? '',
                'youtube' => $data['social_youtube'] ?? '',
            ];
            unset($data['social_vk'], $data['social_telegram'], $data['social_instagram'], $data['social_youtube']);

            // Сохраняем только значения существующих настроек
            foreach ($data as $key => $value) {
                $setting = $this->getSettingRepository()->findByKey($key);
                if ($setting) {
                    if ($setting->type === 'boolean') {
                        $value = $value ? '1' : '0';
                    }
                    $this->getSettingRepository()->setValue($key, $value, $setting->type);
                }
            }

            // Сохраняем социальные сети
            $this->getSettingRepository()->setValue('social_links', $socialLinks, 'json');

            // --- Новый блок: сохраняем enabled_cities как массив объектов ---
            $cityIds = $data['enabled_cities'] ?? [];
            $cities = $this->getCityRepository()->getNamesByIds($cityIds);
            $enabledCities = [];
            foreach ($cityIds as $id) {
                if (isset($cities[$id])) {
                    $enabledCities[] = [
                        'id' => $id,
                        'name' => $cities[$id],
                    ];
                }
            }
            $this->getSettingRepository()->setValue('enabled_cities', $enabledCities, 'json');
            // --- конец блока ---

            $this->getSettingRepository()->clearCache();

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

    private function getSettingRepository(): SettingRepositoryInterface
    {
        return app(SettingRepositoryInterface::class);
    }

    private function getCityRepository(): CityRepositoryInterface
    {
        return app(CityRepositoryInterface::class);
    }
}
