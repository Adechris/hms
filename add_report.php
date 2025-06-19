<?php
session_start();
ini_get('display_errors');
error_reporting(E_ALL);
require_once 'config/db.php';

// Fetch all patients for dropdown
try {
    $stmt = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name ASC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $patients = [];
}

// check all doctors for dropdown
try {
    $stmt = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name ASC");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $doctors = [];
};


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? null;
    $doctor_id = $_POST['doctor_id'] ?? null;
    $diagnosis = trim($_POST['diagnosis']);
    $treatment = trim($_POST['treatment']);
    $doctor_notes = trim($_POST['doctor_notes']);
    $record_date = $_POST['record_date'];

    if (!$patient_id || !$diagnosis || !$treatment || !$record_date) {
        $_SESSION['error'] = "Please fill all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, doctor_notes, record_date)
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $doctor_id, $diagnosis, $treatment, $doctor_notes, $record_date]);
            $_SESSION['success'] = "Medical report added successfully.";
            header("Location: report.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }
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
                        <h6 class="mb-4 text-white">Add Medical Report</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form action="add_report.php" method="POST">
                            <!-- Patient -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="patient_id" name="patient_id" required>
                                    <option value="" disabled selected>Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['id'] ?>"><?= htmlspecialchars($patient['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="patient_id">Patient</label>
                            </div>


                            <!-- Doctor -->
                                                    <div class="form-floating mb-3">
                                <select class="form-select" id="doctor_id" name="doctor_id" required>
                                    <option value="" disabled selected>Select Doctor</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= $doctor['id'] ?>"><?= htmlspecialchars($doctor['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="doctor_id">Doctor</label>
                            </div>
                            <!-- Diagnosis -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="diagnosis" name="diagnosis" placeholder="Diagnosis" style="height: 100px;" required></textarea>
                                <label for="diagnosis">Diagnosis</label>
                            </div>

                            <!-- Treatment -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="treatment" name="treatment" placeholder="Treatment" style="height: 100px;" required></textarea>
                                <label for="treatment">Treatment</label>
                            </div>

                            <!-- Doctor Notes -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="doctor_notes" name="doctor_notes" placeholder="Doctor Notes" style="height: 100px;"></textarea>
                                <label for="doctor_notes">Doctor Notes</label>
                            </div>

                            <!-- Record Date -->
                            <div class="form-floating mb-4">
                                <input type="date" class="form-control" id="record_date" name="record_date" required>
                                <label for="record_date">Date of Record</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Save Report</button>
                        </form>
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
