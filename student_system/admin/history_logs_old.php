<?php
// Start the session and include necessary files
include '../db.php';
include('includes/header.php');

// Handle AJAX search request
if (isset($_POST['search']) || isset($_POST['filter_date']) || isset($_POST['filter_department'])) {
$search = mysqli_real_escape_string($conn, $_POST['search'] ?? '');
$filter_date = mysqli_real_escape_string($conn, $_POST['filter_date'] ?? '');
$filter_department = mysqli_real_escape_string($conn, $_POST['filter_department'] ?? '');

$conditions = [];

if (!empty($search)) {
	$conditions[] = "(s.name LIKE '%$search%' OR l.student_id LIKE '%$search%')";
}
if (!empty($filter_date)) {
	$conditions[] = "DATE(l.entry_time) = '$filter_date'";
}
if (!empty($filter_department)) {
	$conditions[] = "l.program_id = '$filter_department'";
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$log_query = mysqli_query($conn, "
	SELECT l.*, 
			p.program_code, 
			s.name AS student_name, 
			DATE_FORMAT(l.entry_time, '%b. %d, %Y') AS entry_date,
			DATE_FORMAT(l.exit_time, '%b. %d, %Y') AS exit_date, 
			TIME(l.entry_time) AS entry_time,
			TIME(l.exit_time) AS exit_time
	FROM history_logs l
	LEFT JOIN programs p ON l.program_id = p.id
	LEFT JOIN students s ON l.student_uid = s.uid
	$where_clause
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
exit();
}

$dept_query = mysqli_query($conn, "SELECT id, program_code FROM programs ORDER BY program_code ASC");
?>

<?php include('includes/navbar.php'); ?>
<?php include('includes/right_sidebar.php'); ?>
<?php include('includes/left_sidebar.php'); ?>

<style>
#table-preloader {
	display: none;
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(255, 255, 255, 0.75);
	z-index: 10;
	justify-content: center;
	align-items: center;
	border-radius: 4px;
}

#table-preloader.active {
	display: flex;
}

.preloader-spinner {
	width: 48px;
	height: 48px;
	border: 5px solid #e0e0e0;
	border-top-color: #3d85c8;
	/* matches .text-blue */
	border-radius: 50%;
	animation: spin 0.8s linear infinite;
}

@keyframes spin {
	to {
		transform: rotate(360deg);
	}
}

/* Make card-box a positioning context for the overlay */
.card-box {
	position: relative;
}

@media print {
	body * {
		visibility: hidden;
	}

	#print-section,
	#print-section * {
		visibility: visible;
	}

	#print-section {
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
	}
}
</style>

<div class="mobile-menu-overlay"></div>

<div class="main-container">
<div class="pd-ltr-20 xs-pd-20-10 main-body">
	<div class="min-height-200px">
		<div class="page-header">
			<div class="row">
				<div class="col-md-6 col-sm-12">
					<div class="title">
						<h4>History Logs</h4>
					</div>
					<nav aria-label="breadcrumb" role="navigation">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
							<li class="breadcrumb-item active" aria-current="page">Logs Overview</li>
						</ol>
					</nav>
				</div>

				<div class="col-md-6 col-sm-12 d-flex justify-content-end align-items-center flex-wrap gap-2">
					<form id="searchForm" class="d-flex align-items-center flex-wrap gap-2" style="gap: 8px;">

						<input type="text" name="search" class="form-control" placeholder="Search by Name or ID"
							id="search-input" style="height: 34px; font-size: 14px; width: 200px;">

						<input type="date" name="filter_date" id="filter-date" class="form-control"
							style="height: 34px; font-size: 14px; width: 150px;">

						<select name="filter_department" id="filter-department" class="form-control"
							style="height: 34px; font-size: 14px; width: 150px;">
							<option value="">All Departments</option>
							<?php while ($dept = mysqli_fetch_assoc($dept_query)): ?>
								<option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['program_code']) ?></option>
							<?php endwhile; ?>
						</select>

						<button type="submit" class="btn btn-primary btn-sm" style="height: 34px;">
							<i class="fa fa-search"></i> Search
						</button>

						<button type="button" id="clear-filters" class="btn btn-secondary btn-sm" style="height: 34px;">
							<i class="fa fa-times"></i> Clear
						</button>

					</form>
				</div>
			</div>
		</div>

		<div class="card-box mb-30">

			<div id="table-preloader">
				<div class="preloader-spinner"></div>
			</div>

			<div class="pd-20 d-flex justify-content-between">
				<h2 class="text-blue h4">Attendance Logs</h2>

				<button class="btn btn-primary" onclick="printTable()">Print</button>
			</div>

			<div class="pb-20" id="print-section">
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
						<tr>
							<td colspan='7' class='text-center'>Please search <b style="color: red;">STUDENT NAME|ID</b> or filter by <b style="color: red;">DATE|DEPARTMENT</b> to view logs.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
	const preloader = document.getElementById('table-preloader');

	function showPreloader() {
		preloader.classList.add('active');
	}

	function hidePreloader() {
		preloader.classList.remove('active');
	}

	function fetchLogs() {
		const search = document.getElementById('search-input').value;
		const filterDate = document.getElementById('filter-date').value;
		const filterDepartment = document.getElementById('filter-department').value;

		showPreloader();

		fetch('history_logs.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: `search=${encodeURIComponent(search)}&filter_date=${encodeURIComponent(filterDate)}&filter_department=${encodeURIComponent(filterDepartment)}`
			})
			.then(response => response.text())
			.then(html => {
				document.getElementById('log-table').innerHTML = html;
				hidePreloader();
			})
			.catch(err => {
				console.error('Error fetching logs:', err)
				hidePreloader();
			});
	}

	document.getElementById('searchForm').addEventListener('submit', function(e) {
		e.preventDefault();
		fetchLogs();
	});

	document.getElementById('filter-date').addEventListener('change', fetchLogs);
	document.getElementById('filter-department').addEventListener('change', fetchLogs);

	document.getElementById('clear-filters').addEventListener('click', function() {
		document.getElementById('search-input').value = '';
		document.getElementById('filter-date').value = '';
		document.getElementById('filter-department').value = '';
		document.getElementById('log-table').innerHTML = `
			<tr><td colspan='7' class='text-center'>Please search <b style="color: red;">STUDENT NAME|ID</b> or filter by <b style="color: red;">DATE|DEPARTMENT</b> to view logs.</td></tr>
		`;
	});

	function printTable() {
		const search = document.getElementById('search-input').value || 'All';
		const date = document.getElementById('filter-date').value || 'All Dates';

		const departmentSelect = document.getElementById('filter-department');
		const department =
			departmentSelect.options[departmentSelect.selectedIndex].text;

		const printContents = document.getElementById('print-section').innerHTML;

		const printWindow = window.open('', '', 'width=1000,height=700');

		printWindow.document.write(`
	<html>
	<head>
		<title>Attendance Logs</title>
		<style>
			body {
				font-family: Arial, sans-serif;
				padding: 20px;
			}

			h2 {
				text-align: center;
				margin-bottom: 20px;
			}

			table {
				width: 100%;
				border-collapse: collapse;
			}

			th, td {
				border: 1px solid #000;
				padding: 8px;
				text-align: left;
			}

			th {
				background: #f2f2f2;
			}

			img {
				display: none;
			}
		</style>
	</head>
	<body>
		<div style="text-align:center;">
			<h2>Talibon Polytechnic College Security and Monitoring System</h2>
			<h4>History Logs Report</h4>
			<p>Generated: ${new Date().toLocaleString()}</p>
		</div>
		<p><strong>Search:</strong> ${search}</p>
		<p><strong>Date:</strong> ${date}</p>
		<p><strong>Department:</strong> ${department}</p>
		${printContents}
	</body>
	</html>
`);

		printWindow.document.close();
		printWindow.focus();

		setTimeout(() => {
			printWindow.print();
			printWindow.close();
		}, 500);
	}
</script>

<?php include('includes/footer.php'); ?>
<?php include('includes/scripts.php'); ?>
</body>

</html>