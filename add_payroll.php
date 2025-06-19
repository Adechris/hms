<?php
session_start();
require_once 'config/db.php';

// Fetch staff and doctors
$staffList = $pdo->query("SELECT id, full_name FROM staff ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$doctorList = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleType = $_POST['role_type'] ?? '';
    $salary_month = $_POST['salary_month'] ?? '';
    $base_salary = floatval($_POST['base_salary'] ?? 0);
    $bonuses = floatval($_POST['bonuses'] ?? 0);
    $deductions = floatval($_POST['deductions'] ?? 0);
    $net_salary = $base_salary + $bonuses - $deductions;

    $staff_id = ($roleType === 'staff') ? $_POST['staff_id'] : null;
    $doctor_id = ($roleType === 'doctor') ? $_POST['doctor_id'] : null;

    // Validation: Ensure the correct ID is selected
    if (($roleType === 'staff' && !$staff_id) || ($roleType === 'doctor' && !$doctor_id)) {
        $_SESSION['error'] = "Please select a valid staff or doctor.";
        header("Location: add_payroll.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO payrolls 
            (staff_id, doctor_id, salary_month, base_salary, bonuses, deductions, net_salary, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");

        $stmt->execute([$staff_id, $doctor_id, $salary_month, $base_salary, $bonuses, $deductions, $net_salary]);

        $_SESSION['success'] = "Payroll added successfully.";
        header("Location: payrolllist.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding payroll: " . $e->getMessage();
        header("Location: add_payroll.php");
        exit();
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
        <h4 class="mb-4 text-white">Add Payroll</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php elseif (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group mb-3">
                <label class="text-white">Role Type</label>
                <select class="form-select" name="role_type" id="roleType" required>
                    <option value="">Select</option>
                    <option value="staff">Staff</option>
                    <option value="doctor">Doctor</option>
                </select>
            </div>

            <div class="form-group mb-3" id="staffSelect" style="display: none;">
                <label class="text-white">Select Staff</label>
                <select class="form-select" name="staff_id">
                    <option value="">Choose Staff</option>
                    <?php foreach ($staffList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3" id="doctorSelect" style="display: none;">
                <label class="text-white">Select Doctor</label>
                <select class="form-select" name="doctor_id">
                    <option value="">Choose Doctor</option>
                    <?php foreach ($doctorList as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="text-white">Salary Month</label>
                <input type="month" class="form-control" name="salary_month" required>
            </div>

            <div class="form-group mb-3">
                <label class="text-white">Base Salary</label>
                <input type="number" step="0.01" name="base_salary" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label class="text-white">Bonuses</label>
                <input type="number" step="0.01" name="bonuses" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label class="text-white">Deductions</label>
                <input type="number" step="0.01" name="deductions" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Payroll</button>
        </form>
    </div>
</div>

<script>
document.getElementById('roleType').addEventListener('change', function () {
    document.getElementById('staffSelect').style.display = this.value === 'staff' ? 'block' : 'none';
    document.getElementById('doctorSelect').style.display = this.value === 'doctor' ? 'block' : 'none';
});
</script>

</div></div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
