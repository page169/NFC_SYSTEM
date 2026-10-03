<?php
// Start the session and include necessary files
include '../db.php';
include('includes/header.php');
$get_id = $_GET['edit']; // Get the student ID to edit

// Fetch the current student record to pre-fill the form fields
$query = "SELECT * FROM students WHERE id='$get_id'";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

if (isset($_POST['update_student'])) {
    // Sanitize form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $parent_name = mysqli_real_escape_string($conn, $_POST['parent_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $uid = mysqli_real_escape_string($conn, $_POST['uid']);
    $program_id = mysqli_real_escape_string($conn, $_POST['program_id']); // New program_id field

    // Check if the new student_id or uid already exists in the database (excluding the current student)
    $duplicate_check_query = "SELECT * FROM students WHERE (student_id = '$student_id' OR uid = '$uid') AND id != '$get_id'";
    $duplicate_check_result = mysqli_query($conn, $duplicate_check_query);

    if (mysqli_num_rows($duplicate_check_result) > 0) {
        // Display an error message if a duplicate is found
        echo "<script>alert('A student with this UID or Student ID already exists.');</script>";
    } else {
        // Check if UID has been changed
        // Check if UID has been changed
        if ($uid !== $student['uid']) {
            // Step 1: Set related logs entries to NULL
            $set_null_query = "UPDATE logs SET student_uid = NULL WHERE student_uid = '{$student['uid']}'";
            if (!mysqli_query($conn, $set_null_query)) {
                die("Error setting logs to NULL: " . mysqli_error($conn));
            }

            // Step 2: Update the UID in the students table
            $update_student_query = "UPDATE students SET uid = '$uid' WHERE id = '$get_id'";
            if (!mysqli_query($conn, $update_student_query)) {
                die("Error updating student UID: " . mysqli_error($conn));
            }

            // Step 3: Restore student_uid in logs with the new UID
            $restore_logs_query = "UPDATE logs SET student_uid = '$uid' WHERE student_uid IS NULL";
            if (!mysqli_query($conn, $restore_logs_query)) {
                die("Error restoring logs: " . mysqli_error($conn));
            }
        }


        // Default to the current picture URL if no new image uploaded
        $picture_url = $student['picture_url'];

        // Check if a new picture is uploaded
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
            $file_tmp = $_FILES['picture']['tmp_name'];
            $file_name = $_FILES['picture']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Validate file extension
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_extensions)) {
                // Set a unique file name
                $new_file_name = uniqid('student_') . '.' . $file_ext;
                $upload_dir = 'admin/uploads/';
                $upload_file = $upload_dir . $new_file_name;

                // Check if upload directory exists and create if it doesn't
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
                }

                // Move the uploaded file to the server
                if (move_uploaded_file($file_tmp, $upload_file)) {
                    // Update the picture_url with the new file name
                    $picture_url = $new_file_name;
                } else {
                    echo "<script>alert('Error uploading the file.');</script>";
                }
            } else {
                echo "<script>alert('Invalid file type. Only JPG, PNG, and GIF are allowed.');</script>";
            }
        }

        // Update the student record in the database
        $update_query = "UPDATE students SET name='$name', parent_name='$parent_name', phone='$phone', student_id='$student_id', uid='$uid', picture_url='$picture_url', program_id='$program_id' WHERE id='$get_id'";

        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Student record successfully updated');</script>";
            echo "<script type='text/javascript'> document.location = 'student.php'; </script>";
        } else {
            die("Error updating student: " . mysqli_error($conn));
        }
    }
}
?>

<body>
    <?php include('includes/navbar.php') ?>
    <?php include('includes/right_sidebar.php') ?>
    <?php include('includes/left_sidebar.php') ?>

    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10 main-body">
            <div class="min-height-200px">
                <div class="page-header">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="title">
                                <h4>Student Portal</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Edit Student</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="pd-20 card-box mb-30">
                    <div class="clearfix">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Edit Student</h4>
                        </div>
                    </div>
                    <div class="wizard-content">
                        <form method="post" action="" enctype="multipart/form-data">
                            <section>
                                <?php
                                $query = mysqli_query($conn, "SELECT * FROM students WHERE id = '$get_id'") or die(mysqli_error($conn));
                                $row = mysqli_fetch_array($query);
                                ?>

                                <div class="row">
                                    <!-- Full Name -->
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label>Full Name:</label>
                                            <input name="name" type="text" class="form-control" required value="<?php echo $row['name']; ?>">
                                        </div>
                                    </div>

                                    <!-- Student ID -->
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label>Student ID:</label>
                                            <input name="student_id" type="text" class="form-control" required value="<?php echo $row['student_id']; ?>">
                                        </div>
                                    </div>

                                    <!-- UID -->
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label>UID:</label>
                                            <input name="uid" type="text" class="form-control" required value="<?php echo $row['uid']; ?>">
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Parent/Guardian :</label>
                                            <input name="parent_name" type="text" class="form-control" required value="<?php echo $row['parent_name']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Phone :</label>
                                            <input name="phone" type="tel" class="form-control" required value="<?php echo $row['phone']; ?>">
                                        </div>
                                    </div>
                                </div>


                                <!-- Program Dropdown and Student Picture Side by Side (Student Picture on the left and Program Dropdown on the right) -->
                                <div class="row">
                                    <!-- Student Picture (Left Side) -->
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Student Picture:</label>
                                            <input type="file" name="picture" class="form-control" accept="image/*">
                                            <?php if (!empty($row['picture_url'])): ?>
                                                <div class="mt-2">
                                                    <label>Current Picture:</label>
                                                    <img src="admin/uploads/<?php echo $row['picture_url']; ?>" class="border-radius-100 box-shadow" style="width: 100px; height: 100px; object-fit: cover;" alt="Student Picture">
                                                </div>
                                            <?php else: ?>
                                                <p>No picture available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Select Program (Right Side) -->
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Select Program:</label>
                                            <select name="program_id" class="custom-select form-control" required>
                                                <option value="">Select Program</option>
                                                <?php
                                                $programs = mysqli_query($conn, "SELECT * FROM programs") or die(mysqli_error($conn));
                                                while ($program = mysqli_fetch_assoc($programs)) {
                                                    $selected = ($row['program_id'] == $program['id']) ? 'selected' : '';
                                                    echo "<option value='{$program['id']}' {$selected}>{$program['program_code']} - {$program['program_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>



                                <!-- Update Button -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group text-center">
                                            <button class="btn btn-primary" name="update_student">Update Student</button>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </form>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/scripts.php') ?>
</body>

</html>