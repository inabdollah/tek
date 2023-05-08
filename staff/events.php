<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();

if (!isset($_SESSION['staff_id'])) {
    header('Location: staff_login.php');
    exit;
}

require_once 'config/db_config.php';
require_once 'access_control.php';

if (!has_permission($_SESSION['role'], 'tickets')) {
    header('Location: index.php');
    exit;
}

// Retrieve events from the database
$sql = "SELECT e.id, e_ml.content as event_name FROM cbs_events e JOIN cbs_multi_lang e_ml ON e.id = e_ml.foreign_id AND e_ml.model = 'pjEvent' AND e_ml.field = 'title'";
$result = $conn->query($sql);
$events = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
        <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>

  <div class="container">
    <h1>Events</h1>
    <div class="row">
      <?php foreach ($events as $event): ?>
        <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($event['event_name']) ?></h5>
              <a href="edit_shows.php?event_id=<?= htmlspecialchars($event['id']) ?>" class="btn btn-primary">Shows</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
