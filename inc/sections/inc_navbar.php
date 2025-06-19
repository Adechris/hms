<?php
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT full_name, picture FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $userName = $user['full_name'] ?? 'User';

    $userPic = (!empty($user['picture']) && file_exists(__DIR__ . '/../uploads/' . $user['picture']))
        ? 'uploads/' . $user['picture']
        : 'img/user.jpg';
} else {
    $userName = 'Guest';
    $userPic = 'img/user.jpg';
}
?>

<nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
    <a href="dashboard.php" class="navbar-brand d-flex d-lg-none me-4">
        <h2 class="text-primary mb-0"><i class="fa fa-user-edit"></i></h2>
    </a>
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars text-white"></i>
    </a>

    <div class="navbar-nav align-items-center ms-auto">

        <!-- 🌗 Light Mode Toggle -->
        <!-- <div class="nav-item me-3">
            <div class="form-check form-switch text-white">
                <input class="form-check-input" type="checkbox" id="themeToggle">
                <label class="form-check-label" for="themeToggle" style="cursor: pointer;">Light Mode</label>
            </div>
        </div> -->

        <?php if ($user): ?>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown">
                    <img class="rounded-circle me-lg-2" src="<?= htmlspecialchars($userPic) ?>" alt="User" style="width: 40px; height: 40px;">
                    <span class="d-none d-lg-inline-flex"><?= htmlspecialchars($userName) ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                    <a href="profile.php" class="dropdown-item">My Profile</a>
                    <a href="settings.php" class="dropdown-item">Settings</a>
                    <a href="logout.php" class="dropdown-item" onclick="return confirm('Are you sure you want to logout?')">Log Out</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</nav>
