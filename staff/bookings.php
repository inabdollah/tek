<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bookings List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>

  <div class="container">
    <h1>Bookings List</h1>
            <?php include 'fetch_stats.php'; ?>
  <div class="col-md-16">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Payments This Week</h5>
      <canvas id="paymentsChart"></canvas>
    </div>
  </div>
</div>

    <div class="row mb-3">
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Total Paid (All Time)</h5>
        <p class="card-text"><?= htmlspecialchars(number_format($total_paid_all_time, 2)) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Total Paid (Today)</h5>
        <p class="card-text"><?= htmlspecialchars(number_format($total_paid_today, 2)) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Total Bookings</h5>
        <p class="card-text"><?= htmlspecialchars($total_confirmed_bookings) ?></p>
      </div>
    </div>
  </div>
</div>


    <div class="row mb-3">
  <div class="col-md-12">
    <input type="text" id="search-input" class="form-control" placeholder="Search...">
  </div>
</div>
    <div class="row" id="bookings-container">
        <?php include 'fetch_bookings.php'; ?>
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" id="load-more">Load More</button>
        </div>
    </div>
  </div>
  <script>
document.getElementById("search-input").addEventListener("input", function() {
  // Remove existing bookings
  document.getElementById("bookings-container").innerHTML = '';

  // Reset the Load More button
  document.getElementById("load-more").disabled = false;
  document.getElementById("load-more").innerText = "Load More";

  // Trigger the click event on the Load More button
  document.getElementById("load-more").click();
});

document.getElementById("load-more").addEventListener("click", function() {
  const offset = document.querySelectorAll("#bookings-container .col-md-6").length;
  const limit = 10;
  const search = document.getElementById("search-input").value;

  fetch("fetch_bookings.php?offset=" + offset + "&limit=" + limit + "&search=" + encodeURIComponent(search))
    .then(response => {
      if (response.status === 204) {
        document.getElementById("load-more").disabled = true;
        document.getElementById("load-more").innerText = "No more bookings";
      } else {
        return response.text();
      }
    })
    .then(html => {
      if (html) {
        document.getElementById("bookings-container").insertAdjacentHTML("beforeend", html);
      }
    });
});

  const dailyPaymentsPastWeek = <?= json_encode($daily_payments_past_week) ?>;

  </script>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const labels = [];
    const data = [];
    const today = new Date();
    for (let i = 6; i >= 0; i--) {
      const currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() - i);
      const currentDateString = currentDate.toISOString().split('T')[0];
      labels.push(currentDateString);
      data.push(dailyPaymentsPastWeek[currentDateString] || 0);
    }

    const ctx = document.getElementById('paymentsChart').getContext('2d');
    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Daily Payments',
          data: data,
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  });
</script>

</body>
</html>
