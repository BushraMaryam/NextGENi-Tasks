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
    // Retrieve comments for a specific blog (Assuming you have a blog_id parameter in the URL)
    $blogId = isset($_GET['blog_id']) ? $_GET['blog_id'] : null;

    if ($blogId !== null) {
        $query = "SELECT * FROM Comments WHERE blog_id = :blog_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid or missing blog_id parameter."));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create a new comment
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->blog_id) && !empty($data->content)) {
        $blogId = $data->blog_id;
        $content = $data->content;

        $query = "INSERT INTO Comments (blog_id, content) VALUES (:blog_id, :content)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->bindParam(':content', $content);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Comment created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to create the comment."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid or missing data. Comment creation failed."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}
?>
