<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

$equipment_id = $_POST["equipment_id"] ?? "";
$user_id      = $_POST["user_id"] ?? "";
$start_date   = $_POST["start_date"] ?? "";
$end_date     = $_POST["end_date"] ?? "";
$total_price  = $_POST["total_price"] ?? "";
$operator_id  = $_POST["operator_id"] ?? NULL; // optional

// Required validation
if ($equipment_id == "" || $user_id == "" || $start_date == "" || $end_date == "") {
    echo json_encode([
        "status" => "error",
        "message" => "equipment_id, user_id, start_date, end_date are required"
    ]);
    exit;
}

// Insert booking
$stmt = $conn->prepare("
    INSERT INTO bookings (equipment_id, user_id, operator_id, start_date, end_date, total_price)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("iiissd", $equipment_id, $user_id, $operator_id, $start_date, $end_date, $total_price);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Equipment booked successfully",
        "booking_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Booking failed",
        "sql_error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
