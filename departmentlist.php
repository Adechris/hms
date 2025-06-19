<?php
session_start();
require_once 'config/db.php';

// ✅ Handle Delete Request
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $dept_id = intval($_GET['delete_id']);

    try {
        // Check if department exists
        $check = $pdo->prepare("SELECT id FROM departments WHERE id = ?");
        $check->execute([$dept_id]);

        if (!$check->fetch()) {
            $_SESSION['error'] = "Department not found.";
        } else {
            // Try deleting
            $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([$dept_id]);

            if ($stmt->rowCount()) {
                $_SESSION['success'] = "Department deleted successfully.";
            } else {
                $_SESSION['error'] = "Department could not be deleted.";
            }
        }

    } catch (PDOException $e) {
        // Catch foreign key violation
        if ($e->getCode() === '23000') {
            $_SESSION['error'] = "Cannot delete: Department is linked to existing staff.";
        } else {
            $_SESSION['error'] = "Deletion failed: " . $e->getMessage();
        }
    }

    header("Location: departmentlist.php");
    exit;
}

// ✅ Fetch All Departments
try {
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY created_at ASC");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error loading departments: " . $e->getMessage();
    $departments = [];
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
            <div class="bg-secondary rounded p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-white">Departments</h6>
                    <a href="add_department.php" class="btn btn-light btn-sm">Add Department</a>
                </div>

                <!-- ✅ Feedback Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered text-white">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($departments): ?>
                                <?php foreach ($departments as $index => $dept): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($dept['name']) ?></td>
                                        <td><?= nl2br(htmlspecialchars($dept['description'])) ?></td>
                                        <td>
                                            <a href="edit_department.php?id=<?= $dept['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="?delete_id=<?= $dept['id'] ?>" class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this department?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No departments found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
