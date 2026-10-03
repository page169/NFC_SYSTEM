<?php
// Start the session and include necessary files
include '../db.php';
include('includes/header.php');

// Handle AJAX search request
if (isset($_POST['search'])) {
$search = mysqli_real_escape_string($conn, $_POST['search']);

$log_query = mysqli_query($conn, "
	SELECT b.FirstName, b.LastName, b.username, a.activity, a.created_at
	FROM system_logs AS a
	INNER JOIN admins AS b ON a.admin_uid = b.id
	WHERE b.FirstName LIKE '%$search%' OR b.LastName LIKE '%$search%'
	ORDER BY a.created_at DESC
") or die(mysqli_error($conn));

if (mysqli_num_rows($log_query) > 0) {
	while ($row = mysqli_fetch_array($log_query)) {
		echo "
		<tr class='log-row'>
			<td class='table-plus'>{$row['FirstName']}</td>
			<td>{$row['LastName']}</td>
			<td>{$row['username']}</td>
			<td>{$row['activity']}</td>
			<td>" . date('F j, Y h:i A', strtotime($row['created_at'])) . "</td>
		</tr>";
	}
} else {
	echo "<tr><td colspan='5' class='text-center'>No matching records found.</td></tr>";
}
exit();
}

// Fetch log data without a search query
$log_query = mysqli_query($conn, "
SELECT
	b.FirstName,
	b.LastName,
	b.username,
	a.activity,
	a.created_at
FROM system_logs AS a
INNER JOIN admins AS b ON a.admin_uid = b.id
ORDER BY a.created_at DESC
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
							<h4>System Logs</h4>
						</div>
						<nav aria-label="breadcrumb" role="navigation">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
								<li class="breadcrumb-item active" aria-current="page">System Logs</li>
							</ol>
						</nav>
					</div>
					<div class="col-md-6 col-sm-12 d-flex justify-content-end">
						<form id="searchForm" style="width: 250px;">
							<input type="text" name="search" class="form-control search-bar"
								placeholder="Search by First | Last Name" id="search-input"
								style="height: 30px; font-size: 14px;">
						</form>
					</div>
				</div>
			</div>

			<div class="card-box mb-30">
				<div class="pd-20 d-flex justify-content-between">
					<h2 class="text-blue h4">System Logs</h2>
				</div>

				<div class="pb-20">
					<table class="data-table table stripe hover nowrap">
						<thead>
							<tr>
								<th class="table-plus">First name</th>
								<th>Last name</th>
								<th>Username</th>
								<th>Activity</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody id="log-table">
							<?php
						while ($row = mysqli_fetch_array($log_query)) {
						?>
							<tr class="log-row">
								<td class="table-plus">
									<?php echo $row['FirstName']; ?>
								</td>
								<td><?php echo $row['LastName']; ?></td>
								<td><?php echo $row['username']; ?></td>
								<td><?php echo $row['activity']; ?></td>
								<td>
									<?php
									echo date("F j, Y h:i A", strtotime($row['created_at']));
									?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- JS for Modal Handling -->
	<script>
	// AJAX search functionality
	document.getElementById('search-input').addEventListener('input', function() {
		const searchInput = this.value;

		// Perform the search via AJAX
		fetch('system_logs.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: `search=${encodeURIComponent(searchInput)}`
			})
			.then(response => response.text())
			.then(html => {
				document.getElementById('log-table').innerHTML =
					html; // Update the table with filtered results
			})
			.catch(err => console.error('Error fetching logs:', err)); // Error handling
	});
	</script>

	<?php include('includes/footer.php'); ?>
	<?php include('includes/scripts.php'); ?>
	</body>

	</html>