-- Database Schema for Studio Hasanarofid

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100),
    preview_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    template_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'paid', 'verified', 'failed') DEFAULT 'pending',
    payment_receipt VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (template_id) REFERENCES templates(id)
);

-- Insert Admin Account (Hasanarofid)
-- Password 'DemiAllah@1' hashed with bcrypt
INSERT INTO users (name, email, password, role) 
VALUES ('Hasan Arofid', 'hasanarofid@gmail.com', '$2y$10$KUHv/0XqCDRcQs1YaXFEIeNv2rP0oqQmSUj13x/Wj0NEC40wh8il2', 'admin');

-- Initial Templates
INSERT INTO templates (name, description, price, category, preview_url) VALUES 
('Business Landing Page', 'Modern business template', 250000, 'Landing Page', 'templates/business.html'),
('Portfolio Personal', 'Creative portfolio for professionals', 150000, 'Portfolio', 'templates/portfolio.html'),
('UMKM Product Showcase', 'Simple and clean for small businesses', 100000, 'UMKM', 'templates/umkm.html');
