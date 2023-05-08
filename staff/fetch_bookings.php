<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
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

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Retrieve user details
$sql = "SELECT id, role, event_permissions FROM cbs_staff WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_SESSION['staff_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Decode stored event permissions
if ($user['event_permissions'] !== null) {
    $event_permissions = json_decode($user['event_permissions'], true);
    if ($event_permissions === ["all"] || $user['role'] === 'Super Admin') {
        $event_permissions = [];
    }
} else {
    $event_permissions = [];
}

// Prepare the SQL condition for event permissions
$event_permissions_condition = "";
if (!empty($event_permissions)) {
    $event_permissions_condition = " AND event_id IN (" . implode(',', $event_permissions) . ")";
}

// Prepare the SQL query
$sql = "SELECT
    b.id,
    b.uuid,
    b.c_name,
    b.c_email,
    b.c_phone,
    b.status,
    b.total,
    e_ml.content as event_name,
    b.created,
    GROUP_CONCAT(s.name) as booked_seats
FROM
    cbs_bookings b
JOIN cbs_events e ON b.event_id = e.id
JOIN cbs_multi_lang e_ml ON e.id = e_ml.foreign_id
AND e_ml.model = 'pjEvent'
AND e_ml.field = 'title'
JOIN cbs_bookings_tickets bt ON bt.booking_id = b.id
JOIN cbs_seats s ON bt.seat_id = s.id
WHERE 1"
. $event_permissions_condition
. (!empty($search) ? " AND (b.uuid LIKE '%$search%' OR b.c_name LIKE '%$search%' OR b.c_phone LIKE '%$search%' OR b.c_email LIKE '%$search%')" : "")
. " GROUP BY b.id, e_ml.content"
. " ORDER BY b.id DESC"
. " LIMIT $offset, $limit";

$result = $conn->query($sql);
$num_rows = $result->num_rows;

function getStatusColor($status) {
    if ($status === 'confirmed') {
        return 'bg-success';
    } elseif ($status === 'cancelled') {
        return 'bg-danger';
    } else {
        return 'bg-warning';
    }
}

function getStatusIcon($status) {
    if ($status === 'confirmed') {
        return 'fas fa-check-circle';
    } elseif ($status === 'cancelled') {
        return 'fas fa-times-circle';
    } else {
        return 'fas fa-exclamation-circle';
    }
}

if ($num_rows === 0) {
    http_response_code(204);
    exit;
}

while ($row = $result->fetch_assoc()): ?>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($row['c_name']) ?></h5>
                    <div>
                        <span class="contact-info me-2">
                            <i class="fas fa-envelope"></i>
                            <span class="info"><?= htmlspecialchars($row['c_email']) ?></span>
                        </span>
                        <span class="contact-info">
                            <i class="fas fa-phone"></i>
                            <span class="info"><?= htmlspecialchars($row['c_phone']) ?></span>
                        </span>
                    </div>
                </div>
                <p class="card-text mb-1">
                    <small class="text-muted"><?= htmlspecialchars($row['uuid']) ?></small>
                </p>
                <hr class="mt-1 mb-2" style="opacity: 0.5;">
                <div class="card-text mb-1">
                    <strong>Event:</strong> <?= htmlspecialchars($row['event_name']) ?>
                </div>
                <div class="card-text mb-1">
                    <strong>Seats:</strong> <?= htmlspecialchars($row['booked_seats']) ?>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <small class="text-muted"><?= date("M j, Y, g:i A", strtotime($row['created'])) ?></small>
                <div class="d-flex">
                    <?php if ($row['status'] === 'confirmed'): ?>
                        <span class="badge bg-success d-flex align-items-center px-2">
                            <i class="fas fa-check-circle me-1"></i>
                            <span>KD <?= htmlspecialchars($row['total']) ?></span>
                        </span>
                    <?php elseif ($row['status'] === 'cancelled'): ?>
                        <span class="badge bg-danger d-flex align-items-center px-2">
                            <i class="fas fa-times-circle me-1"></i>
                            <span>KD <?= htmlspecialchars($row['total']) ?></span>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning d-flex align-items-center px-2">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <span>KD <?= htmlspecialchars($row['total']) ?></span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>