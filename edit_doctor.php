<?php
session_start();
require_once 'config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: doctorlist.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    $_SESSION['error'] = "Doctor not found.";
    header('Location: doctorlist.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $specialization = $_POST['specialization'];
    $department_id = $_POST['department_id'];
    $license_number = $_POST['license_number'];
    $years_of_experience = $_POST['years_of_experience'];
    $education = $_POST['education'];
    $availability = $_POST['availability'];
    $status = $_POST['status'];
    $bio = $_POST['bio'];

    $picture = $doctor['picture'];
    if (!empty($_FILES['picture']['name'])) {
        $picture = time() . '_' . $_FILES['picture']['name'];
        move_uploaded_file($_FILES['picture']['tmp_name'], 'uploads/doctors/' . $picture);
    }

    $update = $pdo->prepare("UPDATE doctors SET 
        full_name=?, email=?, phone=?, gender=?, dob=?, address=?, 
        specialization=?, department_id=?, license_number=?, years_of_experience=?, 
        education=?, availability=?, status=?, bio=?,   picture=? 
        WHERE id=?");

    $update->execute([
        $full_name, $email, $phone, $gender, $dob, $address,
        $specialization, $department_id, $license_number, $years_of_experience,
        $education, $availability, $status, $bio,  $picture,
        $id
    ]);

    $_SESSION['message'] = "Doctor updated successfully.";
    header("Location: doctorlist.php");
    exit();
}

// Fetch departments
$departments = $pdo->query("SELECT id, name FROM departments")->fetchAll(PDO::FETCH_ASSOC);
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
                <h4 class="mb-4 text-white">Edit Doctor</h4>
            <form method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6 mb-3 form-floating">
            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($doctor['full_name']); ?>" required>
            <label>Full Name</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($doctor['email']); ?>" required>
            <label>Email</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($doctor['phone']); ?>" required>
            <label>Phone</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <select class="form-select" name="gender">
                <option value="Male" <?= $doctor['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $doctor['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
            </select>
            <label>Gender</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <input type="date" class="form-control" name="dob" value="<?= htmlspecialchars($doctor['dob']); ?>">
            <label>Date of Birth</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <textarea class="form-control" name="address" style="height: 80px"><?= htmlspecialchars($doctor['address']); ?></textarea>
            <label>Address</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <select class="form-select" name="specialization">
                <option value="Cardiologist" <?= $doctor['specialization'] == 'Cardiologist' ? 'selected' : '' ?>>Cardiologist</option>
                <option value="Dermatologist" <?= $doctor['specialization'] == 'Dermatologist' ? 'selected' : '' ?>>Dermatologist</option>
                <option value="Pediatrician" <?= $doctor['specialization'] == 'Pediatrician' ? 'selected' : '' ?>>Pediatrician</option>
                <option value="Surgeon" <?= $doctor['specialization'] == 'Surgeon' ? 'selected' : '' ?>>Surgeon</option>
                <option value="General Physician" <?= $doctor['specialization'] == 'General Physician' ? 'selected' : '' ?>>General Physician</option>
            </select>
            <label>Specialization</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <select class="form-select" name="department_id">
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $doctor['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Department</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <input type="text" class="form-control" name="license_number" value="<?= htmlspecialchars($doctor['license_number']); ?>">
            <label>License Number</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <input type="number" class="form-control" name="years_of_experience" value="<?= htmlspecialchars($doctor['years_of_experience']); ?>">
            <label>Years of Experience</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <textarea class="form-control" name="education" style="height: 80px"><?= htmlspecialchars($doctor['education']); ?></textarea>
            <label>Education</label>
        </div>

        <div class="col-md-6 mb-3 form-floating">
            <input type="text" class="form-control" name="availability" value="<?= htmlspecialchars($doctor['availability']); ?>">
            <label>Availability</label>
        </div>

        <div class="col-md-12 mb-3 form-floating">
            <select class="form-select" name="status">
                <option value="Active" <?= $doctor['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $doctor['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
            <label>Status</label>
        </div>

        <div class="col-md-12 mb-3 form-floating">
            <textarea class="form-control" name="bio" style="height: 100px"><?= htmlspecialchars($doctor['bio']); ?></textarea>
            <label>Short Bio</label>
        </div>

        <div class="col-md-12 mb-3">
            <label class="text-white">Change Picture</label>
            <input type="file" class="form-control" name="picture">
            <small class="text-light">Current: <?= htmlspecialchars($doctor['picture']); ?></small>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary w-100">Update</button>
        </div>
    </div>
</form>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</div>
</body>
</html>
