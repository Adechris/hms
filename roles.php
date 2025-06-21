<?php
session_start();
require_once 'config/db.php';

// DELETE role if `?delete=id` is set
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM staff_roles WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Role deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: roles.php");
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
            $where_clause = "WHERE title LIKE ? OR description LIKE ?";
            $params = ["%$search%", "%$search%"];
        }

        // Get total count for pagination
        $count_sql = "SELECT COUNT(*) FROM staff_roles $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $limit);

        // Get roles with pagination
        $sql = "SELECT * FROM staff_roles $where_clause ORDER BY id ASC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'roles' => $roles,
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
        echo json_encode(['success' => false, 'error' => 'Error loading roles: ' . $e->getMessage()]);
        exit;
    }
}

// Initial page load - fetch first page
$limit = 10;
$stmt = $pdo->query("SELECT * FROM staff_roles ORDER BY id ASC LIMIT $limit");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_stmt = $pdo->query("SELECT COUNT(*) FROM staff_roles");
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
                    <h6 class="mb-4 text-white">Staff Roles</h6>
                    <a href="add_roles.php" class="btn btn-light btn-sm">+ Add New Role</a>
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Search roles..." />
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">Total: <span id="totalRecords"><?= $total_records ?></span> roles</small>
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
                                <th>Title</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="rolesTableBody">
                            <?php foreach ($roles as $i => $role): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($role['title']) ?></td>
                                    <td><?= htmlspecialchars($role['description']) ?></td>
                                    <td>
                                        <a href="roles.php?delete=<?= $role['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this role?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($roles) === 0): ?>
                                <tr><td colspan="4">No roles found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Roles pagination">
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

    // Load roles with AJAX
    function loadRoles(page = 1, search = '') {
        $('#loadingIndicator').show();

        $.ajax({
            url: 'roles.php',
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
                    updateTable(response.roles, page, response.pagination.per_page);
                    updatePagination(response.pagination);
                    $('#totalRecords').text(response.pagination.total_records);
                } else {
                    showAlert('danger', response.error || 'Error loading roles');
                }
            },
            error: function() {
                $('#loadingIndicator').hide();
                showAlert('danger', 'Network error occurred');
            }
        });
    }

    // Update table content
    function updateTable(roles, page, perPage) {
        let tbody = $('#rolesTableBody');
        tbody.empty();
        if (roles.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center">No roles found.</td></tr>');
            return;
        }
        roles.forEach(function(role, index) {
            let rowNumber = ((page - 1) * perPage) + index + 1;
            tbody.append(`
                <tr>
                    <td>${rowNumber}</td>
                    <td>${escapeHtml(role.title)}</td>
                    <td>${escapeHtml(role.description)}</td>
                    <td>
                        <a href="roles.php?delete=${role.id}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this role?')">Delete</a>
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
            loadRoles(currentPage, searchValue);
        }, 300);
    });

    // Pagination click
    $(document).on('click', '.page-link[data-page]', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        loadRoles(currentPage, $('#searchInput').val());
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
