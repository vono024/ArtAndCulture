CREATE DATABASE IF NOT EXISTS simple_blog CHARACTER SET utf8mb4;
USE simple_blog;

CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) NOT NULL,
                       email VARCHAR(100) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL,
                       role ENUM('user', 'admin') DEFAULT 'user'
);

INSERT INTO users (username, email, password, role) VALUES
    ('admin', 'admin@admin.com', '$2y$10$XvgE1TfOG32Tu6prYnSABOBP2B37o9YkdtGoFqG6GoDjVzN5SY8Se', 'admin');

CREATE TABLE categories (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            name VARCHAR(100) NOT NULL
);

INSERT INTO categories (name) VALUES ('Кіно'), ('Книги'), ('Мистецтво');

CREATE TABLE posts (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       user_id INT,
                       category_id INT,
                       title VARCHAR(255),
                       content TEXT,
                       image VARCHAR(255),
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       FOREIGN KEY (user_id) REFERENCES users(id),
                       FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE comments (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          post_id INT,
                          user_id INT,
                          text TEXT,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (post_id) REFERENCES posts(id),
                          FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE likes (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       post_id INT,
                       user_id INT,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       FOREIGN KEY (post_id) REFERENCES posts(id),
                       FOREIGN KEY (user_id) REFERENCES users(id)
);
