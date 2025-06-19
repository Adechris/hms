<?php
require_once("./config/db.php");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $full_name = $_POST["full_name"];
  $email = $_POST["email"];
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];
  $role = "patient"; // default role

  if ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } else {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
      $error = "Email already registered.";
    } else {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
      if ($stmt->execute([$full_name, $email, $hashedPassword, $role])) {
        $success = "Account created successfully. You can now log in.";
      } else {
        $error = "Something went wrong.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include_once("../inc/sections/inc_head.php"); ?>
<body>
  <div class="container-fluid">
    <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
      <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
        <div class="bg-secondary rounded p-4 p-sm-5 my-4 mx-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="text-primary"><i class="fa fa-user-plus me-2"></i>Register</h3>
            <a href="login.php" class="text-white">Login</a>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
          <?php endif; ?>

          <form method="post" action="">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="full_name" id="floatingName" placeholder="Full Name" required>
              <label for="floatingName">Full Name</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" name="email" id="floatingEmail" placeholder="name@example.com" required>
              <label for="floatingEmail">Email address</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" required>
              <label for="floatingPassword">Password</label>
            </div>
            <div class="form-floating mb-4">
              <input type="password" class="form-control" name="confirm_password" id="floatingConfirm" placeholder="Confirm Password" required>
              <label for="floatingConfirm">Confirm Password</label>
            </div>
            <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Create Account</button>
            <p class="text-center mb-0">Already have an account? <a href="login.php">Login</a></p>
          </form>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
