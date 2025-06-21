<?php
session_start();
require_once 'config/db.php';

// ✅ Handle AJAX Request
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 10; // Records per page
    $offset = ($page - 1) * $limit;

    try {
        // Build query with search
        $where_clause = '';
        $params = [];

        if (!empty($search)) {
            $where_clause = "WHERE name LIKE ? OR description LIKE ?";
            $params = ["%$search%", "%$search%"];
        }

        // Get total count for pagination
        $count_sql = "SELECT COUNT(*) FROM departments $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $limit);

        // Get departments with pagination
        $sql = "SELECT * FROM departments $where_clause ORDER BY name ASC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'departments' => $departments,
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
        echo json_encode([
            'success' => false,
            'error' => 'Error loading departments: ' . $e->getMessage()
        ]);
        exit;
    }
}

// ✅ Handle Delete Request
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $dept_id = intval($_GET['delete_id']);
    try {
        // Check if department exists
        $check = $pdo->prepare("SELECT id FROM departments WHERE id = ?");
        $check->execute([$dept_id]);
        if (!$check->fetch()) {
            $_SESSION['error'] = "Department not found.";
        } else {
            // Try deleting
            $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([$dept_id]);
            if ($stmt->rowCount()) {
                $_SESSION['success'] = "Department deleted successfully.";
            } else {
                $_SESSION['error'] = "Department could not be deleted.";
            }
        }
    } catch (PDOException $e) {
        // Catch foreign key violation
        if ($e->getCode() === '23000') {
            $_SESSION['error'] = "Cannot delete: Department is linked to existing staff.";
        } else {
            $_SESSION['error'] = "Deletion failed: " . $e->getMessage();
        }
    }
    header("Location: departmentlist.php");
    exit;
}

// ✅ Initial page load - fetch first page
try {
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY name ASC LIMIT 10");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM departments");
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / 10);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error loading departments: " . $e->getMessage();
    $departments = [];
    $total_records = 0;
    $total_pages = 0;
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
            <div class="bg-secondary rounded p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-white">Departments</h6>
                    <a href="add_department.php" class="btn btn-light btn-sm">Add Department</a>
                </div>
                <!-- ✅ Feedback Messages -->
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
                <!-- ✅ Search Box -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search departments..." />
                    </div>
                    <!-- <div class="col-md-6 text-end">
                        <small class="text-muted">Total: <span id="totalRecords"><?= $total_records ?></span> departments</small>
                    </div> -->
                </div>
                <!-- ✅ Loading Indicator -->
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
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="departmentTableBody">
                            <?php if ($departments): ?>
                                <?php foreach ($departments as $index => $dept): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($dept['name']) ?></td>
                                        <td><?= nl2br(htmlspecialchars($dept['description'])) ?></td>
                                        <td>
                                            <a href="edit_department.php?id=<?= $dept['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="?delete_id=<?= $dept['id'] ?>" class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this department?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No departments found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- ✅ Pagination -->
                <nav aria-label="Department pagination">
                    <ul class="pagination justify-content-center" id="paginationContainer">
                        <!-- Pagination will be populated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<script src="js/main.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    let searchTimeout;

    // ✅ Load departments with AJAX
    function loadDepartments(page = 1, search = '') {
        $('#loadingIndicator').show();

        $.ajax({
            url: 'departmentlist.php',
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
                    updateTable(response.departments, page, response.pagination.per_page);
                    updatePagination(response.pagination);
                    $('#totalRecords').text(response.pagination.total_records);
                } else {
                    showAlert('danger', response.error || 'Error loading departments');
                }
            },
            error: function() {
                $('#loadingIndicator').hide();
                showAlert('danger', 'Network error occurred');
            }
        });
    }

    // ✅ Update table content
    function updateTable(departments, page, perPage) {
        let tbody = $('#departmentTableBody');
        tbody.empty();
        if (departments.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center">No departments found.</td></tr>');
            return;
        }
        departments.forEach(function(dept, index) {
            let rowNumber = ((page - 1) * perPage) + index + 1;
            let description = dept.description ? dept.description.replace(/\n/g, '<br>') : '';

            tbody.append(`
                <tr>
                    <td>${rowNumber}</td>
                    <td>${escapeHtml(dept.name)}</td>
                    <td>${escapeHtml(description)}</td>
                    <td>
                        <a href="edit_department.php?id=${dept.id}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete_id=${dept.id}" class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this department?')">Delete</a>
                    </td>
                </tr>
            `);
        });
    }

    // ✅ Update pagination
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

    // ✅ Search functionality
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        let searchValue = $(this).val();

        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadDepartments(currentPage, searchValue);
        }, 300);
    });

    // ✅ Pagination click
    $(document).on('click', '.page-link[data-page]', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        loadDepartments(currentPage, $('#searchInput').val());
    });

    // ✅ Utility functions
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
