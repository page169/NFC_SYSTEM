<?php
session_start();
include '../db.php';
include('includes/header.php');

// Check if the admin is logged in by verifying the session
if (!isset($_SESSION['id'])) {
    // Redirect to login if the session ID is not set
    header("Location: ../login.php");
    exit();
}

// Fetch admin details based on the session ID
$admin_id = $_SESSION['id'];
$query = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch admin information
        $admin = $result->fetch_assoc();
    } else {
        echo "Admin not found.";
        exit();
    }
} else {
    echo "Database error: Unable to prepare statement.";
    exit();
}
?>

<body>
    <?php include('includes/navbar.php'); ?>
    <?php include('includes/right_sidebar.php'); ?>
    <?php include('includes/left_sidebar.php'); ?>

    <div class="main-container">
        <div class="pd-ltr-20 main-body">
            <div class="card-box pd-20 height-100-p mb-30">
                <div class="row align-items-center">
                    <div class="col-md-4 user-icon">
                        <img src="../vendors/images/banner-img.png" alt="">
                    </div>
                    <div class="col-md-8">
                        <h4 class="font-20 weight-500 mb-10 text-capitalize">
                            Welcome back, <div class="weight-600 font-30 text-blue">
                                <?php echo htmlspecialchars($admin['FirstName']) . ' ' . htmlspecialchars($admin['LastName']); ?>,
                            </div>
                        </h4>
                        <p class="font-18 max-width-600">You are in an institution established to serve the wider community.</p>
                    </div>
                </div>
            </div>

            <div class="title pb-20">
                <h2 class="h3 mb-0">Data Information</h2>
            </div>
            <div class="row pb-10">
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <?php
                        $sql = "SELECT id FROM students";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $studentCount = $query->rowCount();
                        ?>
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo ($studentCount); ?></div>
                                <div class="font-14 text-secondary weight-500">Total Students</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#00eccf"><i class="icon-copy dw dw-user-2"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-80-p widget-style3">
                        <?php
                        // Get today's attendance count
                        $today = date('Y-m-d');
                        $sql_today = "SELECT id FROM logs WHERE entry_time LIKE ?";
                        $query_today = $dbh->prepare($sql_today);
                        $query_today->execute([$today . '%']);
                        $attendanceTodayCount = $query_today->rowCount();
                        ?>
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo ($attendanceTodayCount); ?></div>
                                <div class="font-14 text-secondary weight-500">Today's Record</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#09cc06"><span class="icon-copy fa fa-check-circle"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-80-p widget-style3">
                        <?php
                        // Get yesterday's attendance count
                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                        $sql_yesterday = "SELECT id FROM logs WHERE entry_time LIKE ?";
                        $query_yesterday = $dbh->prepare($sql_yesterday);
                        $query_yesterday->execute([$yesterday . '%']);
                        $attendanceYesterdayCount = $query_yesterday->rowCount();
                        ?>
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo ($attendanceYesterdayCount); ?></div>
                                <div class="font-14 text-secondary weight-500">Yesterday Record's</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#f1c40f"><span class="icon-copy fa fa-calendar-check-o"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-12 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between pb-10">
                            <div class="h5 mb-0">Recent Students</div>
                            <div class="table-actions">
                                <a title="VIEW" href="student.php"><i class="icon-copy ion-disc" data-color="#17a2b8"></i></a>
                            </div>
                        </div>
                        <div class="user-list">
                            <ul>
                                <?php
                                $query = mysqli_query($conn, "
            SELECT students.id, students.name, students.student_id, students.uid, students.picture_url, programs.program_code
            FROM students
            JOIN programs ON students.program_id = programs.id
            ORDER BY students.id DESC
            LIMIT 5
        ") or die(mysqli_error($conn));
                                while ($row = mysqli_fetch_array($query)) {
                                ?>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <div class="name-avatar d-flex align-items-center pr-2">
                                            <div class="avatar mr-2 flex-shrink-0">
                                                <img src="<?php echo $row['picture_url'] ? 'admin/uploads/' . $row['picture_url'] : '../uploads/NO-IMAGE-AVAILABLE.jpg'; ?>" class="border-radius-100 box-shadow" style="width: 50px; height: 50px; object-fit: cover;" alt="">
                                            </div>
                                            <div class="txt">
                                                <!-- Display program_code instead of program_id -->
                                                <span class="badge badge-pill badge-sm" data-bgcolor="#e7ebf5" data-color="#265ed7"><?php echo $row['program_code']; ?></span>
                                                <div class="font-14 weight-600"><?php echo $row['name']; ?></div>
                                                <div class="font-12 weight-500" data-color="#b2b1b6"><?php echo $row['student_id']; ?></div>
                                            </div>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between">
                            <div class="h5 mb-0">Recent Attendance Logs</div>
                            <div class="table-actions">
                                <a title="VIEW" href="logs.php"><i class="icon-copy ion-disc" data-color="#17a2b8"></i></a>
                            </div>
                        </div>
                        <div class="user-list">
                            <ul>
                                <?php
                                $query = mysqli_query($conn, "
                                    SELECT s.name AS student_name, logs.student_id, logs.entry_time, logs.picture_url, p.program_code
                                    FROM logs
                                    JOIN students s ON logs.student_uid = s.uid
                                    JOIN programs p ON s.program_id = p.id
                                    ORDER BY logs.id DESC
                                    LIMIT 5
                                ") or die(mysqli_error($conn));

                                while ($row = mysqli_fetch_array($query)) {
                                    $entry_time = date("g:i A", strtotime($row['entry_time']));
                                    // Format entry date to Y-m-d format
                                    $entry_date = date("M. d, Y", strtotime($row['entry_time']));
                                ?>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <div class="name-avatar d-flex align-items-center pr-2">
                                            <div class="avatar mr-2 flex-shrink-0">
                                                <img src="<?php echo $row['picture_url'] ? 'admin/uploads/' . $row['picture_url'] : '../uploads/NO-IMAGE-AVAILABLE.jpg'; ?>" class="border-radius-100 box-shadow" style="width: 50px; height: 50px; object-fit: cover;" alt="">
                                            </div>
                                            <div class="txt">
                                                <div class="badge badge-pill badge-sm" data-bgcolor="#e7ebf5" data-color="#265ed7"><?php echo $row['program_code']; ?></div>
                                                <div class="font-14 weight-600"><?php echo $row['student_name']; ?></div>
                                                <div class="font-12 weight-500" data-color="#b2b1b6"><?php echo $row['student_id']; ?></div>


                                            </div>
                                        </div>
                                        <div class="font-12 weight-500" data-color="#17a2b8"><?php echo $entry_date; ?></div>
                                        <div class="font-12 weight-500" data-color="#17a2b8"><?php echo $entry_time; ?></div>

                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>
    <!-- js -->
    <?php include('includes/scripts.php'); ?>
</body>

</html>