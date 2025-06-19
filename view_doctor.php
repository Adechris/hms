<?php
session_start();
require_once 'config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: doctorlist.php');
    exit();
}

// Fetch doctor and join department name
$stmt = $pdo->prepare("
    SELECT d.*, dept.name AS department_name
    FROM doctors d
    LEFT JOIN departments dept ON d.department_id = dept.id
    WHERE d.id = ?
");
$stmt->execute([$id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    $_SESSION['error'] = "Doctor not found.";
    header('Location: doctorlist.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php') ?>
<body>
<div class="container-fluid position-relative d-flex p-0">

    <!-- Sidebar Start -->
    <?php include('inc/sections/inc_sidebar.php') ?>

    <!-- Content Start -->
    <div class="content">

        <!-- Navbar Start -->
        <?php include('inc/sections/inc_navbar.php') ?>
        <!-- Navbar End -->

        <div class="container-fluid pt-4 px-4">
            <div class="row">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4 text-white">Doctor Profile</h6>

                        <div class="d-flex align-items-center mb-4">
                            <img src="uploads/doctors/<?php echo htmlspecialchars($doctor['picture'] ?? 'default-user.jpg'); ?>"
                                 alt="Doctor Picture" class="rounded-circle me-4"
                                 style="width: 120px; height: 120px; object-fit: cover;"
                                 onerror="this.src='img/user.jpg'">
                            <div class="text-white">
                                <h4 class="mb-1"><?php echo htmlspecialchars($doctor['full_name']); ?></h4>
                                <p class="mb-0"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                            </div>
                        </div>

                        <div class="row text-white">
                            <div class="col-md-4 mb-3"><strong>Email:</strong><br><?php echo htmlspecialchars($doctor['email']); ?></div>
                            <div class="col-md-4 mb-3"><strong>Phone:</strong><br><?php echo htmlspecialchars($doctor['phone']); ?></div>
                            <div class="col-md-4 mb-3"><strong>Gender:</strong><br><?php echo htmlspecialchars($doctor['gender']); ?></div>

                            <div class="col-md-4 mb-3"><strong>Date of Birth:</strong><br><?php echo htmlspecialchars($doctor['dob']); ?></div>
                            <div class="col-md-4 mb-3"><strong>Specialization:</strong><br><?php echo htmlspecialchars($doctor['specialization']); ?></div>
                            <div class="col-md-4 mb-3"><strong>Department:</strong><br><?php echo htmlspecialchars($doctor['department_name']); ?></div>

                            <div class="col-md-4 mb-3"><strong>License No:</strong><br><?php echo htmlspecialchars($doctor['license_number']); ?></div>
                            <div class="col-md-4 mb-3"><strong>Experience:</strong><br><?php echo htmlspecialchars($doctor['years_of_experience']); ?> years</div>
                            <div class="col-md-4 mb-3"><strong>Availability:</strong><br><?php echo htmlspecialchars($doctor['availability']); ?></div>

                            <div class="col-md-4 mb-3"><strong>Status:</strong><br><?php echo htmlspecialchars($doctor['status']); ?></div>
                            <div class="col-md-4 mb-3"><strong>Joined:</strong><br><?php echo date('d M Y', strtotime($doctor['created_at'])); ?></div>

                            <div class="col-md-6 mb-3"><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($doctor['address'])); ?></div>
                            <div class="col-md-6 mb-3"><strong>Education:</strong><br><?php echo nl2br(htmlspecialchars($doctor['education'])); ?></div>

                            <div class="col-12 mb-3"><strong>Bio:</strong><br><?php echo nl2br(htmlspecialchars($doctor['bio'])); ?></div>
                        </div>

                        <a href="doctorlist.php" class="btn btn-light mt-4 w-100">Back to Doctor List</a>
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
<script src="js/main.js"></script>
</body>
</html>
