<?php
session_start();
require_once 'config/db.php';

// Fetch all patients and doctors
$patients = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$doctors = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? null;
    $doctor_id = $_POST['doctor_id'] ?? null;
    $appointment_date = $_POST['appointment_date'] ?? null;
    $status = $_POST['status'] ?? 'Pending';
    $reason = trim($_POST['reason'] ?? '');

    if (!$patient_id || !$doctor_id || !$appointment_date) {
        $_SESSION['error'] = "Please fill all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments 
                (patient_id, doctor_id, appointment_date, status, reason) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $doctor_id, $appointment_date, $status, $reason]);
            $_SESSION['success'] = "Appointment added successfully.";
            header("Location: appointmentlist.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: appointmentlist.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php'); ?>

<body>
<div class="container-fluid position-relative d-flex p-0">

    <!-- Sidebar Start -->
    <?php include('inc/sections/inc_sidebar.php'); ?>
    <!-- Sidebar End -->

    <!-- Content Start -->
    <div class="content">

        <!-- Navbar Start -->
        <?php include('inc/sections/inc_navbar.php'); ?>
        <!-- Navbar End -->

        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4 text-white">Add Appointment</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form action="add_appointment.php" method="POST">
                            <!-- Patient -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="patient_id" name="patient_id" required>
                                    <option value="" disabled selected>Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['id']; ?>"><?= htmlspecialchars($patient['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="patient_id">Patient</label>
                            </div>

                            <!-- Doctor -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="doctor_id" name="doctor_id" required>
                                    <option value="" disabled selected>Select Doctor</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= $doctor['id']; ?>"><?= htmlspecialchars($doctor['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="doctor_id">Doctor</label>
                            </div>

                            <!-- Appointment Date -->
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                                <label for="appointment_date">Appointment Date & Time</label>
                            </div>

                            <!-- Status -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Pending" selected>Pending</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                                <label for="status">Status</label>
                            </div>

                            <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Reason for appointment" id="reason" name="reason" style="height: 120px;"></textarea>
                            <label for="reason">Reason</label>
                        </div>

                          

                            <button type="submit" class="btn btn-primary w-100">Add Appointment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content End -->

</div>

<!-- JavaScript Libraries -->
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
