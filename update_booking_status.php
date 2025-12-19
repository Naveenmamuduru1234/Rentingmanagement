<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

$booking_id = $_POST["booking_id"] ?? "";
$status     = $_POST["booking_status"] ?? "";

if ($booking_id == "" || $status == "") {
    echo json_encode([
        "status" => "error",
        "message" => "booking_id and booking_status are required"
    ]);
    exit;
}

// Validate accepted values
$allowed = ["Pending", "Accepted", "Rejected"];
if (!in_array($status, $allowed)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid status value. Use: Pending, Accepted, Rejected"
    ]);
    exit;
}

$sql = "UPDATE bookings SET booking_status='$status' WHERE booking_id='$booking_id'";

if ($conn->query($sql)) {
    echo json_encode([
        "status" => "success",
        "message" => "Booking status updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Update failed",
        "sql_error" => $conn->error
    ]);
}

$conn->close();
?>
