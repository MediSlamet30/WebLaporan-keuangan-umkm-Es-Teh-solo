-- Buat database
CREATE DATABASE IF NOT EXISTS esteh_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE esteh_db;

-- Tabel users (sederhana)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(150) DEFAULT 'Admin'
);

-- Password default: admin123 (hash menggunakan password_hash di PHP)
INSERT INTO users (username, password, name) VALUES
('admin','$2y$10$wH9xJ/5kZ2rRz1Q6gX3bRew6G7oYV6t9m3q2nQz6o7kE4a9hYg2aO','Admin');

-- Tabel products (menu es teh sederhana)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  price INT NOT NULL
);

INSERT INTO products (name, price) VALUES
('Es Teh Original', 8000),
('Es Teh Lemon', 10000),
('Es Teh Tarik', 12000),
('Es Teh Leci', 13000),
('Es Teh Strawberry', 14000);

-- Tabel sales
CREATE TABLE IF NOT EXISTS sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  total INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Tabel sale_items
CREATE TABLE IF NOT EXISTS sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id INT NOT NULL,
  product_id INT NOT NULL,
  qty INT NOT NULL,
  price INT NOT NULL,
  FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);
