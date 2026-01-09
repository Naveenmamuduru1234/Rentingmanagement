<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'db.php';

$user_id = $_GET['user_id'] ?? -1;

if ($user_id == -1) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

// Get pending payments (approved bookings awaiting payment)
$query = "SELECT 
            b.id as booking_id,
            e.name as equipment_name,
            e.image as equipment_image,
            DATE_FORMAT(b.start_date, '%b %d, %Y - %h:%i %p') as date,
            CONCAT(TIMESTAMPDIFF(HOUR, b.start_date, b.end_date), ' hours') as duration,
            b.total_price as total_amount,
            COALESCE(o.operator_charge, 0) as operator_charges,
            (b.total_price - COALESCE(o.operator_charge, 0)) as rental_cost,
            CASE 
                WHEN b.payment_status = 'pending' THEN 'Payment Pending'
                WHEN b.payment_status = 'paid' THEN 'Paid'
                ELSE 'Payment Pending'
            END as status
          FROM bookings b
          INNER JOIN equipment e ON b.equipment_id = e.id
          LEFT JOIN operators o ON b.operator_id = o.id
          WHERE b.user_id = ? 
          AND b.status = 'approved'
          AND (b.payment_status = 'pending' OR b.payment_status IS NULL)
          ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$payments = [];
while ($row = $result->fetch_assoc()) {
    // Calculate GST (18%)
    $subtotal = $row['rental_cost'] + $row['operator_charges'];
    $gst = $subtotal * 0.18;
    $total_with_gst = $subtotal + $gst;
    
    $payments[] = [
        "booking_id" => (int) $row['booking_id'],
        "equipment_name" => $row['equipment_name'],
        "equipment_image" => $row['equipment_image'],
        "date" => $row['date'],
        "duration" => $row['duration'],
        "rental_cost" => (float) $row['rental_cost'],
        "operator_charges" => (float) $row['operator_charges'],
        "total_amount" => (float) $total_with_gst,
        "status" => $row['status']
    ];
}

echo json_encode([
    "status" => "success",
    "message" => "Pending payments retrieved successfully",
    "data" => $payments
]);

$stmt->close();
$conn->close();
?>
