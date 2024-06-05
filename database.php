<?php

$host = "localhost";
$dbname = "norjay_db";   
$username = "root";
$password = "";

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;

$mysqli = require __DIR__ . "/database.php";

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the raw POST data
    $postData = file_get_contents('php://input');

    // Decode the JSON data
    $courseData = json_decode($postData, true);

    // Validate the data (you may want to add more validation)
    if (!empty($courseData) && is_array($courseData)) {
        // Prepare a statement to insert the courses into the database
        $stmt = $mysqli->prepare("INSERT INTO courses (id, name) VALUES (?, ?)");

        // Bind parameters
        $stmt->bind_param("ss", $courseId, $courseName);

        // Insert each course into the database
        foreach ($courseData as $course) {
            // Assign values to parameters
            $courseId = $course['id'];
            $courseName = $course['name'];

            // Execute the statement
            $stmt->execute();
        }

        // Close the statement
        $stmt->close();

        // Send a response back
        http_response_code(200);
        echo json_encode(array("message" => "Courses saved successfully"));
    } else {
        // Send a response back indicating a bad request
        http_response_code(400);
        echo json_encode(array("message" => "Bad request"));
    }
} else {
    // Send a response back indicating a method not allowed
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}

// Close the database connection
$mysqli->close();
?>
