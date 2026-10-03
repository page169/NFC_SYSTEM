<?php
// Start the session and include necessary files
include '../db.php';
include('includes/header.php');

// Delete all logs functionality
if (isset($_GET['delete_all'])) {
    $sql = "INSERT INTO history_logs 
                (student_uid, entry_time, exit_time, student_name, student_id, picture_url, program_id)
            SELECT 
                student_uid, entry_time, exit_time, student_name, student_id, picture_url, program_id
            FROM logs";

    $transfer = mysqli_query($conn, $sql);

    if ($transfer) {
        $sql = "DELETE FROM logs";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            echo "<script>alert('All logs deleted and archived successfully');</script>";
            echo "<script type='text/javascript'> document.location = 'logs.php'; </script>";
        } else {
            echo "<script>alert('Failed to delete all logs');</script>";
        }
    } else {
        echo "<script>alert('Failed to archive logs before deletion');</script>";
    }
}

// Handle AJAX search request
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);

    // Fetch logs matching the searched student name or ID
    $log_query = mysqli_query($conn, "
        SELECT l.*, 
               p.program_code, 
               s.name AS student_name, 
               DATE_FORMAT(l.entry_time, '%b. %d, %Y') AS entry_date,
               DATE_FORMAT(l.exit_time, '%b. %d, %Y') AS exit_date, 
               TIME(l.entry_time) AS entry_time,
               TIME(l.exit_time) AS exit_time
        FROM logs l
        LEFT JOIN programs p ON l.program_id = p.id
        LEFT JOIN students s ON l.student_uid = s.uid
        WHERE s.name LIKE '%$search%' OR l.student_id LIKE '%$search%'
        ORDER BY l.entry_time DESC
    ") or die(mysqli_error($conn));

    if (mysqli_num_rows($log_query) > 0) {
        while ($row = mysqli_fetch_array($log_query)) {
            $entry_time = date("g:i:s A", strtotime($row['entry_time']));
            $picturePath = $row['picture_url']
                ? 'admin/uploads/' . $row['picture_url']
                : '../uploads/NO-IMAGE-AVAILABLE.jpg';

            echo "
            <tr class='log-row'>
                <td class='table-plus'>
                    <img src='{$picturePath}' class='border-radius-100 box-shadow' style='width: 50px; height: 50px; object-fit: cover;' alt='Picture'>
                    {$row['student_name']}
                </td>
                <td>{$row['student_id']}</td>
                <td>{$entry_time}</td>
                <td>{$row['entry_date']}</td>
                <td>" . ($row['exit_time'] ? date("g:i:s A", strtotime($row['exit_time'])) : 'Not yet exited') . "</td>
                <td>" . ($row['exit_date'] ? date("M. d, Y", strtotime($row['exit_date'])) : 'Not yet exited') . "</td>
                <td>{$row['program_code']}</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='7' class='text-center'>No matching records found.</td></tr>";
    }
    exit(); // Stop further output for AJAX response
}

// Fetch log data without a search query
$log_query = mysqli_query($conn, "
    SELECT l.*, 
           p.program_code, 
           s.name AS student_name, 
           DATE_FORMAT(l.entry_time, '%b. %d, %Y') AS entry_date,
           DATE_FORMAT(l.exit_time, '%b. %d, %Y') AS exit_date, 
           TIME(l.entry_time) AS entry_time,
           TIME(l.exit_time) AS exit_time
    FROM logs l
    LEFT JOIN programs p ON l.program_id = p.id
    LEFT JOIN students s ON l.student_uid = s.uid
    ORDER BY l.entry_time DESC
") or die(mysqli_error($conn));

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
                            <h4>Logs</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Logs Overview</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 d-flex justify-content-end">
                        <form id="searchForm" style="width: 250px;">
                            <input type="text" name="search" class="form-control search-bar" placeholder="Search by Name or Student ID" id="search-input" style="height: 30px; font-size: 14px;">
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="pd-20 d-flex justify-content-between">
                    <h2 class="text-blue h4">Attendance Logs</h2>
                    <a href="javascript:void(0);" class="btn btn-danger" data-toggle="modal" data-target="#deleteAllLogsModal">Delete All Logs</a>
                </div>

                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th class="table-plus">STUDENT NAME</th>
                                <th>STUDENT ID</th>
                                <th>ENTRY TIME</th>
                                <th>ENTRY DATE</th>
                                <th>EXIT TIME</th>
                                <th>EXIT DATE</th>
                                <th class="datatable-nosort">Department</th>
                            </tr>
                        </thead>
                        <tbody id="log-table">
                            <?php
                            while ($row = mysqli_fetch_array($log_query)) {
                                $id = $row['id'];
                                $entry_time = date("g:i:s A", strtotime($row['entry_time']));
                                $picturePath = $row['picture_url']
                                    ? 'admin/uploads/' . $row['picture_url']
                                    : '../uploads/NO-IMAGE-AVAILABLE.jpg';
                            ?>
                                <tr class="log-row">
                                    <td class="table-plus">
                                        <img src="<?php echo $picturePath; ?>" class="border-radius-100 box-shadow" style="width: 50px; height: 50px; object-fit: cover;" alt="Picture">
                                        <?php echo $row['student_name']; ?>
                                    </td>
                                    <td><?php echo $row['student_id']; ?></td>
                                    <td><?php echo $entry_time; ?></td>
                                    <td><?php echo $row['entry_date']; ?></td>
                                    <td>
                                        <?php
                                        echo $row['exit_time'] ? date("g:i:s A", strtotime($row['exit_time'])) : 'Not yet exited';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo $row['exit_date'] ? date("M. d, Y", strtotime($row['exit_date'])) : 'Not yet exited';
                                        ?>
                                    </td>
                                    <td><?php echo $row['program_code']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Delete All Logs Confirmation -->
    <div class="modal" id="deleteAllLogsModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllLogsModalLabel">Confirm Delete All Logs</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete all logs? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a href="javascript:void(0);" id="confirmDeleteAllButton" class="btn btn-danger">Delete All Logs</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JS for Modal Handling -->
    <script>
        // Confirm delete all action
        document.getElementById('confirmDeleteAllButton').addEventListener('click', function() {
            // Redirect to the deletion URL
            window.location.href = 'logs.php?delete_all=true';
        });

        // AJAX search functionality
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission
            const searchInput = document.getElementById('search-input').value;

            // Perform the search via AJAX
            fetch('logs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `search=${encodeURIComponent(searchInput)}`
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('log-table').innerHTML = html; // Update the table with filtered results
                })
                .catch(err => console.error('Error fetching logs:', err)); // Error handling
        });
    </script>

    <?php include('includes/footer.php'); ?>
    <?php include('includes/scripts.php'); ?>
    </body>

    </html>