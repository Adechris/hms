<?php
session_start();
require_once 'config/db.php';

// DELETE staff if `?delete=id` is set
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Staff member deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: stafflist.php");
    exit;
}

// FETCH staff list with roles and departments
$stmt = $pdo->query("
    SELECT 
        s.*, 
        r.title AS role_title, 
        d.name AS department_name 
    FROM 
        staff s
    LEFT JOIN staff_roles r ON s.role_id = r.id
    LEFT JOIN departments d ON s.department_id = d.id
    ORDER BY s.id DESC
");
$staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <h6 class="mb-4 text-white">Staff List</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <a href="add_staff.php" class="btn btn-primary mb-3">+ Add New Staff</a>

                        <div class="table-responsive">
                            <table class="table text-white table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Picture</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Gender</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($staffList as $index => $staff): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <img src="uploads/staff/<?= $staff['picture'] ?? 'default-staff.png' ?>" 
                                                 alt="Pic" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        </td>
                                        <td><?= htmlspecialchars($staff['full_name']) ?></td>
                                        <td><?= htmlspecialchars($staff['email']) ?></td>
                                        <td><?= htmlspecialchars($staff['phone']) ?></td>
                                        <td><?= htmlspecialchars($staff['gender']) ?></td>
                                        <td><?= htmlspecialchars($staff['role_title'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($staff['department_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($staff['status']) ?></td>
                                        <td>
                                            <a href="stafflist.php?delete=<?= $staff['id'] ?>" 
                                               onclick="return confirm('Are you sure you want to delete this staff member?')"
                                               class="btn btn-sm btn-danger">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($staffList) === 0): ?>
                                    <tr><td colspan="10">No staff found.</td></tr>
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

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
