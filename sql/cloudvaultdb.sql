CREATE DATABASE cloudvaultdb;

USE cloudvaultdb;

CREATE TABLE uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255),
    file_type VARCHAR(100),
    file_size BIGINT,
    s3_url TEXT,
    uploaded_by VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);