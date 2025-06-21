<?php
session_start();
require_once 'config/db.php';

// DELETE staff if `?delete=id` is set
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Staff member deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: stafflist.php");
    exit;
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
            $where_clause = "WHERE s.full_name LIKE ? OR s.email LIKE ? OR s.phone LIKE ? OR r.title LIKE ? OR d.name LIKE ?";
            $params = ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"];
        }

        // Get total count for pagination
        $count_sql = "SELECT COUNT(*) FROM staff s LEFT JOIN staff_roles r ON s.role_id = r.id LEFT JOIN departments d ON s.department_id = d.id $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $limit);

        // Get staff with pagination
        $sql = "SELECT s.*, r.title AS role_title, d.name AS department_name FROM staff s LEFT JOIN staff_roles r ON s.role_id = r.id LEFT JOIN departments d ON s.department_id = d.id $where_clause ORDER BY s.id DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'staff' => $staffList,
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
        echo json_encode(['success' => false, 'error' => 'Error loading staff: ' . $e->getMessage()]);
        exit;
    }
}

// Initial page load - fetch first page
$limit = 10;
$stmt = $pdo->query("
    SELECT s.*, r.title AS role_title, d.name AS department_name
    FROM staff s
    LEFT JOIN staff_roles r ON s.role_id = r.id
    LEFT JOIN departments d ON s.department_id = d.id
    ORDER BY s.id DESC
    LIMIT $limit
");
$staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_stmt = $pdo->query("SELECT COUNT(*) FROM staff");
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);
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
            <div class="bg-secondary rounded p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-4 text-white">Staff List</h6>
                    <a href="add_staff.php" class="btn btn-light btn-sm">Add Staff</a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Search Box -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search staff..." />
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">Total: <span id="totalRecords"><?= $total_records ?></span> staff</small>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered text-white">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Picture</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="staffTableBody">
                            <?php foreach ($staffList as $index => $staff): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <img src="uploads/staff/<?= $staff['picture'] ?? 'default-staff.png' ?>" alt="Pic" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                    </td>
                                    <td><?= htmlspecialchars($staff['full_name']) ?></td>
                                    <td><?= htmlspecialchars($staff['email']) ?></td>
                                    <td><?= htmlspecialchars($staff['phone']) ?></td>
                                    <td><?= htmlspecialchars($staff['gender']) ?></td>
                                    <td><?= htmlspecialchars($staff['role_title'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($staff['department_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($staff['status']) ?></td>
                                    <td>
                                        <a href="edit_staff.php?id=<?= $staff['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="stafflist.php?delete=<?= $staff['id'] ?>" onclick="return confirm('Are you sure you want to delete this staff member?')" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($staffList) === 0): ?>
                                <tr><td colspan="10">No staff found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Staff pagination">
                    <ul class="pagination justify-content-center" id="paginationContainer">
                        <!-- Pagination will be populated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    let searchTimeout;

    // Load staff with AJAX
    function loadStaff(page = 1, search = '') {
        $('#loadingIndicator').show();

        $.ajax({
            url: 'stafflist.php',
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
                    updateTable(response.staff, page, response.pagination.per_page);
                    updatePagination(response.pagination);
                    $('#totalRecords').text(response.pagination.total_records);
                } else {
                    showAlert('danger', response.error || 'Error loading staff');
                }
            },
            error: function() {
                $('#loadingIndicator').hide();
                showAlert('danger', 'Network error occurred');
            }
        });
    }

    // Update table content
    function updateTable(staff, page, perPage) {
        let tbody = $('#staffTableBody');
        tbody.empty();
        if (staff.length === 0) {
            tbody.append('<tr><td colspan="10" class="text-center">No staff found.</td></tr>');
            return;
        }
        staff.forEach(function(staffMember, index) {
            let rowNumber = ((page - 1) * perPage) + index + 1;
            tbody.append(`
                <tr>
                    <td>${rowNumber}</td>
                    <td><img src="uploads/staff/${staffMember.picture || 'default-staff.png'}" alt="Pic" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;"></td>
                    <td>${escapeHtml(staffMember.full_name)}</td>
                    <td>${escapeHtml(staffMember.email)}</td>
                    <td>${escapeHtml(staffMember.phone)}</td>
                    <td>${escapeHtml(staffMember.gender)}</td>
                    <td>${escapeHtml(staffMember.role_title || 'N/A')}</td>
                    <td>${escapeHtml(staffMember.department_name || 'N/A')}</td>
                    <td>${escapeHtml(staffMember.status)}</td>
                    <td>
                        <a href="edit_staff.php?id=${staffMember.id}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="stafflist.php?delete=${staffMember.id}" onclick="return confirm('Are you sure you want to delete this staff member?')" class="btn btn-sm btn-danger">Delete</a>
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
            loadStaff(currentPage, searchValue);
        }, 300);
    });

    // Pagination click
    $(document).on('click', '.page-link[data-page]', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        loadStaff(currentPage, $('#searchInput').val());
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
