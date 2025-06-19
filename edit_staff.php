<?php
session_start();
require_once 'config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = "Invalid staff ID.";
    header("Location: stafflist.php");
    exit;
}

// FETCH roles and departments
$roles = $pdo->query("SELECT id, title FROM staff_roles")->fetchAll(PDO::FETCH_ASSOC);
$departments = $pdo->query("SELECT id, name FROM departments")->fetchAll(PDO::FETCH_ASSOC);

// FETCH existing staff data
$stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->execute([$id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    $_SESSION['error'] = "Staff not found.";
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

    // Picture upload
    if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $imageName = uniqid('staff_') . '.' . $ext;
            $dest = "uploads/staff/$imageName";
            if (!is_dir("uploads/staff")) mkdir("uploads/staff", 0777, true);
            move_uploaded_file($_FILES['picture']['tmp_name'], $dest);
            $data['picture'] = $imageName;
        } else {
            $_SESSION['error'] = "Invalid image format.";
        }
    }

    if (!isset($_SESSION['error'])) {
        $setClause = "";
        foreach ($data as $key => $value) {
            $setClause .= "$key = :$key, ";
        }
        $setClause = rtrim($setClause, ', ');

        $sql = "UPDATE staff SET $setClause WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $data['id'] = $id;

        try {
            $stmt->execute($data);
            $_SESSION['success'] = "Staff updated successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Update failed: " . $e->getMessage();
        }

        header("Location: stafflist.php");
        exit;
    }
}

// Helper for selected value
function selected($a, $b) {
    return $a == $b ? 'selected' : '';
}
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
            <div class="bg-secondary rounded h-100 p-4">
                <h6 class="mb-4 text-white">Edit Staff</h6>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php elseif (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($staff['full_name']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($staff['email']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($staff['phone']) ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <select class="form-select" name="gender">
                                <option value="">Select Gender</option>
                                <option <?= selected($staff['gender'], 'Male') ?>>Male</option>
                                <option <?= selected($staff['gender'], 'Female') ?>>Female</option>
                                <option <?= selected($staff['gender'], 'Other') ?>>Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <input type="date" class="form-control" name="dob" value="<?= $staff['dob'] ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <select class="form-select" name="role_id">
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= selected($staff['role_id'], $role['id']) ?>><?= htmlspecialchars($role['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <select class="form-select" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= selected($staff['department_id'], $dept['id']) ?>><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <input type="date" class="form-control" name="hire_date" value="<?= $staff['hire_date'] ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <input type="number" class="form-control" name="salary" step="0.01" value="<?= $staff['salary'] ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <select class="form-select" name="status">
                                <option <?= selected($staff['status'], 'Active') ?>>Active</option>
                                <option <?= selected($staff['status'], 'Inactive') ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Change Picture</label>
                            <input type="file" class="form-control" name="picture" accept="image/*">
                            <?php if (!empty($staff['picture'])): ?>
                                <img src="uploads/staff/<?= $staff['picture'] ?>" class="mt-2 rounded" style="width: 80px; height: 80px; object-fit: cover;">
                            <?php endif; ?>
                        </div>

                        <div class="col-12 mb-3">
                            <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($staff['address']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Update Staff</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
</body>
</html>
