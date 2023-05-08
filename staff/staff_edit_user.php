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

// Get the user ID from the URL
$user_id = isset($_GET['id']) ? $_GET['id'] : '';

// Retrieve user details
$sql = "SELECT id, name, email, role, event_permissions FROM cbs_staff WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Retrieve all available events
$events_sql = "SELECT e.id, ml.content as event_name
FROM cbs_events e
JOIN cbs_multi_lang ml ON e.id = ml.foreign_id AND ml.model = 'pjEvent' AND ml.field = 'title'";
$events_result = $conn->query($events_sql);

// Decode stored event permissions
$event_permissions = json_decode($user['event_permissions'], true);
if ($event_permissions === null) {
    $event_permissions = [];
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $event_permissions = isset($_POST['event_permissions']) ? $_POST['event_permissions'] : [];

    // Update the user in the database
    $event_permissions_json = json_encode($event_permissions);
    $sql = "UPDATE cbs_staff SET name = ?, email = ?, role = ?, event_permissions = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $name, $email, $role, $event_permissions_json, $user_id);
    $stmt->execute();

    header('Location: staff_users.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
        <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>

  <div class="container">
    <h1>Edit User</h1>
    <form action="" method="POST">
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-select" id="role" name="role" required>
          <option value="Super Admin" <?= $user['role'] == 'Super Admin' ? 'selected' : '' ?>>Super Admin</option>
          <option value="Staff Admin" <?= $user['role'] == 'Staff Admin' ? 'selected' : '' ?>>Staff Admin</option>
          <option value="Staff Member" <?= $user['role'] == 'Staff Member' ? 'selected' : '' ?>>Staff Member</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="event_permissions" class="form-label">Event Permissions</label>
        <select class="form-select" id="event_permissions" name="event_permissions[]" multiple required>
          <?php while ($event = $events_result->fetch_assoc()): ?>
            <option value="<?= $event['id'] ?>" <?= in_array($event['id'], $event_permissions) ? 'selected' : '' ?>>
              <?= htmlspecialchars($event['event_name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</body>
</html>
