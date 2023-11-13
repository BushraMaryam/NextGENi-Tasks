<?php

include_once './config/database.php';

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Function to validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$firstName = '';
$lastName = '';
$email = '';
$password = '';
$conn = null;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Validate input fields
if (
    !empty($data->first_name) &&
    !empty($data->last_name) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    if (!isValidEmail($data->email)) {
        // If the email format is invalid, return a 400 Bad Request response with error code 101
        http_response_code(400);
        echo json_encode(array("code" => 101, "message" => "Invalid email format. Registration failed."));
    } elseif (strlen($data->password) <= 8) {
        // If the password length is less than or equal to 8 characters, return a 400 Bad Request response with error code 102
        http_response_code(400);
        echo json_encode(array("code" => 102, "message" => "Password must be more than 8 characters. Registration failed."));
    } else {
        // Check if the username already exists
        $checkQuery = "SELECT id FROM Users WHERE email = :email";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // If the username already exists, return a 400 Bad Request response with error code 103
            http_response_code(400);
            echo json_encode(array("code" => 103, "message" => "Username already exists. Registration failed."));
        } else {
            // If the username is unique and the password meets the length requirement, proceed with registration
            $table_name = 'Users';

            $insertQuery = "INSERT INTO " . $table_name . "
                            SET first_name = :firstname,
                                last_name = :lastname,
                                email = :email,
                                password = :password";

            $insertStmt = $conn->prepare($insertQuery);

            $insertStmt->bindParam(':firstname', $data->first_name);
            $insertStmt->bindParam(':lastname', $data->last_name);
            $insertStmt->bindParam(':email', $data->email);

            $password_hash = password_hash($data->password, PASSWORD_BCRYPT);

            $insertStmt->bindParam(':password', $password_hash);

            if ($insertStmt->execute()) {
                // If registration is successful, return a 200 OK response
                http_response_code(200);
                echo json_encode(array("code" => 200, "message" => "User was successfully registered."));
            } else {
                // If registration fails, return a 400 Bad Request response with error code 104
                http_response_code(400);
                echo json_encode(array("code" => 104, "message" => "Unable to register the user."));
            }
        }
    }
} else {
    // If validation fails due to missing data, return a 400 Bad Request response with error code 100
    http_response_code(400);
    echo json_encode(array("code" => 100, "message" => "Invalid or missing data. Registration failed."));
}
?>
