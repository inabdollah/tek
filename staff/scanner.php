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
require_once 'access_control.php';

if (!has_permission($_SESSION['role'], 'booking')) {
    header('Location: index.php');
    exit;
}
$ticket_number = isset($_GET['ticket_number']) ? $_GET['ticket_number'] : '';

// Check if the check_in parameter is set
if (isset($_GET['check_in'])) {
    $new_status = $_GET['check_in'] == '1' ? 'T' : 'F';
    // Update the ticket status in the cbs_bookings_tickets table
    $update_sql = "UPDATE cbs_bookings_tickets SET is_used = ? WHERE ticket_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ss', $new_status, $ticket_number);
    $update_stmt->execute();
}

$sql = <<<SQL
SELECT
  bt.id as ticket_id,
  b.uuid as booking_number,
  b.c_name as customer_name,
  b.c_phone as customer_phone,
  e_ml.content as event_name,
  v_ml.content as venue_name,
  s.name as seat_name,
  bt.is_used as ticket_status,
  b.status as status
FROM
  cbs_bookings_tickets bt
  JOIN cbs_bookings b ON bt.booking_id = b.id
  JOIN cbs_events e ON b.event_id = e.id
  JOIN cbs_multi_lang e_ml ON e.id = e_ml.foreign_id AND e_ml.model = 'pjEvent' AND e_ml.field = 'title'
  JOIN cbs_seats s ON bt.seat_id = s.id
  JOIN cbs_venues v ON s.venue_id = v.id
  JOIN cbs_multi_lang v_ml ON v.id = v_ml.foreign_id AND v_ml.model = 'pjVenue'
WHERE
  bt.ticket_id = ?
SQL;

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $ticket_number);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();


$related_tickets = array();

if ($booking) {
    $related_tickets_sql = <<<SQL
SELECT
  bt.ticket_id as ticket_id,
  s.name as seat_name,
  bt.is_used as ticket_status
FROM
  cbs_bookings_tickets bt
  JOIN cbs_bookings b ON bt.booking_id = b.id
  JOIN cbs_seats s ON bt.seat_id = s.id
WHERE
  b.uuid = ? AND b.c_phone = ?
SQL;

    $related_tickets_stmt = $conn->prepare($related_tickets_sql);
    $related_tickets_stmt->bind_param('ss', $booking['booking_number'], $booking['customer_phone']);
    $related_tickets_stmt->execute();
    $related_tickets_result = $related_tickets_stmt->get_result();
    $related_tickets = $related_tickets_result->fetch_all(MYSQLI_ASSOC);
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tekets Staff | Check-in</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="https://tekets.com/qsc/style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css"> </head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>

		<div class="container">
			<h1>Check-in</h1>
			<form action="" method="GET" id="qr-form" class="mb-3">
				<div class="barcode-container">
					<div id="livestream_scanner">
						<div class="scanner-wrapper" id="scanner-wrapper">
							<video id="qr-scanner-container" playsinline></video>
						</div>
						<div class="input-group">
							<input type="text" class="form-control" name="ticket_number" id="qr-result-input" placeholder="Ticket number..." value="<?= htmlspecialchars($ticket_number) ?>">
							<button type="submit" class="btn btn-primary" style="display:none;">Search</button>
						</div>
						<button id="start-camera" class="camera-btn btn btn-default" type="button" data-toggle="modal" data-target="#livestream_scanner" style="color: white; margin-left: -2px;"> <i class="fa fa-camera"></i> </button>
			</form>
			<div class="row">
				<div class="col-12">
					<?php if ($ticket_number && $booking && $booking['status'] != 'cancelled'): ?>
						<?php if ($booking['ticket_status'] == 'F'): ?> <a href="?ticket_number=<?= $ticket_number ?>&check_in=1" class="btn btn-primary btn-chech btn-chechin mb-2 mt-2">Check-in</a>
							<?php else: ?> <button disabled class="btn btn-secondary btn-danger btn-chech mb-2 mt-2">Checked-in</button>
								<?php endif; ?>
									<?php endif; ?>
				</div>
			</div>
			</div>

			<?php if ($ticket_number): ?>
				<?php if ($booking): ?>
					<?php if ($booking['status'] == 'cancelled'): ?>
						<div class="alert alert-danger mb-2 mt-2" role="alert"> Booking is Cancelled </div>
						<?php elseif ($booking['status'] == 'confirmed'): ?>
							<?php endif; ?>
								<div class="card">
									<div class="card-body">
										<div class="d-flex justify-content-between align-items-center text-muted">
											<div>
												<?= htmlspecialchars($booking['customer_name']) ?>
											</div>
											<div>
												<?= htmlspecialchars($booking['customer_phone']) ?>
											</div>
										</div>
										<hr>
										<h1 class="text-center"><span class="me-2">Seat:</span><?= htmlspecialchars($booking['seat_name']) ?></h1>
										<div> <strong>Event:</strong>
											<?= htmlspecialchars($booking['event_name']) ?>
										</div>
										<div> <strong>Venue:</strong>
											<?= htmlspecialchars($booking['venue_name']) ?>
										</div>
									</div>
									<div class="card-footer d-flex justify-content-between align-items-center font-13">
										<?= htmlspecialchars($booking['booking_number']) ?>
										
																		<?php if ($ticket_number && $booking && $booking['status'] != 'cancelled'): ?>
							<?php if (count($related_tickets) > 1): ?>
					<div>
						<button type="button" class="btn btn-primary btn-chech font-13 related-btn" data-bs-toggle="modal" data-bs-target="#relatedTicketsModal"> Related Tickets </button>
						<div class="modal fade" id="relatedTicketsModal" tabindex="-1" aria-labelledby="relatedTicketsModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="relatedTicketsModalLabel">Related Tickets</h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
										<ul class="list-group">
											<?php foreach ($related_tickets as $related_ticket): ?>
												<?php if ($related_ticket['ticket_id'] !== $ticket_number): ?> 
          <li class="list-group-item d-flex justify-content-between align-items-center txt-link-nodoc" data-ticket-id="<?= htmlspecialchars($related_ticket['ticket_id']) ?>" data-seat-name="<?= htmlspecialchars($related_ticket['seat_name']) ?>">
            <span>Seat: <?= htmlspecialchars($related_ticket['seat_name']) ?></span>
            <?php if ($related_ticket['ticket_status'] == 'F'): ?>
              <button onclick="updateTicketStatus('<?= htmlspecialchars($related_ticket['ticket_id']) ?>', 1, false)" class="btn btn-primary btn-sm check-in-button">Check-in</button>
            <?php else: ?>
              <button disabled onclick="updateTicketStatus('<?= htmlspecialchars($related_ticket['ticket_id']) ?>', 0, false)" class="btn btn-danger btn-sm check-in-button">Checked-in</button>
            <?php endif; ?>
          </li>
        
													<?php endif; ?>
														<?php endforeach; ?>
										</ul>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>
					<?php endif; ?>

            </span> </div>
								</div>
								
								
								</div>

								<?php else: ?>
									<?php if ($ticket_number): ?>
										<p>No booking found for ticket number
											<?= htmlspecialchars($ticket_number) ?>.</p>
										<?php endif; ?>
											<?php endif; ?>
												<?php endif; ?>
		</div>
		<script src="https://tekets.com/qsc/zxing.min.js"></script>
		<script src="https://tekets.com/qsc/script.js"></script>
		<script>
		document.getElementById('qr-form').addEventListener('submit', function(event) {
			const checkInButtons = document.getElementsByClassName('check-in-button');
			for(let i = 0; i < checkInButtons.length; i++) {
				if(checkInButtons[i] === document.activeElement) {
					event.preventDefault();
					break;
				}
			}
		});
		</script>
		<script>
		function updateTicketStatus(ticketId, checkInStatus, closeModal = true) {
			const xhr = new XMLHttpRequest();
			xhr.open('GET', '?ticket_number=' + ticketId + '&check_in=' + checkInStatus, true);
			xhr.onload = function() {
				if(xhr.status === 200) {
					if(closeModal) {
						location.reload();
					} else {
						// Get the current ticket's list element and update its content
						const ticketElement = document.querySelector(`[data-ticket-id="${ticketId}"]`);
						if(checkInStatus == 1) {
							ticketElement.innerHTML = `
            Seat: ${ticketElement.dataset.seatName}
            <button onclick="updateTicketStatus('${ticketId}', 0, false)" class="btn btn-danger btn-sm check-in-button">Cancel Check-in</button>
          `;
						} else {
							ticketElement.innerHTML = `
            Seat: ${ticketElement.dataset.seatName}
            <button onclick="updateTicketStatus('${ticketId}', 1, false)" class="btn btn-primary btn-sm btn-chechin check-in-button">Check-in</button>
          `;
						}
					}
				} else {
					console.error('Request failed. Returned status: ' + xhr.status);
				}
			};
			xhr.send();
		}
		</script>
</body>

</html>