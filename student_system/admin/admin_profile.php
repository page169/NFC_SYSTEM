<?php
session_start();
include '../db.php';
include('includes/header.php');

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin information based on the session ID
$admin_id = $_SESSION['id'];
$query = "SELECT id, username, FirstName, LastName, picture_url FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if any admin data is found
if ($result->num_rows > 0) {
    $admins = $result->fetch_assoc();
} else {
    $admins = null;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['FirstName']);
    $last_name = trim($_POST['LastName']);
    $new_password = $_POST['new_password'];
    $new_username = trim($_POST['username']);
    $profile_picture = $_FILES['profile_picture'];

    // Initialize a flag to track if the update was successful
    $update_successful = false;

    // Check if password is to be updated
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE admins SET FirstName = ?, LastName = ?, password = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ssssi', $first_name, $last_name, $hashed_password, $new_username, $admin_id);
    } else {
        $update_query = "UPDATE admins SET FirstName = ?, LastName = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('sssi', $first_name, $last_name, $new_username, $admin_id);
    }

    // Execute the query for profile info update
    if ($stmt->execute()) {
        $update_successful = true;

        // Handling profile picture upload
        if ($profile_picture['error'] == 0) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($profile_picture['name'], PATHINFO_EXTENSION));
            $upload_dir = 'uploads/profile_pics/';

            // Check if file type is allowed
            if (in_array($file_extension, $allowed_extensions)) {
                $new_file_name = time() . '.' . $file_extension;
                $file_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($profile_picture['tmp_name'], $file_path)) {
                    // Update picture URL in the database
                    $update_picture_query = "UPDATE admins SET picture_url = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_picture_query);
                    $stmt->bind_param('si', $file_path, $admin_id);
                    if ($stmt->execute()) {
                        $update_successful = true;
                    } else {
                        $error_message = "Error uploading the picture. Please try again.";
                    }
                } else {
                    $error_message = "Error uploading the picture. Please try again.";
                }
            } else {
                $error_message = "Only jpg, jpeg, png, or gif files are allowed for profile picture.";
            }
        }
    } else {
        $error_message = "Error updating profile. Please try again.";
    }
if ($update_successful) {
        $success_message = "Profile updated successfully!";
        header('Location: admin_profile.php'); // Redirect to the same page to reflect changes immediately
        exit(); // Make sure no further code is executed after the redirect
    }
}
?>
<!-- Displaying success or error messages -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success">
        <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

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
                            <div class="title">
                                <h4>Admin Profile</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Admin Profile</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card-box pd-20 height-100-p">
                            <h2 class="mb-30 h4">Profile Information</h2>
                            <?php if (isset($success_message)): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($error_message)): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>

                            <form action="admin_profile.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Username:</label>
                                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($admins['username']); ?>" required>
                                        </div>
                                    </div>
									
									<div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>New Password (Leave blank to keep current password):</label>
                                            <input type="password" class="form-control" name="new_password">
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>First Name:</label>
                                            <input type="text" class="form-control" name="FirstName" value="<?php echo htmlspecialchars($admins['FirstName']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Last Name:</label>
                                            <input type="text" class="form-control" name="LastName" value="<?php echo htmlspecialchars($admins['LastName']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php include('includes/footer.php'); ?>
    <?php include('includes/scripts.php'); ?>
</body>
</html>
