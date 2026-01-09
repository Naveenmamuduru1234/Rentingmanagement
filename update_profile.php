<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : "";
$email = isset($_POST['email']) ? trim($_POST['email']) : "";
$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : "";
$location = isset($_POST['location']) ? trim($_POST['location']) : "";

// Validation
if ($user_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

if ($name == "" || $email == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Name and email are required"
    ]);
    exit;
}

// Handle image upload
$profile_image = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = "uploads/profiles/";
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid image format. Allowed: jpg, jpeg, png, gif"
        ]);
        exit;
    }
    
    $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $profile_image = $upload_path;
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to upload image"
        ]);
        exit;
    }
}

// Update user profile
if ($profile_image) {
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, mobile=?, location=?, profile_image=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $email, $mobile, $location, $profile_image, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, mobile=?, location=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $mobile, $location, $user_id);
}

if ($stmt->execute()) {
    // Get updated user data
    $getData = $conn->prepare("SELECT id, name, email, mobile, location, profile_image FROM users WHERE id=?");
    $getData->bind_param("i", $user_id);
    $getData->execute();
    $result = $getData->get_result();
    $userData = $result->fetch_assoc();
    $getData->close();
    
    echo json_encode([
        "status" => "success",
        "message" => "Profile updated successfully",
        "data" => $userData
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update profile"
    ]);
}

$stmt->close();
$conn->close();
?>
