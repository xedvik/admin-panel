-- Создание базовых таблиц, которые имеются в Laravel по умолчанию
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

CREATE TABLE failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id);

-- Таблица категорий
CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    parent_id BIGINT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE CASCADE
);
CREATE INDEX categories_is_active_sort_order_index ON categories (is_active, sort_order);
CREATE INDEX categories_parent_id_is_active_index ON categories (parent_id, is_active);

-- Таблица клиентов
CREATE TABLE clients (
    id BIGSERIAL PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(255) NULL,
    addresses JSONB NULL,
    accepts_marketing BOOLEAN NOT NULL DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX clients_email_index ON clients (email);
CREATE INDEX clients_is_active_created_at_index ON clients (is_active, created_at);

-- Таблица товаров
CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    short_description TEXT NULL,
    sku VARCHAR(255) NOT NULL UNIQUE,
    price INTEGER NOT NULL,
    compare_price INTEGER NULL,
    stock_quantity INTEGER NOT NULL DEFAULT 0,
    track_quantity BOOLEAN NOT NULL DEFAULT TRUE,
    continue_selling_when_out_of_stock BOOLEAN NOT NULL DEFAULT FALSE,
    weight DECIMAL(8, 2) NULL,
    weight_unit VARCHAR(255) NOT NULL DEFAULT 'kg',
    images JSONB NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    category_id BIGINT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_featured BOOLEAN NOT NULL DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
);
CREATE INDEX products_is_active_published_at_index ON products (is_active, published_at);
CREATE INDEX products_category_id_is_active_index ON products (category_id, is_active);
CREATE INDEX products_is_featured_is_active_index ON products (is_featured, is_active);
CREATE INDEX products_sku_index ON products (sku);

-- Таблица заказов
CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    order_number VARCHAR(255) NOT NULL UNIQUE,
    client_id BIGINT NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    payment_status VARCHAR(255) NOT NULL DEFAULT 'pending',
    subtotal INTEGER NOT NULL,
    tax_amount INTEGER NOT NULL DEFAULT 0,
    shipping_amount INTEGER NOT NULL DEFAULT 0,
    discount_amount INTEGER NOT NULL DEFAULT 0,
    total_amount INTEGER NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'RUB',
    payment_method VARCHAR(255) NULL,
    billing_address JSONB NOT NULL,
    shipping_address JSONB NOT NULL,
    notes TEXT NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);
CREATE INDEX orders_status_created_at_index ON orders (status, created_at);
CREATE INDEX orders_client_id_status_index ON orders (client_id, status);
CREATE INDEX orders_order_number_index ON orders (order_number);
CREATE INDEX orders_payment_status_index ON orders (payment_status);

-- Таблица позиций заказа
CREATE TABLE order_items (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(255) NOT NULL,
    product_price INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    total_price INTEGER NOT NULL,
    product_variant JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);
CREATE INDEX order_items_order_id_product_id_index ON order_items (order_id, product_id);
CREATE INDEX order_items_product_id_index ON order_items (product_id);

-- Таблица атрибутов товаров
CREATE TABLE product_attributes (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    type VARCHAR(255) NOT NULL DEFAULT 'text',
    description TEXT NULL,
    options JSONB NULL,
    is_required BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_filterable BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX product_attributes_is_active_sort_order_index ON product_attributes (is_active, sort_order);

-- Таблица значений атрибутов товаров
CREATE TABLE product_attribute_values (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL,
    attribute_id BIGINT NOT NULL,
    value TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES product_attributes (id) ON DELETE CASCADE,
    UNIQUE(product_id, attribute_id)
);
CREATE INDEX product_attribute_values_attribute_id_index ON product_attribute_values (attribute_id);

-- Таблица адресов клиентов
CREATE TABLE client_addresses (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'shipping',
    label VARCHAR(255) NULL,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    company VARCHAR(255) NULL,
    street VARCHAR(255) NOT NULL,
    city VARCHAR(255) NOT NULL,
    state VARCHAR(255) NULL,
    postal_code VARCHAR(255) NULL,
    country VARCHAR(255) NOT NULL DEFAULT 'Russia',
    phone VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);
CREATE INDEX client_addresses_client_id_type_index ON client_addresses (client_id, type);
CREATE INDEX client_addresses_client_id_is_default_index ON client_addresses (client_id, is_default);
CREATE INDEX client_addresses_type_is_default_index ON client_addresses (type, is_default);

-- Таблица настроек
CREATE TABLE settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'string',
    group VARCHAR(255) NULL,
    label VARCHAR(255) NULL,
    description VARCHAR(255) NULL,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);