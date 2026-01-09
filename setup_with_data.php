<?php
/**
 * Complete Database Setup with Sample Equipment Data
 * Run this in browser: http://localhost/Rentingmanagement/setup_with_data.php
 */

header("Content-Type: text/html; charset=UTF-8");

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "rentingmanagement";

echo "<h1>🚜 Equipment Rental - Complete Database Setup</h1>";
echo "<pre style='background:#f5f5f5; padding:20px; border-radius:10px;'>";

// Create connection WITHOUT database first
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
echo "✅ Connected to MySQL server\n";

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database '$dbname' ready\n";
} else {
    echo "❌ Error creating database: " . $conn->error . "\n";
}

// Select the database
$conn->select_db($dbname);

// ==========================================
// CREATE ALL TABLES
// ==========================================

echo "\n📋 Creating Tables...\n";

// Drop and recreate users table with all columns
$conn->query("DROP TABLE IF EXISTS users");
$conn->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255),
    location VARCHAR(255),
    user_type ENUM('user', 'owner', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "  ✅ users table\n";

// Drop and recreate Equipments table
$conn->query("DROP TABLE IF EXISTS equipments");
$conn->query("CREATE TABLE equipments (
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
    INDEX idx_owner_id (owner_id)
)");
echo "  ✅ equipments table\n";

// Drop and recreate Bookings table
$conn->query("DROP TABLE IF EXISTS bookings");
$conn->query("CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    user_id INT NOT NULL,
    operator_id INT DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price VARCHAR(50),
    location VARCHAR(255),
    booking_status ENUM('Pending', 'Approved', 'Declined', 'Completed', 'Cancelled') DEFAULT 'Pending',
    status VARCHAR(50) DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    booking_reference VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "  ✅ bookings table\n";

// Drop and recreate Operators table
$conn->query("DROP TABLE IF EXISTS operators");
$conn->query("CREATE TABLE operators (
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
)");
echo "  ✅ operators table\n";

// Drop and recreate Notifications table
$conn->query("DROP TABLE IF EXISTS notifications");
$conn->query("CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    is_read TINYINT DEFAULT 0,
    reference_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "  ✅ notifications table\n";

// Drop and recreate Categories table
$conn->query("DROP TABLE IF EXISTS categories");
$conn->query("CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(255),
    color VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "  ✅ categories table\n";

// ==========================================
// INSERT SAMPLE DATA
// ==========================================

echo "\n📦 Adding Sample Data...\n";

// Add test user if not exists
$checkUser = $conn->query("SELECT id FROM users WHERE email = 'test@test.com'");
if ($checkUser->num_rows == 0) {
    $hashedPassword = password_hash("test123", PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, mobile, password, user_type) VALUES ('Test User', 'test@test.com', '9876543210', '$hashedPassword', 'owner')");
    echo "  ✅ Test user created (email: test@test.com, password: test123)\n";
} else {
    echo "  ℹ️ Test user already exists\n";
}

// Get the user ID for owner_id
$userResult = $conn->query("SELECT id FROM users WHERE email = 'test@test.com'");
$userRow = $userResult->fetch_assoc();
$ownerId = $userRow['id'] ?? 1;

// Add categories
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

$conn->query("DELETE FROM categories");
foreach ($categories as $cat) {
    $conn->query("INSERT INTO categories (name, icon, color) VALUES ('{$cat[0]}', '{$cat[1]}', '{$cat[2]}')");
}
echo "  ✅ 8 Categories added\n";

// Add sample equipment
$equipments = [
    [
        'name' => 'John Deere 5310',
        'spec' => '55 HP, 2WD',
        'type' => 'Tractor',
        'price' => 500,
        'year' => '2022',
        'model' => '5310',
        'fuel' => 'Diesel',
        'power' => '55 HP',
        'desc' => 'Powerful and reliable tractor perfect for medium to large farms. Features excellent fuel efficiency and comfortable operation.'
    ],
    [
        'name' => 'Mahindra 575 DI',
        'spec' => '45 HP, 2WD',
        'type' => 'Tractor',
        'price' => 400,
        'year' => '2021',
        'model' => '575 DI',
        'fuel' => 'Diesel',
        'power' => '45 HP',
        'desc' => 'India\'s most popular tractor with excellent performance and low maintenance cost.'
    ],
    [
        'name' => 'Swaraj 744 FE',
        'spec' => '48 HP, 4WD',
        'type' => 'Tractor',
        'price' => 450,
        'year' => '2023',
        'model' => '744 FE',
        'fuel' => 'Diesel',
        'power' => '48 HP',
        'desc' => 'Modern 4WD tractor with advanced features and superior ground clearance.'
    ],
    [
        'name' => 'Kubota DC-68G',
        'spec' => 'Combine Harvester',
        'type' => 'Harvester',
        'price' => 1200,
        'year' => '2022',
        'model' => 'DC-68G',
        'fuel' => 'Diesel',
        'power' => '68 HP',
        'desc' => 'High-capacity combine harvester for efficient wheat and paddy harvesting.'
    ],
    [
        'name' => 'Shaktiman Rotavator',
        'spec' => '5 feet width',
        'type' => 'Rotavator',
        'price' => 350,
        'year' => '2023',
        'model' => 'SRT-150',
        'fuel' => 'N/A',
        'power' => '45-55 HP required',
        'desc' => 'Heavy-duty rotavator for superior soil preparation and mixing.'
    ],
    [
        'name' => 'KS Sprayer',
        'spec' => '16L Tank, Battery',
        'type' => 'Sprayer',
        'price' => 100,
        'year' => '2024',
        'model' => 'KS-16B',
        'fuel' => 'Battery',
        'power' => '12V DC',
        'desc' => 'Rechargeable battery-operated sprayer for pesticide and fertilizer application.'
    ],
    [
        'name' => 'MB Plough 3-Bottom',
        'spec' => 'Reversible',
        'type' => 'Plough',
        'price' => 250,
        'year' => '2022',
        'model' => 'MB-3R',
        'fuel' => 'N/A',
        'power' => '40+ HP required',
        'desc' => 'Professional grade reversible plough for deep tillage operations.'
    ],
    [
        'name' => 'Seed Drill Machine',
        'spec' => '9 Row',
        'type' => 'Seeder',
        'price' => 300,
        'year' => '2023',
        'model' => 'SD-9R',
        'fuel' => 'N/A',
        'power' => '35+ HP required',
        'desc' => 'Precision seed drill for accurate seed placement and spacing.'
    ],
    [
        'name' => 'Power Tiller',
        'spec' => '12 HP',
        'type' => 'Tiller',
        'price' => 200,
        'year' => '2023',
        'model' => 'PT-12',
        'fuel' => 'Diesel',
        'power' => '12 HP',
        'desc' => 'Compact power tiller ideal for small farms and gardens.'
    ],
    [
        'name' => 'Spring Cultivator',
        'spec' => '9 Tyne',
        'type' => 'Cultivator',
        'price' => 180,
        'year' => '2022',
        'model' => 'SC-9T',
        'fuel' => 'N/A',
        'power' => '35+ HP required',
        'desc' => 'Spring-loaded cultivator for effective secondary tillage.'
    ]
];

// Clear existing equipment and add fresh
$conn->query("DELETE FROM equipments");

foreach ($equipments as $eq) {
    $stmt = $conn->prepare("INSERT INTO equipments (equipment_name, specification, type, price_per_hour, availability, year, model, fuel_type, power, description, owner_id, rating) VALUES (?, ?, ?, ?, 'Available', ?, ?, ?, ?, ?, ?, ?)");
    $rating = rand(40, 50) / 10; // Random rating 4.0-5.0
    $stmt->bind_param("sssdsssssid", 
        $eq['name'], $eq['spec'], $eq['type'], $eq['price'], 
        $eq['year'], $eq['model'], $eq['fuel'], $eq['power'], 
        $eq['desc'], $ownerId, $rating
    );
    $stmt->execute();
    $stmt->close();
}
echo "  ✅ 10 Sample Equipments added\n";

// Add sample operators
$conn->query("DELETE FROM operators");
$operators = [
    ['Ram Kumar', '8 years', 'Tractor, Harvester, Rotavator', 4.9, 300, '9876543001'],
    ['Suresh Patel', '5 years', 'Tractor, Tiller, Cultivator', 4.7, 280, '9876543002'],
    ['Venkat Reddy', '10 years', 'All Equipment Types', 4.8, 350, '9876543003'],
    ['Raju Singh', '3 years', 'Tractor, Sprayer', 4.5, 250, '9876543004'],
    ['Mohan Yadav', '6 years', 'Harvester, Seeder', 4.6, 320, '9876543005']
];

foreach ($operators as $op) {
    $stmt = $conn->prepare("INSERT INTO operators (operator_name, experience, specification, availability, rating, price_per_hour, phone) VALUES (?, ?, ?, 'Available', ?, ?, ?)");
    $stmt->bind_param("sssdds", $op[0], $op[1], $op[2], $op[3], $op[4], $op[5]);
    $stmt->execute();
    $stmt->close();
}
echo "  ✅ 5 Sample Operators added\n";

// ==========================================
// DISPLAY RESULTS
// ==========================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 DATABASE SETUP COMPLETE!\n";
echo str_repeat("=", 50) . "\n";

// Show counts
echo "\n📊 Database Statistics:\n";
$tables = ['users', 'equipments', 'bookings', 'operators', 'categories'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
    $row = $result->fetch_assoc();
    echo "  • $table: " . $row['cnt'] . " records\n";
}

echo "\n🔐 Test Login Credentials:\n";
echo "  Email: test@test.com\n";
echo "  Password: test123\n";

echo "</pre>";

// Show equipment list
echo "<h2>📋 Equipment in Database:</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#4CAF50; color:white;'><th>ID</th><th>Name</th><th>Type</th><th>Price/Hr</th><th>Availability</th><th>Rating</th></tr>";

$result = $conn->query("SELECT * FROM equipments ORDER BY type, equipment_name");
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td><strong>{$row['equipment_name']}</strong><br><small>{$row['specification']}</small></td>";
    echo "<td>{$row['type']}</td>";
    echo "<td>₹{$row['price_per_hour']}</td>";
    echo "<td>{$row['availability']}</td>";
    echo "<td>⭐ {$row['rating']}</td>";
    echo "</tr>";
}
echo "</table>";

// API test links
echo "<h2>🔗 Test API Endpoints:</h2>";
echo "<ul>";
echo "<li><a href='view_equipment.php' target='_blank'>View All Equipment (JSON)</a></li>";
echo "<li><a href='get_categories.php' target='_blank'>Get Categories (JSON)</a></li>";
echo "<li><a href='get_available_operators.php' target='_blank'>Get Operators (JSON)</a></li>";
echo "<li><a href='get_equipment_detail.php?equipment_id=1' target='_blank'>Equipment Detail ID=1 (JSON)</a></li>";
echo "</ul>";

echo "<p style='color:green; font-size:18px;'><strong>✅ Your database is ready! You can now use the Android app.</strong></p>";

$conn->close();
?>
