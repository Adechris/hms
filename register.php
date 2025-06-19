<?php
require_once("./config/db.php");

$success = "";
$error = "";

// Create uploads directory if it doesn't exist
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

// Only run this block if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $full_name = $_POST["full_name"];
  $email = $_POST["email"];
  $phone = $_POST["phone"];
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];

  $pictureName = "";
  if (isset($_FILES["picture"]) && $_FILES["picture"]["error"] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES["picture"]["tmp_name"];
    $originalName = basename($_FILES["picture"]["name"]);
    $pictureName = time() . "_" . $originalName;
    move_uploaded_file($tmpName, $uploadDir . $pictureName);
  }

  if ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      $error = "Email already registered.";
    } else {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, picture) VALUES (?, ?, ?, ?, ?)");
      if ($stmt->execute([$full_name, $email, $phone, $hashedPassword, $pictureName])) {
        // ✅ Store success message in session and redirect
        session_start();
        $_SESSION['register_success'] = "Account created successfully. You can now log in.";
        header("Location: login.php");
        exit(); // ⛔ Stop execution to prevent re-processing
      } else {
        $error = "Something went wrong.";
      }
    }
  }
}

// Check for success message from session
session_start();
if (isset($_SESSION['register_success'])) {
  $success = $_SESSION['register_success'];
  unset($_SESSION['register_success']); // clear it so it doesn't persist
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include_once("inc/sections/inc_head.php"); ?>
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

          <form method="post" action="" enctype="multipart/form-data">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="full_name" id="floatingName" placeholder="Full Name" required>
              <label for="floatingName">Full Name</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" name="email" id="floatingEmail" placeholder="name@example.com" required>
              <label for="floatingEmail">Email address</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="phone" id="floatingPhone" placeholder="Phone" required>
              <label for="floatingPhone">Phone</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" required>
              <label for="floatingPassword">Password</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" name="confirm_password" id="floatingConfirm" placeholder="Confirm Password" required>
              <label for="floatingConfirm">Confirm Password</label>
            </div>
            <div class="mb-3">
              <label for="picture" class="form-label text-white">Upload Picture</label>
              <input type="file" class="form-control" name="picture" id="picture" accept="image/*">
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
