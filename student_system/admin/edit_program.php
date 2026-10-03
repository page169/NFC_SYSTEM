<?php
// Start session and include database connection
session_start();
include '../db.php';
include('includes/header.php');

// Check if program ID is provided
if (isset($_GET['id'])) {
    $program_id = $_GET['id'];

    // Fetch program details
    $query = mysqli_query($conn, "SELECT * FROM programs WHERE id = $program_id") or die(mysqli_error($conn));
    if (mysqli_num_rows($query) > 0) {
        $program = mysqli_fetch_assoc($query);
    } else {
        echo "<script>alert('Program not found!'); window.location='program.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request!'); window.location='program.php';</script>";
    exit();
}

// Update program details
if (isset($_POST['update_program'])) {
    $program_code = $_POST['program_code'];
    $program_name = $_POST['program_name'];

    // Check for duplicate program code (excluding current program)
    $check_query = mysqli_query($conn, "SELECT * FROM programs WHERE program_code = '$program_code' AND id != $program_id") or die(mysqli_error($conn));
    if (mysqli_num_rows($check_query) > 0) {
        echo "<script>alert('Program code already exists!');</script>";
    } else {
        mysqli_query($conn, "UPDATE programs SET program_code = '$program_code', program_name = '$program_name' WHERE id = $program_id") or die(mysqli_error($conn));
        echo "<script>alert('Program updated successfully!'); window.location='program.php';</script>";
    }
}
?>

<body>
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
                                <h4>Edit Program</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="program.php">Programs</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Edit Program</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Edit Program Form -->
                <div class="pd-20 card-box mb-30">
                    <div class="clearfix">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Edit Program</h4>
                        </div>
                    </div>
                    <form method="post" action="">
                        <div class="form-group">
                            <label>Program Code:</label>
                            <input type="text" name="program_code" class="form-control" value="<?php echo $program['program_code']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Program Name:</label>
                            <input type="text" name="program_name" class="form-control" value="<?php echo $program['program_name']; ?>" required>
                        </div>
                        <button type="submit" name="update_program" class="btn btn-success">Update Program</button>
                        <a href="program.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/scripts.php'); ?>
</body>

</html>