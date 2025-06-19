<?php
session_start();
require_once("./config/db.php");

$message = "";
$error = "";

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = generateToken();
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour validity

        // Save token and expiry in DB
        $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
        $update->execute([$token, $expiry, $user['id']]);

        // Create reset link
        $resetLink = "http://localhost/hms/reset_password.php?token=$token";

        // Simulate sending email (for real: use PHPMailer or mail())
        // mail($email, "Reset Your Password", "Click the link: $resetLink");

        $message = "A password reset link has been sent to your email: <br><code>$resetLink</code>";
    } else {
        $error = "No account found with that email address.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<?php include("inc/sections/inc_head.php"); ?>

<body>
  <div class="container-fluid">
    <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
      <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
        <div class="bg-secondary rounded p-4 p-sm-5 my-4 mx-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="#">
              <h3 class="text-primary"><i class="fa fa-key me-2"></i>Reset Password</h3>
            </a>
            <h3>Forgot Password</h3>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php elseif ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
          <?php endif; ?>

          <form method="post" action="">
            <div class="form-floating mb-4">
              <input type="email" class="form-control" name="email" id="floatingEmail" placeholder="name@example.com" required>
              <label for="floatingEmail">Enter your email</label>
            </div>

            <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Send Reset Link</button>
            <p class="text-center mb-0">
              <a href="login.php" class="text-white">Back to Login</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- JS Libraries -->
  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="lib/chart/chart.min.js"></script>
  <script src="lib/easing/easing.min.js"></script>
  <script src="lib/waypoints/waypoints.min.js"></script>
  <script src="lib/owlcarousel/owl.carousel.min.js"></script>
  <script src="lib/tempusdominus/js/moment.min.js"></script>
  <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
  <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
