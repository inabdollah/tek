<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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

function fetch_daily_payments_past_week($conn, $event_permissions_condition) {
    $date_today = (new DateTime('now', new DateTimeZone('Etc/GMT-3')))->format('Y-m-d');
    $date_week_ago = (new DateTime('now', new DateTimeZone('Etc/GMT-3')))->modify('-7 days')->format('Y-m-d');

    $sql = "SELECT DATE(created) as date, SUM(total) as total_paid
            FROM cbs_bookings
            WHERE status = 'confirmed'
            AND DATE(created) >= '$date_week_ago'
            AND DATE(created) <= '$date_today'
            $event_permissions_condition
            GROUP BY DATE(created)
            ORDER BY DATE(created)";

    $result = $conn->query($sql);

    $daily_payments = [];
    while ($row = $result->fetch_assoc()) {
        $daily_payments[$row['date']] = $row['total_paid'];
    }

    return $daily_payments;
}

$daily_payments_past_week = fetch_daily_payments_past_week($conn, $event_permissions_condition);


// Query to fetch the total paid of all time confirmed bookings
$total_paid_all_time_sql = "SELECT SUM(total) as total_paid FROM cbs_bookings WHERE status = 'confirmed'"
. $event_permissions_condition;
$result = $conn->query($total_paid_all_time_sql);
$row = $result->fetch_assoc();
$total_paid_all_time = $row['total_paid'] ?? 0;

// Query to fetch the total paid of confirmed bookings today only
$date = new DateTime('now', new DateTimeZone('Etc/GMT-3'));
$date_today = $date->format('Y-m-d');
$total_paid_today_sql = "SELECT SUM(total) as total_paid FROM cbs_bookings WHERE status = 'confirmed' AND DATE(created) = '$date_today'"
. $event_permissions_condition;
$result = $conn->query($total_paid_today_sql);
$row = $result->fetch_assoc();
$total_paid_today = $row['total_paid'] ?? 0;

// Query to fetch the total number of confirmed bookings
$total_confirmed_bookings_sql = "SELECT COUNT(*) as total_confirmed FROM cbs_bookings WHERE status = 'confirmed'"
. $event_permissions_condition;
$result = $conn->query($total_confirmed_bookings_sql);
$row = $result->fetch_assoc();
$total_confirmed_bookings = $row['total_confirmed'];


?>