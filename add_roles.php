<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title)) {
        $_SESSION['error'] = "Role title is required.";
        header("Location: add_roles.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO staff_roles (title, description, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$title, $description]);
        $_SESSION['success'] = "Role added successfully.";
        header("Location: roles.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_roles.php");
        exit;
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
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4 text-white">Add Staff Role</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="add_roles.php">
                            <div class="mb-3">
                                <label class="form-label text-white">Role Title</label>
                                <input type="text" name="title" class="form-control bg-dark text-white" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white">Description</label>
                                <textarea name="description" class="form-control bg-dark text-white" rows="3"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Add Role</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
