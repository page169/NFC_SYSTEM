<?php
// Start the session and include necessary files
include '../db.php';
include('includes/header.php');

// Initialize filter variables
$filter_date_start = '';
$filter_date_end = '';
$filter_course = '';

// Check if the form for filtering is submitted
if (isset($_GET['filter_date_start'])) {
    $filter_date_start = $_GET['filter_date_start'];
}

if (isset($_GET['filter_date_end'])) {
    $filter_date_end = $_GET['filter_date_end'];
}

if (isset($_GET['course_type'])) {
    $filter_course = $_GET['course_type'];
}

// Build the query with filters
$query = "SELECT students.name, students.student_id, programs.program_name AS course_type, 
                 DATE_FORMAT(logs.entry_time, '%h:%i %p') AS entry_time, 
                 DATE(logs.entry_time) as entry_date,
                 DATE_FORMAT(logs.exit_time, '%h:%i %p') AS exit_time,
                 DATE(logs.exit_time) as exit_date 
          FROM logs 
          JOIN students ON logs.student_uid = students.uid 
          JOIN programs ON students.program_id = programs.id 
          WHERE 1=1";

// Add filtering conditions
if (!empty($filter_date_start) && !empty($filter_date_end)) {
    $query .= " AND logs.entry_time BETWEEN ? AND ?";
} elseif (!empty($filter_date_start)) {
    $query .= " AND DATE(logs.entry_time) >= ?";
} elseif (!empty($filter_date_end)) {
    $query .= " AND DATE(logs.entry_time) <= ?";
}

if (!empty($filter_course)) {
    $query .= " AND programs.program_code = ?";
}

$query .= " ORDER BY logs.entry_time DESC";

// Prepare the statement
$stmt = $conn->prepare($query);

// Bind parameters dynamically
$params = [];
$types = '';

if (!empty($filter_date_start) && !empty($filter_date_end)) {
    $params[] = $filter_date_start;
    $params[] = $filter_date_end;
    $types .= 'ss';
} elseif (!empty($filter_date_start)) {
    $params[] = $filter_date_start;
    $types .= 's';
} elseif (!empty($filter_date_end)) {
    $params[] = $filter_date_end;
    $types .= 's';
}

if (!empty($filter_course)) {
    $params[] = $filter_course;
    $types .= 's';
}

if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch programs for the dropdown
$programs_query = "SELECT program_code, program_name FROM programs ORDER BY program_name";
$programs_result = $conn->query($programs_query);
?>

<?php include('includes/navbar.php'); ?>
<?php include('includes/right_sidebar.php'); ?>
<?php include('includes/left_sidebar.php'); ?>

<body>
    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
            <div class="min-height-200px">
                <div class="page-header">
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-between">
                            <div class="title">
                                <h4>Print Attendance Records</h4>
                            </div>
                            <button class="btn btn-primary" onclick="window.print()">Print</button>
                        </div>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Print Attendance Records</li>
                        </ol>
                    </nav>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card-box pd-20 height-100-p">
                            <h2 class="mb-30 h4">Attendance Filter</h2>
                            <form action="print.php" method="GET" id="filterForm">
                                <div class="form-row">
                                    <div class="col-md-4">
                                        <label for="filter_date_start">Start Date:</label>
                                        <input type="date" id="filter_date_start" name="filter_date_start" class="form-control" value="<?php echo $filter_date_start; ?>" onchange="submitForm()">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="filter_date_end">End Date:</label>
                                        <input type="date" id="filter_date_end" name="filter_date_end" class="form-control" value="<?php echo $filter_date_end; ?>" onchange="submitForm()">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="course_type">Department:</label>
                                        <select name="course_type" class="custom-select form-control" onchange="submitForm()">
                                            <option value="">Select Department</option>
                                            <?php while ($program = $programs_result->fetch_assoc()): ?>
                                                <option value="<?php echo $program['program_code']; ?>" <?php echo ($filter_course == $program['program_code']) ? 'selected' : ''; ?>>
                                                    <?php echo $program['program_name']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </form>

                            <h2 class="mb-30 h4 mt-5">Attendance Records</h2>

                            <?php if ($result->num_rows > 0): ?>
                                <div id="print-section">
                                    <div id="school-info" style="text-align: center; margin-bottom: 15px;">
                                        <img src="uploads/logo.png" alt="Logo" style="height: 100px; width: 100px;">
                                        <h3>Talibon Polytechnic College</h3>
                                        <p>San Isidro, Talibon, Bohol</p>
                                        <p>talibonpolytechniccollegetalib@gmail.com</p>
                                    </div>

                                    <div style="text-align: center; margin-bottom: 15px;">
                                        <p><strong>Printed on: </strong><?php echo date('F j, Y'); ?></p>
                                    </div>

                                    <div class="pb-20">
    <table class="table" style="width: 100%; border-collapse: collapse; table-layout: auto;">
        <thead>
            <tr>
                <th style="padding: 6.3px; text-align: center;">Student ID</th>
                <th style="padding: 6.3px; text-align: center;">Student Name</th>
                <th style="padding: 6.3px; text-align: center;">Department</th>
                <th style="padding: 6.3px; text-align: center;">Entry Time</th>
                <th style="padding: 6.3px; text-align: center;">Entry Date</th>
                <th style="padding: 6.3px; text-align: center;">Exit Time</th>
                <th style="padding: 6.3px; text-align: center;">Exit Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="padding: 6.3px; text-align: center;"><?php echo $row['student_id']; ?></td>
                    <td style="padding: 6.3px; text-align: center;"><?php echo $row['name']; ?></td>
                    <td style="padding: 6.3px; text-align: center;"><?php echo $row['course_type']; ?></td>
                    <td style="padding: 6.3px; text-align: center;"><?php echo $row['entry_time']; ?></td>
                    <td style="padding: 6.3px; text-align: center;"><?php echo date("M. j, Y", strtotime($row['entry_date'])); ?></td>
                    <td style="padding: 6.3px; text-align: center;"><?php echo $row['exit_time'] ?: 'N/A'; ?></td>
                    <td style="padding: 6.3px; text-align: center;"><?php echo $row['exit_date'] ? date("M. j, Y", strtotime($row['exit_date'])) : 'N/A'; ?></td>
                </tr>
            <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p>No records found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function submitForm() {
            document.getElementById("filterForm").submit();
        }
        </script>

        <style>
            @media print {
                body * {
                    visibility: hidden;
                }
                #print-section, #print-section * {
                    visibility: visible;
                }
                #print-section {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    text-align: center;
                }

                table {
                    margin: auto;
                }

                .btn {
                    display: none;
                }
            }
        </style>

        <?php include('includes/footer.php'); ?>
        <?php include('includes/scripts.php'); ?>
    </body>
</html>
