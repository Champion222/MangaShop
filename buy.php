<?php

session_start();
include 'db_connect.php';

$message = '';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=buy&product_id=" . ($_GET['product_id'] ?? ''));
    exit();
}

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $user_id = $_SESSION['user_id'];

    $sql_check_product = "SELECT id FROM products WHERE id = ?";
    $params_check_product = array($product_id);
    $stmt_check_product = sqlsrv_query($conn, $sql_check_product, $params_check_product);

    if ($stmt_check_product === false) {
        error_log("Error checking product existence: " . print_r(sqlsrv_errors(), true));
        $message = "មានបញ្ហាក្នុងការដំណើរការការទិញ។ សូមព្យាយាមម្តងទៀត។";
    } else {
        $rows_found = sqlsrv_has_rows($stmt_check_product);
        sqlsrv_free_stmt($stmt_check_product);

        if ($rows_found) {
            $sql_insert_order = "INSERT INTO orders (user_id, product_id, order_date) VALUES (?, ?, GETDATE())"; // Added GETDATE() for SQL Server
            $params_insert_order = array($user_id, $product_id);
            $stmt_insert_order = sqlsrv_query($conn, $sql_insert_order, $params_insert_order);

            if ($stmt_insert_order === false) {
                error_log("Error inserting order: " . print_r(sqlsrv_errors(), true));
                $message = "មានបញ្ហាក្នុងការដំណើរការការទិញ។ សូមព្យាយាមម្តងទៀត។";
            } else {
                $message = "ការទិញបានជោគជ័យ! សូមអរគុណសម្រាប់ការបញ្ជាទិញ។";
            }
            sqlsrv_free_stmt($stmt_insert_order);
        } else {
            $message = "ផលិតផលដែលបានជ្រើសរើសមិនមានទេ។";
        }
    }
} else {
    $message = "មិនមានលេខសម្គាល់ផលិតផលត្រូវបានផ្តល់ឱ្យទេ។";
}
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ស្ថានភាពការទិញ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">ស្ថានភាពការទិញ</h2>
        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-md <?php echo strpos($message, 'ជោគជ័យ') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-semibold transition duration-300 ease-in-out">
            Back to store
        </a>
        <a href="dashboard.php" class="ml-4 bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-md font-semibold transition duration-300 ease-in-out">
            View my orders
        </a>
    </div>
</body>
</html>