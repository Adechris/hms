<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Fetch counts
$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalStaff = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

// Fetch recent data for table
$recentAdmissions = $pdo->query("
    SELECT a.id, p.full_name AS patient_name, r.room_number, a.admission_date, a.status
    FROM admissions a
    JOIN patients p ON p.id = a.patient_id
    JOIN rooms r ON r.id = a.room_id
    ORDER BY a.admission_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$recentPayments = $pdo->query("
    SELECT pay.id, b.id AS bill_id, pay.amount_paid, pay.payment_method, pay.payment_date
    FROM payments pay
    JOIN bills b ON b.id = pay.bill_id
    ORDER BY pay.payment_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$recentBills = $pdo->query("
    SELECT b.id, p.full_name AS patient_name, b.total_amount, b.status, b.issued_at
    FROM bills b
    JOIN patients p ON p.id = b.patient_id
    ORDER BY b.issued_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<?php include('inc/sections/inc_head.php'); ?>
<body id="body" class="dark-mode">

<div class="container-fluid position-relative d-flex p-0">

<?php include('inc/sections/inc_sidebar.php'); ?>

<div class="content">
    <?php include('inc/sections/inc_navbar.php'); ?>

    <!-- Summary Cards -->
 <!-- Summary Cards (Improved Layout) -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <?php
        $cardData = [
            ['icon' => 'fa-users', 'title' => 'Total Patients', 'count' => $totalPatients],
            ['icon' => 'fa-user-md', 'title' => 'Total Doctors', 'count' => $totalDoctors],
            ['icon' => 'fa-user-tie', 'title' => 'Total Staff', 'count' => $totalStaff],
            ['icon' => 'fa-calendar-check', 'title' => 'Appointments', 'count' => $totalAppointments],
        ];
        foreach ($cardData as $card): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-secondary rounded d-flex align-items-center p-4" style="height: 140px;">
                    <div class="me-4">
                        <i class="fa <?= $card['icon'] ?> fa-3x text-white"></i>
                    </div>
                    <div class="flex-grow-1 text-white">
                        <p class="mb-1 fw-bold fs-6"><?= $card['title'] ?></p>
                        <h4 class="mb-0"><?= $card['count'] ?></h4>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


    <!-- Table Section -->
    <div class="container-fluid pt-4 px-4">
        <div class="row">
            <div class="col-12">
                <div class="bg-secondary rounded p-4">
                    <h6 class="mb-4">Recent Activities</h6>
                    <div class="table-responsive">
                        <table class="table text-white">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Details</th>
                                    <th>Status / Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $index = 1;
                                foreach ($recentAdmissions as $row): ?>
                                    <tr>
                                        <td><?= $index++ ?></td>
                                        <td>Admission</td>
                                        <td><?= $row['patient_name'] ?> (Room <?= $row['room_number'] ?>)</td>
                                        <td><span class="badge bg-info"><?= $row['status'] ?></span></td>
                                        <td><?= date('M d, Y H:i', strtotime($row['admission_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php foreach ($recentBills as $row): ?>
                                    <tr>
                                        <td><?= $index++ ?></td>
                                        <td>Bill</td>
                                        <td><?= $row['patient_name'] ?> — ₦<?= $row['total_amount'] ?></td>
                                        <td><span class="badge bg-<?= $row['status'] === 'Paid' ? 'success' : ($row['status'] === 'Partial' ? 'warning' : 'danger') ?>"><?= $row['status'] ?></span></td>
                                        <td><?= date('M d, Y', strtotime($row['issued_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php foreach ($recentPayments as $row): ?>
                                    <tr>
                                        <td><?= $index++ ?></td>
                                        <td>Payment</td>
                                        <td>Bill #<?= $row['bill_id'] ?> — ₦<?= $row['amount_paid'] ?></td>
                                        <td><?= $row['payment_method'] ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($row['payment_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if ($index === 1): ?>
                                    <tr>
                                        <td colspan="5">No recent activity.</td>
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

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/chart/chart.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/tempusdominus/js/moment.min.js"></script>
<script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="js/main.js"></script>



<script>
    const body = document.getElementById('body'); // Your body should have id="body"
    const toggle = document.getElementById('themeToggle');

    if (localStorage.getItem('theme') === 'light') {
        body.classList.remove('dark-mode');
        body.classList.add('light-mode');
        toggle.checked = true;
    }

    toggle.addEventListener('change', function () {
        if (this.checked) {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            localStorage.setItem('theme', 'light');
        } else {
            body.classList.remove('light-mode');
            body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
        }
    });
</script>


</body>
</html>
