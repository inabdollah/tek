<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db_config.php';

$show_id = isset($_POST['show_id']) ? $_POST['show_id'] : 0;

$sql = "SELECT s.id, s.name, s.top, s.left, IF(ss.show_id = ? AND ss.seat_id = s.id, 1, 0) as is_selected
        FROM cbs_seats s
        LEFT JOIN cbs_shows_seats ss ON s.id = ss.seat_id
        WHERE s.venue_id = (SELECT venue_id FROM cbs_shows WHERE id = ?)
        ORDER BY s.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $show_id, $show_id);
$stmt->execute();
$result = $stmt->get_result();
$seats = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($seats);
?>
