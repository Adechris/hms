<?php
session_start();
require_once 'config/db.php';

$admission_id = $_GET['id'] ?? null;
if (!$admission_id || !is_numeric($admission_id)) {
    $_SESSION['error'] = "Invalid admission ID.";
    header("Location: admissionlist.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM admissions WHERE id = ?");
$stmt->execute([$admission_id]);
$admission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admission) {
    $_SESSION['error'] = "Admission not found.";
    header("Location: admissionlist.php");
    exit;
}

$patients = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$rooms = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number")->fetchAll(PDO::FETCH_ASSOC);
$doctors = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? null;
    $room_id = $_POST['room_id'] ?? null;
    $doctor_id = $_POST['doctor_id'] ?: null;
    $reason = trim($_POST['reason'] ?? '');
    $admission_date = $_POST['admission_date'] ?? '';
    $status = $_POST['status'] ?? 'Admitted';
    $discharge_date = ($status === 'Discharged') ? ($_POST['discharge_date'] ?? null) : null;

    if (!$patient_id || !$room_id || !$admission_date) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE admissions SET patient_id = ?, room_id = ?, doctor_id = ?, reason = ?, admission_date = ?, status = ?, discharge_date = ? WHERE id = ?");
            $stmt->execute([$patient_id, $room_id, $doctor_id, $reason, $admission_date, $status, $discharge_date, $admission_id]);

            if ($room_id != $admission['room_id']) {
                $pdo->prepare("UPDATE rooms SET availability_status = 'Available' WHERE id = ?")->execute([$admission['room_id']]);
                $pdo->prepare("UPDATE rooms SET availability_status = 'Occupied' WHERE id = ?")->execute([$room_id]);
            }

            $_SESSION['success'] = "Admission updated successfully.";
            header("Location: admissionlist.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: edit_admission.php?id=$admission_id");
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
                        <h6 class="mb-4 text-white">Edit Admission</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="patient_id" required>
                                    <?php foreach ($patients as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $admission['patient_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Patient</label>
                            </div>

                            <div class="form-floating mb-3">
                                <select class="form-select" name="room_id" required>
                                    <?php foreach ($rooms as $r): ?>
                                        <option value="<?= $r['id'] ?>" <?= $r['id'] == $admission['room_id'] ? 'selected' : '' ?>>
                                            Room <?= htmlspecialchars($r['room_number']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Room</label>
                            </div>

                            <div class="form-floating mb-3">
                                <select class="form-select" name="doctor_id">
                                    <option value="">-- Optional --</option>
                                    <?php foreach ($doctors as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= $d['id'] == $admission['doctor_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Assigned Doctor</label>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea class="form-control" name="reason" style="height: 100px;" required><?= htmlspecialchars($admission['reason']) ?></textarea>
                                <label>Reason for Admission</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="admission_date" value="<?= $admission['admission_date'] ?>" required>
                                <label>Admission Date</label>
                            </div>

                            <div class="form-floating mb-3">
                                <select class="form-select" name="status" id="statusSelect" required onchange="toggleDischargeDate()">
                                    <option value="Admitted" <?= $admission['status'] === 'Admitted' ? 'selected' : '' ?>>Admitted</option>
                                    <option value="Discharged" <?= $admission['status'] === 'Discharged' ? 'selected' : '' ?>>Discharged</option>
                                </select>
                                <label>Status</label>
                            </div>

                            <div class="form-floating mb-3" id="dischargeDateField" style="display: <?= $admission['status'] === 'Discharged' ? 'block' : 'none' ?>;">
                                <input type="date" class="form-control" name="discharge_date" value="<?= $admission['discharge_date'] ?? '' ?>">
                                <label>Discharge Date</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Update Admission</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function toggleDischargeDate() {
    const status = document.getElementById('statusSelect').value;
    document.getElementById('dischargeDateField').style.display = (status === 'Discharged') ? 'block' : 'none';
}
</script>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
