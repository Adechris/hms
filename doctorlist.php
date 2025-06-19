<?php
session_start();
require_once 'config/db.php'; // Create this to centralize your DB config

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->execute([$delete_id]);

        $_SESSION['message'] = "Doctor deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Failed to delete doctor: " . $e->getMessage();
    }

    // Redirect to remove the delete_id from URL
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

try {
    $stmt = $pdo->query("SELECT * FROM doctors ORDER BY id DESC");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $doctors = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php')?>

<body>
<div class="container-fluid position-relative d-flex p-0">

    <!-- Sidebar -->
    <?php include('inc/sections/inc_sidebar.php')?>

    <!-- Content -->
    <div class="content">
        <?php include('inc/sections/inc_navbar.php')?>

        <!-- Doctor Table -->
        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-white">Doctors</h6>
                    <a href="add_doctor.php" class="btn btn-light btn-sm">Add Doctors</a>
                </div>
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4">Doctor List</h6>

                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table text-white">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($doctors) > 0): ?>
                                        <?php foreach ($doctors as $index => $doctor): ?>
                                            <tr>
                                                <th><?php echo $index + 1; ?></th>
                                                <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                                <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                                <td>
                                                    <a href="view_doctor.php?id=<?php echo $doctor['id']; ?>" class="btn btn-info btn-sm">View</a>
                                                    <a href="edit_doctor.php?id=<?php echo $doctor['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                 <a href="?delete_id=<?php echo $doctor['id']; ?>" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete this doctor?')">Delete</a>

                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6">No doctors found.</td>
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

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</div>
</body>
</html>
