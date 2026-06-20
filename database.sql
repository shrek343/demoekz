-- ============================================================
--  БАЗА ДАННЫХ ПРОЕКТА.
--  object и status — текстовые (VARCHAR): какие именно значения
--  показывать, задаётся прямо в php-файлах (application.php, admin.php).
--  Импорт: phpMyAdmin -> "Импорт" -> этот файл -> "Вперёд".
--  ER-диаграмма: база demo -> вкладка "Designer".
--  Связи: users 1—∞ applications 1—1 reviews
-- ============================================================

CREATE DATABASE IF NOT EXISTS demo
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE demo;

-- 1) ПОЛЬЗОВАТЕЛИ
CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  login      VARCHAR(50)  NOT NULL UNIQUE,     -- уникальный логин (лат.+цифры, мин.6)
  password   VARCHAR(255) NOT NULL,            -- ХЕШ пароля (мин.8 при регистрации)
  fio        VARCHAR(150) NOT NULL,            -- ФИО
  birth_date DATE         NULL,                -- дата рождения (не используется, оставлено для совместимости)
  phone      VARCHAR(30)  NOT NULL,            -- телефон
  email      VARCHAR(150) NOT NULL,            -- e-mail
  role       ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2) ЗАЯВКИ
CREATE TABLE applications (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,                     -- ВНЕШНИЙ КЛЮЧ -> users.id
  object     VARCHAR(100) NOT NULL,            -- что выбрали (транспорт/помещение/курс)
  start_date VARCHAR(10)  NOT NULL,            -- дата начала (ДД.ММ.ГГГГ)
  start_time VARCHAR(20)  NOT NULL,            -- предпочтительное время
  payment    VARCHAR(50)  NOT NULL,            -- способ оплаты
  status     VARCHAR(40)  NOT NULL DEFAULT 'Новая', -- начальный статус новой заявки
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3) ОТЗЫВЫ (1 на заявку, только после смены статуса админом)
CREATE TABLE reviews (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT NOT NULL UNIQUE,          -- ВНЕШНИЙ КЛЮЧ -> applications.id (1:1)
  text           TEXT NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_review_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- ===== НАЧАЛЬНЫЕ ДАННЫЕ =====
-- Админ: логин Admin26 / пароль Demo20
INSERT INTO users (login, password, fio, phone, email, role) VALUES
('Admin26', '$2b$10$.aYKdj.LRtdKBD5cUV5b2OEmb3hU00R9ChOVJXq8q9ldLHiJGPOwO',
 'Администратор', '+7 (495) 123-45-67', 'admin@demo.ru', 'admin');

-- Тест-пользователь: логин ivan123 / пароль parol123
INSERT INTO users (login, password, fio, phone, email, role) VALUES
('ivan123', '$2b$10$xILkFbHKhxQgx3o/K7mC1.lB1zf5ygXmo/B5hJPHnYJ/kyiX39noS',
 'Иванов Иван Иванович', '+7 (900) 000-00-00', 'ivan@mail.ru', 'user');
