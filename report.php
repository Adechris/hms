<?php
session_start();
require_once 'config/db.php';

try {
    $stmt = $pdo->query("
        SELECT 
            mr.id,
            mr.diagnosis,
            mr.treatment,
            mr.doctor_notes,
            mr.record_date,
            p.full_name AS patient_name,
            d.full_name AS doctor_name
        FROM medical_records mr
        JOIN patients p ON mr.patient_id = p.id
        JOIN doctors d ON mr.doctor_id = d.id
        ORDER BY mr.record_date DESC
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $records = [];
}
?>


<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php'); ?>

<body>
<div class="container-fluid position-relative d-flex p-0">

    <!-- Sidebar -->
    <?php include('inc/sections/inc_sidebar.php'); ?>

    <!-- Content -->
    <div class="content">
        <?php include('inc/sections/inc_navbar.php'); ?>

        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4 text-white">Medical Reports</h6>

                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table text-white">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Diagnosis</th>
                                        <th>Treatment</th>
                                        <th>Doctor Notes</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($records) > 0): ?>
                                        <?php foreach ($records as $index => $record): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($record['patient_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['doctor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['diagnosis']); ?></td>
                                                <td><?php echo htmlspecialchars($record['treatment']); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($record['doctor_notes'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($record['record_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6">No medical records found.</td>
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
