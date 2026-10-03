<?php
// Start output buffering to prevent the "headers already sent" error
ob_start();

// Start the session and include necessary files
include '../db.php';
include('includes/header.php');

// Handle the file upload for importing student data
if (isset($_POST['import'])) {
    $filename = $_FILES['csv_file']['tmp_name'];

    if ($_FILES['csv_file']['size'] > 0) {
        $file = fopen($filename, 'r');

        // Skip the first line if it has column headings
        fgetcsv($file);

        // Use prepared statements for better security
        $checkStudentIdStmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
        $checkNameStmt = $conn->prepare("SELECT name FROM students WHERE name = ?");
        $getProgramStmt = $conn->prepare("SELECT id FROM programs WHERE program_code = ?");
        $insertStmt = $conn->prepare("INSERT INTO students (name, student_id, uid, picture_url, program_id) VALUES (?, ?, ?, ?, ?)");

        while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {
            // Sanitize the CSV data (Ensure correct order of data)
            $name = htmlspecialchars($data[0]); // name
            $student_id = htmlspecialchars($data[1]); // student_id
            $uid = htmlspecialchars($data[2]); // uid
            $picture_url = htmlspecialchars($data[3]); // picture_url
            $program_code = htmlspecialchars($data[4]); // program_code (to map to program_id)

            // Check for duplicate student_id
            $checkStudentIdStmt->bind_param("s", $student_id);
            $checkStudentIdStmt->execute();
            $checkStudentIdStmt->store_result();

            if ($checkStudentIdStmt->num_rows > 0) {
                // Duplicate student_id found
                echo "<script>console.log('Duplicate student_id found: $student_id. Skipping this entry.');</script>";
                continue;
            }

            // Check for duplicate name
            $checkNameStmt->bind_param("s", $name);
            $checkNameStmt->execute();
            $checkNameStmt->store_result();

            if ($checkNameStmt->num_rows > 0) {
                // Duplicate name found
                echo "<script>console.log('Duplicate name found: $name. Skipping this entry.');</script>";
                continue;
            }

            // Get program_id from the program_code
            $getProgramStmt->bind_param("s", $program_code);
            $getProgramStmt->execute();
            $getProgramStmt->store_result();

            if ($getProgramStmt->num_rows > 0) {
                $getProgramStmt->bind_result($program_id);
                $getProgramStmt->fetch();
            } else {
                // If program_code doesn't exist in the programs table, skip this entry
                echo "<script>console.log('Program code $program_code not found. Skipping this entry.');</script>";
                continue;
            }

            // Insert new student record with program_id
            $insertStmt->bind_param("ssssi", $name, $student_id, $uid, $picture_url, $program_id);
            if (!$insertStmt->execute()) {
                echo "<script>alert('Error importing data: " . $insertStmt->error . "');</script>";
            }
        }

        fclose($file);
        echo "<script>alert('Student data imported successfully');</script>";
        echo "<script type='text/javascript'> document.location = 'student.php'; </script>";
    } else {
        echo "<script>alert('Please select a valid CSV file');</script>";
    }
}

// Handle export of student data
if (isset($_POST['export'])) {
    ob_end_clean();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=students.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Name', 'Student ID', 'UID', 'Picture URL', 'Program Code'));

    $query = $conn->query("SELECT students.name, students.student_id, students.uid, students.picture_url, programs.program_code FROM students JOIN programs ON students.program_id = programs.id");
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            fputcsv($output, $row);
        }
    } else {
        echo "<script>alert('Error exporting data');</script>";
    }

    fclose($output);
    exit();
}
?>



<!-- Your HTML content -->
<?php include('includes/navbar.php'); ?>
<?php include('includes/right_sidebar.php'); ?>
<?php include('includes/left_sidebar.php'); ?>

<div class="mobile-menu-overlay"></div>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>Import/Export Student Data</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Import/Export Module</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Import Section -->
                <div class="col-lg-6 col-md-6 col-sm-12 mb-30">
                    <div class="card-box pd-30 pt-10 height-100-p">
                        <h2 class="mb-30 h4">Import Student Data</h2>
                        <form method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="csv_file">Select CSV File</label>
                                <input type="file" name="csv_file" class="form-control" required>
                            </div>
                            <div class="text-right">
                                <input class="btn btn-primary" type="submit" value="IMPORT" name="import">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Export Section -->
                <div class="col-lg-6 col-md-6 col-sm-12 mb-30">
                    <div class="card-box pd-30 pt-10 height-100-p">
                        <h2 class="mb-30 h4">Export Student Data</h2>
                        <form method="post">
                            <div class="text-right">
                                <input class="btn btn-success" type="submit" value="EXPORT" name="export">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
		</div>
<!-- js -->
<?php include('includes/footer.php'); ?>
<?php include('includes/scripts.php'); ?>
</body>
</html>
