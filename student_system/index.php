<?php

include 'db.php';

$today = date('Y-m-d');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TPC Security & Monitoring System</title>
    <!-- Site favicon -->
    <link rel="icon" type="image/png" href="asset/layout/log.png" sizes="16x16">
    <link rel="icon" type="image/png" href="asset/layout/log.png" sizes="32x32">
    <link rel="icon" type="image/png" href="asset/layout/log.png" sizes="48x48">
    <link rel="shortcut icon" href="asset/layout/log.png">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">

    <link rel="stylesheet" href="asset/style_landing.css" />
</head>

<body>

    <!-- NAVBAR -->
    <nav>
        <a href="#" class="nav-brand">
            <div class="nav-logo">
                <img src="asset/images/logo.png" alt="Your Logo" height="55">
            </div>
            <div class="nav-title">
                Security & Monitoring System
                <span>Talibon Polytechnic College</span>
            </div>
        </a>
        <div class="nav-actions">
            <a href="student_portal.php" class="btn-nav btn-ghost">Student NFC</a>
            <a href="login.php" class="btn-nav btn-gold">
                Login <i class="fa-solid fa-right-to-bracket"></i>
            </a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-inner">
            <div class="hero-content">
                <div class="hero-eyebrow">
                    <span class="dot"></span>
                    System Active &nbsp;·&nbsp; Real-time Monitoring
                </div>
                <h1 class="hero-heading">
                    Smarter Campus<br>
                    <span class="accent">Gate Security</span><br>
                    for Every Student
                </h1>
                <p class="hero-desc">
                    Monitor student entries and exits at the school gate in real time. NFC ID card scanning keeps every movement logged, timestamped, and instantly accessible to school administrators.
                </p>
                <div class="hero-cta">
                    <a href="#how-it-works" class="btn-primary">
                        How it works
                    </a>
                </div>
            </div>

            <!-- Live monitor mockup -->
            <div class="hero-visual">
                <div class="monitor-card">
                    <div class="monitor-header">
                        <span class="monitor-title">Gate Activity</span>
                        <div class="live-badge"><span class="dot"></span>Live</div>
                    </div>
                    <?php

                    $totalEntries = $conn->query("
                                        SELECT COUNT(*) as total
                                        FROM logs
                                        WHERE DATE(entry_time) = '$today'
                                    ")->fetch_assoc()['total'];

                    $totalExits = $conn->query("
                                        SELECT COUNT(*) as total
                                        FROM logs
                                        WHERE DATE(exit_time) = '$today'
                                    ")->fetch_assoc()['total'];

                    $totalLogs = $totalEntries + $totalExits;

                    $recentActivities = [];

                    $activityQuery = $conn->query("
                            (
                                SELECT
                                    student_name,
                                    student_id,
                                    entry_time as activity_time,
                                    'In' as activity_type
                                FROM logs
                                WHERE entry_time IS NOT NULL
                            )

                            UNION ALL

                            (
                                SELECT
                                    student_name,
                                    student_id,
                                    exit_time as activity_time,
                                    'Out' as activity_type
                                FROM logs
                                WHERE exit_time IS NOT NULL
                            )

                            ORDER BY activity_time DESC
                            LIMIT 3
                        ");

                    while ($row = $activityQuery->fetch_assoc()) {
                        $recentActivities[] = [
                            'name' => $row['student_name'],
                            'student_id' => $row['student_id'],
                            'time' => date(
                                'F j, Y h:i A',
                                strtotime($row['activity_time'])
                            ),
                            'type' => $row['activity_type']
                        ];
                    }

                    ?>
                    <div class="stat-row">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $totalEntries; ?></div>
                            <div class="stat-label">Entered</div>
                        </div>
                        <div class="stat-box gold">
                            <div class="stat-number"><?php echo $totalLogs; ?></div>
                            <div class="stat-label">On Campus</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $totalExits; ?></div>
                            <div class="stat-label">Exited</div>
                        </div>
                    </div>
                    <div class="log-list">
                        <?php
                        foreach ($recentActivities as $recentActivity) {
                        ?>
                            <div class="log-item">
                                <div class="log-avatar in">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="log-info">
                                    <div class="log-name"><?php echo $recentActivity['name']; ?></div>
                                    <div class="log-time"><?php echo $recentActivity['time']; ?></div>
                                </div>
                                <span class="log-tag <?php echo $recentActivity['type'] == 'In' ? 'in' : 'out'; ?> in"><?php echo $recentActivity['type']; ?></span>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS BAND -->
    <div class="stats-band">
        <div class="stats-band-inner">
            <div class="stat-item">
                <div class="num">1,000<span class="unit">+</span></div>
                <div class="desc">Students Enrolled</div>
            </div>
            <div class="stat-item">
                <div class="num">100<span class="unit">%</span></div>
                <div class="desc">NFC Entry Coverage</div>
            </div>
            <div class="stat-item">
                <div class="num">24<span class="unit">/7</span></div>
                <div class="desc">Monitoring Uptime</div>
            </div>
            <div class="stat-item">
                <div class="num">&lt;1<span class="unit">s</span></div>
                <div class="desc">Scan Response Time</div>
            </div>
        </div>
    </div>

    <!-- FEATURES -->
    <section id="features">
        <div class="section">
            <p class="section-eyebrow">Capabilities</p>
            <h2 class="section-heading">Everything security staff need, in one place</h2>
            <p class="section-sub">Designed specifically for educational institutions that need reliable, tamper-resistant access records without the complexity of enterprise systems.</p>

            <div class="features-grid">
                <div class="feat-card">
                    <div class="feat-icon">📡</div>
                    <div class="feat-title">NFC Card Scanning</div>
                    <p class="feat-desc">Students tap their NFC-enabled ID cards at the gate. The system logs name, time and date no manual entry needed.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">🕐</div>
                    <div class="feat-title">Real-time Timestamps</div>
                    <p class="feat-desc">Every entry and exit is recorded with an exact timestamp. Administrators can pull up the full gate log for any date, any time.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">📊</div>
                    <div class="feat-title">Attendance Analytics</div>
                    <p class="feat-desc">View daily, weekly, and monthly attendance summaries. Spot patterns, identify absences, and generate reports for class advisers.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">🔍</div>
                    <div class="feat-title">Student Lookup</div>
                    <p class="feat-desc">Search any student by name or ID to see their complete movement history on campus useful for investigation or parent inquiries.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">🔔</div>
                    <div class="feat-title">Instant Alerts</div>
                    <p class="feat-desc">Get notified when an unknown card is scanned, a card is flagged, or a student leaves during class hours.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">🔐</div>
                    <div class="feat-title">Role-based Access</div>
                    <p class="feat-desc">Guard, admin, and superadmin roles with different permission levels. Each role sees exactly what they need and nothing they shouldn't.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how-section" id="how-it-works">
        <div class="how-inner">
            <p class="section-eyebrow">How It Works</p>
            <h2 class="section-heading">From tap to record in under a second</h2>
            <p class="section-sub">A simple, reliable four-step flow that keeps gate operations smooth and every movement accounted for.</p>

            <div class="steps-row">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-title">Student Taps Card</div>
                    <p class="step-desc">Student holds their NFC ID near the reader at the school gate.</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-title">System Identifies</div>
                    <p class="step-desc">Card UID is matched to the student database in real time.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-title">Entry is Logged</div>
                    <p class="step-desc">Name, ID, time and date are saved automatically.</p>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <div class="step-title">Admin is Notified</div>
                    <p class="step-desc">The dashboard updates live so security staff stay informed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- NFC DETAIL -->
    <section class="nfc-section">
        <div class="nfc-inner">
            <div>
                <p class="section-eyebrow">NFC Technology</p>
                <h2 class="section-heading">Your student ID is your key</h2>
                <p class="section-sub">No apps, no pins, no lining up. A single tap of the NFC-enabled school ID is all it takes to log entry or exit.</p>
                <ul class="checklist">
                    <li><span class="check-icon">✓</span> Works with standard NFC-enabled ID cards issued by the school</li>
                    <li><span class="check-icon">✓</span> Responds in under 1 second no slowdown at peak hours</li>
                    <li><span class="check-icon">✓</span> Card UIDs are encrypted; personal data never stored on the card</li>
                    <li><span class="check-icon">✓</span> Lost or stolen cards can be deactivated instantly from the admin panel</li>
                    <li><span class="check-icon">✓</span> Works offline entries are queued and synced when connection restores</li>
                </ul>
            </div>
            <div class="nfc-visual">
                <div class="nfc-mockup">
                    <div class="nfc-rings">
                        <div class="ring"></div>
                        <div class="ring"></div>
                        <div class="ring"></div>
                        <div class="nfc-core">📶</div>
                    </div>
                    <div class="nfc-label">NFC Reader Active</div>
                    <div class="nfc-sub">Tap your ID card to proceed</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-inner">
            <div class="footer-brand">
                <p>Talibon Polytechnic College Security and Monitoring System. Keeping campus safe through reliable, real-time student access records.</p>
            </div>
            <div class="footer-col">
                <h4>System</h4>
                <div>Dashboard</div>
                <div>Student Records</div>
                <div>Attendace Logs</div>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <div>User Guide</div>
                <div>Contact Admin</div>
                <div>System Status</div>
            </div>
        </div>
        <div class="footer-bottom" style="max-width:1180px;margin:0 auto;padding:20px 40px;border-top:1px solid rgba(255,255,255,.07);display:flex;justify-content:space-between;align-items:center;font-size:12px;">
            <span>© 2026 Talibon Polytechnic College. All rights reserved.</span>
            <span>Security & Monitoring System v2.0</span>
        </div>
    </footer>

</body>

</html>