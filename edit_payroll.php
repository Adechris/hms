<?php
session_start();
require_once 'config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = "Payroll ID is missing.";
    header("Location: payrolllist.php");
    exit;
}

// Fetch payroll
$stmt = $pdo->prepare("
    SELECT p.*, s.full_name AS staff_name, d.full_name AS doctor_name
    FROM payrolls p
    LEFT JOIN staff s ON p.staff_id = s.id
    LEFT JOIN doctors d ON p.doctor_id = d.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$payroll = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payroll) {
    $_SESSION['error'] = "Payroll not found.";
    header("Location: payrolllist.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salary_month = $_POST['salary_month'];
    $base_salary = (float) $_POST['base_salary'];
    $bonuses = (float) $_POST['bonuses'];
    $deductions = (float) $_POST['deductions'];
    $payment_status = $_POST['payment_status'];

    $net_salary = $base_salary + $bonuses - $deductions;
    $paid_at = ($payment_status === 'Paid') ? date('Y-m-d H:i:s') : null;

    $update = $pdo->prepare("
        UPDATE payrolls SET
            salary_month = ?,
            base_salary = ?,
            bonuses = ?,
            deductions = ?,
            net_salary = ?,
            payment_status = ?,
            paid_at = ?
        WHERE id = ?
    ");
    $update->execute([
        $salary_month, $base_salary, $bonuses, $deductions,
        $net_salary, $payment_status, $paid_at, $id
    ]);

    $_SESSION['success'] = "Payroll updated successfully.";
    header("Location: payrolllist.php");
    exit;
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
                <h4 class="mb-4 text-white">Edit Payroll</h4>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Name</label>
                            <input type="text" class="form-control" readonly
                                   value="<?= htmlspecialchars($payroll['staff_name'] ?? $payroll['doctor_name']) ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Month</label>
                            <input type="month" name="salary_month" class="form-control" required
                                   value="<?= htmlspecialchars($payroll['salary_month']) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Base Salary (₦)</label>
                            <input type="number" name="base_salary" class="form-control" step="0.01" required
                                   value="<?= htmlspecialchars($payroll['base_salary']) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Bonuses (₦)</label>
                            <input type="number" name="bonuses" class="form-control" step="0.01"
                                   value="<?= htmlspecialchars($payroll['bonuses']) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Deductions (₦)</label>
                            <input type="number" name="deductions" class="form-control" step="0.01"
                                   value="<?= htmlspecialchars($payroll['deductions']) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Net Salary</label>
                            <input type="text" class="form-control" readonly
                                   value="₦<?= number_format($payroll['net_salary'], 2) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Payment Status</label>
                            <select name="payment_status" class="form-select" required>
                                <option value="Pending" <?= $payroll['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Paid" <?= $payroll['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Paid At</label>
                            <input type="text" class="form-control" readonly
                                   value="<?= $payroll['paid_at'] ? date('M d, Y H:i', strtotime($payroll['paid_at'])) : '—' ?>">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary w-100">Update Payroll</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
