<?php
session_start();
require_once("./config/db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST["email"];
  $password = $_POST["password"];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user["password"])) {
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_role"] = $user["role"];
    header("Location: home.php");
    exit;
  } else {
    $error = "Invalid email or password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include("inc/sections/inc_head.php"); ?>

<body>
  <!-- Security Awareness Modal -->
  <div class="modal fade" id="securityModal" tabindex="-1" aria-labelledby="securityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-warning">
        <div class="modal-header border-0 pb-1">
          <h5 class="modal-title text-dark fw-bold" id="securityModalLabel">
            <i class="fas fa-shield-exclamation me-2"></i>Security Notice
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-dark">
          <div class="d-flex align-items-start">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-triangle text-danger fs-2 me-3"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="fw-bold mb-2">Important Security Alert!</h6>
              <p class="mb-2">
                <strong>COMPOVINE TECHNOLOGIES LIMITED</strong> will <u>NEVER</u> ask for your account details via:
              </p>
              <ul class="mb-3">
                <li>Email or text messages</li>
                <li>Phone calls</li>
                <li>Social media</li>
                <li>Third-party websites</li>
              </ul>
              <p class="mb-0">
                <strong>Always verify suspicious messages by contacting us directly through official channels.</strong>
                Kindly debunk any such message whenever you see it!
              </p>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">
            <i class="fas fa-check me-1"></i>I Understand
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
      <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
        <div class="bg-secondary rounded p-4 p-sm-5 my-4 mx-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="#" class="">
              <h3 class="text-white"><i class="me-2"></i>HMS</h3>
            </a>
            <h3>Sign In</h3>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="post" action="">
            <div class="form-floating mb-3">
              <input type="email" class="form-control" name="email" id="floatingInput" placeholder="name@example.com" required>
              <label for="floatingInput">Email address</label>
            </div>
            <div class="form-floating mb-4">
              <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" required>
              <label for="floatingPassword">Password</label>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-4">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="exampleCheck1">
                <label class="form-check-label" for="exampleCheck1">Remember me</label>
              </div>
              <a href="forgotPassword.php">Forgot Password</a>
            </div>
            <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Sign In</button>
            <p class="text-center mb-0">Don't have an Account? <a href="register.php">Sign Up</a></p>
          </form>

          <!-- Security Notice Link -->
          <div class="text-center mt-3">
            <small>
              <a href="#" class="text-muted text-decoration-none" data-bs-toggle="modal" data-bs-target="#securityModal">
                <i class="fas fa-shield-alt me-1"></i>Security Notice
              </a>
            </small>
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
  <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
  <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
  <script src="js/main.js"></script>

  <script>
    // Auto-show the security modal when page loads (optional)
    document.addEventListener('DOMContentLoaded', function() {
      // Uncomment the line below if you want the modal to show automatically on page load
      new bootstrap.Modal(document.getElementById('securityModal')).show();
      
      // Alternative: Show modal only on first visit (using localStorage)
      if (!localStorage.getItem('securityNoticeShown')) {
        new bootstrap.Modal(document.getElementById('securityModal')).show();
        localStorage.setItem('securityNoticeShown', 'true');
      }
    });
  </script>
</body>
</html>