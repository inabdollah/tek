<?php
function has_permission($role, $page_name) {
    global $conn;
    $sql = "SELECT is_allowed FROM cbs_role_permissions WHERE role = ? AND page_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $role, $page_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $permission = $result->fetch_assoc();

    return $permission['is_allowed'] == 1;
}
?>