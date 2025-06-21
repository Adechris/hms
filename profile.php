<?php
session_start();
require_once('./config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Debug: Check if user data is fetched properly
if (!$user) {
    $error = "User not found.";
}

$current_picture = $user['picture'] ?? null;

// Store user data in separate variables to protect from interference
$user_full_name = $user['full_name'] ?? '';
$user_email = $user['email'] ?? '';
$user_phone = $user['phone'] ?? '';
$user_picture = $user['picture'] ?? null;

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $picture = $current_picture; // Keep old one unless a new one is uploaded

    // Validate inputs
    if (empty($full_name)) {
        $error = "Full name is required.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Valid email is required.";
    }

    // Handle image upload if any
    if (!$error && !empty($_FILES['picture']['name']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $new_name = uniqid('user_') . '.' . $ext;
            $uploadDir = "uploads/users/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $dest = $uploadDir . $new_name;
            if (move_uploaded_file($_FILES['picture']['tmp_name'], $dest)) {
                // Delete old picture if it exists
                if ($current_picture && file_exists($uploadDir . $current_picture)) {
                    unlink($uploadDir . $current_picture);
                }
                $picture = $new_name;
            } else {
                $error = "Failed to upload the image. Check folder permission.";
            }
        } else {
            $error = "Invalid image format. Allowed: JPG, JPEG, PNG, WEBP.";
        }
    }

    if (!$error) {
        try {
            $update = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, picture = ? WHERE id = ?");
            $update->execute([$full_name, $email, $phone, $picture, $user_id]);

            if ($update->rowCount() > 0) {
                $success = "Profile updated successfully.";
            } else {
                $success = "No changes made to profile.";
            }

            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update our protected variables
            $user_full_name = $user['full_name'] ?? '';
            $user_email = $user['email'] ?? '';
            $user_phone = $user['phone'] ?? '';
            $user_picture = $user['picture'] ?? null;
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php'); ?>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include('inc/sections/inc_sidebar.php'); ?>

    <div class="content">
        <?php include('inc/sections/inc_navbar.php'); ?>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary p-4 rounded">
                <h4 class="mb-4 text-white">Edit Profile</h4>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($user): ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-white">Full Name</label>
                            <input type="text" name="full_name" class="form-control"
                                   value="<?= htmlspecialchars($user_full_name) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($user_email) ?>" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-white">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= htmlspecialchars($user_phone) ?>">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-white">Profile Picture</label>
                            <input type="file" name="picture" class="form-control" accept="image/*">
                            <?php if (!empty($user_picture) && file_exists("uploads/users/" . $user_picture)): ?>
                                <div class="mt-2">
                                    <small class="text-white">Current picture:</small><br>
                                    <img src="uploads/users/<?= htmlspecialchars($user_picture) ?>" 
                                         class="mt-1 rounded border" 
                                         style="width: 80px; height: 80px; object-fit: cover;"
                                         alt="Current profile picture">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Unable to load user data. Please try refreshing the page or contact support.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>