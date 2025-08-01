<?php

session_start();
include '../db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');

$admin_profile_picture = $_SESSION['admin_profile_picture'] ?? 'https://via.placeholder.com/40/DC2626/FFFFFF?text=AD';

if (!str_contains($admin_profile_picture, 'via.placeholder.com') && !file_exists($admin_profile_picture)) {
    $admin_profile_picture = 'https://via.placeholder.com/40/DC2626/FFFFFF?text=AD';
}

$orders = [];
$search_query = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = htmlspecialchars($_GET['search']);
    $sql = "SELECT o.id, u.username, p.name AS product_name, p.price, o.order_date
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN products p ON o.product_id = p.id
            WHERE u.username LIKE ? OR p.name LIKE ? OR CONVERT(NVARCHAR(50), o.id) LIKE ?
            ORDER BY o.order_date DESC";
    $search_param = '%' . $search_query . '%';
    $params = array(&$search_param, &$search_param, &$search_param);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        error_log("Error searching orders: " . print_r(sqlsrv_errors(), true));
    } else {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Format DateTime object to string for display
            if ($row['order_date'] instanceof DateTime) {
                $row['order_date'] = $row['order_date']->format('Y-m-d H:i:s');
            }
            $orders[] = $row;
        }
        sqlsrv_free_stmt($stmt);
    }
} else {
    $sql = "SELECT o.id, u.username, p.name AS product_name, p.price, o.order_date
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN products p ON o.product_id = p.id
            ORDER BY o.order_date DESC";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        error_log("Error fetching all orders: " . print_r(sqlsrv_errors(), true));
    } else {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Format DateTime object to string for display
            if ($row['order_date'] instanceof DateTime) {
                $row['order_date'] = $row['order_date']->format('Y-m-d H:i:s');
            }
            $orders[] = $row;
        }
        sqlsrv_free_stmt($stmt);
    }
}
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្រប់គ្រងការបញ្ជាទិញ - NxaYGzz Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0;
            border-radius: 8px;
            overflow: hidden;
            top: calc(100% + 10px);
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #dc2626;
            color: white;
            padding: 8px 16px;
            border-radius: 9999px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .profile-btn:hover {
            background-color: #b91c1c;
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-red-700 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="admin_dashboard.php" class="text-2xl font-bold">ផ្ទាំងគ្រប់គ្រងអ្នកគ្រប់គ្រង</a>
            <div>
                <div class="flex items-center gap-4">
                    <a href="admin_dashboard.php" class="bg-red-600 hover:bg-red-800 text-white px-4 py-2 rounded-md"><i class="fa-solid fa-gauge-high"></i></a>

                    <div class="dropdown">
                        <button class="profile-btn">
                            <img src="<?php echo $admin_profile_picture; ?>" alt="Admin Profile Picture" class="profile-img">
                            <span class="font-semibold hidden md:inline"><?php echo $admin_username; ?></span>
                            <i class="fa-solid fa-caret-down ml-1"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="admin_profile.php">Profile</a>
                            <a href="../form/logout.php">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">ការបញ្ជាទិញទាំងអស់</h1>

        <div class="mb-8 flex justify-center">
            <form action="admin_orders.php" method="GET" class="flex w-full max-w-lg">
                <input type="text" name="search" placeholder="ស្វែងរកតាមឈ្មោះអ្នកប្រើប្រាស់, ផលិតផល ឬ លេខសម្គាល់បញ្ជាទិញ..." value="<?php echo htmlspecialchars($search_query); ?>"
                       class="flex-grow p-3 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <button type="submit" class="bg-red-500 text-white px-5 py-3 rounded-r-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <?php if (empty($orders)): ?>
            <p class="text-center text-gray-600">មិនទាន់មានការបញ្ជាទិញណាមួយនៅឡើយទេ។ <?php echo !empty($search_query) ? 'សម្រាប់ "' . htmlspecialchars($search_query) . '"' : ''; ?></p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                លេខសម្គាល់បញ្ជាទិញ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ឈ្មោះអ្នកប្រើប្រាស់
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ឈ្មោះផលិតផល
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                តម្លៃ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                កាលបរិច្ឆេទបញ្ជាទិញ
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($order['id']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($order['username']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($order['product_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    $<?php echo number_format($order['price'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($order['order_date']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="admin_dashboard.php" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-md font-semibold transition duration-300 ease-in-out">
                ត្រឡប់ទៅផ្ទាំងគ្រប់គ្រង
            </a>
        </div>
    </div>

    <script>
        window.addEventListener('click', function(event) {
            if (!event.target.matches('.profile-btn') && !event.target.closest('.profile-btn')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        });

        document.querySelector('.profile-btn').addEventListener('click', function(event) {
            event.stopPropagation();
            var dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    </script>
</body>
</html>