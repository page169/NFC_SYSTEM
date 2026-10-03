<?php include('includes/header.php'); ?>
<?php include('../includes/session.php'); ?>


    <?php include('includes/navbar.php'); ?>
    <?php include('includes/right_sidebar.php'); ?>
    <?php include('includes/left_sidebar.php'); ?>
    <div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="card-box pd-20 height-100-p mb-30">
                <div class="row align-items-center">
                    <div class="col-md-4 user-icon">
                        <img src="../vendors/images/banner-img.png" alt="">
                    </div>
                    <div class="col-md-8">
                        <?php
                        $query = mysqli_query($conn, "SELECT * FROM admins WHERE id = '$session_id'") or die(mysqli_error());
                        $row = mysqli_fetch_array($query);
                        ?>
                        <h4 class="font-20 weight-500 mb-10 text-capitalize">
                            Welcome back <div class="weight-600 font-30 text-blue"><?php echo $row['username']; ?>,</div>
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
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $studentCount = $query->rowCount();
                        ?>
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo($studentCount); ?></div>
                                <div class="font-14 text-secondary weight-500">Total Students</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#00eccf"><i class="icon-copy dw dw-user-2"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <?php
                        $sql = "SELECT id FROM logs WHERE entry_time IS NOT NULL";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $attendanceCount = $query->rowCount();
                        ?>
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo($attendanceCount); ?></div>
                                <div class="font-14 text-secondary weight-500">Attendance Records</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#09cc06"><span class="icon-copy fa fa-check-circle"></span></div>
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
                                <a title="VIEW" href="students.php"><i class="icon-copy ion-disc" data-color="#17a2b8"></i></a>
                            </div>
                        </div>
                        <div class="user-list">
                            <ul>
                                <?php
                                $query = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC LIMIT 5") or die(mysqli_error());
                                while ($row = mysqli_fetch_array($query)) {
                                ?>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <div class="name-avatar d-flex align-items-center pr-2">
                                            <div class="avatar mr-2 flex-shrink-0">
                                                <img src="<?php echo (!empty($row['picture_url'])) ? '../uploads/' . $row['picture_url'] : '../uploads/NO-IMAGE-AVAILABLE.jpg'; ?>" class="border-radius-100 box-shadow" width="50" height="50" alt="">
                                            </div>
                                            <div class="txt">
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
                                <a title="VIEW" href="attendance_logs.php"><i class="icon-copy ion-disc" data-color="#17a2b8"></i></a>
                            </div>
                        </div>
                        <div class="user-list">
                            <ul>
                                <?php
                                $query = mysqli_query($conn, "SELECT * FROM logs ORDER BY id DESC LIMIT 5") or die(mysqli_error());
                                while ($row = mysqli_fetch_array($query)) {
                                ?>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <div class="name-avatar d-flex align-items-center pr-2">
                                            <div class="txt">
                                                <div class="font-14 weight-600"><?php echo $row['student_name']; ?></div>
                                                <div class="font-12 weight-500" data-color="#b2b1b6"><?php echo $row['student_id']; ?></div>
                                            </div>
                                        </div>
                                        <div class="font-12 weight-500" data-color="#17a2b8"><?php echo $row['entry_time']; ?></div>
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
