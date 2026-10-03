<?php
include '../db.php';

$row = [
    'firstname' => 'Admin',
    'lastname' => '',
    'location' => 'logo.png' // Default image path
];

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];

    // Prepare statement to get admin details
    $sql = "SELECT username, firstname, lastname, location FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        }
        $stmt->close();
    }
}
?>

<div class="header">
    <div class="header-left">
        <div class="menu-icon dw dw-menu"></div>
        <div class="search-toggle-icon dw dw-search2" data-toggle="header_search"></div>
    </div>
    <div class="header-right">
        <div class="dashboard-setting user-notification">
            <div class="dropdown">
                <a class="dropdown-toggle no-arrow" href="javascript:;" data-toggle="right-sidebar">
                    <i class="dw dw-settings2"></i>
                </a>
            </div>
        </div>

        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <span class="user-icon">
<img src="<?php echo !empty($row['location']) ? 'uploads/profile_pics/' . $row['location'] : 'uploads/profile_pics/logo.png'; ?>" alt="Profile Image">
                    </span>
                    <span class="user-name"><?php echo htmlspecialchars($row['firstname'] . " " . $row['lastname']); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                    <a class="dropdown-item" href="admin_profile.php"><i class="dw dw-user1"></i> Profile</a>
                    <a class="dropdown-item" href="../index.php"><i class="dw dw-logout"></i> Log Out</a>
                </div>
            </div>
        </div>
    </div>
</div>
 