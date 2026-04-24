-- db_backup.sql

CREATE DATABASE IF NOT EXISTS movie_tracker;
USE movie_tracker;

-- 1. Users table (if your app has login)
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- store hashed passwords
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Watchlist table (core table)
CREATE TABLE watchlist (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    tmdb_id       INT NOT NULL,                 -- movie ID from the API
    title         VARCHAR(255) NOT NULL,
    poster_path   VARCHAR(255),                 -- URL from API
    genre         VARCHAR(100),
    release_year  YEAR,
    status        ENUM('want_to_watch', 'watching', 'watched') DEFAULT 'want_to_watch',
    user_rating   TINYINT CHECK (user_rating BETWEEN 1 AND 10),
    notes         TEXT,
    added_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie (user_id, tmdb_id)  -- no duplicate movies per user
);

-- 3. (Optional bonus) Reviews table
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    movie_id   INT NOT NULL,
    review     TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES watchlist(id) ON DELETE CASCADE
);
