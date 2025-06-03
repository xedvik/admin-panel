
-- Удаление таблиц в правильном порядке (сначала зависимые таблицы)
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `clients`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;

-- =============================================
-- 1. Таблица пользователей (администраторы)
-- =============================================
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(255) NOT NULL DEFAULT 'user',
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. Таблица категорий товаров
-- =============================================
CREATE TABLE `categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `image` VARCHAR(255) NULL DEFAULT NULL,
    `meta_title` VARCHAR(255) NULL DEFAULT NULL,
    `meta_description` TEXT NULL DEFAULT NULL,
    `parent_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `categories_slug_unique` (`slug`),
    INDEX `categories_is_active_sort_order_index` (`is_active`, `sort_order`),
    INDEX `categories_parent_id_is_active_index` (`parent_id`, `is_active`),
    FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. Таблица клиентов
-- =============================================
CREATE TABLE `clients` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(255) NULL DEFAULT NULL,
    `date_of_birth` DATE NULL DEFAULT NULL,
    `gender` ENUM('male', 'female') NULL DEFAULT NULL,
    `addresses` JSON NULL DEFAULT NULL,
    `accepts_marketing` BOOLEAN NOT NULL DEFAULT FALSE,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `clients_email_unique` (`email`),
    INDEX `clients_email_index` (`email`),
    INDEX `clients_is_active_created_at_index` (`is_active`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. Таблица товаров
-- =============================================
CREATE TABLE `products` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `short_description` TEXT NULL DEFAULT NULL,
    `sku` VARCHAR(255) NOT NULL,
    `price` INT NOT NULL,
    `compare_price` INT NULL DEFAULT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `track_quantity` BOOLEAN NOT NULL DEFAULT TRUE,
    `continue_selling_when_out_of_stock` BOOLEAN NOT NULL DEFAULT FALSE,
    `weight` DECIMAL(8,2) NULL DEFAULT NULL,
    `weight_unit` VARCHAR(255) NOT NULL DEFAULT 'kg',
    `images` JSON NULL DEFAULT NULL,
    `meta_title` VARCHAR(255) NULL DEFAULT NULL,
    `meta_description` TEXT NULL DEFAULT NULL,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `is_featured` BOOLEAN NOT NULL DEFAULT FALSE,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `products_slug_unique` (`slug`),
    UNIQUE KEY `products_sku_unique` (`sku`),
    INDEX `products_is_active_published_at_index` (`is_active`, `published_at`),
    INDEX `products_category_id_is_active_index` (`category_id`, `is_active`),
    INDEX `products_is_featured_is_active_index` (`is_featured`, `is_active`),
    INDEX `products_sku_index` (`sku`),
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. Таблица заказов
-- =============================================
CREATE TABLE `orders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_number` VARCHAR(255) NOT NULL,
    `client_id` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    `subtotal` INT NOT NULL,
    `tax_amount` INT NOT NULL DEFAULT 0,
    `shipping_amount` INT NOT NULL DEFAULT 0,
    `discount_amount` INT NOT NULL DEFAULT 0,
    `total_amount` INT NOT NULL,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'RUB',
    `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `payment_method` VARCHAR(255) NULL DEFAULT NULL,
    `billing_address` JSON NOT NULL,
    `shipping_address` JSON NOT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `shipped_at` TIMESTAMP NULL DEFAULT NULL,
    `delivered_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `orders_order_number_unique` (`order_number`),
    INDEX `orders_status_created_at_index` (`status`, `created_at`),
    INDEX `orders_client_id_status_index` (`client_id`, `status`),
    INDEX `orders_order_number_index` (`order_number`),
    INDEX `orders_payment_status_index` (`payment_status`),
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 6. Таблица позиций заказа
-- =============================================
CREATE TABLE `order_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `product_sku` VARCHAR(255) NOT NULL,
    `product_price` INT NOT NULL,
    `quantity` INT NOT NULL,
    `total_price` INT NOT NULL,
    `product_variant` JSON NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `order_items_order_id_product_id_index` (`order_id`, `product_id`),
    INDEX `order_items_product_id_index` (`product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7. Таблица настроек сайта
-- =============================================
CREATE TABLE `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(255) NOT NULL,
    `value` TEXT NULL DEFAULT NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT 'string',
    `group` VARCHAR(255) NULL DEFAULT NULL,
    `label` VARCHAR(255) NULL DEFAULT NULL,
    `description` VARCHAR(255) NULL DEFAULT NULL,
    `is_public` BOOLEAN NOT NULL DEFAULT FALSE,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `settings_key_unique` (`key`),
    INDEX `settings_key_index` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Комментарии к таблицам
-- =============================================

-- users: Администраторы интернет-магазина
-- categories: Иерархические категории товаров с SEO полями
-- clients: Клиенты магазина с адресами в JSON формате
-- products: Товары с ценами в копейках, остатками и изображениями
-- orders: Заказы с адресами доставки/биллинга в JSON
-- order_items: Позиции заказа с сохранением цен на момент заказа
-- settings: Настройки сайта с типизацией и группировкой


-- =============================================
-- Примечания по типам данных:
-- =============================================

-- - JSON поля для гибкого хранения адресов, изображений, настроек
-- - ENUM для ограниченных наборов значений (статусы, пол)
-- - Индексы настроены для оптимизации частых запросов
-- - Внешние ключи с CASCADE для автоматического удаления связанных записей
