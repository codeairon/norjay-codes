<?php

if (empty($_POST["name"])) {
    die("Name is required");
}

if (empty($_POST["student_id"])) {
    die("Student ID is required");
}



if (empty($_POST["dob"])) {
    die("Date of Birth is required");
}

if ( ! filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

if ( ! preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

if ( ! preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match");
}

$mysqli = require __DIR__ . "/database.php";

// Check if the student ID already exists
$check_student_id_query = "SELECT COUNT(*) as count FROM user WHERE student_id = ?";
$stmt = $mysqli->prepare($check_student_id_query);
$stmt->bind_param("s", $_POST["student_id"]);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row["count"] > 0) {
    die("Student ID already exists");
}

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$sql = "INSERT INTO user (name, student_id, date_of_birth, email, password_hash)
        VALUES ( ?, ?, ?, ?, ?)";
        
$stmt = $mysqli->stmt_init();

if ( ! $stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

$stmt->bind_param("sssss",
                  $_POST["name"],
                  $_POST["student_id"],
                  $_POST["dob"],
                  $_POST["email"],
                  $password_hash);
                  
if ($stmt->execute()) {

    header("Location: signup-success.html");
    exit;
    
} else {
    
    if ($mysqli->errno === 1062) {
        die("Email already taken");
    } else {
        die($mysqli->error . " " . $mysqli->errno);
    }
}

?>
