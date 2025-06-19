<?php
session_start();
require_once 'config/db.php';

// Fetch patients, rooms, doctors
$patients = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$rooms = $pdo->query("SELECT id, room_number FROM rooms WHERE availability_status = 'Available' ORDER BY room_number")->fetchAll(PDO::FETCH_ASSOC);
$doctors = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? null;
    $room_id = $_POST['room_id'] ?? null;
    $doctor_id = $_POST['doctor_id'] ?: null;
    $reason = trim($_POST['reason'] ?? '');
    $admission_date = $_POST['admission_date'] ?? '';
    $status = $_POST['status'] ?? 'Admitted';

    if (!$patient_id || !$room_id || !$admission_date) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        try {
            // Insert admission
            $stmt = $pdo->prepare("INSERT INTO admissions (patient_id, room_id, doctor_id, reason, admission_date, status)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $room_id, $doctor_id, $reason, $admission_date, $status]);

            // Mark room as Occupied
            $pdo->prepare("UPDATE rooms SET availability_status = 'Occupied' WHERE id = ?")->execute([$room_id]);

            $_SESSION['success'] = "Admission recorded successfully.";
            header("Location: admissionlist.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: add_admission.php");
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
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded p-4">
                        <h6 class="mb-4 text-white">Add Admission</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="add_admission.php">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="patient_id" name="patient_id" required>
                                    <option disabled selected>Select Patient</option>
                                    <?php foreach ($patients as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="patient_id">Patient</label>
                            </div>

                            <div class="form-floating mb-3">
                                <select class="form-select" id="room_id" name="room_id" required>
                                    <option disabled selected>Select Room</option>
                                    <?php foreach ($rooms as $r): ?>
                                        <option value="<?= $r['id'] ?>">Room <?= htmlspecialchars($r['room_number']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="room_id">Room</label>
                            </div>

                            <div class="form-floating mb-3">
                                <select class="form-select" id="doctor_id" name="doctor_id">
                                    <option value="">-- Optional --</option>
                                    <?php foreach ($doctors as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="doctor_id">Assigned Doctor</label>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea class="form-control" name="reason" id="reason" placeholder="Reason" style="height: 100px;"></textarea>
                                <label for="reason">Reason for Admission</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="admission_date" required>
                                <label for="admission_date">Admission Date</label>
                            </div>

                            <div class="form-floating mb-3">
                                <select class="form-select" name="status" required>
                                    <option value="Admitted" selected>Admitted</option>
                                    <option value="Discharged">Discharged</option>
                                </select>
                                <label for="status">Status</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Add Admission</button>
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
