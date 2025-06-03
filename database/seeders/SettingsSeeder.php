<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Основные настройки сайта
            [
                'key' => 'site_name',
                'value' => 'Мой Интернет-Магазин',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Название сайта',
                'description' => 'Основное название интернет-магазина',
                'is_public' => true,
            ],
            [
                'key' => 'site_description',
                'value' => 'Лучший интернет-магазин электроники и техники',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Описание сайта',
                'description' => 'Краткое описание магазина для SEO',
                'is_public' => true,
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@shop.ru',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Email для связи',
                'description' => 'Основной email для обратной связи',
                'is_public' => true,
            ],
            [
                'key' => 'contact_phone',
                'value' => '88005553535',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Телефон для связи',
                'description' => 'Основной телефон магазина',
                'is_public' => true,
            ],
            [
                'key' => 'site_logo',
                'value' => '/images/logo.png',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Логотип сайта',
                'description' => 'Путь к файлу логотипа',
                'is_public' => true,
            ],

            // Настройки доставки
            [
                'key' => 'free_shipping_threshold',
                'value' => '3000',
                'type' => 'integer',
                'group' => 'shipping',
                'label' => 'Сумма бесплатной доставки',
                'description' => 'Минимальная сумма заказа для бесплатной доставки',
                'is_public' => true,
            ],
            [
                'key' => 'shipping_cost',
                'value' => '500',
                'type' => 'integer',
                'group' => 'shipping',
                'label' => 'Стоимость доставки',
                'description' => 'Базовая стоимость доставки',
                'is_public' => true,
            ],
            [
                'key' => 'delivery_time',
                'value' => '1-3 рабочих дня',
                'type' => 'string',
                'group' => 'shipping',
                'label' => 'Время доставки',
                'description' => 'Стандартное время доставки (необязательно)',
                'is_public' => true,
            ],

            // SEO настройки
            [
                'key' => 'meta_keywords',
                'value' => 'интернет-магазин, электроника, техника, купить',
                'type' => 'string',
                'group' => 'seo',
                'label' => 'Ключевые слова',
                'description' => 'Meta keywords для главной страницы',
                'is_public' => false,
            ],
            [
                'key' => 'google_analytics_id',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'label' => 'Google Analytics ID',
                'description' => 'Идентификатор Google Analytics (GA4)',
                'is_public' => false,
            ],
            [
                'key' => 'yandex_metrika_id',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'label' => 'Яндекс.Метрика ID',
                'description' => 'Идентификатор Яндекс.Метрики',
                'is_public' => false,
            ],

            // Социальные сети
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'vk' => 'https://vk.com/myshop',
                    'telegram' => '@myshop',
                    'instagram' => 'https://instagram.com/myshop',
                    'youtube' => 'https://youtube.com/myshop',
                ]),
                'type' => 'json',
                'group' => 'social',
                'label' => 'Ссылки на соцсети',
                'description' => 'JSON с ссылками на социальные сети',
                'is_public' => true,
            ],

            // Настройки магазина
            [
                'key' => 'store_status',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'store',
                'label' => 'Магазин открыт',
                'description' => 'Включить/выключить возможность оформления заказов',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Сайт временно недоступен. Ведутся технические работы.',
                'type' => 'string',
                'group' => 'store',
                'label' => 'Сообщение при закрытии',
                'description' => 'Сообщение когда магазин закрыт',
                'is_public' => true,
            ],
            [
                'key' => 'min_order_amount',
                'value' => '1000',
                'type' => 'integer',
                'group' => 'store',
                'label' => 'Минимальная сумма заказа',
                'description' => 'Минимальная сумма для оформления заказа (в копейках)',
                'is_public' => true,
            ],

            // Уведомления
            [
                'key' => 'admin_email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Email уведомления админу',
                'description' => 'Отправлять уведомления о новых заказах на admin email',
                'is_public' => false,
            ],
            [
                'key' => 'notification_emails',
                'value' => 'admin@shop.ru,manager@shop.ru',
                'type' => 'string',
                'group' => 'notifications',
                'label' => 'Email для уведомлений',
                'description' => 'Список email через запятую для уведомлений',
                'is_public' => false,
            ],

            // Валюта и формат
            [
                'key' => 'currency_symbol',
                'value' => '₽',
                'type' => 'string',
                'group' => 'format',
                'label' => 'Символ валюты',
                'description' => 'Символ валюты для отображения цен',
                'is_public' => true,
            ],
            [
                'key' => 'currency_code',
                'value' => 'RUB',
                'type' => 'string',
                'group' => 'format',
                'label' => 'Код валюты',
                'description' => 'ISO код валюты',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
