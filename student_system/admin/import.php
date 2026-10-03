<?php

ob_start();

include '../db.php';
include('includes/header.php');

if (isset($_POST['import'])) {

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['size'] <= 0) {
        echo "<script>alert('Please select a valid CSV file');</script>";
    } else {

        $filename = $_FILES['csv_file']['tmp_name'];
        $file = fopen($filename, 'r');

        fgetcsv($file);

        $checkStudentIdStmt = $conn->prepare("
            SELECT student_id
            FROM students
            WHERE student_id = ?
        ");

        $checkNameStmt = $conn->prepare("
            SELECT name
            FROM students
            WHERE name = ?
        ");

        $getProgramStmt = $conn->prepare("
            SELECT id
            FROM programs
            WHERE program_code = ?
        ");

        $insertStmt = $conn->prepare("
            INSERT INTO students
            (
                name,
                parent_name,
                phone,
                student_id,
                uid,
                program_id
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        while (($data = fgetcsv($file, 1000, ',')) !== false) {

            if (count($data) < 6) {
                continue;
            }

            $name = trim($data[0]);
            $parent_name = trim($data[1]);
            $phone = trim($data[2]);
            $student_id = trim($data[3]);
            $uid = trim($data[4]);
            $program_code = trim($data[5]);

            $checkStudentIdStmt->bind_param("s", $student_id);
            $checkStudentIdStmt->execute();
            $checkStudentIdStmt->store_result();

            if ($checkStudentIdStmt->num_rows > 0) {
                continue;
            }

            $checkNameStmt->bind_param("s", $name);
            $checkNameStmt->execute();
            $checkNameStmt->store_result();

            if ($checkNameStmt->num_rows > 0) {
                continue;
            }

            $getProgramStmt->bind_param("s", $program_code);
            $getProgramStmt->execute();
            $getProgramStmt->store_result();

            if ($getProgramStmt->num_rows == 0) {
                continue;
            }

            $getProgramStmt->bind_result($program_id);
            $getProgramStmt->fetch();

            $insertStmt->bind_param(
                "sssssi",
                $name,
                $parent_name,
                $phone,
                $student_id,
                $uid,
                $program_id
            );

            if (!$insertStmt->execute()) {
                echo "<script>alert('Error importing data: " . addslashes($insertStmt->error) . "');</script>";
            }
        }

        fclose($file);

        echo "<script>
            alert('Student data imported successfully');
            window.location.href = 'student.php';
        </script>";

        exit;
    }
}

if (isset($_POST['export'])) {

    ob_end_clean();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=students.csv');

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'Name',
        'Parent Name',
        'Phone',
        'Student ID',
        'UID',
        'Program Code'
    ]);

    $query = $conn->query("
        SELECT
            students.name,
            students.parent_name,
            students.phone,
            students.student_id,
            students.uid,
            programs.program_code
        FROM students
        JOIN programs
            ON students.program_id = programs.id
        ORDER BY students.name ASC
    ");

    if ($query) {

        while ($row = $query->fetch_assoc()) {

            fputcsv($output, [
                $row['name'],
                $row['parent_name'],
                $row['phone'],
                $row['student_id'],
                $row['uid'],
                $row['program_code']
            ]);
        }
    }

    fclose($output);

    exit;
}
?>

<?php include('includes/navbar.php'); ?>
<?php include('includes/right_sidebar.php'); ?>
<?php include('includes/left_sidebar.php'); ?>

<div class="mobile-menu-overlay"></div>

<div class="main-container">
	<div class="pd-ltr-20 xs-pd-20-10 main-body">
		<div class="min-height-200px">

			<div class="page-header">
				<div class="row">
					<div class="col-md-6 col-sm-12">
						<div class="title">
							<h4>Import/Export Student Data</h4>
						</div>

						<nav aria-label="breadcrumb" role="navigation">
							<ol class="breadcrumb">
								<li class="breadcrumb-item">
									<a href="admin_dashboard.php">Dashboard</a>
								</li>

								<li class="breadcrumb-item active" aria-current="page">
									Import/Export Module
								</li>
							</ol>
						</nav>
					</div>
				</div>
			</div>

			<div class="row">

				<div class="col-lg-6 col-md-6 col-sm-12 mb-30">
					<div class="card-box pd-30 pt-10 height-100-p">

						<h2 class="mb-30 h4">
							Import Student Data
						</h2>

						<form method="post" enctype="multipart/form-data">

							<div class="form-group">
								<label for="csv_file">
									Select CSV File
								</label>

								<input type="file" name="csv_file" class="form-control" accept=".csv" required>
							</div>

							<div class="text-right">
								<input class="btn btn-primary" type="submit" value="IMPORT" name="import">
							</div>

						</form>

					</div>
				</div>

				<div class="col-lg-6 col-md-6 col-sm-12 mb-30">
					<div class="card-box pd-30 pt-10 height-100-p">

						<h2 class="mb-30 h4">
							Export Student Data
						</h2>

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

	<?php include('includes/footer.php'); ?>
	<?php include('includes/scripts.php'); ?>

	</body>

	</html>