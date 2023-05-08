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

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Delete the user from the database
    $sql = "DELETE FROM cbs_staff WHERE id = ? AND role = 'Staff Member'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // Redirect to the staff users page
    header('Location: staff_users.php');
    exit;
} else {
    // Redirect to the staff users page if the 'id' parameter is not set
    header('Location: staff_users.php');
    exit;
}
