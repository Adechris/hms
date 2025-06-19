<?php
require_once 'config/db.php';

// Set headers to prompt download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="payrolls_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Output header row
fputcsv($output, [
    'Name', 'Role', 'Month', 'Base Salary', 'Bonuses',
    'Deductions', 'Net Salary', 'Status', 'Paid At'
]);

// Query payrolls with staff and doctors
$stmt = $pdo->query("
    SELECT 
        p.*, 
        s.full_name AS staff_name, 
        d.full_name AS doctor_name
    FROM payrolls p
    LEFT JOIN staff s ON p.staff_id = s.id
    LEFT JOIN doctors d ON p.doctor_id = d.id
    ORDER BY p.salary_month DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $name = $row['staff_name'] ?? $row['doctor_name'];
    $role = $row['staff_id'] ? 'Staff' : 'Doctor';

    fputcsv($output, [
        $name,
        $role,
        $row['salary_month'],
        $row['base_salary'],
        $row['bonuses'],
        $row['deductions'],
        $row['net_salary'],
        $row['payment_status'],
        $row['paid_at'] ?? '—'
    ]);
}

fclose($output);
exit;
