<?php
// Start the session and include necessary files
include '../db.php';
include('includes/header.php');

// Delete student functionality
if (isset($_GET['delete'])) {
    $delete = intval($_GET['delete']); // Use intval to sanitize the input

    // First, delete related records in the logs table
    $deleteLogsSql = "DELETE FROM logs WHERE student_uid = ?";
    $stmt = $conn->prepare($deleteLogsSql);
    $stmt->bind_param("s", $delete);
    $stmt->execute();

    // Then, delete the student record
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Student and related logs deleted successfully');</script>";
        echo "<script type='text/javascript'> document.location = 'student.php'; </script>";
    } else {
        echo "<script>alert('Failed to delete student');</script>";
    }
}

// Search functionality
$searchQuery = '';
if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $searchQuery = " WHERE s.name LIKE '%$search%' OR s.student_id LIKE '%$search%' OR s.uid LIKE '%$search%'"; // Allow search by name, student_id, or uid
}

// Fetch student data with search condition if applied
?>

<!-- Include other HTML and PHP files -->
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
							<h4>Student Portal</h4>
						</div>
						<nav aria-label="breadcrumb" role="navigation">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
								<li class="breadcrumb-item active" aria-current="page">Student Overview</li>
							</ol>
						</nav>
					</div>
					<div class="col-md-6 col-sm-12 d-flex justify-content-end">
						<form method="POST" action="student.php">
							<input type="text" name="search" class="form-control search-bar"
								placeholder="Search by Name, ID, or UID" id="search-input"
								value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>"
								style="width: 250px; height: 30px; font-size: 14px;">
						</form>
					</div>
				</div>
			</div>

			<div class="card-box mb-30">
				<div class="pd-20">
					<h2 class="text-blue h4">All Students</h2>
				</div>

				<div class="pb-20">
					<table class="data-table table stripe hover nowrap">
						<thead>
							<tr>
								<th class="table-plus">FULL NAME</th>
								<th>STUDENT ID</th>
								<th>UID</th>
								<th class="datatable-nosort">PROGRAM</th> <!-- Change from COURSE TYPE to PROGRAM -->
								<th class="datatable-nosort">ACTION</th>
							</tr>
						</thead>
						<tbody id="student-table">
							<?php
                            // Fetch student data based on the search query or no search
                            $query = "
                            SELECT s.*, p.program_code 
                            FROM students s 
                            LEFT JOIN programs p ON s.program_id = p.id
                            $searchQuery 
                            ORDER BY s.id
                        ";
                            $student_query = mysqli_query($conn, $query) or die(mysqli_error($conn));
                            while ($row = mysqli_fetch_array($student_query)) {
                                $id = $row['id'];
                            ?>
							<tr class="student-row">
								<td class="table-plus">
									<div class="name-avatar d-flex align-items-center">
										<div class="avatar mr-2 flex-shrink-0">
											<?php
                                                $picturePath = $row['picture_url']
                                                    ? 'admin/uploads/' . $row['picture_url']
                                                    : '../uploads/NO-IMAGE-AVAILABLE.jpg'; // Default image if no picture
                                                ?>
											<img src="<?php echo $picturePath; ?>" class="border-radius-100 box-shadow"
												style="width: 50px; height: 50px; object-fit: cover;" alt="Picture">
										</div>
										<div class="txt">
											<div class="weight-600"><?php echo $row['name']; ?></div>
										</div>
									</div>
								</td>
								<td><?php echo $row['student_id']; ?></td>
								<td><?php echo $row['uid']; ?></td>
								<td><?php echo $row['program_code']; ?></td> <!-- Display Program Name -->
								<td>
									<div class="dropdown">
										<a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
											href="#" role="button" data-toggle="dropdown">
											<i class="dw dw-more"></i>
										</a>
										<div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
											<a class="dropdown-item" href="view_student.php?edit=<?php echo $id; ?>"><i
													class="dw dw-eye"></i> View</a>
											<a class="dropdown-item" href="edit_student.php?edit=<?php echo $id; ?>"><i
													class="dw dw-edit2"></i> Edit</a>
											<a class="dropdown-item" href="javascript:void(0);"
												onclick="openDeleteModal(<?php echo $id; ?>)">
												<i class="dw dw-delete-3"></i> Delete
											</a>
										</div>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- Modal for Delete Confirmation -->
	<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
		aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					Are you sure you want to delete this student?
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<a href="javascript:void(0);" id="confirmDeleteButton" class="btn btn-danger">Delete</a>
				</div>
			</div>
		</div>
	</div>
</div>
</body>

<!-- JS for Modal Handling -->
<script>
function openDeleteModal(studentId) {
	// Show the delete modal
	$('#deleteModal').modal('show');

	// Set the delete link in the confirm button with the student ID
	$('#confirmDeleteButton').attr('href', 'student.php?delete=' + studentId);
}
</script>

<?php include('includes/footer.php'); ?>
<?php include('includes/scripts.php'); ?>

</html>