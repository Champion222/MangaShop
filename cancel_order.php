<?php

session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./form/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    $sql_check_owner = "SELECT id FROM orders WHERE id = ? AND user_id = ?";
    $params_check_owner = array($order_id, $user_id);
    $stmt_check_owner = sqlsrv_query($conn, $sql_check_owner, $params_check_owner);

    if ($stmt_check_owner === false) {
        error_log("Error checking order owner: " . print_r(sqlsrv_errors(), true));
        header("Location: dashboard.php?status=error");
        exit();
    }

    $rows_found = sqlsrv_has_rows($stmt_check_owner);
    sqlsrv_free_stmt($stmt_check_owner);

    if ($rows_found) {
        $sql_delete_order = "DELETE FROM orders WHERE id = ?";
        $params_delete_order = array($order_id);
        $stmt_delete_order = sqlsrv_query($conn, $sql_delete_order, $params_delete_order);

        if ($stmt_delete_order === false) {
            error_log("Error deleting order: " . print_r(sqlsrv_errors(), true));
            header("Location: dashboard.php?status=error");
            exit();
        } else {
            $rows_affected = sqlsrv_rows_affected($stmt_delete_order);
            if ($rows_affected > 0) {
                header("Location: dashboard.php?status=success");
                exit();
            } else {
                header("Location: dashboard.php?status=notfound");
                exit();
            }
        }
        sqlsrv_free_stmt($stmt_delete_order);
    } else {
        header("Location: dashboard.php?status=unauthorized");
        exit();
    }
} else {
    header("Location: dashboard.php?status=notfound");
    exit();
}
sqlsrv_close($conn);
?>