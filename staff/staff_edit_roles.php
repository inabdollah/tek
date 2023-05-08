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

// Fetch the permissions from the database
$sql = "SELECT * FROM cbs_role_permissions";
$result = $conn->query($sql);
$permissions_db = $result->fetch_all(MYSQLI_ASSOC);

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['permissions'] as $role => $permissions) {
        foreach ($permissions as $page_name => $is_allowed) {
            $is_allowed = $is_allowed == '1' ? 1 : 0;
            $sql = "UPDATE cbs_role_permissions SET is_allowed = ? WHERE role = ? AND page_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iss', $is_allowed, $role, $page_name);
            $stmt->execute();
        }
    }
    header('Location: staff_edit_roles.php');
    exit;
}

// Convert the fetched permissions into the required format
$roles = [
    'Super Admin' => [],
    'Staff Admin' => [],
    'Staff Member' => [],
];

foreach ($permissions_db as $permission) {
    $roles[$permission['role']][$permission['page_name']] = $permission['is_allowed'] == 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Roles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
        <?php include 'header.php'; ?>
    <?php include 'tabbar.php'; ?>

    <div class="container">
        <h1>Edit Roles</h1>
        <form action="staff_edit_roles.php" method="POST">
            <?php foreach ($roles as $role => $permissions): ?>
                <?php if ($role !== 'Super Admin'): ?>
                    <div class="card mb-3">
                        <div class="card-header"><?= $role ?></div>
                        <div class="card-body">
                            <?php foreach ($permissions as $page_name => $is_allowed): ?>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="<?= $role ?>-<?= $page_name ?>" name="permissions[<?= $role ?>][<?= $page_name ?>]" value="1" <?= $is_allowed ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="<?= $role ?>-<?= $page_name ?>"><?= $page_name ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>
