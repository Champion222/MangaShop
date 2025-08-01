<?php

session_start();
include '../db_connect.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM users WHERE username = ?";
    $params = array($username);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        error_log("Error fetching user for login: " . print_r(sqlsrv_errors(), true));
        $message = "មានបញ្ហាក្នុងការចូលគណនី។ សូមព្យាយាមម្តងទៀត។";
    } else {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location: ../index.php");
            exit();
        } else {
            $message = "ឈ្មោះអ្នកប្រើប្រាស់ ឬពាក្យសម្ងាត់មិនត្រឹមត្រូវ។";
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
    <title>ចូលគណនី - ហាងឌីជីថល</title>
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
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">ចូលគណនី</h2>
        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST" class="space-y-4">
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
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                ចូល
            </button>
        </form>
        <p class="mt-6 text-center text-gray-600">
            មិនទាន់មានគណនីមែនទេ? <a href="register.php" class="text-blue-600 hover:underline">Register</a>
        </p>
        <p class="mt-2 text-center text-gray-600">
            ត្រឡប់ទៅ <a href="../index.php" class="text-blue-600 hover:underline">ទំព័រដើម</a>
        </p>
    </div>
</body>
</html>