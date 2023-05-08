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

// Fetch the event name from the database
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;
$event_name_sql = "SELECT e_ml.content as event_name FROM cbs_events e JOIN cbs_multi_lang e_ml ON e.id = e_ml.foreign_id AND e_ml.model = 'pjEvent' AND e_ml.field = 'title' WHERE e.id = ?";
$event_name_stmt = $conn->prepare($event_name_sql);
$event_name_stmt->bind_param('i', $event_id);
$event_name_stmt->execute();
$event_name_result = $event_name_stmt->get_result();
$event_name_row = $event_name_result->fetch_assoc();
$event_name = $event_name_row['event_name'];

$sql = "SELECT s.id, s.event_id, s.venue_id, v_ml.content as venue_name, s.date_time, s.price_id, s.price, ml.content as seat_type, ml2.content as seat_color
        FROM cbs_shows s
        JOIN cbs_multi_lang ml ON s.price_id = ml.foreign_id AND ml.field = 'price_name' AND ml.model = 'pjPrice'
        JOIN cbs_multi_lang ml2 ON s.price_id = ml2.foreign_id AND ml2.field = 'price_color' AND ml2.model = 'pjPrice'
        JOIN cbs_venues v ON s.venue_id = v.id
        JOIN cbs_multi_lang v_ml ON v.id = v_ml.foreign_id AND v_ml.field = 'name' AND v_ml.model = 'pjVenue'
        WHERE s.event_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$result = $stmt->get_result();
$shows = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $show_ids = $_POST['show_id'];
  $new_prices = $_POST['new_price'];

  for ($i = 0; $i < count($show_ids); $i++) {
    $show_id = $show_ids[$i];
    $new_price = $new_prices[$i];

    $update_sql = "UPDATE cbs_shows SET price = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('di', $new_price, $show_id);
    $update_stmt->execute();
  }

  header("Location: edit_shows.php?event_id=$event_id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Shows</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
  <link rel="stylesheet" href="css/zoom1.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .seat-color {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      display: inline-block;
    }
    
    .svg-map {
    }
    
    .zoom-btns {
    padding-top: 15px;
    padding-left: 15px;
    position: absolute;
    z-index: 10001;
}

  .modal-dialog.modal-dialog-centered {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    width: 100vw;
    height: 100%;
    max-width: none;
    margin: 0;
  }
  .modal-content {
    height: 100%;
  }
  
  .modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 0 !important;
}

#seatsContainer {
  background-size: 100% 100%;
  width: 700px;
  height: 700px;
}

#seatsSvg {
  width: 700px;
  height: 700px;
  viewBox: 0 0 800 800;
}

@media (max-width: 768px) {
  #seatsContainer {
    width: 380px;
    height: 380px;
    margin-top: -100px;
  }

  #seatsSvg {
    width: 380px;
    height: 380px;
  }
}



</style>


  </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>
  <div class="container">
    <h1><?= htmlspecialchars($event_name ?? 'Unknown Event') ?></h1>
    <form method="post">
      <div class="row">
        <?php foreach ($shows as $show): ?>
          <div class="col-md-4 mb-3">
            <div class="card">
              <div class="card-body">
               <h5 class="card-title"> <span class="seat-color" style="background-color: <?= htmlspecialchars($show['seat_color']) ?>;"></span>
                  <span class="ms-2"><?= htmlspecialchars($show['seat_type']) ?></span> </h5>
                <div class="d-flex align-items-center">
                </div>
                <input type="hidden" name="show_id[]" value="<?= $show['id'] ?>">
                <div class="input-group mb-3">
                  <span class="input-group-text">Price</span>
                  <input type="number" name="new_price[]" value="<?= $show['price'] ?>" step="0.01" class="form-control">
                </div>
                <div class="input-group mb-3">
                  <span class="input-group-text">Color</span>
                  <input type="text" value="<?= htmlspecialchars($show['seat_color']) ?>" class="form-control" readonly>
                </div>
                <div class="input-group mb-3">
                  <span class="input-group-text">Venue</span>
                  <input type="text" value="<?= htmlspecialchars($show['venue_name']) ?>" class="form-control" readonly>
                </div>
                <p>Date Time: <?= htmlspecialchars(date('Y-m-d h:i A', strtotime($show['date_time']))) ?></p>
                <div class="d-flex">
                  <button type="submit" class="btn btn-primary me-2">Save</button>
                  <button type="button" class="btn btn-primary seats-btn" data-show-id="<?= $show['id'] ?>">Seats</button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="btn btn-primary mb-3">Save All</button>
    </form>
  </div>

    
  <!-- Seats Modal -->
<div class="modal" tabindex="-1" id="seatsModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="width: 100%; height: 100%;">
      <div class="modal-header">
        <h5 class="modal-title">Seats</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex align-items-center justify-content-center" style="position: relative;">
<div id="seatsContainer" style="background-image: url('https://tekets.com/m/app/web/upload/maps/9_63c23e4685eea09fa5b7324aa6fba5e6.jpg');">
  <svg id="seatsSvg" viewBox="0 0 800 800"></svg>
</div>
      </div>
    </div>
  </div>
</div>

    
    
  </div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="js/zoom1.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const seatsBtns = document.querySelectorAll('.seats-btn');
  const seatsModal = new bootstrap.Modal(document.getElementById('seatsModal'));
  const seatsModalCloseBtns = document.querySelectorAll('[data-bs-dismiss="modal"]');
  const seatsSvg = document.getElementById('seatsSvg');

  seatsBtns.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const showId = btn.dataset.showId;
      const seatColor = btn.closest('.card-body').querySelector('.seat-color').style.backgroundColor;
      
      try {
        const response = await fetch('fetch_seats.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `show_id=${showId}`,
        });

        const seats = await response.json();
        console.log('Seats fetched:', seats);
        seatsSvg.innerHTML = '';

        seats.forEach((seat) => {
          const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
          circle.setAttribute('cx', seat.left);
          circle.setAttribute('cy', seat.top);
          circle.setAttribute('r', 5);
          circle.setAttribute('fill', seat.is_selected ? seatColor : '#ccc');
          seatsSvg.appendChild(circle);
        });

        seatsModal.show();
      } catch (error) {
        console.error('Error fetching seats:', error);
      }
    });
  });

  seatsModalCloseBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      seatsModal.hide();
    });
  });
});
</script>

</body>
</html>
