<?php
session_start();
require_once 'config/db.php';

// ✅ Validate department ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid department ID.";
    header("Location: departmentlist.php");
    exit;
}

$dept_id = intval($_GET['id']);

// ✅ Fetch existing department data
try {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$dept_id]);
    $department = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$department) {
        $_SESSION['error'] = "Department not found.";
        header("Location: departmentlist.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: departmentlist.php");
    exit;
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (!$name) {
        $_SESSION['error'] = "Department name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE departments SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $dept_id]);

            $_SESSION['success'] = "Department updated successfully.";
            header("Location: departmentlist.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Update failed: " . $e->getMessage();
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
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded p-4">
                        <h6 class="mb-4 text-white">Edit Department</h6>

                        <!-- ✅ Feedback -->
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <!-- ✅ Edit Form -->
                        <form method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?= htmlspecialchars($department['name']) ?>">
                                <label for="name">Department Name</label>
                            </div>

                            <div class="form-floating mb-4">
                                <textarea class="form-control" id="description" name="description" style="height: 120px;"><?= htmlspecialchars($department['description']) ?></textarea>
                                <label for="description">Description</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Update Department</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
