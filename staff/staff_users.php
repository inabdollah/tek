<?php
session_start();

if (!isset($_SESSION['staff_id'])) {
    header('Location: staff_login.php');
    exit;
}

require_once 'config/db_config.php';
require_once 'access_control.php';

if (!has_permission($_SESSION['role'], 'users')) {
    header('Location: index.php');
    exit;
}

// Fetch all staff users
$sql = "SELECT id, name, email, role FROM cbs_staff";
$result = $conn->query($sql);
$staff_users = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
        <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>

  <div class="container">
    <h1>Staff Users</h1>
    <div class="row">
      <div class="col">
        <a href="staff_add_user.php" class="btn btn-primary">Add New User</a>
      </div>
      <div class="col">
        <a href="staff_edit_roles.php" class="btn btn-primary">Edit Roles</a>
      </div>
    </div>
    <div class="row mt-4">
      <?php foreach ($staff_users as $user): ?>
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($user['name']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($user['email']) ?></p>
            <p class="card-text"><?= htmlspecialchars($user['role']) ?></p>
            <a href="staff_edit_user.php?id=<?= $user['id'] ?>" class="btn btn-primary">Edit</a>
            <?php if ($user['role'] === 'Staff Member'): ?>
              <a href="staff_delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger">Delete</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
