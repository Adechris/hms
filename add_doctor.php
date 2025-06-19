<?php
session_start();
require_once 'config/db.php';

// DELETE doctor if `?delete=id` is set
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Doctor deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: doctorlist.php");
    exit;
}

// FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'gender' => $_POST['gender'] ?? null,
        'dob' => $_POST['dob'] ?? null,
        'address' => $_POST['address'] ?? null,
        'specialization' => $_POST['specialization'],
        'department_id' => $_POST['department_id'] ?? null,
        'license_number' => $_POST['license_number'] ?? null,
        'years_of_experience' => $_POST['years_of_experience'] ?? null,
        'education' => $_POST['education'] ?? null,
        'availability' => $_POST['availability'] ?? 'Available',
        'status' => $_POST['status'] ?? 'Active',
        'bio' => $_POST['bio'] ?? null,
    ];

    // Upload picture
    $imageName = 'default-doctor.png';
    if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $imageName = uniqid('doctor_') . '.' . $ext;
            $dest = "uploads/doctors/$imageName";
            if (!is_dir("uploads/doctors")) mkdir("uploads/doctors", 0777, true);
            move_uploaded_file($_FILES['picture']['tmp_name'], $dest);
        } else {
            $_SESSION['error'] = "Invalid image format.";
        }
    }

    if (!isset($_SESSION['error'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO doctors (
                full_name, email, phone, gender, dob, address, specialization, department_id,
                license_number, years_of_experience, education, availability, status, picture, bio, created_at
            ) VALUES (
                :full_name, :email, :phone, :gender, :dob, :address, :specialization, :department_id,
                :license_number, :years_of_experience, :education, :availability, :status, :picture, :bio, NOW()
            )");

            $stmt->execute(array_merge($data, ['picture' => $imageName]));
            $_SESSION['success'] = "Doctor added successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: doctorlist.php");
    exit;
}

// FETCH departments
$departments = $pdo->query("SELECT id, name FROM departments")->fetchAll(PDO::FETCH_ASSOC);
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
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4 text-white">Add Doctor</h6>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                            <?php elseif (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                            <?php endif; ?>

                            <form action="add_doctor.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                                            <label>Full Name</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" name="email" placeholder="Email" required>
                                            <label>Email</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="phone" placeholder="Phone" required>
                                            <label>Phone</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" name="gender" required>
                                                <option value="" disabled selected>Select Gender</option>
                                                <option>Male</option>
                                                <option>Female</option>
                                                <option>Other</option>
                                            </select>
                                            <label>Gender</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" name="dob">
                                            <label>Date of Birth</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" name="specialization" required>
                                                <option value="" disabled selected>Select Specialization</option>
                                                <option>Cardiologist</option>
                                                <option>Dermatologist</option>
                                                <option>Pediatrician</option>
                                                <option>Surgeon</option>
                                                <option>General Physician</option>
                                            </select>
                                            <label>Specialization</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" name="department_id">
                                                <option value="" selected disabled>Select Department</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label>Department</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="license_number" placeholder="License No">
                                            <label>License Number</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" name="years_of_experience" placeholder="Years of Experience">
                                            <label>Experience (Years)</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" name="availability">
                                                <option value="Available" selected>Available</option>
                                                <option value="Unavailable">Unavailable</option>
                                            </select>
                                            <label>Availability</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" name="status">
                                                <option value="Active" selected>Active</option>
                                                <option value="Suspended">Suspended</option>
                                                <option value="Retired">Retired</option>
                                            </select>
                                            <label>Status</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Profile Picture</label>
                                        <input class="form-control bg-dark text-white" type="file" name="picture" accept="image/*">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <textarea class="form-control" name="address" placeholder="Address" style="height: 100px;"></textarea>
                                            <label>Address</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <textarea class="form-control" name="education" placeholder="Education" style="height: 100px;"></textarea>
                                            <label>Education</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <textarea class="form-control" name="bio" placeholder="Short Bio" style="height: 100px;"></textarea>
                                            <label>Short Bio</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary w-100">Add Doctor</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
