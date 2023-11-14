<?php

include_once './config/database.php';

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$conn = null;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve all blogs
    $query = "SELECT * FROM Blogs";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create a new blog
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->title) && !empty($data->content)) {
        $title = $data->title;
        $content = $data->content;

        $query = "INSERT INTO Blogs (title, content) VALUES (:title, :content)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Blog created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to create the blog."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid or missing data. Blog creation failed."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}
?>
