<?php
session_start();
require_once 'config/db.php';

// Handle deletion
if (isset($_GET['delete'])) {
    $idToDelete = $_GET['delete'];

    try {
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$idToDelete]);
        $_SESSION['message'] = "Patient deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting patient: " . $e->getMessage();
    }

    // Redirect to avoid resubmission
    header("Location: patientlist.php");
    exit();
}

// Handle AJAX Request
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 10; // Records per page
    $offset = ($page - 1) * $limit;

    try {
        $where_clause = '';
        $params = [];

        if (!empty($search)) {
            $where_clause = "WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR gender LIKE ? OR dob LIKE ?";
            $params = ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"];
        }

        // Get total count for pagination
        $count_sql = "SELECT COUNT(*) FROM patients $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $limit);

        // Get patients with pagination
        $sql = "SELECT * FROM patients $where_clause ORDER BY id ASC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'patients' => $patients,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total_records,
                'per_page' => $limit
            ]
        ]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error loading patients: ' . $e->getMessage()]);
        exit;
    }
}

// Initial page load - fetch first page
$limit = 10;
$stmt = $pdo->query("SELECT * FROM patients ORDER BY id ASC LIMIT $limit");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_stmt = $pdo->query("SELECT COUNT(*) FROM patients");
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);
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

        <!-- Patient Table -->
        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-white">Patients</h6>
                    <a href="add_patient.php" class="btn btn-light btn-sm">Add Patients</a>
                </div>
                <div class="col-12">
                    <div class="bg-secondary rounded p-4">
                        <h6 class="mb-4">Patient List</h6>

                        <!-- Display session messages -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php elseif (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Search Box -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search patients..." />
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">Total: <span id="totalRecords"><?= $total_records ?></span> patients</small>
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table text-white">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Gender</th>
                                        <th>Date of Birth</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="patientsTableBody">
                                    <?php if (count($patients) > 0): ?>
                                        <?php foreach ($patients as $index => $patient): ?>
                                            <tr>
                                                <th><?php echo $index + 1; ?></th>
                                                <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['dob']); ?></td>
                                                <td>
                                                    <a href="view_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-info btn-sm">View</a>
                                                    <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="patientlist.php?delete=<?php echo $patient['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this patient?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7">No patients found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="Patient pagination">
                            <ul class="pagination justify-content-center" id="paginationContainer">
                                <!-- Pagination will be populated by JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    let searchTimeout;

    // Load patients with AJAX
    function loadPatients(page = 1, search = '') {
        $('#loadingIndicator').show();

        $.ajax({
            url: 'patientlist.php',
            method: 'GET',
            data: {
                ajax: 1,
                page: page,
                search: search
            },
            dataType: 'json',
            success: function(response) {
                $('#loadingIndicator').hide();

                if (response.success) {
                    updateTable(response.patients, page, response.pagination.per_page);
                    updatePagination(response.pagination);
                    $('#totalRecords').text(response.pagination.total_records);
                } else {
                    showAlert('danger', response.error || 'Error loading patients');
                }
            },
            error: function() {
                $('#loadingIndicator').hide();
                showAlert('danger', 'Network error occurred');
            }
        });
    }

    // Update table content
    function updateTable(patients, page, perPage) {
        let tbody = $('#patientsTableBody');
        tbody.empty();
        if (patients.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center">No patients found.</td></tr>');
            return;
        }
        patients.forEach(function(patient, index) {
            let rowNumber = ((page - 1) * perPage) + index + 1;
            tbody.append(`
                <tr>
                    <td>${rowNumber}</td>
                    <td>${escapeHtml(patient.full_name)}</td>
                    <td>${escapeHtml(patient.email)}</td>
                    <td>${escapeHtml(patient.phone)}</td>
                    <td>${escapeHtml(patient.gender)}</td>
                    <td>${escapeHtml(patient.dob)}</td>
                    <td>
                        <a href="view_patient.php?id=${patient.id}" class="btn btn-info btn-sm">View</a>
                        <a href="edit_patient.php?id=${patient.id}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="patientlist.php?delete=${patient.id}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this patient?');">Delete</a>
                    </td>
                </tr>
            `);
        });
    }

    // Update pagination
    function updatePagination(pagination) {
        let container = $('#paginationContainer');
        container.empty();
        if (pagination.total_pages <= 1) return;

        // Previous button
        if (pagination.current_page > 1) {
            container.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                </li>
            `);
        }

        // Page numbers
        let startPage = Math.max(1, pagination.current_page - 2);
        let endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
        if (startPage > 1) {
            container.append('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
            if (startPage > 2) {
                container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
        }
        for (let i = startPage; i <= endPage; i++) {
            let activeClass = i === pagination.current_page ? 'active' : '';
            container.append(`
                <li class="page-item ${activeClass}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }
        if (endPage < pagination.total_pages) {
            if (endPage < pagination.total_pages - 1) {
                container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
            container.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${pagination.total_pages}">${pagination.total_pages}</a>
                </li>
            `);
        }

        // Next button
        if (pagination.current_page < pagination.total_pages) {
            container.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
                </li>
            `);
        }
    }

    // Search functionality
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        let searchValue = $(this).val();

        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadPatients(currentPage, searchValue);
        }, 300);
    });

    // Pagination click
    $(document).on('click', '.page-link[data-page]', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        loadPatients(currentPage, $('#searchInput').val());
    });

    // Utility function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        let map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Utility function to show alerts
    function showAlert(type, message) {
        let alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('.bg-secondary.rounded.p-4').prepend(alertHtml);
    }
});
</script>
</body>
</html>
