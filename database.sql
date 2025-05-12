CREATE DATABASE IF NOT EXISTS we_are_in_ukraine CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE we_are_in_ukraine;

CREATE TABLE IF NOT EXISTS Users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    verificated BOOLEAN DEFAULT FALSE,
    show_comments BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verification_token VARCHAR(255),
    UNIQUE INDEX email_idx (email)
);

CREATE TABLE IF NOT EXISTS Administrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    verificated BOOLEAN DEFAULT FALSE,
    added_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verification_token CHAR(20) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (added_id) REFERENCES Users(id)
);

CREATE TABLE IF NOT EXISTS Posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    picture1_path VARCHAR(255),
    picture2_path VARCHAR(255),
    picture3_path VARCHAR(255),
    post_likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES Users(id)
);

CREATE TABLE IF NOT EXISTS Comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    redacted BOOLEAN DEFAULT FALSE,
    comments_likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES Posts(id),
    FOREIGN KEY (user_id) REFERENCES Users(id)
);

CREATE TABLE IF NOT EXISTS CommentLikes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES Comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    UNIQUE KEY unique_comment_like (comment_id, user_id)
);

CREATE TABLE IF NOT EXISTS PostLikes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES Posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    UNIQUE KEY unique_post_like (post_id, user_id)
);

CREATE TABLE IF NOT EXISTS Feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    feedback_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Dustbin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_id INT NOT NULL,
    content_type ENUM('post', 'comment', 'user') NOT NULL,
    reason TEXT NOT NULL,
    deleted_by INT NOT NULL,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deleted_by) REFERENCES Users(id)
);

CREATE TABLE IF NOT EXISTS Reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_id INT NOT NULL,
    content_type ENUM('post', 'comment', 'user') NOT NULL,
    reported_by_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by_id) REFERENCES Users(id),
    UNIQUE KEY unique_report (content_id, content_type, reported_by_id)
);
