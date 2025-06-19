<?php
session_start();
require_once 'config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: patientlist.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    $_SESSION['error'] = "Patient not found.";
    header('Location: patientlist.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = $_POST['address'] ?? '';
    $blood_type = $_POST['blood_type'] ?? '';
    $emergency_contact_name = $_POST['emergency_contact_name'] ?? '';
    $emergency_contact_phone = $_POST['emergency_contact_phone'] ?? '';

    $update = $pdo->prepare("UPDATE patients SET full_name=?, email=?, phone=?, gender=?, dob=?, address=?, blood_type=?, emergency_contact_name=?, emergency_contact_phone=? WHERE id=?");
    $update->execute([$full_name, $email, $phone, $gender, $dob, $address, $blood_type, $emergency_contact_name, $emergency_contact_phone, $id]);

    $_SESSION['message'] = "Patient updated successfully.";
    header("Location: patientlist.php");
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
                <h4 class="mb-4 text-white">Edit Patient</h4>
               <form method="POST" enctype="multipart/form-data">
    <div class="row">
        <!-- Full Name -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($patient['full_name']); ?>" required>
                <label>Full Name</label>
            </div>
        </div>

        <!-- Email -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                <label>Email</label>
            </div>
        </div>

        <!-- Phone -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>" required>
                <label>Phone</label>
            </div>
        </div>

        <!-- Gender -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select class="form-select" name="gender">
                    <option value="Male" <?php if ($patient['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if ($patient['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                    <option value="Other" <?php if ($patient['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                </select>
                <label>Gender</label>
            </div>
        </div>

        <!-- Date of Birth -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="date" class="form-control" name="dob" value="<?php echo htmlspecialchars($patient['dob']); ?>" required>
                <label>Date of Birth</label>
            </div>
        </div>

        <!-- Blood Type -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" name="blood_type" value="<?php echo htmlspecialchars($patient['blood_type'] ?? ''); ?>">
                <label>Blood Type</label>
            </div>
        </div>

        <!-- Emergency Contact Name -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" name="emergency_contact_name" value="<?php echo htmlspecialchars($patient['emergency_contact_name'] ?? ''); ?>">
                <label>Emergency Contact Name</label>
            </div>
        </div>

        <!-- Emergency Contact Phone -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" name="emergency_contact_phone" value="<?php echo htmlspecialchars($patient['emergency_contact_phone'] ?? ''); ?>">
                <label>Emergency Contact Phone</label>
            </div>
        </div>

        <!-- Address (full width) -->
        <div class="col-12 mb-4">
            <div class="form-floating">
                <textarea class="form-control" name="address" style="height: 120px;"><?php echo htmlspecialchars($patient['address'] ?? ''); ?></textarea>
                <label>Address</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100">Update</button>
</form>

            </div>
        </div>
    </div>

    <!-- JavaScript -->
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
