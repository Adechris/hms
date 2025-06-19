<?php
session_start();
require_once 'config/db.php';

// Delete appointment if `delete_id` is present
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['message'] = "Appointment deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Failed to delete appointment: " . $e->getMessage();
    }

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Fetch appointments
try {
    $stmt = $pdo->query("
        SELECT a.id, a.appointment_date, a.status,  a.reason,
               p.full_name AS patient_name, d.full_name AS doctor_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        ORDER BY a.appointment_date DESC
    ");
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $appointments = [];
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-white">Appointments</h6>
                    <a href="add_appointment.php" class="btn btn-light btn-sm">Add Appointment</a>
                </div>

                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4 text-white">Appointment List</h6>

                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-info"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table text-white">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (count($appointments) > 0): ?>
                                    <?php foreach ($appointments as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                            <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                                            <td><?= date('M d, Y H:i', strtotime($row['appointment_date'])) ?></td>
                                            <td>
                                                <?php
                                                    $badge = match ($row['status']) {
                                                        'Pending' => 'warning',
                                                        'Completed' => 'success',
                                                        'Cancelled' => 'danger',
                                                        default => 'secondary',
                                                    };
                                                ?>
                                                <span class="badge bg-<?= $badge ?>"><?= $row['status'] ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($row['reason']) ?></td>
                                            <td>
                                                <a href="edit_appointment.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Delete this appointment?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">No appointments found.</td>
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

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
