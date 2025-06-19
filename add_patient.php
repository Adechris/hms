<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'] ?? null;
    $dob = $_POST['dob'] ?? null;
    $blood_type = $_POST['blood_type'] ?? null;
    $address = trim($_POST['address']);
    $emergency_contact_name = trim($_POST['emergency_contact_name']);
    $emergency_contact_phone = trim($_POST['emergency_contact_phone']);

    if (!$full_name || !$gender || !$dob || !$address || !$emergency_contact_name || !$emergency_contact_phone) {
        $_SESSION['error'] = "Please fill all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO patients (
                full_name, email, phone, gender, dob, blood_type, address,
                emergency_contact_name, emergency_contact_phone, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->execute([
                $full_name, $email, $phone, $gender, $dob, $blood_type,
                $address, $emergency_contact_name, $emergency_contact_phone
            ]);

            $_SESSION['success'] = "Patient added successfully.";
            header("Location: patientlist.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: add_patient.php");
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
                        <h6 class="mb-4 text-white">Add Patient</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                      <form action="add_patient.php" method="POST">
    <div class="row">
        <!-- Full Name -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" id="fullName" name="full_name" placeholder="Full Name" required>
                <label for="fullName">Full Name</label>
            </div>
        </div>

        <!-- Email -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com">
                <label for="email">Email Address</label>
            </div>
        </div>

        <!-- Phone -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone Number">
                <label for="phone">Phone Number</label>
            </div>
        </div>

        <!-- Blood Type -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" id="blood_type" name="blood_type" placeholder="Blood Type">
                <label for="blood_type">Blood Type (e.g., A+, B-, O+)</label>
            </div>
        </div>

        <!-- Emergency Contact Name -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" placeholder="Emergency Contact Name" required>
                <label for="emergency_contact_name">Emergency Contact Name</label>
            </div>
        </div>

        <!-- Emergency Contact Phone -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" placeholder="Emergency Contact Phone" required>
                <label for="emergency_contact_phone">Emergency Contact Phone</label>
            </div>
        </div>

        <!-- Gender -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select class="form-select" id="gender" name="gender" required>
                    <option value="" selected disabled>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <label for="gender">Gender</label>
            </div>
        </div>

        <!-- Date of Birth -->
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="date" class="form-control" id="dob" name="dob" placeholder="Date of Birth" required>
                <label for="dob">Date of Birth</label>
            </div>
        </div>

        <!-- Address -->
        <div class="col-md-12 mb-4">
            <div class="form-floating">
                <textarea class="form-control" placeholder="Patient Address" id="address" name="address" style="height: 100px;" required></textarea>
                <label for="address">Address</label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary w-100">Add Patient</button>
        </div>
    </div>
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
