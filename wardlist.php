<?php
session_start();
require_once 'config/db.php';

// ✅ Handle Delete Request at the Top
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $ward_id = intval($_GET['delete_id']);

    try {
        // Optional: Confirm the ward exists
        $check = $pdo->prepare("SELECT id FROM wards WHERE id = ?");
        $check->execute([$ward_id]);

        if (!$check->fetch()) {
            $_SESSION['error'] = "Ward not found.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM wards WHERE id = ?");
            $stmt->execute([$ward_id]);
            $_SESSION['success'] = "Ward deleted successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Deletion failed: " . $e->getMessage();
    }

    // Redirect to remove delete_id from URL
    header("Location: wardlist.php");
    exit;
}

// ✅ Fetch all wards
try {
    $stmt = $pdo->query("SELECT * FROM wards ORDER BY created_at ASC");
    $wards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to fetch wards: " . $e->getMessage();
    $wards = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php'); ?>

<body>
<div class="container-fluid position-relative d-flex p-0">

    <!-- Sidebar -->
    <?php include('inc/sections/inc_sidebar.php'); ?>

    <!-- Content -->
    <div class="content">
        <?php include('inc/sections/inc_navbar.php'); ?>

        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="text-white">Ward List</h6>
                            <a href="add_ward.php" class="btn btn-light btn-sm">Add Ward</a>
                        </div>

                        <!-- Alerts -->
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

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table text-white">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ward Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($wards) > 0): ?>
                                        <?php foreach ($wards as $index => $ward): ?>
                                            <tr>
                                                <td><?= $index + 1; ?></td>
                                                <td><?= htmlspecialchars($ward['name']); ?></td>
                                                <td><?= htmlspecialchars($ward['description']); ?></td>
                                                <td>
                                                    <a href="edit_ward.php?id=<?= $ward['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="?delete_id=<?= $ward['id']; ?>" class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Are you sure you want to delete this ward?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5">No wards found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
