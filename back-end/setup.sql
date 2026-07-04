-- Crear banco de dados
CREATE DATABASE IF NOT EXISTS precio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE precio_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120)  NOT NULL,
    email      VARCHAR(191)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migração para bancos já existentes (ignore o erro se a coluna já existir):
-- ALTER TABLE users ADD COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user';

-- O primeiro usuário cadastrado no sistema é promovido a admin
-- automaticamente pelo back-end (ver api.php, ação "register").
-- Para promover manualmente um usuário existente:
-- UPDATE users SET role = 'admin' WHERE email = 'seu-email@exemplo.com';

-- Tabela de histórico de pesquisas realizadas no comparador
CREATE TABLE IF NOT EXISTS search_log (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NULL,
    term          VARCHAR(120) NOT NULL,
    results_count INT UNSIGNED NOT NULL DEFAULT 0,
    source        ENUM('cache','live') NOT NULL DEFAULT 'live',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_search_log_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_search_log_created_at ON search_log (created_at);
