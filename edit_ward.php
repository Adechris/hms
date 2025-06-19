<?php
session_start();
require_once 'config/db.php';

// Fetch the ward by ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid ward ID.";
    header("Location: wardlist.php");
    exit;
}

$ward_id = intval($_GET['id']);

// Fetch the ward details
try {
    $stmt = $pdo->prepare("SELECT * FROM wards WHERE id = ?");
    $stmt->execute([$ward_id]);
    $ward = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ward) {
        $_SESSION['error'] = "Ward not found.";
        header("Location: wardlist.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: wardlist.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (!$name) {
        $_SESSION['error'] = "Ward name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE wards SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $ward_id]);

            $_SESSION['success'] = "Ward updated successfully.";
            header("Location: wardlist.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Update failed: " . $e->getMessage();
        }
    }
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
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded p-4">
                        <h6 class="mb-4 text-white">Edit Ward</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($ward['name']) ?>" required>
                                <label for="name">Ward Name</label>
                            </div>

                            <div class="form-floating mb-4">
                                <textarea class="form-control" id="description" name="description" style="height: 120px;"><?= htmlspecialchars($ward['description']) ?></textarea>
                                <label for="description">Description</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Update</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
