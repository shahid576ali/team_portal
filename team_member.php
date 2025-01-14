<?php
include("php/dbconn.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['member_name'];
    $description = $_POST['description'];
    $domain_id = $_POST['domain_id'];

    if (isset($_FILES['member_image']) && $_FILES['member_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'images/member/';
        $fileExtension = pathinfo($_FILES['member_image']['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('member_') . '.' . $fileExtension;
        $targetFilePath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['member_image']['tmp_name'], $targetFilePath)) {
            $sql = "INSERT INTO `members` (`Name`, `Description`, `Image`,`Team_id`) VALUES ('$name', '$description', '$targetFilePath','$domain_id')";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Domain added successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file upload']);
    }
}
// SQL query to fetch data
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];  // Casting to integer
    $sql = "SELECT * FROM `members` WHERE `Team_id` = '$id'";
    $result = mysqli_query($conn, $sql);

    // Initialize an array to store the fetched data
    $data = array();

    if ($result) {
        // Fetch rows as associative arrays and add them to the $data array
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        // Convert the $data array to JSON format
        echo json_encode($data, JSON_PRETTY_PRINT);
    } else {
        // Handle the error if the query fails
        echo json_encode(["error" => "Failed to fetch data"]);
    }
}

if (isset($_GET['name'])) {
    $name = $_GET['name'];
    $sql = "SELECT * FROM `team` WHERE `Id` = '$name'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        // Fetch only the first record
        $data = mysqli_fetch_assoc($result);
        echo json_encode($data, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["error" => "Domain not found"]);
    }
}
if (isset($_GET['remove_domain'])) {
    $id = $_GET['remove_domain'];
    $sql = "SELECT `Image` FROM `members` WHERE `Id` = '$id'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $imagePath = $row['Image'];

        $sql = "DELETE FROM `members` WHERE `Id` = '$id'";
        if (mysqli_query($conn, $sql)) {
            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the image file
            }
            echo json_encode(['success' => true, 'message' => 'Domain removed successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to remove domain']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Domain not found']);
    }
}
// Close the database connection
mysqli_close($conn);
?>