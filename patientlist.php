<?php
session_start();
require_once 'config/db.php';

// Handle deletion
if (isset($_GET['delete'])) {
    $idToDelete = $_GET['delete'];

    try {
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$idToDelete]);
        $_SESSION['message'] = "Patient deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting patient: " . $e->getMessage();
    }

    // Redirect to avoid resubmission
    header("Location: patientlist.php");
    exit();
}

// Fetch patient list
try {
    $stmt = $pdo->query("SELECT * FROM patients ORDER BY id DESC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $patients = [];
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

        <!-- Patient Table -->
        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-white">Patients</h6>
                    <a href="add_patient.php" class="btn btn-light btn-sm">Add Patients</a>
                </div>
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4">Patient List</h6>

                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success">
                                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table text-white">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Gender</th>
                                        <th>Date of Birth</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($patients) > 0): ?>
                                        <?php foreach ($patients as $index => $patient): ?>
                                            <tr>
                                                <th><?php echo $index + 1; ?></th>
                                                <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['dob']); ?></td>
                                                <td>
                                                    <a href="view_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-info btn-sm">View</a>
                                                    <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="patientlist.php?delete=<?php echo $patient['id']; ?>"
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Are you sure you want to delete this patient?');">
                                                       Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7">No patients found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- JS Libraries -->
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
</div>
</body>
</html>
