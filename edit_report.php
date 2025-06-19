<?php
session_start();
require_once 'config/db.php';

// Get report ID from query string
$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch report details
if ($report_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM medical_records WHERE id = ?");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        $_SESSION['error'] = "Medical report not found.";
        header("Location: reports.php");
        exit;
    }
} else {
    $_SESSION['error'] = "No report ID provided.";
    header("Location: reports.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis = trim($_POST['diagnosis']);
    $treatment = trim($_POST['treatment']);
    $doctor_notes = trim($_POST['doctor_notes']);
    $record_date = $_POST['record_date'];

    if (!$diagnosis || !$treatment || !$record_date) {
        $_SESSION['error'] = "Diagnosis, treatment, and record date are required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE medical_records SET diagnosis = ?, treatment = ?, doctor_notes = ?, record_date = ? WHERE id = ?");
            $stmt->execute([$diagnosis, $treatment, $doctor_notes, $record_date, $report_id]);
            $_SESSION['success'] = "Medical report updated successfully.";
            header("Location: reports.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating report: " . $e->getMessage();
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
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4 text-white">Edit Medical Report</h6>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php elseif (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form action="edit_report.php?id=<?php echo $report_id; ?>" method="POST">
                            <!-- Diagnosis -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" name="diagnosis" id="diagnosis" style="height: 100px;" required><?php echo htmlspecialchars($report['diagnosis']); ?></textarea>
                                <label for="diagnosis">Diagnosis</label>
                            </div>

                            <!-- Treatment -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" name="treatment" id="treatment" style="height: 100px;" required><?php echo htmlspecialchars($report['treatment']); ?></textarea>
                                <label for="treatment">Treatment</label>
                            </div>

                            <!-- Doctor Notes -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" name="doctor_notes" id="doctor_notes" style="height: 100px;"><?php echo htmlspecialchars($report['doctor_notes']); ?></textarea>
                                <label for="doctor_notes">Doctor Notes</label>
                            </div>

                            <!-- Record Date -->
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="record_date" id="record_date" value="<?php echo $report['record_date']; ?>" required>
                                <label for="record_date">Record Date</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Update Report</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
