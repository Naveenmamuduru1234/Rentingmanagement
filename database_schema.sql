-- Equipment Rental App - Database Schema
-- Run these SQL commands to set up all required tables

-- =====================================================
-- USERS TABLE (if not exists)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    profile_image VARCHAR(255),
    user_type ENUM('customer', 'owner') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- PASSWORD RESET OTP TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS password_reset_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- =====================================================
-- WALLET TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS wallet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    balance DECIMAL(10, 2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'INR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- TRANSACTIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255),
    reference_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- RATINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT DEFAULT NULL,
    operator_id INT DEFAULT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_equipment (equipment_id),
    INDEX idx_operator (operator_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- PAYMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    user_id INT NOT NULL,
    razorpay_payment_id VARCHAR(100),
    razorpay_order_id VARCHAR(100),
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_booking (booking_id),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking_confirmed', 'operator_assigned', 'payment', 'offer', 'review_request', 'general') DEFAULT 'general',
    is_read TINYINT DEFAULT 0,
    reference_id INT DEFAULT NULL,
    reference_type VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- EQUIPMENT INSURANCE TABLE (if not exists)
-- =====================================================
CREATE TABLE IF NOT EXISTS equipment_insurance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    insurance_company VARCHAR(255) NOT NULL,
    policy_number VARCHAR(100) NOT NULL,
    insurance_type VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    insurance_amount DECIMAL(10, 2),
    damage_deductible DECIMAL(10, 2),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_equipment (equipment_id)
);

-- =====================================================
-- ALTER EXISTING TABLES
-- =====================================================

-- Add profile_image to users if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL;

-- Add payment columns to bookings if not exists
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid') DEFAULT 'pending';
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_id INT DEFAULT NULL;

-- =====================================================
-- SAMPLE DATA FOR TESTING
-- =====================================================

-- Sample notifications (replace user_id with actual user)
INSERT INTO notifications (user_id, title, message, type, is_read) VALUES
(1, 'Booking Confirmed', 'Your booking #AGR-2025-001 has been confirmed for Jan 5', 'booking_confirmed', 0),
(1, 'Operator Assigned', 'Ram Kumar has been assigned as your operator', 'operator_assigned', 0),
(1, 'Special Offer!', 'Get 20% off on your next booking. Use code SAVE20', 'offer', 1),
(1, 'Review Request', 'How was your experience with John Deere 5310?', 'review_request', 1),
(1, 'Payment Successful', 'Your payment of ₹5,605 was successful', 'payment', 1);

-- Sample wallet entry (replace user_id with actual user)
INSERT INTO wallet (user_id, balance) VALUES (1, 2400.00)
ON DUPLICATE KEY UPDATE balance = balance;

-- =====================================================
-- APP UPDATES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS app_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version_name VARCHAR(20) NOT NULL,
    version_code INT NOT NULL,
    update_type ENUM('major', 'minor', 'patch', 'critical') DEFAULT 'minor',
    update_title VARCHAR(255) NOT NULL,
    update_message TEXT,
    release_notes TEXT,
    download_url VARCHAR(500),
    force_update TINYINT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update notifications table to include app_update type
ALTER TABLE notifications MODIFY COLUMN type ENUM('booking_confirmed', 'operator_assigned', 'payment', 'offer', 'review_request', 'general', 'app_update') DEFAULT 'general';

-- Sample app update (for testing)
INSERT INTO app_updates (version_name, version_code, update_type, update_title, update_message, release_notes, force_update, is_active) VALUES
('2.0.0', 2, 'major', '🚀 Major Update Available!', 
'We have made significant improvements to your experience. Update now to enjoy new features and better performance!',
'New OTP email verification for password reset\nApp update notifications\nImproved notification system\nBug fixes and performance improvements',
0, 1);
