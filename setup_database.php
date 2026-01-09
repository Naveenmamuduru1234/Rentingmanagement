<?php
/**
 * Complete Database Setup Script for Agro Rent
 * 
 * This script creates all required tables and adds sample data for testing.
 * Run this once to set up your database.
 * 
 * Access via: http://localhost/your-folder/setup_database.php
 * Add ?sample=true to add sample test data
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require "db.php";

$results = [];
$errors = [];

// 1. Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    profile_image VARCHAR(255),
    user_type ENUM('customer', 'owner') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql)) {
    $results[] = "✓ Users table created/verified";
} else {
    $errors[] = "✗ Users table: " . $conn->error;
}

// 2. Create equipments table (note: your PHP uses 'equipments' but schema might use 'equipment')
$sql = "CREATE TABLE IF NOT EXISTS equipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_name VARCHAR(255) NOT NULL,
    specification TEXT,
    type VARCHAR(100),
    price_per_hour DECIMAL(10, 2),
    availability VARCHAR(50) DEFAULT 'Available',
    year VARCHAR(10),
    model VARCHAR(100),
    fuel_type VARCHAR(50),
    power VARCHAR(50),
    insurance VARCHAR(100),
    description TEXT,
    image VARCHAR(500),
    owner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_owner_id (owner_id),
    INDEX idx_type (type)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Equipments table created/verified";
} else {
    $errors[] = "✗ Equipments table: " . $conn->error;
}

// Also create 'equipment' as alias for compatibility
$sql = "CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    equipment_name VARCHAR(255),
    specification TEXT,
    type VARCHAR(100),
    price_per_hour DECIMAL(10, 2),
    availability VARCHAR(50) DEFAULT 'Available',
    year VARCHAR(10),
    model VARCHAR(100),
    fuel_type VARCHAR(50),
    power VARCHAR(50),
    insurance VARCHAR(100),
    description TEXT,
    image VARCHAR(500),
    owner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_owner_id (owner_id)
)";
$conn->query($sql);

// 3. Create bookings table
$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    user_id INT NOT NULL,
    operator_id INT DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price VARCHAR(50),
    location VARCHAR(255) DEFAULT NULL,
    booking_status ENUM('Pending', 'Approved', 'Declined', 'Completed', 'Cancelled') DEFAULT 'Pending',
    status VARCHAR(50) DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_id INT DEFAULT NULL,
    booking_reference VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (booking_status)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Bookings table created/verified";
} else {
    $errors[] = "✗ Bookings table: " . $conn->error;
}

// 4. Create operators table
$sql = "CREATE TABLE IF NOT EXISTS operators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operator_name VARCHAR(255) NOT NULL,
    experience VARCHAR(100),
    specification TEXT,
    availability VARCHAR(50) DEFAULT 'Available',
    rating DECIMAL(3, 2) DEFAULT 0,
    phone VARCHAR(20),
    image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql)) {
    $results[] = "✓ Operators table created/verified";
} else {
    $errors[] = "✗ Operators table: " . $conn->error;
}

// 5. Create notifications table
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking_confirmed', 'operator_assigned', 'payment', 'offer', 'review_request', 'general', 'app_update', 'booking_request', 'booking_cancelled') DEFAULT 'general',
    is_read TINYINT DEFAULT 0,
    reference_id INT DEFAULT NULL,
    reference_type VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Notifications table created/verified";
} else {
    $errors[] = "✗ Notifications table: " . $conn->error;
}

// 6. Create password_reset_otp table
$sql = "CREATE TABLE IF NOT EXISTS password_reset_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_otp (otp)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Password Reset OTP table created/verified";
} else {
    $errors[] = "✗ Password Reset OTP table: " . $conn->error;
}

// 7. Create app_updates table
$sql = "CREATE TABLE IF NOT EXISTS app_updates (
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_version_code (version_code),
    INDEX idx_is_active (is_active)
)";
if ($conn->query($sql)) {
    $results[] = "✓ App Updates table created/verified";
} else {
    $errors[] = "✗ App Updates table: " . $conn->error;
}

// 8. Create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    razorpay_payment_id VARCHAR(255) DEFAULT NULL,
    razorpay_order_id VARCHAR(255) DEFAULT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Payments table created/verified";
} else {
    $errors[] = "✗ Payments table: " . $conn->error;
}

// 9. Create wallet table
$sql = "CREATE TABLE IF NOT EXISTS wallet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'INR',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Wallet table created/verified";
} else {
    $errors[] = "✗ Wallet table: " . $conn->error;
}

// 10. Create transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255),
    reference_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Transactions table created/verified";
} else {
    $errors[] = "✗ Transactions table: " . $conn->error;
}

// 11. Create ratings table
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT DEFAULT NULL,
    operator_id INT DEFAULT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_operator_id (operator_id)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Ratings table created/verified";
} else {
    $errors[] = "✗ Ratings table: " . $conn->error;
}

// 12. Create equipment_insurance table
$sql = "CREATE TABLE IF NOT EXISTS equipment_insurance (
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
    INDEX idx_equipment_id (equipment_id)
)";
if ($conn->query($sql)) {
    $results[] = "✓ Equipment Insurance table created/verified";
} else {
    $errors[] = "✗ Equipment Insurance table: " . $conn->error;
}

// Add sample data if requested
$addSampleData = isset($_GET['sample']) && $_GET['sample'] === 'true';

if ($addSampleData) {
    // Get first user for sample data
    $userCheck = $conn->query("SELECT id FROM users LIMIT 1");
    if ($userCheck && $userCheck->num_rows > 0) {
        $user = $userCheck->fetch_assoc();
        $userId = $user['id'];
        
        // Check if sample notifications already exist
        $notifCheck = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = $userId");
        $notifCount = $notifCheck->fetch_assoc()['count'];
        
        if ($notifCount == 0) {
            $sampleNotifs = [
                ["Welcome to Agro Rent! 🚜", "Your account has been created successfully. Start exploring equipment for your farming needs!", "general", 1],
                ["Booking Confirmed", "Your booking #AGR-2025-0001 for John Deere 5310 has been confirmed!", "booking_confirmed", 2],
                ["Special Offer! 🎉", "Get 15% off on your first booking! Use code: WELCOME15", "offer", 5],
                ["Operator Assigned", "Ram Kumar has been assigned as your operator for booking #AGR-2025-0001", "operator_assigned", 8],
                ["App Update Available", "A new version of Agro Rent is available with exciting new features!", "app_update", 24]
            ];
            
            foreach ($sampleNotifs as $notif) {
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? HOUR))");
                $stmt->bind_param("isssi", $userId, $notif[0], $notif[1], $notif[2], $notif[3]);
                $stmt->execute();
                $stmt->close();
            }
            $results[] = "✓ Sample notifications added for user ID: $userId";
        } else {
            $results[] = "ℹ Sample notifications already exist for user ID: $userId";
        }
        
        // Add wallet for user
        $conn->query("INSERT INTO wallet (user_id, balance) VALUES ($userId, 2500.00) ON DUPLICATE KEY UPDATE balance = balance");
        $results[] = "✓ Wallet initialized for user ID: $userId";
    } else {
        $results[] = "ℹ No users found - create a user first to add sample data";
    }
    
    // Add sample app update
    $updateCheck = $conn->query("SELECT COUNT(*) as count FROM app_updates");
    $updateCount = $updateCheck->fetch_assoc()['count'];
    
    if ($updateCount == 0) {
        $sql = "INSERT INTO app_updates (version_name, version_code, update_type, update_title, update_message, release_notes, force_update, is_active) 
                VALUES ('1.1.0', 2, 'minor', 'New Features Available!', 
                'Update to get the latest features including improved booking flow, better notifications, and bug fixes.',
                'AI-powered availability checking\nReal-time notifications\nImproved booking flow\nPayment integration\nBug fixes',
                0, 1)";
        if ($conn->query($sql)) {
            $results[] = "✓ Sample app update added (version 1.1.0, code: 2)";
        }
    } else {
        $results[] = "ℹ App updates already exist";
    }
}

// Test OTP table by creating and deleting a test record
$testOtp = sprintf("%06d", mt_rand(0, 999999));
$testEmail = "test_otp_check@temp.com";
$stmt = $conn->prepare("INSERT INTO password_reset_otp (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
$stmt->bind_param("ss", $testEmail, $testOtp);
if ($stmt->execute()) {
    $results[] = "✓ OTP table is working correctly";
    // Clean up test record
    $conn->query("DELETE FROM password_reset_otp WHERE email = '$testEmail'");
} else {
    $errors[] = "✗ OTP table test failed: " . $stmt->error;
}
$stmt->close();

$conn->close();

// Output results
echo json_encode([
    "status" => count($errors) === 0 ? "success" : "partial",
    "message" => "Database setup complete",
    "total_tables" => 12,
    "created" => count($results),
    "results" => $results,
    "errors" => $errors,
    "next_steps" => [
        "1. Copy all PHP files to your web server",
        "2. Run setup_database.php?sample=true to add test data",
        "3. Configure SMTP in send_otp.php for email OTP",
        "4. Update Razorpay key in verify_payment.php",
        "5. Rebuild and run the Android app"
    ]
], JSON_PRETTY_PRINT);
?>
