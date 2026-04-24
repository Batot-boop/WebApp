-- db_backup.sql
CREATE DATABASE IF NOT EXISTS movie_tracker;
USE movie_tracker;

CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE watchlist (
    ID      INT AUTO_INCREMENT PRIMARY KEY,
    title   VARCHAR(255) NOT NULL,          -- increased from 50
    image   VARCHAR(500) NOT NULL,          -- increased from 100
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
);