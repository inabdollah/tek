<?php
require_once '../config/db_config.php';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$sql = <<<SQL
SELECT
  b.id as booking_id,
  b.created as booking_date,
  b.c_name as customer_name,
  b.c_phone as customer_phone,
  b.uuid as booking_number,
  b.status as booking_status,
  b.total as booking_total,
  COUNT(bt.id) as booked_seats
FROM
  cbs_bookings b
  JOIN cbs_bookings_tickets bt ON bt.booking_id = b.id
GROUP BY
  b.id
ORDER BY
  b.id DESC
LIMIT ?
OFFSET ?
SQL;

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($bookings);
