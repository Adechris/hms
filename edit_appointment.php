<?php
session_start();
require_once 'config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: appointmentlist.php');
    exit();
}

// Fetch appointment
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: appointmentlist.php");
    exit();
}

// Fetch patients and doctors
$patients = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$doctors = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $status = $_POST['status'];
    $reason = $_POST['reason'];

    $updateStmt = $pdo->prepare("
        UPDATE appointments 
        SET patient_id = ?, doctor_id = ?, appointment_date = ?, status = ?, reason = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([
        $patient_id, $doctor_id, $appointment_date, $status, $reason, $id
    ]);

    $_SESSION['message'] = "Appointment updated successfully.";
    header("Location: appointmentlist.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php') ?>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include('inc/sections/inc_sidebar.php') ?>

    <div class="content">
        <?php include('inc/sections/inc_navbar.php') ?>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded p-4">
                <h4 class="mb-4 text-white">Edit Appointment</h4>
                <form method="POST">
                    <div class="form-floating mb-3">
                        <select class="form-select" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>" <?= $appointment['patient_id'] == $patient['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($patient['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>Patient</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="doctor_id" required>
                            <option value="">Select Doctor</option>
                            <?php foreach ($doctors as $doc): ?>
                                <option value="<?= $doc['id'] ?>" <?= $appointment['doctor_id'] == $doc['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($doc['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>Doctor</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="datetime-local" class="form-control" name="appointment_date" value="<?= date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])) ?>" required>
                        <label>Appointment Date</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="status" required>
                            <option value="Pending" <?= $appointment['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Completed" <?= $appointment['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $appointment['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <label>Status</label>
                    </div>

                    <div class="form-floating mb-4">
                        <textarea class="form-control" name="reason" style="height: 150px;"><?= htmlspecialchars($appointment['reason']) ?></textarea>
                        <label>Reason</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update</button>
                </form>
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
