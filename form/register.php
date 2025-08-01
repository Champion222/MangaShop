<?php

session_start();
include '../db_connect.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql_check_username = "SELECT id FROM users WHERE username = ?";
    $params_check_username = array($username);
    $stmt_check_username = sqlsrv_query($conn, $sql_check_username, $params_check_username);

    if ($stmt_check_username === false) {
        error_log("Error checking username existence: " . print_r(sqlsrv_errors(), true));
        $message = "មានបញ្ហាក្នុងការចុះឈ្មោះ។ សូមព្យាយាមម្តងទៀត។";
    } else {
        $rows_found = sqlsrv_has_rows($stmt_check_username);
        sqlsrv_free_stmt($stmt_check_username);

        if ($rows_found) {
            $message = "ឈ្មោះអ្នកប្រើប្រាស់នេះមានរួចហើយ។ សូមជ្រើសរើសឈ្មោះផ្សេង។";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert_user = "INSERT INTO users (username, password) VALUES (?, ?)";
            $params_insert_user = array($username, $hashed_password);
            $stmt_insert_user = sqlsrv_query($conn, $sql_insert_user, $params_insert_user);

            if ($stmt_insert_user === false) {
                error_log("Error inserting new user: " . print_r(sqlsrv_errors(), true));
                $message = "មានបញ្ហាក្នុងការចុះឈ្មោះ។ សូមព្យាយាមម្តងទៀត។";
            } else {
                $message = "ការចុះឈ្មោះបានជោគជ័យ! ឥឡូវអ្នកអាចចូលបាន។";
            }
            sqlsrv_free_stmt($stmt_insert_user);
        }
    }
}
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ចុះឈ្មោះ - ហាងឌីជីថល</title>
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
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">ចុះឈ្មោះគណនីថ្មី</h2>
        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-md <?php echo strpos($message, 'ជោគជ័យ') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">ឈ្មោះអ្នកប្រើប្រាស់:</label>
                <input type="text" id="username" name="username" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">ពាក្យសម្ងាត់:</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                ចុះឈ្មោះ
            </button>
        </form>
        <p class="mt-6 text-center text-gray-600">
            មានគណនីរួចហើយមែនទេ? <a href="login.php" class="text-blue-600 hover:underline">Login</a>
        </p>
        <p class="mt-2 text-center text-gray-600">
            ត្រឡប់ទៅ <a href="../index.php" class="text-blue-600 hover:underline">ទំព័រដើម</a>
        </p>
    </div>
</body>
</html>