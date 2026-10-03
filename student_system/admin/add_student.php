<?php
// Start the session and include necessary files
session_start();
include '../db.php';
include('includes/header.php');

if(isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $student_id = $_POST['student_id'];
    $uid = $_POST['uid'];
    $program_id = $_POST['program_id'];
    $picture_url = $_FILES['picture_url']['name'] ? $_FILES['picture_url']['name'] : 'NO-IMAGE-AVAILABLE.jpg';

    // Upload the picture if provided
    if ($picture_url != 'NO-IMAGE-AVAILABLE.jpg') {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["picture_url"]["name"]);
        move_uploaded_file($_FILES["picture_url"]["tmp_name"], $target_file);
    }

    // Check for duplicate student_id or UID
    $query = mysqli_query($conn, "SELECT * FROM students WHERE student_id = '$student_id' OR uid = '$uid'") or die(mysqli_error());
    $count = mysqli_num_rows($query);

    if ($count > 0) { ?>
        <script>
            alert('Student already exists with this ID or UID');
        </script>
    <?php
    } else {
        // Insert new student with program_id
        mysqli_query($conn, "INSERT INTO students (name, student_id, uid, picture_url, program_id) 
            VALUES ('$name', '$student_id', '$uid', '$picture_url', '$program_id')") 
            or die(mysqli_error());
        ?>
        <script>
            alert('Student record successfully added');
            window.location = "student.php"; 
        </script>
    <?php   
    }
}
?>

<body>
    <?php include('includes/navbar.php')?>
    <?php include('includes/right_sidebar.php')?>
    <?php include('includes/left_sidebar.php')?>

    <div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
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
                                    <li class="breadcrumb-item active" aria-current="page">Student Module</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="pd-20 card-box mb-30">
                    <div class="clearfix">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Student Form</h4>
                            <p class="mb-20">Please enter student details below:</p>
                        </div>
                    </div>
                    <div class="wizard-content">
                        <form method="post" action="" enctype="multipart/form-data">
                            <section>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Full Name :</label>
                                            <input name="name" type="text" class="form-control" required="true" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Student ID :</label>
                                            <input name="student_id" type="text" class="form-control" required="true" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Program :</label>
                                            <select name="program_id" id="program_id" class="custom-select form-control" required="true">
                                                <option value="">Select Program</option>
    <?php
    $programs = mysqli_query($conn, "SELECT * FROM programs") or die(mysqli_error($conn));
    while ($program = mysqli_fetch_assoc($programs)) {
        // Display both program_code and program_name in the dropdown
        echo "<option value='{$program['id']}'>{$program['program_code']} - {$program['program_name']}</option>";
    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>UID :</label>
                                            <input name="uid" type="text" class="form-control" required="true" autocomplete="off" value="<?php echo uniqid(); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Upload Picture :</label>
                                            <input name="picture_url" type="file" class="form-control" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <div class="modal-footer justify-content-center">
                                                <button class="btn btn-primary" name="add_student" id="add_student">Add&nbsp;Student</button>
                                            </div>
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

    <!-- Include necessary scripts -->
    <?php include('includes/scripts.php')?>
</body>
</html>
