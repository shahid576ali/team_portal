<?php
include 'php/dbconn.php'; // Include database connection file

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$username = $input['username'];
$password = $input['password'];

// Query database for user
$stmt = $conn->prepare("SELECT * FROM `user` WHERE `Email` = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($password === $user['Password']) {
    // Generate session token (simplified)
    $token = bin2hex(random_bytes(16));

    // Store token in the database or session storage
    // Example: $stmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
    //          $stmt->bind_param("si", $token, $user['id']);
    //          $stmt->execute();

    echo json_encode(['token' => $token, 'message' => 'Login successful']);
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => $password]); 
}
?>
