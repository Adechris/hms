<?php
session_start();
require_once 'config/db.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM payrolls WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Payroll record deleted.";
    header("Location: payrolllist.php");
    exit;
}

// Fetch all payrolls with associated names
$query = "
    SELECT 
        p.*, 
        s.full_name AS staff_name,
        d.full_name AS doctor_name
    FROM payrolls p
    LEFT JOIN staff s ON p.staff_id = s.id
    LEFT JOIN doctors d ON p.doctor_id = d.id
    ORDER BY p.salary_month DESC, p.created_at DESC
";
$payrolls = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
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
                    <h4 class="text-white">Payroll List</h4>
                    <a href="add_payroll.php" class="btn btn-sm btn-light">+ Add Payroll</a>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <a href="export_payroll_csv.php" class="btn btn-sm btn-success mb-3">
    <i class="fa fa-file-csv me-1"></i> Export to CSV
</a>


                <div class="table-responsive">
                    <table class="table table-bordered text-white">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Role</th>
                                <th>Name</th>
                                <th>Month</th>
                                <th>Base</th>
                                <th>Bonuses</th>
                                <th>Deductions</th>
                                <th>Net</th>
                                <th>Status</th>
                                <th>Paid At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($payrolls as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= $row['staff_id'] ? 'Staff' : 'Doctor' ?></td>
                                <td><?= htmlspecialchars($row['staff_name'] ?? $row['doctor_name']) ?></td>
                                <td><?= htmlspecialchars($row['salary_month']) ?></td>
                                <td>₦<?= number_format($row['base_salary'], 2) ?></td>
                                <td>₦<?= number_format($row['bonuses'], 2) ?></td>
                                <td>₦<?= number_format($row['deductions'], 2) ?></td>
                                <td><strong>₦<?= number_format($row['net_salary'], 2) ?></strong></td>
                                <td>
                                    <?php
                                        $badge = match($row['payment_status']) {
                                            'Paid' => 'success',
                                            'Pending' => 'warning',
                                            default => 'secondary',
                                        };
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= $row['payment_status'] ?></span>
                                </td>
                                <td><?= $row['paid_at'] ? date('M d, Y', strtotime($row['paid_at'])) : '—' ?></td>
                                <td>
                                    <a href="edit_payroll.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($payrolls) === 0): ?>
                            <tr><td colspan="11">No payrolls found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
     <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
