<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

// Get current app version from request
$current_version = isset($_GET['version']) ? trim($_GET['version']) : "";
$current_version_code = isset($_GET['version_code']) ? intval($_GET['version_code']) : 0;

// Default response
$response = [
    "status" => "success",
    "update_available" => false,
    "latest_version" => "1.0.0",
    "latest_version_code" => 1,
    "update_type" => "none",
    "update_title" => "",
    "update_message" => "",
    "download_url" => "",
    "release_notes" => [],
    "force_update" => false
];

// Check if app_updates table exists, if not create it
$createTable = "CREATE TABLE IF NOT EXISTS app_updates (
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
)";
$conn->query($createTable);

// Get latest active update
$sql = "SELECT * FROM app_updates WHERE is_active = 1 ORDER BY version_code DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $update = $result->fetch_assoc();
    
    $latestVersionCode = intval($update['version_code']);
    
    if ($current_version_code > 0 && $latestVersionCode > $current_version_code) {
        $response['update_available'] = true;
        $response['latest_version'] = $update['version_name'];
        $response['latest_version_code'] = $latestVersionCode;
        $response['update_type'] = $update['update_type'];
        $response['update_title'] = $update['update_title'];
        $response['update_message'] = $update['update_message'];
        $response['download_url'] = $update['download_url'] ?? "";
        $response['force_update'] = $update['force_update'] == 1;
        
        // Parse release notes
        $notes = $update['release_notes'];
        if (!empty($notes)) {
            $response['release_notes'] = array_filter(array_map('trim', explode("\n", $notes)));
        }
    }
}

echo json_encode($response);

$conn->close();
?>
