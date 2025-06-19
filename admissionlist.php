<?php
session_start();
require_once 'config/db.php';

// Fetch all admissions with patient, room, and doctor details
$query = "
    SELECT 
        a.id,
        p.full_name AS patient_name,
        r.room_number,
        d.full_name AS doctor_name,
        a.reason,
        a.admission_date,
        a.discharge_date,
        a.status
    FROM admissions a
    JOIN patients p ON a.patient_id = p.id
    JOIN rooms r ON a.room_id = r.id
    LEFT JOIN doctors d ON a.doctor_id = d.id
    ORDER BY a.admission_date DESC
";

$admissions = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
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
            <div class="bg-secondary text-center rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="mb-0 text-white">Admission List</h6>
                    <a href="add_admission.php" class="btn btn-sm btn-primary">Add Admission</a>
                </div>

                <div class="table-responsive">
                    <table class="table text-start text-white align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th scope="col">#</th>
                                <th scope="col">Patient</th>
                                <th scope="col">Room</th>
                                <th scope="col">Doctor</th>
                                <!-- <th scope="col">Reason</th> -->
                                <th scope="col">Admission Date</th>
                                <th scope="col">Discharge Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($admissions) > 0): ?>
                                <?php foreach ($admissions as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                        <td><?= htmlspecialchars($row['room_number']) ?></td>
                                        <td><?= $row['doctor_name'] ? htmlspecialchars($row['doctor_name']) : 'N/A' ?></td>
                                        <!-- <td><?= htmlspecialchars($row['reason']) ?></td> -->
                                        <td><?= date('d M Y', strtotime($row['admission_date'])) ?></td>
                                        <td><?= $row['discharge_date'] ? date('d M Y, H:i', strtotime($row['discharge_date'])) : '-' ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['status'] === 'Admitted' ? 'success' : 'danger' ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_admission.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="delete_admission.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No admissions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
