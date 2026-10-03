<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>TPC Security and Monitoring System</title>
	<!-- Site favicon -->
	<link rel="icon" type="image/png" href="asset/layout/log.png" sizes="16x16">
	<link rel="icon" type="image/png" href="asset/layout/log.png" sizes="32x32">
	<link rel="icon" type="image/png" href="asset/layout/log.png" sizes="48x48">
	<link rel="shortcut icon" href="asset/layout/log.png">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">

	<style>
	* {
		margin: 0;
		padding: 0;
		box-sizing: border-box;
	}

	body {
		overflow-x: hidden;
	}

	.main-body {
		position: relative;
		overflow: hidden;
		min-height: calc(100vh - 71px);
	}

	.main-body::before {
		content: '';
		position: absolute;
		inset: 0;

		background-image: url('asset/images/logo.png');
		background-repeat: no-repeat;
		background-position: center;
		background-size: contain;

		filter: blur(3px);
		opacity: 0.15;

		z-index: 0;
	}

	.main-body>* {
		position: relative;
		z-index: 1;
	}

	@keyframes pulse-ring {

		0%,
		100% {
			box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.3);
		}

		50% {
			box-shadow: 0 0 0 12px rgba(46, 204, 113, 0);
		}
	}
	</style>
</head>

<body>
	<nav class="navbar navbar-expand-lg" style="background-color: #1a6b3c;">
		<div class="container-fluid">
			<div class="d-flex align-items-center gap-2">
				<img src="asset/images/logo.png" alt="Your Logo" height="55">
				<h5 class="text-white d-none d-md-block">Talibon Polytechnic College Security and Monitoring System</h5>
			</div>
			<div class="d-flex align-items-center gap-2">
				<a class="btn btn-outline-light" href="/NFC_SYSTEM/student_system/" title="Exit">
					<i class="fa-solid fa-share-from-square mr-2"></i> <!-- Logout icon -->
				</a>
			</div>
		</div>
	</nav>
	<div class="container-fluid main-body py-3">
		<div class="row">
			<div class="col-lg-6 col-md-12">
				<div class="card h-100 px-1">
					<div class="card-body">
						<div class="row">
							<div class="col-12 px-0">
								<div class="card">
									<div class="card-header" style="background-color: #1a6b3c;">
										<span class="text-white">
											<i class="fa-solid fa-barcode"></i>
											Last scanned student
										</span>
									</div>
									<div class="card-body">
										<div class="row">
											<div class="col-lg-6 col-md-6">
												<img class="img-thumbnail rounded student-image"
													src="admin/uploads/profile_pics/NO-IMAGE-AVAILABLE.jpg"
													alt="Student Image"
													style="width: 100%; height: 450px; object-fit: cover;">
											</div>
											<div class="col-lg-6 col-md-6 d-flex flex-column">
												<div class="row">
													<div class="col-12 text-center">
														<h1 class="fw-bold">
															Student Details
														</h1>
													</div>
													<hr>
													<div class="col-12">
														<span class="fs-4 fw-bold">
															<i class="fa-solid fa-user"></i>
															<span class="student-name">Sample Sample</span>
														</span>
													</div>
													<div class="col-12 mt-2">
														<span class="fs-4 fw-bold">
															<i class="fa-solid fa-id-card"></i>
															<span class="student-id">123456</span>
														</span>
													</div>
													<div class="col-12 mt-2">
														<span class="fs-4 fw-bold">
															<i class="fa-solid fa-graduation-cap"></i>
															<span class="student-course">BSIS</span>
														</span>
													</div>
													<div class="col-12 mt-2">
														<span class="fs-4 fw-bold">
															<i class="fa-solid fa-clock"></i>
															<span class="student-time">Entry: May 25, 2023 12:00
																AM</span>
														</span>
													</div>
													<div class="col-12 mt-2">
														<span class="fs-4 fw-bold">
															<i class="fa-solid fa-arrow-right-to-bracket"></i>
															Today entries: <span class="student-entries">0</span>
														</span>
													</div>
													<div class="col-12 mt-2">
														<span class="fs-4 fw-bold">
															<i class="fa-solid fa-arrow-right-from-bracket"></i>
															Today exits: <span class="student-exits">0</span>
														</span>
													</div>
													<div class="col-12 mt-2">
														<span class="fs-4 fw-bold text-success">
															<i class="fa-solid fa-circle-check"></i>
															Status: Active
														</span>
													</div>
													<div class="col-12 mt-2">
														<span class="fs-4 fw-bold text-danger">
															<i class="fa-solid fa-clipboard-list"></i>
															<span class="student-logs">0</span> logs today
														</span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row mt-3">
							<div class="col-12 px-0">
								<div class="card">
									<div class="card-header" style="background-color: #1a6b3c;">
										<span class="text-white">
											<i class="fa-solid fa-clipboard-list"></i>
											Recent activity
										</span>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table class="table table-striped table-hover table-bordered text-center">
												<thead>
													<tr>
														<th scope="col">Student Name</th>
														<th scope="col">Date</th>
														<th class="d-none d-md-table-cell" scope="col">Action</th>
													</tr>
												</thead>
												<tbody id="recent-activity-body">
													<tr>
														<td colspan="3">No Recent Activity</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-12">
				<div class="card h-100">
					<div class="card-body p-3 d-flex flex-column">
						<!-- Stat Cards -->
						<div class="row g-2 mb-3">
							<div class="col-12 col-md-4">
								<div
									class="rounded-3 p-3 bg-danger d-flex flex-column justify-content-center align-items-center">
									<small class="text-white">
										<i class="fa-solid fa-clipboard-list me-1"></i>
										Total logs
									</small>
									<h2 class="text-white fw-bold mb-0" id="on-campus-val">0</h2>
									<small class="text-white">today so far</small>
								</div>
							</div>
							<div class="col-12 col-md-4">
								<div
									class="rounded-3 p-3 bg-success d-flex flex-column justify-content-center align-items-center">
									<small class="text-white"><i
											class="fa-solid fa-arrow-right-to-bracket me-1"></i>Total entries</small>
									<h2 class="text-white fw-bold mb-0" id="entries-val">0</h2>
									<small class="text-white">today so far</small>
								</div>
							</div>
							<div class="col-12 col-md-4">
								<div
									class="rounded-3 p-3 bg-primary d-flex flex-column justify-content-center align-items-center">
									<small class="text-white"><i
											class="fa-solid fa-arrow-right-from-bracket me-1"></i>Total exits</small>
									<h2 class="text-white fw-bold mb-0" id="exits-val">0</h2>
									<small class="text-white">today so far</small>
								</div>
							</div>
						</div>

						<!-- Clock -->
						<div class="rounded-3 p-4 text-center mb-3" style="background:#2a2a2c;">
							<p class="text-white mb-1" id="clock-date">June 10, 2026</p>
							<h1 class="text-white fw-bold mb-0" id="clock-time">9:27:43 AM</h1>
						</div>

						<!-- NFC Scanner -->
						<div class="rounded-3 p-4 text-center d-flex flex-column align-items-center justify-content-center border border-2 border-success"
							style="flex-grow: 1; border-style: dashed !important;">
							<div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
								style="width:80px;height:80px;border-radius:50%;border:2px dashed #2ecc71;animation:pulse-ring 2s ease-in-out infinite;">
								<i class="fa-solid fa-wifi fs-2 text-success"></i>
							</div>
							<h5 class="fw-semibold">Tap your NFC ID card</h5>
							<p class="text-secondary small">Hold your card near the reader to log entry or exit</p>
							<span class="text-success small">
								<span class="spinner-grow spinner-grow-sm me-1"></span> Reader online — ready to scan
							</span>
							<input id="nfc-input" type="text" autocomplete="off"
								style="position:absolute;opacity:0;pointer-events:none;width:1px;height:1px;left:-9999px;" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
	function updateClock() {
		const now = new Date();
		document.getElementById('clock-date').textContent = now.toLocaleDateString('en-US', {
			year: 'numeric',
			month: 'long',
			day: 'numeric'
		});
		document.getElementById('clock-time').textContent = now.toLocaleTimeString('en-US', {
			hour: 'numeric',
			minute: '2-digit',
			second: '2-digit',
			hour12: true
		});
	}
	updateClock();
	setInterval(updateClock, 1000);

	const nfcInput = document.getElementById('nfc-input');
	let buffer = '';
	let lastKeyTime = 0;

	function focusNfc() {
		nfcInput.focus();
	}
	focusNfc();
	document.addEventListener('click', () => focusNfc());

	nfcInput.addEventListener('keydown', e => {
		const now = Date.now();
		const gap = now - lastKeyTime;
		lastKeyTime = now;

		if (e.key === 'Enter') {
			const data = buffer.trim();
			buffer = '';
			if (data.length > 0) handleScan(data);
			return;
		}

		if (e.key.length === 1) {
			if (buffer === '' || gap < 100) {
				buffer += e.key;
			} else {
				buffer = e.key;
			}
		}
	});

	function handleScan(uid) {
		Swal.fire({
			title: 'Scanning...',
			text: 'Please wait',
			allowOutsideClick: false,
			allowEscapeKey: false,
			showConfirmButton: false,
			width: '280px',
			didOpen: () => {
				Swal.showLoading();
			}
		});

		fetch('nfc_scan.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					uid: uid
				})
			})
			.then(async response => {
				const text = await response.text();
				try {
					return JSON.parse(text);
				} catch (error) {
					throw new Error('Invalid response from server: ' + text);
				}
			})
			.then(res => {

				if (!res.found) {
					Swal.fire({
						icon: 'error',
						title: 'Student Not Found',
						text: res.message || 'No student is registered with this NFC ID.',
						timer: 1000,
						showConfirmButton: false,
						timerProgressBar: true,
						width: '320px'
					});

					return;
				}

				if (res.type === 'too_soon') {
					Swal.fire({
						icon: 'warning',
						title: 'Please Wait',
						text: res.message,
						timer: 1000,
						showConfirmButton: false,
						timerProgressBar: true,
						width: '320px'
					});

					return;
				}

				if (res.type === 'already_completed') {
					Swal.fire({
						icon: 'info',
						title: 'Already Completed',
						text: res.message,
						timer: 1000,
						showConfirmButton: false,
						timerProgressBar: true,
						width: '320px'
					});

					return;
				}

				if (!res.student_stats || !res.dashboard_stats) {
					throw new Error('Incomplete response from server.');
				}

				document.querySelector('.student-name').textContent = res.name;
				document.querySelector('.student-id').textContent = res.student_id;
				document.querySelector('.student-course').textContent = res.course;
				document.querySelector('.student-image').src = res.picture;
				document.querySelector('.student-time').textContent = res.time_label;

				document.querySelector('.student-entries').textContent =
					res.student_stats.entries;

				document.querySelector('.student-exits').textContent =
					res.student_stats.exits;

				document.querySelector('.student-logs').textContent =
					res.student_stats.logs;

				document.getElementById('on-campus-val').textContent =
					res.dashboard_stats.total_logs;

				document.getElementById('entries-val').textContent =
					res.dashboard_stats.total_entries;

				document.getElementById('exits-val').textContent =
					res.dashboard_stats.total_exits;

				const tbody = document.getElementById('recent-activity-body');

				tbody.innerHTML = '';

				if (res.recent_activities && res.recent_activities.length > 0) {

					res.recent_activities.forEach(activity => {

						const badgeClass =
							activity.type === 'Entry' ?
							'text-success border-success' :
							'text-danger border-danger';

						const icon =
							activity.type === 'Entry' ?
							'fa-arrow-right-to-bracket' :
							'fa-arrow-right-from-bracket';

						tbody.innerHTML += `
                    <tr>
                        <td>${activity.name}</td>
                        <td>${activity.time}</td>
                        <td class="d-none d-md-table-cell">
                            <span class="${badgeClass} fw-bold bg-white border px-3 py-1 rounded-pill">
                                <i class="fa-solid ${icon}"></i>
                                ${activity.type}
                            </span>
                        </td>
                    </tr>
                `;
					});

				} else {

					tbody.innerHTML = `
                <tr>
                    <td colspan="3">No Recent Activity</td>
                </tr>
            `;
				}

				if (res.type === 'entry') {

					Swal.fire({
						icon: 'success',
						title: 'Entry Recorded',
						timer: 1000,
						showConfirmButton: false,
						timerProgressBar: true,
						width: '320px'
					});

				} else if (res.type === 'exit') {

					Swal.fire({
						icon: 'success',
						title: 'Exit Recorded',
						timer: 1000,
						showConfirmButton: false,
						timerProgressBar: true,
						width: '320px'
					});
				}

			})
			.catch(err => {

				Swal.fire({
					icon: 'error',
					title: 'Scan Failed',
					text: err.message || 'Unable to process the NFC scan.',
					timer: 1000,
					showConfirmButton: false,
					timerProgressBar: true,
					width: '320px'
				});
			});
	}
	</script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
	</script>
</body>

</html>