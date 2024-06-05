<?php
include __DIR__ . "/database.php";

session_start();

// Ensure $mysqli is defined
if (!isset($mysqli)) {
    die("Database connection error.");
}

if (isset($_SESSION["user_id"])) {
    $sql = "SELECT * FROM user WHERE id = {$_SESSION["user_id"]}";
    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();
}

// Fetch total number of courses
$totalCoursesQuery = "SELECT COUNT(*) as total FROM courses";
$totalCoursesResult = $mysqli->query($totalCoursesQuery);
$totalCoursesData = $totalCoursesResult->fetch_assoc();
$totalCourses = $totalCoursesData['total'];

// Fetch total number of subjects
$totalSubjectsQuery = "SELECT COUNT(*) as total FROM subjects";
$totalSubjectsResult = $mysqli->query($totalSubjectsQuery);
$totalSubjectsData = $totalSubjectsResult->fetch_assoc();
$totalSubjects = $totalSubjectsData['total'];

// Handle adding a new course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_course"])) {
    $course_id = $_POST["course_id"];
    $course_name = $_POST["course_name"];
    $insert_sql = "INSERT INTO courses (id, name, description) VALUES ('$course_id', '$course_name', '')";
    $mysqli->query($insert_sql);

    // Fetch total number of courses after adding new course
    $totalCoursesQuery = "SELECT COUNT(*) as total FROM courses";
    $totalCoursesResult = $mysqli->query($totalCoursesQuery);
    $totalCoursesData = $totalCoursesResult->fetch_assoc();
    $totalCourses = $totalCoursesData['total'];
}

// Handle deleting a course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_course"])) {
    $course_id = $_POST["course_id"];
    $delete_sql = "DELETE FROM courses WHERE id = '$course_id'";
    $mysqli->query($delete_sql);

    // Fetch total number of courses after deleting course
    $totalCoursesQuery = "SELECT COUNT(*) as total FROM courses";
    $totalCoursesResult = $mysqli->query($totalCoursesQuery);
    $totalCoursesData = $totalCoursesResult->fetch_assoc();
    $totalCourses = $totalCoursesData['total'];
}

// Handle adding a new subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_subject"])) {
    $subject_id = $_POST["subject_id"];
    $subject_name = $_POST["subject_name"];
    $insert_sql = "INSERT INTO subjects (id, name) VALUES ('$subject_id', '$subject_name')";
    $mysqli->query($insert_sql);

    // Fetch total number of subjects after adding new subject
    $totalSubjectsQuery = "SELECT COUNT(*) as total FROM subjects";
    $totalSubjectsResult = $mysqli->query($totalSubjectsQuery);
    $totalSubjectsData = $totalSubjectsResult->fetch_assoc();
    $totalSubjects = $totalSubjectsData['total'];
}

// Handle deleting a subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_subject"])) {
    $subject_id = $_POST["subject_id"];
    $delete_sql = "DELETE FROM subjects WHERE id = '$subject_id'";
    $mysqli->query($delete_sql);

    // Fetch total number of subjects after deleting subject
    $totalSubjectsQuery = "SELECT COUNT(*) as total FROM subjects";
    $totalSubjectsResult = $mysqli->query($totalSubjectsQuery);
    $totalSubjectsData = $totalSubjectsResult->fetch_assoc();
    $totalSubjects = $totalSubjectsData['total'];
}

// Fetch and display saved courses
$courses_query = "SELECT * FROM courses";
$courses_result = $mysqli->query($courses_query);
$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch and display saved subjects
$subjects_query = "SELECT * FROM subjects";
$subjects_result = $mysqli->query($subjects_query);
$subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
}

// Fetch and display saved students
$students_query = "SELECT * FROM students";
$students_result = $mysqli->query($students_query);
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// Function to display a message for adding a new student as an alert
function displayAddStudentMessage($message, $type = 'success') {
    echo "<div class='alert alert-$type' role='alert'>$message</div>";
    echo "<a href='index.php' class='btn btn-primary'>Add Another Student</a>";
}

// Handle adding a new student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_student"])) {
    // Retrieve form data
    $student_name = $_POST["student_name"];
    $student_email = $_POST["student_email"];
    // Insert student data into the students table
    $insert_student_sql = "INSERT INTO students (name, email) 
                           VALUES ('$student_name', '$student_email')";

    // Execute the SQL query
    if ($mysqli->query($insert_student_sql) === TRUE) {
        // Student added successfully
        $addStudentMessage = "Student added successfully: $student_name";
        displayAddStudentMessage($addStudentMessage, 'success');
    } else {
        // Error occurred while adding student
        $addStudentMessage = "Error adding student: " . $mysqli->error;
        displayAddStudentMessage($addStudentMessage, 'danger');
    }

    // Send JSON response
    echo json_encode($addStudentMessage);
    exit; // Stop further execution
}
// HTML code for displaying saved students
function displayStudents($students) {
    $counter = 1;
    foreach ($students as $student) {
        echo "<div>";
        echo "<label><b>NEW STUDENT" . $counter . "</b></label><br>";
        echo "<label><b>Name:</b></label> " . htmlspecialchars($student["name"]) . "<br>";
        echo "<label><b>Email:</b></label> " . htmlspecialchars($student["email"]) . "<br>";
        // Delete button for each student
        echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' onsubmit='return confirm(\"Are you sure you want to delete Student #" . $counter . "?\");'>";
        echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($student["student_id"]) . "'>";
        echo "<button type='submit' name='delete_student' style='font-size: 12px;'>Delete</button>";
        echo "</form>";
        echo "</div>";
        $counter++;
    }
}
// Handle deleting a student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_student"])) {
    $student_id = $_POST["student_id"];
    $delete_sql = "DELETE FROM students WHERE student_id = '$student_id'";
    if ($mysqli->query($delete_sql) === TRUE) {
        // Student deleted successfully
        $delete_message = "Student deleted successfully.";
    } else {
        // Error occurred while deleting student
        $delete_message = "Error: " . $mysqli->error;
    }
}
// Check if subjects data is received
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["subjects"])) {
    $enrolledSubjects = json_decode($_POST["subjects"]);

    // Prepare and execute SQL statement to insert enrolled subjects into the database
    $success = true;
    foreach ($enrolledSubjects as $subjectID) {
        $insert_sql = "INSERT INTO enrolled_subjects (subject_id) VALUES ('$subjectID')";
        if (!$mysqli->query($insert_sql)) {
            $success = false;
            break;
        }
    }

    // Prepare response
    $response = array("success" => $success);
    echo json_encode($response);
} 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(to bottom right, #f0f0f0, #59d98e); 
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      width: 400px;
      background-color: #ffffff;
      border-radius: 20px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      padding: 40px;
      text-align: center;
    }

    h1 {
      color: #333333;
      margin-bottom: 30px;
      font-weight: 600;
    }

    p {
      font-size: 16px;
      margin-bottom: 20px;
      
    }

    a {
      color: #4CAF50;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    a:hover {
      color: #45a049;
    }

        /* Style for tab menu */
    .tab-menu {
      list-style-type: none;
      padding: 0;
      margin: 0;
      position: absolute;
      top: 20px;
      right: 20px;
      display: flex; /* Display tabs horizontally */
    }

    .tab-menu li {
      margin-right: 20px;
      position: relative;
    }

    .tab-menu li a {
      text-decoration: none;
      color: white;
      font-weight: bold;
      padding: 10px;
      border-radius: 5px;
      background-color: #59d98e; /* Green color */
      position: relative;
    }

    .tab-menu li a:hover {
      background-color: #4CAF50; /* Darker green color on hover */
    }
        /* Style for submenu */
        .submenu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1001; /* Ensure it's above other content */
        }

        .submenu li {
            margin-right: 0;
        }

        .submenu li a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
        }

        .submenu li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Style for modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* CSS for about panel */
        .about-section {
            display: flex;
            justify-content: center;
        }

        .about-image {
            text-align: center;
            margin-bottom: 20px;
        }

        .about-image img {
            width: 100px; /* Adjust the size as needed */
            height: auto;
            border-radius: 10px;
        }

        .image-description {
            margin-top: 5px;
            position: relative;
            font-size: 1;
            color: black;
        }

            /* Logout link */
    .logout-link {
      text-decoration: none;
      color: white;
      font-weight: bold;
      padding: 10px;
      border-radius: 5px;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .logout-link:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }
        /* Panel for About */
        #aboutModal .modal-content {
            background-color: transparent; /* White with 10% opacity */
            width: 100%;
            max-width: 1500px; /* Adjust the max-width as needed */
        }

        /* Panel for Course */
        #coursePanel .modal-content {
            background-color: rgba(0, 0, 0, 0.5); /* White with 10% opacity */
            width:fit-content;
        }

        /* Panel for Subject */
        #subjectPanel .modal-content {
            background-color: transparent; /* White with 10% opacity */
            width:fit-content;
        }

        /* Panel for Students */
        #studentsPanel .modal-content {
            background-color: transparent; /* White with 10% opacity */
            width:fit-content;
        }

        /* Responsive layout - makes the menu and the panel stack on top of each other instead of next to each other on smaller screens (600px wide or less) */
        @media screen and (max-width: 600px) {
            .tab-menu, .modal-content {
                flex-direction: column;
            }
        }
        </style>
</head>
<body>

<div class="container">
    <h1>Welcome User!</h1>
    <!-- Tab menu -->
    <?php if (isset($user)): ?>
          <!-- Tab menu -->
      <ul class="tab-menu">
        <li><a href="#" id="setup-menu">SETUP</a>
          <ul class="submenu">
            <li><a href="#" id="course-submenu">Course</a></li>
            <li><a href="#" id="subject-submenu">Subject</a></li>
            <li><a href="#" id="students-submenu">Students</a></li>
          </ul>
        </li>
        <li><a href="#" id="transaction-menu">Transaction</a>
          <ul class="submenu">
            <li><a href="#" id="enrollment-submenu">Enrollment</a></li>
          </ul>
        </li>
        <li><a href="#" id="reports-menu">Reports</a>
          <ul class="submenu">
            <li><a href="#" id="assessment-submenu">Assessment</a></li>
          </ul>
        </li>
        <li><a href="#" id="about-menu">ABOUT</a></li>
        <li><a href="logout.php" class="logout-link">Logout</a></li>
      </ul>
      <!-- End of Tab menu -->
    <?php endif; ?>

    <h1>Home</h1>

    <?php if (isset($user)): ?>
        <p>Hello <?= htmlspecialchars($user["username"]) ?></p>
        
    <?php else: ?>
        <p><a href="login.php">Log in</a> or <a href="signup.php">sign up</a></p>
    <?php endif; ?>

    <!-- Modal for About -->
    <div id="aboutModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>About Page</h2>
        <!-- Description and images -->
        <div class="about-section">
            <!-- Image 1 with description -->
            <div class="about-image">
                <img src="image/lovely.png" alt="Image 1">
                <div class="image-description">
                    <p>Lovely Joyce Languian</p>
                    <p>Tumauini Isabela</p>
                    <p>BSIT 2B</p>
                </div>
            </div>
            <!-- Image 2 with description -->
            <div class="about-image">
                <img src="image/kristine.png" alt="Image 2">
                <div class="image-description">
                    <p>Kristne Mae Baquiran</p>
                    <p>Cabagan Isabela</p>
                    <p>BSIT 2B</p>
                </div>
            </div>
            <!-- Image 3 with description -->
            <div class="about-image">
                <img src="image/joyce.png" alt="Image 3">
                <div class="image-description">
                    <p>Joyce Baquiran</p>
                    <p>Cabagan Isabela</p>
                    <p>BSIT 2B</p>
                </div>
            </div>
        </div>
        <!-- Logout link -->
        <p style="position: absolute; bottom: 20px; left: 20px;">
            <a href="logout.php" style="color: red;">Log out</a>
        </p>
    </div>
</div>

     <!-- Panel for Course -->
     <div id="coursePanel" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Course</h2>  
        <!-- Display saved courses -->
        <div id="saved-courses">
        <?php foreach ($courses as $index => $course): ?>
            <div>
                <label><b>Course <?php echo $index + 1; ?></b></label><br>    
                <label>Course Code:</label> <?php echo htmlspecialchars($course['id']); ?><br>
                <label>Course Name:</label> <?php echo htmlspecialchars($course['name']); ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                    <button type="submit" name="delete_course" style="font-size: 12px;">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
        <!-- Add course form -->
        <button id="add-course-btn">Add</button>
        <form id="add-course-form" style="display: none;" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <input type="text" name="course_id" placeholder="Course ID" required><br>
            <input type="text" name="course_name" placeholder="Course Name" required><br>
            <button type="submit" name="add_course">Save</button>
        </form>
    </div>
</div>
    <!-- Panel for Subject -->
    <div id="subjectPanel" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Subject</h2>  
        <!-- Display saved subjects -->
        <div id="saved-subjects">
            <?php foreach ($subjects as $index => $subject): ?>
                <div>
                    <label><b>Subject <?php echo $index + 1; ?></b></label><br>
                    <label>Subject Code:</label> <?php echo htmlspecialchars($subject['id']); ?><br>
                    <label>Subject Name:</label> <?php echo htmlspecialchars($subject['name']); ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subject['id']); ?>">
                        <button type="submit" name="delete_subject" style="font-size: 12px;">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Add subject form -->
        <button id="add-subject-btn">Add Subject</button>
        <form id="add-subject-form" style="display: none;" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="text" name="subject_id" placeholder="Subject ID" required><br>
            <input type="text" name="subject_name" placeholder="Subject Name" required><br>
            <button type="submit" name="add_subject">Save</button>
        </form>
    </div>
</div>

    <!-- Panel for Students -->
    <div id="studentsPanel" class="modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <!-- Panel content -->
    <div class="modal-content" style="background-color: #fefefe;">
        <span class="close">&times;</span>
        <h2>Students Information</h2>
        <!-- Display existing user information -->
        <?php if (isset($user)): ?>
            <div>
                <h4>User Information<h4>
                <label><b>Name:</b></label> <?= htmlspecialchars($user["username"]) ?><br>
                <label><b>Email:</b></label> <?= htmlspecialchars($user["email"]) ?><br>
            </div>
        <?php endif; ?>

        <?php displayStudents($students); ?>
        <!-- Add student form -->
        <button id="add-student-btn">Add Student</button>
        <form id="add-student-form" style="display: none;" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="text" name="student_name" placeholder="Student Name" required><br>
            <input type="email" name="student_email" placeholder="Email" required><br>
            <button type="submit" name="add_student">Save</button>
        </form>
    </div>
</div>

<!-- Panel for Enrollment -->
<div id="enrollmentPanel" class="modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <!-- Panel content -->
    <div class="modal-content" style="background-color: #fefefe;">
        <span class="close">&times;</span>
        <h2 style="color: black;">Enrollment</h2>
        <h4 style="color: black;">2nd Semester SY 2023 - 2024</h4>
        
        <!-- Add a checklist alongside the table -->
        <div style="overflow-x:auto;">
            <!-- Table for enrollment -->
            <table id="enrollment-table">
                <thead>
                    <tr>
                        <th>Enroll</th>
                        <th>Subject Code</th>
                        <th>Subject Description</th>
                        <th>Units</th>
                    </tr>
                </thead>
                <tbody>
                     <!-- Sample row, add more rows dynamically -->
                    <tr>
                        <td><input type="checkbox" name="enroll_1"></td>
                        <td>IT 221</td>
                        <td>Information Management</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="enroll_2"></td>
                        <td>IT 222</td>
                        <td>Networking 1</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="enroll_3"></td>
                        <td>IT GE ELEC 4</td>
                        <td>The Entrepreneurial Mind</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="enroll_4"></td>
                        <td>IT 223</td>
                        <td>Quantitative Methods (including Modeling and Simulation)</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="enroll_5"></td>
                        <td>IT 224</td>
                        <td>Integrative Programming and Technologies</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="enroll_6"></td>
                        <td>GEC 9</td>
                        <td>The Life and Works of Rizal</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="enroll_7"></td>
                        <td>IT 225</td>
                        <td>Accounting for Information Technology</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="enroll_8"></td>
                        <td>IT APPDEV 1</td>
                        <td>Fundamentals of Mobile Technology</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="enroll_9"></td>
                        <td>PE 4</td>
                        <td>Physical Activity Towards Health and Fitness IV</td>
                        <td>2</td>
                    </tr>
                    <!-- Display subjects from enrolled_subject table if available -->
                    <?php if (!empty($enrolled_subjects)): ?>
                        <?php foreach ($enrolled_subjects as $subject): ?>
                            <tr>
                                <td><input type="checkbox" name="enroll_subject[]" value="<?php echo htmlspecialchars($subject['id']); ?>"></td>
                                <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                <td><?php echo htmlspecialchars($subject['subject_description']); ?></td>
                                <td><?php echo htmlspecialchars($subject['units']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Enroll button -->
        <button id="enroll-btn">Enroll</button>
        <!-- Message for successful enrollment -->
        <p id="enrollment-message" style="color: green; display: none;">Enrollment successful!</p>

    </div>
</div>

      
  <!-- Assessment panel content -->
<div id="assessmentPanel" class="modal">
    <!-- Panel content -->
    <div class="modal-content">

    <span class="close">&times;</span>
        <h2 style="color: black;">Enrolled Subject</h2>
        <h4 style="color: black;">2nd Semester SY 2023 - 2024</h4>
        
            <!-- Table for enrollment -->
            <table id="enrollment-table">
                
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Description</th>
                        <th>Units</th>
                    </tr>
                <tbody>
                     <!-- Sample row, add more rows dynamically -->
                     <tr>
                        <td>IT 221</td>
                        <td>Information Management</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        
                        <td>GEC 9</td>
                        <td>The Life and Works of Rizal</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>IT 222</td>
                        <td>Networking 1</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>IT GE ELEC 4</td>
                        <td>The Entrepreneurial Mind</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>IT 223</td>
                        <td>Quantitative Methods (including Modeling and Simulation)</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>IT 224</td>
                        <td>Integrative Programming and Technologies</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>IT 225</td>
                        <td>Accounting for Information Technology</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>IT APPDEV 1</td>
                        <td>Fundamentals of Mobile Technology</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>PE 4</td>
                        <td>Physical Activity Towards Health and Fitness IV</td>
                        <td>2</td>
                    </tr>
                </tbody>
            </table>
            <button id="print-assessment-btn">Print</button>
        </div>

    <!-- JavaScript to toggle submenu and modal -->
    <script>
        // Function to toggle submenu
function toggleSubMenu(index) {
    var submenus = document.querySelectorAll('.submenu');
    submenus.forEach(function(submenu, i) {
        submenu.style.display = (i === index) ? 'block' : 'none';
    });
}
 // Event listener for Enrollment Submenu
 document.getElementById('enrollment-submenu').addEventListener('click', function() {
        document.getElementById('enrollmentPanel').style.display = 'block';
    });

    
// Event listeners for submenu toggling
document.getElementById('setup-menu').addEventListener('click', function() {
    toggleSubMenu(0);
});

document.getElementById('transaction-menu').addEventListener('click', function() {
    toggleSubMenu(1);
});

document.getElementById('reports-menu').addEventListener('click', function() {
    toggleSubMenu(2);
});

// Event listener to display the About modal
document.getElementById('about-menu').addEventListener('click', function() {
    var modal = document.getElementById('aboutModal');
    modal.style.display = 'block';
});

// Event listeners to display different panels
document.getElementById('course-submenu').addEventListener('click', function() {
    document.getElementById('coursePanel').style.display = 'block';
});

document.getElementById('subject-submenu').addEventListener('click', function() {
    document.getElementById('subjectPanel').style.display = 'block';
});

document.getElementById('students-submenu').addEventListener('click', function() {
    document.getElementById('studentsPanel').style.display = 'block';
});


document.getElementById('assessment-submenu').addEventListener('click', function() {
    document.getElementById('assessmentPanel').style.display = 'block';
});
// Event listener for enroll button in the Enrollment panel
document.getElementById('enroll-btn').addEventListener('click', function() {
    handleEnrollment();
});
// Event listener for print button in the Assessment panel
document.getElementById('print-assessment-btn').addEventListener('click', function() {
    printAssessment();
});
// Function to handle enrollment
function handleEnrollment() {
    // Get the checked subjects
    var checkedSubjects = document.querySelectorAll('input[name="enroll_subject[]"]:checked');

    // Prepare an array to store the subject IDs
    var subjectIDs = [];
    checkedSubjects.forEach(function(subject) {
        subjectIDs.push(subject.value);
    });

    // Send the enrolled subjects to the server
    fetch('enroll_subject.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ subjects: subjectIDs })
    })
    .then(response => response.json())
    .then(data => {
        // Handle the server's response
        if (data.success) {
            // Display a success message
            var enrollmentMessage = document.getElementById('enrollment-message');
            enrollmentMessage.textContent = "You are now enrolled!";
            enrollmentMessage.style.display = 'block';
            // Optionally, you can update the UI or display a success message
        } else {
            // Handle errors
            console.error('Error:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Function to print the assessment content
function printAssessment() {
    // Create a new window for the printable content
    var printWindow = window.open('', '_blank');
    
    // Construct the printable content
    var printableContent = '<h2 style="color: black;">Assessment</h2><h4 style="color: black;">2nd Semester SY 2023 - 2024</h4>';
    printableContent += '<table border="1">';
    printableContent += '<thead><tr><th>Subject Code</th><th>Subject Description</th><th>Units</th></tr></thead>';
    printableContent += '<tbody>';

    // Loop through the assessment table and add content to the printable version
    var assessmentRows = document.getElementById('assessment-body').querySelectorAll('tr');
    assessmentRows.forEach(function(row) {
        printableContent += '<tr>';
        var columns = row.querySelectorAll('td');
        columns.forEach(function(column) {
            printableContent += '<td>' + column.textContent + '</td>';
        });
        printableContent += '</tr>';
    });

    printableContent += '</tbody></table>';

    // Write the content to the new window and print it
    printWindow.document.write('<html><head><title>Assessment</title></head><body>' + printableContent + '</body></html>');
    printWindow.document.close(); // Close writing to the document
    printWindow.print(); // Print the document
}

// Close modals when close buttons are clicked
var closeButtons = document.querySelectorAll('.close');
closeButtons.forEach(function(closeButton) {
    closeButton.addEventListener('click', function() {
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            modal.style.display = 'none';
        });
    });
});

// Close modals when clicking outside of them
window.addEventListener('click', function(event) {
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});

// Function to toggle form visibility
function toggleFormVisibility(formId) {
    var form = document.getElementById(formId);
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
// Function to toggle form visibility
function toggleFormVisibility(formId) {
    var form = document.getElementById(formId);
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

// Event listeners to toggle add course, add subject, and add student forms
document.getElementById('add-course-btn').addEventListener('click', function() {
    toggleFormVisibility('add-course-form');
});

document.getElementById('add-subject-btn').addEventListener('click', function() {
    toggleFormVisibility('add-subject-form');
});

// Event listener to toggle add student form visibility
document.getElementById('add-student-btn').addEventListener('click', function() {
    toggleFormVisibility('add-student-form');
});
// Event listener for enroll button
document.getElementById('enroll-btn').addEventListener('click', function() {
    handleEnrollment();
});

</script>


</body>
</html>


