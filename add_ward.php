<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');

    if (!$name) {
        $_SESSION['error'] = "Ward name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO wards (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $_SESSION['success'] = "Ward added successfully.";
            header("Location: wardlist.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: wardlist.php");
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
                        <h6 class="mb-4 text-white">Add Ward</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form action="add_ward.php" method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Ward Name" required>
                                <label for="name">Ward Name</label>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea class="form-control" placeholder="Ward Description" id="description" name="description" style="height: 120px;"></textarea>
                                <label for="description">Description (optional)</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Add Ward</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content End -->

</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
