<?php
session_start();
require_once 'config/db.php';

// DELETE staff if `?delete=id` is set
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Staff deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: stafflist.php");
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
        'role_id' => $_POST['role_id'],
        'department_id' => $_POST['department_id'] ?? null,
        'hire_date' => $_POST['hire_date'] ?? null,
        'salary' => $_POST['salary'] ?? 0.00,
        'status' => $_POST['status'] ?? 'Active',
    ];

    // Upload picture
    $imageName = 'default-avatar.png';
    if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $imageName = uniqid('staff_') . '.' . $ext;
            $dest = "uploads/staff/$imageName";
            if (!is_dir("uploads/staff")) mkdir("uploads/staff", 0777, true);
            move_uploaded_file($_FILES['picture']['tmp_name'], $dest);
        } else {
            $_SESSION['error'] = "Invalid image format.";
        }
    }

    if (!isset($_SESSION['error'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO staff (
                full_name, email, phone, gender, dob, address, role_id, department_id,
                hire_date, salary, picture, status, created_at
            ) VALUES (
                :full_name, :email, :phone, :gender, :dob, :address, :role_id, :department_id,
                :hire_date, :salary, :picture, :status, NOW()
            )");

            $stmt->execute(array_merge($data, ['picture' => $imageName]));
            $_SESSION['success'] = "Staff added successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: stafflist.php");
    exit;
}

// FETCH roles and departments
$roles = $pdo->query("SELECT id, title FROM staff_roles")->fetchAll(PDO::FETCH_ASSOC);
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
                        <h6 class="mb-4 text-white">Add Staff</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form action="add_staff.php" method="POST" enctype="multipart/form-data">
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
                                        <select class="form-select" name="role_id" required>
                                            <option value="" disabled selected>Select Role</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label>Staff Role</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" name="department_id">
                                            <option value="" disabled selected>Select Department</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label>Department</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" name="hire_date">
                                        <label>Hire Date</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" name="salary" step="0.01" placeholder="Salary">
                                        <label>Salary</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" name="status">
                                            <option value="Active" selected>Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                        <label>Status</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-white">Profile Picture</label>
                                    <div class="d-flex align-items-start gap-3">
                                        <div>
                                            <!-- Hidden file input -->
                                            <input type="file" id="pictureInput" name="picture" accept="image/*" style="display: none;">
                                            
                                            <!-- Custom button to trigger file selection -->
                                            <button type="button" class="btn btn-outline-light mb-2" id="pictureBtn">
                                                <i class="fa fa-upload me-2"></i>Choose Picture
                                            </button>
                                            
                                            <!-- Display selected file name -->
                                            <div>
                                                <span id="fileName" class="text-muted small">No file selected</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Image preview -->
                                        <div id="imagePreviewContainer" style="display: none;">
                                            <img id="imagePreview" class="rounded border" style="width: 80px; height: 80px; object-fit: cover;" alt="Preview">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control" name="address" placeholder="Address" style="height: 100px;"></textarea>
                                        <label>Address</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">Add Staff</button>
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

<script>
// Custom file input button functionality
document.getElementById('pictureBtn').addEventListener('click', function() {
    document.getElementById('pictureInput').click();
});

// Display selected file name and image preview
document.getElementById('pictureInput').addEventListener('change', function() {
    const fileName = document.getElementById('fileName');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    
    if (this.files && this.files.length > 0) {
        const file = this.files[0];
        
        // Update file name display
        fileName.textContent = file.name;
        fileName.classList.remove('text-muted');
        fileName.classList.add('text-success');
        
        // Create image preview
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreviewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        fileName.textContent = 'No file selected';
        fileName.classList.remove('text-success');
        fileName.classList.add('text-muted');
        imagePreviewContainer.style.display = 'none';
    }
});
</script>

</body>
</html>