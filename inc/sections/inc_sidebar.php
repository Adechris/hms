<?php
// At the top of your page, add this PHP code to fetch user data
// session_start();

// Database connection (adjust these credentials to match your setup)
$host = 'localhost';
$dbname = 'hms_db'; // Your database name
$username = 'root'; // Your database username
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch user data from users table
    $user_id = $_SESSION['user_id'] ?? null; // Get user ID from session
    
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT full_name, email, picture FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set user data or defaults
        $user_name = $user['full_name'] ?? 'Admin User';
        $user_email = $user['email'] ?? '';
        $user_picture = $user['picture'] ?? 'default-user.jpg';
    } else {
        // Redirect to login if no session
        header('Location: login.php');
        exit();
    }
    
} catch(PDOException $e) {
    // Handle connection error
    $user_name = 'Admin User';
    $user_email = '';
    $user_picture = 'default-user.jpg';
}
?>

<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-secondary navbar-dark">
        <a href="#" class="navbar-brand mx-4 mb-3">
            <img src="assets/logo.jpeg" alt="hms logo" style="width:50px; height:50px;">
            <!-- <h3 class="text-white"> HMS</h3> -->
        </a>
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img class="rounded-circle" 
                     src="uploads/<?php echo htmlspecialchars($user_picture); ?>" 
                     alt="User Profile" 
                     style="width: 40px; height: 40px; object-fit: cover;"
                     onerror="this.src='img/user.jpg'">
                <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
            </div>
            <div class="ms-3">
                <h6 class="mb-0"><?php echo htmlspecialchars($user_name); ?></h6>
                <span>Admin</span>
            </div>
        </div>
        <div class="navbar-nav w-100">
            <a href="dashboard.php" class="nav-item nav-link active text-white"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>

             <div class="nav-item dropdown">
                 <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-building me-2"></i>Department</a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="departmentlist.php" class="dropdown-item text-white">Departments List</a>
                    <a href="add_department.php" class="dropdown-item text-white">Add Departments</a>
                </div>

                <div class="nav-item dropdown ">
              <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-user-tie me-2"></i>Staff Roles</a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="roles.php" class="dropdown-item text-white">Roles</a>
                    <a href="add_roles.php" class="dropdown-item text-white">Add Role</a>
                </div>
                <div class="nav-item dropdown">
              <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-users me-2"></i>Staff</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="stafflist.php" class="dropdown-item text-white">Staff List</a>
                    <a href="add_staff.php" class="dropdown-item text-white">Add Staff</a>
                </div>
            </div>
            <div class="nav-item dropdown">
             <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-user-injured me-2"></i>Patients</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="patientlist.php" class="dropdown-item text-white">Patients List</a>
                    <a href="add_patient.php" class="dropdown-item text-white">Add Patients</a>
                </div>
            </div>
            <div class="nav-item dropdown">
          <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-user-md me-2"></i>Doctors</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="doctorlist.php" class="dropdown-item text-white">Doctors List</a>
                    <a href="add_doctor.php" class="dropdown-item text-white">Add Doctors</a>
                </div>
            </div>
            <div class="nav-item dropdown">
           <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-calendar-check me-2"></i>Appointment</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="appointmentlist.php" class="dropdown-item text-white">Appointment List</a>
                    <a href="add_appointment.php" class="dropdown-item text-white">Add Appointments</a>
                </div>
            </div>

               <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-bed me-2"></i>Wards</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="wardlist.php" class="dropdown-item text-white">Wards List</a>
                    <a href="add_ward.php" class="dropdown-item text-white">Add Wards</a>
                </div>
            </div>

                <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-bed me-2"></i>Room</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="roomlist.php" class="dropdown-item text-white">Room List</a>
                    <a href="add_room.php" class="dropdown-item text-white">Add Rooms</a>
                </div>
            </div>
                <div class="nav-item dropdown">
             <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-hospital me-2"></i>Admission</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="admissionlist.php" class="dropdown-item text-white">Admission List</a>
                    <a href="add_admission.php" class="dropdown-item text-white">Add Admission </a>
                </div>
            </div>
            <div class="nav-item dropdown">
        <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-money-bill me-2"></i>Billing</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="#" class="dropdown-item text-white">Generate</a>
                    <a href="#" class="dropdown-item text-white">Billing List</a>
                </div>
            </div>
            <div class="nav-item dropdown">
           <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown"><i class="fa fa-chart-bar me-2"></i>Reports</a>

                <div class="dropdown-menu bg-transparent border-0">
                    <a href="add_report.php" class="dropdown-item text-white">Add Report</a>
                    <a href="report.php" class="dropdown-item text-white">Medical Report List</a>
                </div>
            </div>
   <a href="logout.php" class="nav-item nav-link text-white" onclick="return confirm('Are you sure you want to logout?');">
    <i class="fa fa-sign-out-alt me-2"></i>Logout
</a>


        </div>
    </nav>
</div>