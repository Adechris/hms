<?php
session_start();
require_once 'config/db.php';

// Auto-generate next available room number
function generateRoomNumber($pdo) {
    $prefix = "RM-";
    $i = 1;

    do {
        $room_number = $prefix . str_pad($i, 3, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
        $stmt->execute([$room_number]);
        $exists = $stmt->fetch();
        $i++;
    } while ($exists);

    return $room_number;
}

// Fetch available wards
try {
    $wards = $pdo->query("SELECT id, name FROM wards ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to fetch wards: " . $e->getMessage();
    $wards = [];
}

$generatedRoomNumber = generateRoomNumber($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = trim($_POST['room_number']);
    $type = $_POST['type'] ?? '';
    $bed_count = intval($_POST['bed_count']);
    $availability_status = $_POST['availability_status'] ?? 'Available';
    $ward_id = $_POST['ward_id'] ?? null;

    if (!$room_number || !$type || $bed_count <= 0 || !$ward_id) {
        $_SESSION['error'] = "Please fill all required fields correctly.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO rooms (room_number, type, bed_count, availability_status, ward_id) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$room_number, $type, $bed_count, $availability_status, $ward_id]);
            $_SESSION['success'] = "Room added successfully.";
            header("Location: roomlist.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }

    header("Location: add_room.php");
    exit;
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
                        <h6 class="mb-4 text-white">Add Room</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form action="add_room.php" method="POST">
                            <!-- Room Number -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="room_number" name="room_number" value="<?= $generatedRoomNumber ?>" readonly required>
                                <label for="room_number">Room Number</label>
                            </div>

                            <!-- Ward -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="ward_id" name="ward_id" required>
                                    <option value="" disabled selected>Select Ward</option>
                                    <?php foreach ($wards as $ward): ?>
                                        <option value="<?= $ward['id']; ?>"><?= htmlspecialchars($ward['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="ward_id">Ward</label>
                            </div>

                            <!-- Type -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="type" name="type" required>
                                    <option disabled selected>Select Type</option>
                                    <option value="General">General</option>
                                    <option value="Private">Private</option>
                                    <option value="ICU">ICU</option>
                                    <option value="Emergency">Emergency</option>
                                </select>
                                <label for="type">Room Type</label>
                            </div>

                            <!-- Bed Count -->
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="bed_count" name="bed_count" min="1" value="1" required>
                                <label for="bed_count">Bed Count</label>
                            </div>

                            <!-- Availability -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="availability_status" name="availability_status" required>
                                    <option value="Available" selected>Available</option>
                                    <option value="Occupied">Occupied</option>
                                    <option value="Maintenance">Maintenance</option>
                                </select>
                                <label for="availability_status">Availability</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Add Room</button>
                        </form>
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
