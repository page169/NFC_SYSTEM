<?php

ob_start();

header('Content-Type: application/json; charset=utf-8');

include './db.php';
include './sms.php';

date_default_timezone_set('Asia/Manila');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['uid']) || empty($data['uid'])) {
    ob_clean();

    echo json_encode([
        'found' => false,
        'message' => 'No UID'
    ]);

    exit;
}

$uid = $conn->real_escape_string(trim($data['uid']));
$currentTime = date('Y-m-d H:i:s');
$today = date('Y-m-d');

$query = "
    SELECT students.*, programs.program_name, programs.program_code
    FROM students
    JOIN programs ON students.program_id = programs.id
    WHERE students.uid = '$uid'
    LIMIT 1
";

$result = $conn->query($query);

if (!$result || $result->num_rows == 0) {
    ob_clean();

    echo json_encode([
        'found' => false,
        'message' => 'Student not found'
    ]);

    exit;
}

$student = $result->fetch_assoc();

$studentName = $conn->real_escape_string($student['name']);
$studentId = $conn->real_escape_string($student['student_id']);
$pictureUrl = $conn->real_escape_string($student['picture_url']);
$programId = (int)$student['program_id'];

$logQuery = $conn->query("
    SELECT id, entry_time, exit_time
    FROM logs
    WHERE student_uid = '$uid'
    AND DATE(entry_time) = '$today'
    ORDER BY id DESC
    LIMIT 1
");

if (!$logQuery) {
    ob_clean();

    echo json_encode([
        'found' => false,
        'message' => 'Failed to check attendance log: ' . $conn->error
    ]);

    exit;
}

if ($logQuery->num_rows == 0) {

    $insert = $conn->query("
        INSERT INTO logs
        (
            student_uid,
            entry_time,
            student_name,
            student_id,
            picture_url,
            program_id
        )
        VALUES
        (
            '$uid',
            '$currentTime',
            '$studentName',
            '$studentId',
            '$pictureUrl',
            '$programId'
        )
    ");

    if (!$insert) {
        ob_clean();

        echo json_encode([
            'found' => false,
            'message' => 'Failed to record entry: ' . $conn->error
        ]);

        exit;
    }

    $type = 'entry';

} else {

    $log = $logQuery->fetch_assoc();

    if ($log['exit_time'] === null) {

        $lastTime = strtotime($log['entry_time']);
        $currentTimestamp = strtotime($currentTime);
        $diff = $currentTimestamp - $lastTime;

        if ($diff < 10) {

            ob_clean();

            echo json_encode([
                'found' => true,
                'type' => 'too_soon',
                'message' => 'Please wait ' . (10 - $diff) . ' seconds before tapping for Exit.'
            ]);

            exit;
        }

        $update = $conn->query("
            UPDATE logs
            SET exit_time = '$currentTime'
            WHERE id = {$log['id']}
            AND student_uid = '$uid'
            AND exit_time IS NULL
        ");

        if (!$update) {
            ob_clean();

            echo json_encode([
                'found' => false,
                'message' => 'Failed to record exit: ' . $conn->error
            ]);

            exit;
        }

        $type = 'exit';

    } else {

        $update = $conn->query("
            UPDATE logs
            SET
                entry_time = '$currentTime',
                exit_time = NULL
            WHERE id = {$log['id']}
            AND student_uid = '$uid'
        ");

        if (!$update) {
            ob_clean();

            echo json_encode([
                'found' => false,
                'message' => 'Failed to record new entry: ' . $conn->error
            ]);

            exit;
        }

        $type = 'entry';
    }
}

$response = buildResponse(
    $conn,
    $uid,
    $student,
    $currentTime,
    $type
);

if (!empty($student['phone'])) {

    if ($type === 'entry') {

        $entryTime = date(
            'F j, Y h:i A',
            strtotime($currentTime)
        );

        $msg = "Hi {$student['parent_name']}, {$student['name']} ({$student['student_id']}) has ENTERED the campus on {$entryTime}.";

    } else {

        $exitTime = date(
            'F j, Y h:i A',
            strtotime($currentTime)
        );

        $msg = "Hi {$student['parent_name']}, {$student['name']} ({$student['student_id']}) has EXITED the campus on {$exitTime}.";
    }

    sendSMS($student['phone'], $msg);
}

ob_clean();

echo json_encode($response);

exit;


function buildResponse($conn, $uid, $student, $currentTime, $type)
{
    $today = date('Y-m-d');

    $studentEntriesQuery = $conn->query("
        SELECT COUNT(*) AS total
        FROM logs
        WHERE student_uid = '$uid'
        AND DATE(entry_time) = '$today'
    ");

    $studentExitsQuery = $conn->query("
        SELECT COUNT(*) AS total
        FROM logs
        WHERE student_uid = '$uid'
        AND DATE(exit_time) = '$today'
    ");

    $studentEntries = 0;
    $studentExits = 0;

    if ($studentEntriesQuery) {
        $studentEntries = (int)$studentEntriesQuery->fetch_assoc()['total'];
    }

    if ($studentExitsQuery) {
        $studentExits = (int)$studentExitsQuery->fetch_assoc()['total'];
    }

    $studentLogs = $studentEntries + $studentExits;

    $totalEntriesQuery = $conn->query("
        SELECT COUNT(*) AS total
        FROM logs
        WHERE DATE(entry_time) = '$today'
    ");

    $totalExitsQuery = $conn->query("
        SELECT COUNT(*) AS total
        FROM logs
        WHERE DATE(exit_time) = '$today'
    ");

    $totalEntries = 0;
    $totalExits = 0;

    if ($totalEntriesQuery) {
        $totalEntries = (int)$totalEntriesQuery->fetch_assoc()['total'];
    }

    if ($totalExitsQuery) {
        $totalExits = (int)$totalExitsQuery->fetch_assoc()['total'];
    }

    $totalLogs = $totalEntries + $totalExits;

    $recentActivities = [];

    $activityQuery = $conn->query("
        SELECT
            student_name,
            student_id,
            entry_time AS activity_time,
            'Entry' AS activity_type
        FROM logs
        WHERE entry_time IS NOT NULL
        AND DATE(entry_time) = '$today'

        UNION ALL

        SELECT
            student_name,
            student_id,
            exit_time AS activity_time,
            'Exit' AS activity_type
        FROM logs
        WHERE exit_time IS NOT NULL
        AND DATE(exit_time) = '$today'

        ORDER BY activity_time DESC
        LIMIT 4
    ");

    if ($activityQuery) {

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
    }

    return [
        'found' => true,
        'type' => $type,
        'name' => $student['name'],
        'student_id' => $student['student_id'],
        'course' => $student['program_code'] ?? 'N/A',
        'picture' => 'admin/admin/uploads/' . basename($student['picture_url']),
        'time_label' => ucfirst($type) . ': ' .
            date(
                'F j, Y h:i A',
                strtotime($currentTime)
            ),

        'student_stats' => [
            'entries' => $studentEntries,
            'exits' => $studentExits,
            'logs' => $studentLogs
        ],

        'dashboard_stats' => [
            'total_logs' => $totalLogs,
            'total_entries' => $totalEntries,
            'total_exits' => $totalExits
        ],

        'recent_activities' => $recentActivities
    ];
}
$response = buildResponse($conn, $uid, $student, $currentTime, 'entry');
if (!empty($student['phone'])) {
    $entryTime = date('F j, Y h:i A', strtotime($currentTime));

    $msg = "Hi {$student['parent_name']}, {$student['name']} ({$student['student_id']}) has ENTERED the campus on {$entryTime}.";

    sendSMS($student['phone'], $msg);
}

ob_clean();

echo json_encode($response);

exit;

if (!empty($student['phone'])) {
    $entryTime = date('F j, Y h:i A', strtotime($currentTime));
    $msg = "Hi {$student['parent_name']}, {$student['name']} ({$student['student_id']}) has ENTERED the campus on {$entryTime}.";
    sendSMS($student['phone'], $msg);
}