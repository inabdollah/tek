<?php
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

$search = isset($_GET['search']) ? $_GET['search'] : '';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = 10;

$sql = "SELECT created, number, text FROM cbs_plugin_sms";

if (!empty($search)) {
  $sql .= " WHERE text LIKE '%$search%' OR number LIKE '%$search%'";
}

$sql .= " ORDER BY created DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tekets Staff | SMS Cards</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
    }

    .card {
      margin-bottom: 10px;
    }

    .separator {
      border-top: 1px solid #ccc;
      margin: 10px 0;
    }
  </style>
<script>
  function loadMore() {
    const searchInput = document.querySelector('input[name="search"]');
    const search = encodeURIComponent(searchInput.value);
    const currentOffsetInput = document.querySelector('input[name="offset"]');
    const currentOffset = parseInt(currentOffsetInput.value, 10);
    const newOffset = currentOffset + 10;
    currentOffsetInput.value = newOffset;

    const url = `tickets.php?search=${search}&offset=${newOffset}`;
    window.location.href = url;
  }
</script>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>
  <div class="container">
    <h1>Sent SMS</h1>
<form action="" method="GET" class="mb-3">
  <div class="input-group">
    <input type="hidden" name="offset" value="<?= $offset ?>">
    <input type="text" class="form-control" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-primary" type="submit">Search</button>
  </div>
</form>
    <?php foreach ($data as $row): ?>
    <div class="card">
      <div class="card-body">
        <div class="row mb-2">
          <div class="col">
           <?= substr($row['text'], 0, 50) ?>...
          </div>
        </div>
        <div class="separator"></div>
        <div class="row">
                   <div class="col">
            <?php
              $date = new DateTime($row['created']);
              $formatted_date = $date->format('Y-m-d h:i A');
            ?>
            <i class="fas fa-calendar"></i> <?= $formatted_date ?>
          </div>
          <div class="col">
            <i class="fas fa-phone"></i> <?= $row['number'] ?>
          </div>
          <div class="col">
            <a href="https://wa.me/<?= $row['number'] ?>?text=<?= urlencode($row['text']) ?>" target="_blank">
              <button class="btn btn-primary"><i class="fa-brands fa-whatsapp"></i> Send</button>
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <button class="btn btn-secondary" onclick="loadMore()">Load More</button>
  </div>
</body>
</html>
