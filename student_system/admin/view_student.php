<?php
// Start the session and include necessary files
include '../db.php';
include('includes/header.php');

// Check if student_id is provided and is numeric to prevent SQL injection
if (!isset($_GET['edit']) || !is_numeric($_GET['edit'])) {
    echo "<script>alert('Invalid Student ID');</script>";
    echo "<script type='text/javascript'>document.location = 'admin_dashboard.php';</script>";
    exit;
}

$student_id = intval($_GET['edit']); // Sanitize the input

// Pagination settings
$limit = 10; // Change number of records per page to 5
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT students.name, students.student_id, programs.program_code, 
                 DATE_FORMAT(logs.entry_time, '%h:%i %p') AS entry_time, 
                 DATE_FORMAT(logs.entry_time, '%b. %d, %Y') as entry_date,
                 COALESCE(DATE_FORMAT(logs.exit_time, '%h:%i %p'), 'N/A') AS exit_time,
                 COALESCE(DATE_FORMAT(logs.exit_time, '%b. %d, %Y'), 'N/A') AS exit_date
          FROM logs 
          JOIN students ON logs.student_uid = students.uid 
          JOIN programs ON students.program_id = programs.id
          WHERE students.id = ? 
          ORDER BY logs.entry_time DESC 
          LIMIT ?, ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $student_id, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Count total records for pagination
$total_query = "SELECT COUNT(*) as total FROM logs JOIN students ON logs.student_uid = students.uid WHERE students.id = ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param('i', $student_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
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
                        <div class="col-md-12">
                            <!-- Breadcrumb Navigation -->
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Student Attendance Profile</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <div class="title">
                                <h4>Student Attendance Profile</h4>
                            </div>
                            <!-- Print Button -->
                            <button class="btn btn-primary" onclick="printPage()">Print</button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card-box pd-20 height-100-p">
                            <h2 class="mb-30 h4">Attendance Details</h2>
                            
                            <?php if ($result->num_rows > 0): ?>
                                <div id="print-section">
                                    <!-- School Information Section for Print -->
                                    <div id="school-info" style="text-align: center; margin-bottom: 15px;">
                                        <img src="uploads/logo.png" alt="Logo" style="height: 100px; width: 100px;">
                                        <h3>Talibon Polytechnic College</h3>
                                        <p>San Isidro, Talibon, Bohol</p>
                                        <p>talibonpolytechniccollegetalib@gmail.com</p>
                                    </div>

                                    <!-- Print Date Section -->
                                    <div style="text-align: center; margin-bottom: 15px;">
                                        <p><strong>Printed on: </strong><?php echo date('F j, Y'); ?></p>
                                    </div>

                                    <table class="table table-bordered" id="attendance-table">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Student Name</th>
                                                <th>Program Type</th>
                                                <th>Entry Time</th>
                                                <th>Entry Date</th>
                                                <th>Exit Time</th>
                                                <th>Exit Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $row['student_id']; ?></td>
                                                    <td><?php echo htmlentities($row['name']); ?></td>
                                                    <td><?php echo htmlentities($row['program_code']); ?></td>
                                                    <td><?php echo htmlentities($row['entry_time']); ?></td>
                                                    <td><?php echo htmlentities($row['entry_date']); ?></td>
                                                    <td><?php echo htmlentities($row['exit_time']); ?></td>
                                                    <td><?php echo htmlentities($row['exit_date']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination Controls -->
                               <nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <!-- Previous Button -->
        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
            <a class="page-link" href="view_student.php?edit=<?php echo $student_id; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>

        <?php
        // Define the range of visible page numbers
        $start = max(1, $page - 2); // Start 2 pages before the current page
        $end = min($total_pages, $page + 2); // End 2 pages after the current page

        // Adjust range to always show 5 numbers when possible
        if ($end - $start < 4) {
            if ($start > 1) {
                $start = max(1, $end - 4);
            } else {
                $end = min($total_pages, $start + 4);
            }
        }
        ?>

        <!-- Page Numbers -->
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                <a class="page-link" href="view_student.php?edit=<?php echo $student_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <!-- Next Button -->
        <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
            <a class="page-link" href="view_student.php?edit=<?php echo $student_id; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
                            <?php else: ?>
                                <p>No attendance records found for this student.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<style>
    @media print {
        body * {
            visibility: hidden; /* Hide everything */
        }

        #print-section, #print-section * {
            visibility: visible; /* Show only the print section */
        }

        #print-section {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        #school-info img {
            max-width: 120px;
            max-height: 120px;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border: 1px solid #000;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        h3 {
            font-size: 24px;
        }

        p {
            font-size: 18px;
        }
    }
</style>

<!-- Print Button -->
<button class="btn btn-primary" onclick="printPage()">Print</button>

<!-- JavaScript Print Function -->
<script>
    function printPage() {
        window.print();
    }
</script>

<?php include('includes/footer.php'); ?>
<?php include('includes/scripts.php'); ?>
</body>
</html>
