<?php
session_start();
require_once 'config/db.php';

// Delete room if requested
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Room deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: roomlist.php");
    exit;
}

// Fetch all rooms
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_number")->fetchAll(PDO::FETCH_ASSOC);
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
                        <h6 class="mb-4 text-white">Room List</h6>
                        

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                          <div class="mt-3 text-start mb-4">
                            <a href="add_room.php" class="btn btn-primary">Add New Room</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-white">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Room Number</th>
                                        <th>Type</th>
                                        <th>Bed Count</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($rooms) > 0): ?>
                                        <?php foreach ($rooms as $i => $room): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($room['room_number']) ?></td>
                                                <td><?= $room['type'] ?></td>
                                                <td><?= $room['bed_count'] ?></td>
                                                <td><?= $room['availability_status'] ?></td>
                                                <td><?= $room['created_at'] ?></td>
                                                <td>
                                                    <a href="roomlist.php?delete=<?= $room['id'] ?>" class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this room?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No rooms found.</td>
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
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
