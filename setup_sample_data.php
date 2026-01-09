<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Create table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS `equipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_name` varchar(255) NOT NULL,
  `specification` varchar(500) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `availability` varchar(50) DEFAULT 'Available',
  `year` varchar(10) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `power` varchar(50) DEFAULT NULL,
  `insurance` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conn->query($createTable)) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to create table: " . $conn->error
    ]);
    exit;
}

// Check if data already exists
$checkResult = $conn->query("SELECT COUNT(*) as count FROM equipments");
$row = $checkResult->fetch_assoc();

if ($row['count'] > 0) {
    echo json_encode([
        "status" => "success",
        "message" => "Sample data already exists. Found " . $row['count'] . " equipment items.",
        "count" => intval($row['count'])
    ]);
    exit;
}

// Insert sample data
$sampleData = [
    // Tractors
    ["John Deere 5310", "55 HP, 2WD, Hydraulic steering", "Tractor", 850.00, "Available", "2022", "JD-5310", "Diesel", "55 HP", "Fully Insured", "Reliable and powerful tractor", "", 1],
    ["Mahindra 575 DI", "45 HP, 2WD, Constant mesh transmission", "Tractor", 700.00, "Available", "2021", "M-575DI", "Diesel", "45 HP", "Fully Insured", "Best-selling tractor", "", 1],
    ["Sonalika DI 750", "50 HP, 4WD, Oil immersed brakes", "Tractor", 900.00, "Booked", "2023", "S-750III", "Diesel", "50 HP", "Fully Insured", "Premium 4WD tractor", "", 2],
    
    // Harvesters
    ["Kubota DC-70", "Self-propelled, 70 HP, 4.2m cutting width", "Harvester", 2500.00, "Available", "2022", "DC-70", "Diesel", "70 HP", "Fully Insured", "High-performance combine harvester", "", 1],
    ["Preet 987", "Combine harvester, 87 HP, Multi-crop", "Harvester", 2200.00, "Available", "2021", "P-987", "Diesel", "87 HP", "Fully Insured", "Versatile harvester", "", 2],
    
    // Tillers
    ["Honda FJ500", "5.5 HP, 4-stroke engine, 500mm tilling width", "Tiller", 400.00, "Available", "2022", "FJ500", "Petrol", "5.5 HP", "Basic Insurance", "Lightweight power tiller", "", 1],
    ["VST Shakti 130 DI", "13 HP, 4WD, Rotary tiller attached", "Tiller", 600.00, "Available", "2023", "VS-130DI", "Diesel", "13 HP", "Fully Insured", "Versatile power tiller", "", 2],
    
    // Sprayers
    ["Aspee Boom Sprayer", "Tractor mounted, 500L tank, 12m boom", "Sprayer", 350.00, "Available", "2022", "ABS-500", "N/A", "N/A", "Basic Insurance", "Large capacity boom sprayer", "", 1],
    ["Neptune Power Sprayer", "Battery operated, 20L tank, 6 bar pressure", "Sprayer", 200.00, "Available", "2022", "NPS-20", "Battery", "N/A", "Basic Insurance", "Rechargeable battery sprayer", "", 2],
    
    // Ploughs
    ["MB Plough 3 Furrow", "3 furrow, Hydraulic reversible", "Plough", 300.00, "Available", "2022", "MBP-3F", "N/A", "N/A", "Basic Insurance", "Reversible mould board plough", "", 1],
    ["Disc Plough 3 Disc", "3 disc, 660mm diameter", "Plough", 280.00, "Available", "2021", "DP-3D", "N/A", "N/A", "Not Insured", "Disc plough for tough soils", "", 2],
    
    // Seeders
    ["Khedut Seed Drill", "9 row, Tractor mounted", "Seeder", 400.00, "Available", "2022", "KSD-9R", "N/A", "N/A", "Basic Insurance", "Precision seed drill", "", 1],
    ["Zero Till Drill", "11 tyne, Direct seeding", "Seeder", 450.00, "Available", "2022", "ZTD-11", "N/A", "N/A", "Basic Insurance", "Zero tillage seed drill", "", 2],
    
    // Cultivators
    ["Spring Loaded Cultivator", "9 tyne, 2.1m width", "Cultivator", 280.00, "Available", "2022", "SLC-9", "N/A", "N/A", "Basic Insurance", "Robust cultivator", "", 1],
    ["Duck Foot Cultivator", "7 tyne, Sweeps attached", "Cultivator", 250.00, "Available", "2021", "DFC-7", "N/A", "N/A", "Not Insured", "Weed control cultivator", "", 2],
    
    // Rotavators
    ["Shaktiman Rotavator", "5 feet, 42 blades", "Rotavator", 500.00, "Available", "2022", "SR-5F", "N/A", "N/A", "Fully Insured", "Premium quality rotavator", "", 1],
    ["Fieldking Rotavator", "6 feet, L-shaped blades", "Rotavator", 550.00, "Available", "2023", "FK-6F", "N/A", "N/A", "Fully Insured", "Wide working width rotavator", "", 2]
];

$insertCount = 0;
$stmt = $conn->prepare("INSERT INTO equipments (equipment_name, specification, type, price_per_hour, availability, year, model, fuel_type, power, insurance, description, image, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($sampleData as $data) {
    $stmt->bind_param("sssdssssssssi", 
        $data[0], $data[1], $data[2], $data[3], $data[4], 
        $data[5], $data[6], $data[7], $data[8], $data[9], 
        $data[10], $data[11], $data[12]
    );
    
    if ($stmt->execute()) {
        $insertCount++;
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    "status" => "success",
    "message" => "Successfully inserted $insertCount sample equipment items!",
    "count" => $insertCount
]);
?>
