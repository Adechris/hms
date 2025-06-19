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
?>

<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php'); ?>
<body>
<div class="container-fluid position-relative d-flex p-0">

    <!-- Sidebar Start -->
    <?php include('inc/sections/inc_sidebar.php'); ?>

    <!-- Content Start -->
    <div class="content">

        <!-- Navbar Start -->
        <?php include('inc/sections/inc_navbar.php'); ?>
        <!-- Navbar End -->

        <div class="container-fluid pt-4 px-4">
            <div class="row">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4 text-white">Patient Profile</h6>

                        <div class="text-white mb-4">
                            <h4 class="mb-1"><?php echo htmlspecialchars($patient['full_name']); ?></h4>
                            <p class="mb-0"><?php echo htmlspecialchars($patient['gender']); ?>, Born on <?php echo htmlspecialchars($patient['dob']); ?></p>
                        </div>

                        <div class="row text-white">
                            <div class="col-md-4 mb-3">
                                <strong>Email:</strong><br>
                                <?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Phone:</strong><br>
                                <?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Blood Type:</strong><br>
                                <?php echo htmlspecialchars($patient['blood_type'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Gender:</strong><br>
                                <?php echo htmlspecialchars($patient['gender']); ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Date of Birth:</strong><br>
                                <?php echo htmlspecialchars($patient['dob']); ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Emergency Contact Name:</strong><br>
                                <?php echo htmlspecialchars($patient['emergency_contact_name'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Emergency Contact Phone:</strong><br>
                                <?php echo htmlspecialchars($patient['emergency_contact_phone'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Address:</strong><br>
                                <?php echo nl2br(htmlspecialchars($patient['address'] ?? 'N/A')); ?>
                            </div>
                        </div>

                        <a href="patientlist.php" class="btn btn-light mt-4 w-100">Back to Patient List</a>
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
