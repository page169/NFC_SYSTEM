<?php
// Start session and include database connection
session_start();
include '../db.php';
include('includes/header.php');

// Add new program (course)
if (isset($_POST['add_program'])) {
    $program_code = $_POST['program_code'];
    $program_name = $_POST['program_name'];

    // Check for duplicate program code
    $query = mysqli_query($conn, "SELECT * FROM programs WHERE program_code = '$program_code'") or die(mysqli_error($conn));
    if (mysqli_num_rows($query) > 0) {
        echo "<script>alert('Program code already exists!');</script>";
    } else {
        mysqli_query($conn, "INSERT INTO programs (program_code, program_name) VALUES ('$program_code', '$program_name')") or die(mysqli_error($conn));
        echo "<script>alert('Program added successfully!'); window.location='program.php';</script>";
    }
}

// Delete program
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM programs WHERE id = $delete_id") or die(mysqli_error($conn));
    echo "<script>alert('Program deleted successfully!'); window.location='program.php';</script>";
}
?>

<body>
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
                                <h4>Manage Programs</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Programs</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Add New Program Form -->
                <div class="pd-20 card-box mb-30">
                    <div class="clearfix">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Add New Program</h4>
                        </div>
                    </div>
                    <form method="post" action="">
                        <div class="form-group">
                            <label>Program Code:</label>
                            <input type="text" name="program_code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Program Name:</label>
                            <input type="text" name="program_name" class="form-control" required>
                        </div>
                        <button type="submit" name="add_program" class="btn btn-primary">Add Program</button>
                    </form>
                </div>

                <!-- Display Existing Programs -->
                <div class="pd-20 card-box mb-30">
                    <h4 class="text-blue h4">Existing Programs</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Program Code</th>
                                    <th>Program Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($conn, "SELECT * FROM programs") or die(mysqli_error($conn));
                                $count = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>
                                        <td>{$count}</td>
                                        <td>{$row['program_code']}</td>
                                        <td>{$row['program_name']}</td>
                                        <td>
										<a href='edit_program.php?id={$row['id']}' class='btn btn-success'>Edit</a>
                                            <a href='program.php?delete_id={$row['id']}' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this program?\")'>Delete</a>
                                        </td>
                                    </tr>";
                                    $count++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/scripts.php'); ?>
</body>
</html>
