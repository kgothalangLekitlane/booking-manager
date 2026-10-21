-- Booking Manager database setup
-- Run this file in MySQL or phpMyAdmin to create the database and table.

CREATE DATABASE IF NOT EXISTS booking_manager
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE booking_manager;

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    booking_date DATE NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
