<?php
session_start();

if ($_SERVER['HTTPS'] != "on") {
    $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirect_url");
    exit;
}


if (!isset($_SESSION['staff_id'])) {
    header('Location: staff_login.php');
    exit;
}

require_once 'config/db_config.php';
require_once 'access_control.php'; // Include the access control file

// Fetch the logged-in staff user's role
$sql = "SELECT role FROM cbs_staff WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['staff_id']);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$role = $staff['role'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
        <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>

  <div class="container">
    <h1>Dashboard</h1>
    <div class="row">
      <?php if (has_permission($role, 'booking')): ?>
        <a href="scanner.php" class="list-group-item list-group-item-action">Check-in</a>
      <?php endif; ?>
       <?php if (has_permission($role, 'bookings_list')): ?>
        <a href="bookings.php" class="list-group-item list-group-item-action">Bookings</a>
      <?php endif; ?>
      <?php if (has_permission($role, 'tickets')): ?>
        <a href="tickets.php" class="list-group-item list-group-item-action">Tickets</a>
      <?php endif; ?>
      <?php if (has_permission($role, 'users')): ?>
        <a href="staff_users.php" class="list-group-item list-group-item-action">Users</a>
      <?php endif; ?>
      <?php if (has_permission($role, 'account')): ?>
        <a href="staff_account.php" class="list-group-item list-group-item-action">My Account</a>
      <?php endif; ?>
        <a href="staff_logout.php" class="list-group-item list-group-item-action">Logout</a>
    </div>
  </div>
</body>
</html>
