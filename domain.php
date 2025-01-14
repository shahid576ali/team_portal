<?php
include("php/dbconn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['domain_name'];
    $description = $_POST['description'];

    if (isset($_FILES['domain_image']) && $_FILES['domain_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'images/domain/';
        $fileExtension = pathinfo($_FILES['domain_image']['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('domain_') . '.' . $fileExtension;
        $targetFilePath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['domain_image']['tmp_name'], $targetFilePath)) {
            $sql = "INSERT INTO `domain` (`Name`, `Description`, `Image`) VALUES ('$name', '$description', '$targetFilePath')";
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

if (isset($_GET['remove_domain'])) {
    $id = $_GET['remove_domain'];
    $sql = "SELECT `Image` FROM `domain` WHERE `Id` = '$id'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $imagePath = $row['Image'];

        $sql = "DELETE FROM `domain` WHERE `Id` = '$id'";
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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['remove_domain'])) {
    $sql = "SELECT * FROM `domain`";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch data']);
    }
}

mysqli_close($conn);
?>
