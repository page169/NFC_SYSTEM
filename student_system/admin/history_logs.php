<?php
include '../db.php';
include('includes/header.php');

$filter_date_start = '';
$filter_date_end = '';
$filter_course = '';

if (isset($_GET['filter_date_start'])) {
$filter_date_start = $_GET['filter_date_start'];
}

if (isset($_GET['filter_date_end'])) {
$filter_date_end = $_GET['filter_date_end'];
}

if (isset($_GET['course_type'])) {
$filter_course = $_GET['course_type'];
}

$query = "SELECT
students.name,
students.student_id,
programs.program_name AS course_type,
DATE_FORMAT(logs.entry_time, '%h:%i %p') AS entry_time,
DATE(logs.entry_time) AS entry_date,
DATE_FORMAT(logs.exit_time, '%h:%i %p') AS exit_time,
DATE(logs.exit_time) AS exit_date
FROM logs
JOIN students ON logs.student_uid = students.uid
JOIN programs ON students.program_id = programs.id
WHERE 1=1";

$params = [];
$types = '';

if (!empty($filter_date_start) && !empty($filter_date_end)) {
$startDate = $filter_date_start . ' 00:00:00';

$endDate = date(
'Y-m-d 00:00:00',
strtotime($filter_date_end . ' +1 day')
);

$query .= " AND logs.entry_time >= ? AND logs.entry_time < ?";

$params[] = $startDate;
$params[] = $endDate;
$types .= 'ss';

} elseif (!empty($filter_date_start)) {
$startDate = $filter_date_start . ' 00:00:00';

$query .= " AND logs.entry_time >= ?";

$params[] = $startDate;
$types .= 's';

} elseif (!empty($filter_date_end)) {
$endDate = date(
'Y-m-d 00:00:00',
strtotime($filter_date_end . ' +1 day')
);

$query .= " AND logs.entry_time < ?";

$params[] = $endDate;
$types .= 's';
}

if (!empty($filter_course)) {
$query .= " AND programs.program_code = ?";

$params[] = $filter_course;
$types .= 's';
}

$query .= " ORDER BY logs.entry_time DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
$stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$programs_query = "
SELECT program_code, program_name
FROM programs
ORDER BY program_name
";

$programs_result = $conn->query($programs_query);

$selected_program_name = '';

if (!empty($filter_course)) {
$program_lookup = $conn->prepare(
"SELECT program_name
FROM programs
WHERE program_code = ?
LIMIT 1"
);

$program_lookup->bind_param('s', $filter_course);
$program_lookup->execute();

$program_lookup_result = $program_lookup->get_result();

if ($program_lookup_result->num_rows > 0) {
$selected_program = $program_lookup_result->fetch_assoc();
$selected_program_name = $selected_program['program_name'];
}

$program_lookup->close();
}
?>

<?php include('includes/navbar.php'); ?>
<?php include('includes/right_sidebar.php'); ?>
<?php include('includes/left_sidebar.php'); ?>

<body>

	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10 main-body">
			<div class="min-height-200px">

				<div class="page-header">
					<div class="row">
						<div class="col-md-12 d-flex justify-content-between align-items-center">
							<div class="title">
								<h4>History Logs</h4>
							</div>

							<button type="button" class="btn btn-primary" onclick="printReport()">
								<i class="fa fa-print"></i>
								Print
							</button>
						</div>
					</div>

					<nav aria-label="breadcrumb" role="navigation">
						<ol class="breadcrumb">
							<li class="breadcrumb-item">
								<a href="admin_dashboard.php">
									Dashboard
								</a>
							</li>

							<li class="breadcrumb-item active">
								Logs Overview
							</li>
						</ol>
					</nav>

					<form action="history_logs.php" method="GET" id="filterForm">
						<div class="form-row pt-20">

							<div class="col-md-4">
								<label for="filter_date_start">
									Start Date:
								</label>

								<input type="date" id="filter_date_start" name="filter_date_start" class="form-control"
									value="<?php echo htmlspecialchars($filter_date_start); ?>" onchange="submitForm()">
							</div>

							<div class="col-md-4">
								<label for="filter_date_end">
									End Date:
								</label>

								<input type="date" id="filter_date_end" name="filter_date_end" class="form-control"
									value="<?php echo htmlspecialchars($filter_date_end); ?>" onchange="submitForm()">
							</div>

							<div class="col-md-4">
								<label for="course_type">
									Department:
								</label>

								<select name="course_type" id="course_type" class="custom-select form-control"
									onchange="submitForm()">
									<option value="">
										Select Department
									</option>

									<?php while ($program = $programs_result->fetch_assoc()): ?>
									<option value="<?php echo htmlspecialchars($program['program_code']); ?>"
										<?php echo ($filter_course == $program['program_code']) ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($program['program_name']); ?>
									</option>
									<?php endwhile; ?>
								</select>
							</div>

						</div>
					</form>
				</div>

				<div class="row mb-30">
					<div class="col-md-12">

						<div class="card-box pd-20 height-100-p">

							<?php if ($result->num_rows > 0): ?>

							<div id="print-section">

								<div id="school-info">
									<img src="../admin/admin/uploads/logo.png" alt="Talibon Polytechnic College Logo"
										class="school-logo">

									<h2>Talibon Polytechnic College</h2>

									<p>
										San Isidro, Talibon, Bohol
									</p>

									<p>
										talibonpolytechniccollegetalib@gmail.com
									</p>

									<h4>
										ATTENDANCE HISTORY LOGS
									</h4>
								</div>

								<div class="report-info">

									<?php if (
										!empty($filter_date_start) ||
										!empty($filter_date_end)
									): ?>

									<p>
										<strong>Date Range:</strong>

										<?php
											if (!empty($filter_date_start)) {
												echo date(
													"M. j, Y",
													strtotime($filter_date_start)
												);
											} else {
												echo 'All';
											}
										?>

										-

										<?php
											if (!empty($filter_date_end)) {
												echo date(
													"M. j, Y",
													strtotime($filter_date_end)
												);
											} else {
												echo 'All';
											}
										?>
									</p>

									<?php endif; ?>

									<?php if (!empty($filter_course)): ?>
									<p>
										<strong>Department:</strong>
										<?php echo htmlspecialchars($selected_program_name); ?>
									</p>
									<?php endif; ?>
									<p>
										<strong>Printed on:</strong>
										<?php echo date('F j, Y h:i A'); ?>
									</p>
								</div>
								<div class="table-container">
									<table class="attendance-table">
										<thead>
											<tr>
												<th>Student ID</th>
												<th>Student Name</th>
												<th>Department</th>
												<th>Entry Time</th>
												<th>Entry Date</th>
												<th>Exit Time</th>
												<th>Exit Date</th>
											</tr>
										</thead>

										<tbody>

											<?php while ($row = $result->fetch_assoc()): ?>

											<tr>

												<td>
													<?php echo htmlspecialchars($row['student_id']); ?>
												</td>

												<td>
													<?php echo htmlspecialchars($row['name']); ?>
												</td>

												<td>
													<?php echo htmlspecialchars($row['course_type']); ?>
												</td>

												<td>
													<?php echo htmlspecialchars($row['entry_time']); ?>
												</td>

												<td>
													<?php
														if (!empty($row['entry_date'])) {
															echo date(
																"M. j, Y",
																strtotime($row['entry_date'])
															);
														} else {
															echo 'N/A';
														}
													?>
												</td>

												<td>
													<?php
														if (!empty($row['exit_time'])) {
															echo htmlspecialchars($row['exit_time']);
														} else {
															echo 'N/A';
														}
													?>
												</td>

												<td>
													<?php
														if (!empty($row['exit_date'])) {
															echo date(
																"M. j, Y",
																strtotime($row['exit_date'])
															);
														} else {
															echo 'N/A';
														}
													?>
												</td>

											</tr>

											<?php endwhile; ?>

										</tbody>
									</table>

								</div>

								<div class="report-footer">
									<p>
										Total Records:
										<strong>
											<?php echo $result->num_rows; ?>
										</strong>
									</p>
								</div>

							</div>

							<?php else: ?>

							<div id="school-info">
								<img src="../admin/admin/uploads/logo.png" alt="Talibon Polytechnic College Logo"
									class="school-logo">

								<h2>Talibon Polytechnic College</h2>

								<p>
									San Isidro, Talibon, Bohol
								</p>

								<p>
									talibonpolytechniccollegetalib@gmail.com
								</p>

								<h4>
									ATTENDANCE HISTORY LOGS
								</h4>
							</div>
							<div class="table-container">
								<table class="attendance-table">
									<thead>
										<tr>
											<th>Student ID</th>
											<th>Student Name</th>
											<th>Department</th>
											<th>Entry Time</th>
											<th>Entry Date</th>
											<th>Exit Time</th>
											<th>Exit Date</th>
										</tr>
									</thead>

									<tbody>
										<tr>
											<td colspan="7">
												No attendance records found.
											</td>
										</tr>
									</tbody>
								</table>

							</div>

							<div class="report-footer">
								<p>
									Total Records:
									<strong>
										<?php echo $result->num_rows; ?>
									</strong>
								</p>
							</div>
							<?php endif; ?>

						</div>

					</div>
				</div>

			</div>
		</div>
	</div>

	<script>
	function submitForm() {
		document.getElementById("filterForm").submit();
	}

	function printReport() {
		const printSection = document.getElementById("print-section");

		if (!printSection) {
			alert("No attendance records available to print.");
			return;
		}

		const printContent = printSection.innerHTML;

		const printWindow = window.open(
			"",
			"_blank",
			"width=900,height=1100,scrollbars=yes,resizable=yes"
		);

		if (!printWindow) {
			alert(
				"The print window was blocked by your browser. Please allow pop-ups for this website."
			);
			return;
		}

		printWindow.document.open();

		printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Attendance History Logs</title>

<style>
@page {
size: A4 portrait;
margin: 10mm;
}

* {
box-sizing: border-box;
}

html,
body {
margin: 0;
padding: 0;
width: 100%;
background: #ffffff;
color: #000000;
font-family: Arial, Helvetica, sans-serif;
}

body {
padding: 0;
}

#print-section {
width: 100%;
margin: 0 auto;
}

#school-info {
width: 100%;
text-align: center;
margin-bottom: 15px;
}

#school-info img {
display: block;
width: 70px;
height: 70px;
object-fit: contain;
margin: 0 auto 6px auto;
}

#school-info h2 {
margin: 3px 0;
font-size: 20px;
font-weight: bold;
}

#school-info p {
margin: 2px 0;
font-size: 11px;
}

#school-info h4 {
margin: 10px 0 0 0;
font-size: 15px;
font-weight: bold;
}

.report-info {
width: 100%;
text-align: center;
margin-bottom: 12px;
}

.report-info p {
margin: 3px 0;
font-size: 10px;
}

.table-container {
width: 100%;
margin: 0;
padding: 0;
overflow: visible;
}

.attendance-table {
width: 100%;
border-collapse: collapse;
border-spacing: 0;
table-layout: fixed;
}

.attendance-table th,
.attendance-table td {
border: 1px solid #000000;
padding: 5px 3px;
text-align: center;
vertical-align: middle;
font-size: 8.5px;
line-height: 1.2;
word-wrap: break-word;
overflow-wrap: break-word;
}

.attendance-table th {
font-weight: bold;
background: #eeeeee;
}

.attendance-table th:nth-child(1) {
width: 13%;
}

.attendance-table th:nth-child(2) {
width: 20%;
}

.attendance-table th:nth-child(3) {
width: 19%;
}

.attendance-table th:nth-child(4) {
width: 11%;
}

.attendance-table th:nth-child(5) {
width: 12%;
}

.attendance-table th:nth-child(6) {
width: 11%;
}

.attendance-table th:nth-child(7) {
width: 14%;
}

.attendance-table thead {
display: table-header-group;
}

.attendance-table tbody {
display: table-row-group;
}

.attendance-table tr {
page-break-inside: avoid;
break-inside: avoid;
}

.attendance-table th,
.attendance-table td {
page-break-inside: avoid;
break-inside: avoid;
}

.report-footer {
width: 100%;
margin-top: 10px;
text-align: right;
font-size: 10px;
}

.report-footer p {
margin: 0;
}
</style>
</head>

<body>
<div id="print-section">
${printContent}
</div>
</body>
</html>
`);

		printWindow.document.close();

		setTimeout(function() {
			printWindow.focus();
			printWindow.print();
		}, 800);
	}
	</script>

	<style>
	#school-info {
		text-align: center;
		margin-bottom: 20px;
	}

	.school-logo {
		width: 90px;
		height: 90px;
		object-fit: contain;
		margin-bottom: 8px;
	}

	#school-info h2 {
		margin: 5px 0;
		font-size: 24px;
	}

	#school-info p {
		margin: 2px 0;
		font-size: 14px;
	}

	#school-info h4 {
		margin-top: 15px;
		font-size: 18px;
	}

	.report-info {
		text-align: center;
		margin-bottom: 20px;
	}

	.report-info p {
		margin: 3px 0;
		font-size: 13px;
	}

	.table-container {
		width: 100%;
		overflow-x: auto;
	}

	.attendance-table {
		width: 100%;
		border-collapse: collapse;
		table-layout: auto;
	}

	.attendance-table th,
	.attendance-table td {
		border: 1px solid #000;
		padding: 7px;
		text-align: center;
		vertical-align: middle;
		font-size: 12px;
	}

	.attendance-table th {
		font-weight: bold;
	}

	.report-footer {
		margin-top: 15px;
		text-align: right;
		font-size: 13px;
	}
	</style>

	<?php include('includes/footer.php'); ?>
	<?php include('includes/scripts.php'); ?>

</body>

</html>