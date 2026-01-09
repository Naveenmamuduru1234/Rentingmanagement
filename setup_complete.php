<?php
/**
 * Complete Database Setup Script
 * Run this once in browser: http://localhost/Rentingmanagement/setup_complete.php
 */

header("Content-Type: text/html; charset=UTF-8");

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "rentingmanagement";

echo "<h1>Equipment Rental Database Setup</h1>";
echo "<pre>";

// Create connection WITHOUT database first
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✓ Connected to MySQL server\n";

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "✓ Database '$dbname' created or already exists\n";
} else {
    echo "✗ Error creating database: " . $conn->error . "\n";
}

// Select the database
$conn->select_db($dbname);
echo "✓ Selected database '$dbname'\n";

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255),
    location VARCHAR(255),
    user_type ENUM('user', 'owner', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'users' created\n";
} else {
    echo "✗ Error creating users table: " . $conn->error . "\n";
}

// Create equipments table
$sql = "CREATE TABLE IF NOT EXISTS equipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_name VARCHAR(255) NOT NULL,
    specification TEXT,
    type VARCHAR(100),
    price_per_hour DECIMAL(10,2),
    availability VARCHAR(50) DEFAULT 'Available',
    year VARCHAR(10),
    model VARCHAR(100),
    fuel_type VARCHAR(50),
    power VARCHAR(50),
    insurance VARCHAR(255),
    description TEXT,
    image VARCHAR(255),
    owner_id INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    total_bookings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_owner_id (owner_id),
    INDEX idx_availability (availability)
)";
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'equipments' created\n";
} else {
    echo "✗ Error creating equipments table: " . $conn->error . "\n";
}

// Create bookings table
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
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'bookings' created\n";
} else {
    echo "✗ Error creating bookings table: " . $conn->error . "\n";
}

// Create operators table
$sql = "CREATE TABLE IF NOT EXISTS operators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operator_name VARCHAR(255) NOT NULL,
    experience VARCHAR(50),
    specification TEXT,
    availability VARCHAR(50) DEFAULT 'Available',
    rating DECIMAL(3,2) DEFAULT 4.5,
    price_per_hour DECIMAL(10,2) DEFAULT 300,
    phone VARCHAR(20),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'operators' created\n";
} else {
    echo "✗ Error creating operators table: " . $conn->error . "\n";
}

// Create notifications table
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
    INDEX idx_is_read (is_read)
)";
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'notifications' created\n";
} else {
    echo "✗ Error creating notifications table: " . $conn->error . "\n";
}

// Create ratings table
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT,
    operator_id INT,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'ratings' created\n";
} else {
    echo "✗ Error creating ratings table: " . $conn->error . "\n";
}

// Create insurance table
$sql = "CREATE TABLE IF NOT EXISTS insurance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    insurance_company VARCHAR(255),
    policy_number VARCHAR(100),
    insurance_type VARCHAR(100),
    start_date DATE,
    end_date DATE,
    insurance_amount VARCHAR(50),
    damage_deductible VARCHAR(50),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'insurance' created\n";
} else {
    echo "✗ Error creating insurance table: " . $conn->error . "\n";
}

// Create password_resets table for OTP
$sql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
)";
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'password_resets' created\n";
} else {
    echo "✗ Error creating password_resets table: " . $conn->error . "\n";
}

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(255),
    color VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'categories' created\n";
} else {
    echo "✗ Error creating categories table: " . $conn->error . "\n";
}

// Insert default categories
$categories = [
    ['Tractor', 'ic_tractor', '#4CAF50'],
    ['Harvester', 'ic_harvester', '#2196F3'],
    ['Tiller', 'ic_tiller', '#FF9800'],
    ['Sprayer', 'ic_sprayer', '#9C27B0'],
    ['Plough', 'ic_plough', '#F44336'],
    ['Seeder', 'ic_seeder', '#00BCD4'],
    ['Cultivator', 'ic_cultivator', '#795548'],
    ['Rotavator', 'ic_rotavator', '#607D8B']
];

foreach ($categories as $cat) {
    $name = $cat[0];
    $icon = $cat[1];
    $color = $cat[2];
    
    $check = $conn->query("SELECT id FROM categories WHERE name = '$name'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO categories (name, icon, color) VALUES ('$name', '$icon', '$color')");
    }
}
echo "✓ Default categories inserted\n";

// Insert sample operators if none exist
$check = $conn->query("SELECT COUNT(*) as count FROM operators");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $operators = [
        ['Ram Kumar', '8 years', 'Tractor, Harvester, Rotavator', 'Available', 4.9, 300],
        ['Suresh Patel', '5 years', 'Tractor, Tiller, Cultivator', 'Available', 4.7, 280],
        ['Venkat Reddy', '10 years', 'All Equipment', 'Available', 4.8, 350],
        ['Raju Singh', '3 years', 'Tractor, Sprayer', 'Available', 4.5, 250]
    ];
    
    foreach ($operators as $op) {
        $stmt = $conn->prepare("INSERT INTO operators (operator_name, experience, specification, availability, rating, price_per_hour) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdd", $op[0], $op[1], $op[2], $op[3], $op[4], $op[5]);
        $stmt->execute();
        $stmt->close();
    }
    echo "✓ Sample operators inserted\n";
}

echo "\n========================================\n";
echo "✓ DATABASE SETUP COMPLETE!\n";
echo "========================================\n";
echo "\nYou can now use the Equipment Rental app.\n";
echo "Database: $dbname\n";

// Show table counts
$tables = ['users', 'equipments', 'bookings', 'operators', 'notifications', 'categories'];
echo "\nTable Statistics:\n";
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "  - $table: " . $row['count'] . " rows\n";
    }
}

echo "</pre>";

// Test links
echo "<h2>Test API Endpoints:</h2>";
echo "<ul>";
echo "<li><a href='view_equipment.php' target='_blank'>View Equipment</a></li>";
echo "<li><a href='get_categories.php' target='_blank'>Get Categories</a></li>";
echo "<li><a href='get_available_operators.php' target='_blank'>Get Operators</a></li>";
echo "</ul>";

$conn->close();
?>
