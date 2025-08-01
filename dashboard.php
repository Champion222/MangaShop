<?php

session_start();
include 'db_connect.php'; // Ensure this path is correct for your database connection, and it's configured for SQL Server

if (!isset($_SESSION['user_id'])) {
    header("Location: ./form/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_profile_picture = isset($_SESSION['user_profile_picture']) ? htmlspecialchars($_SESSION['user_profile_picture']) : 'https://via.placeholder.com/40';

$orders = [];
$message = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $message = "ការបញ្ជាទិញត្រូវបានលុបចោលដោយជោគជ័យ។";
    } elseif ($_GET['status'] == 'error') {
        $message = "មានបញ្ហាក្នុងការលុបចោលការបញ្ជាទិញ។ សូមព្យាយាមម្តងទៀត។";
    } elseif ($_GET['status'] == 'unauthorized') {
        $message = "អ្នកមិនមានសិទ្ធិលុបចោលការបញ្ជាទិញនេះទេ។";
    } elseif ($_GET['status'] == 'notfound') {
        $message = "ការបញ្ជាទិញមិនត្រូវបានរកឃើញទេ។";
    }
}

// Prepare the SQL Server statement
$sql = "SELECT o.id AS order_id, p.name, p.price, o.order_date FROM orders o JOIN products p ON o.product_id = p.id WHERE o.user_id = ? ORDER BY o.order_date DESC";
$params = array($user_id); // Parameters for the query

// Execute the query using sqlsrv_query
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    // Handle query execution error
    die(print_r(sqlsrv_errors(), true));
}

// Fetch results using sqlsrv_fetch_array
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // For datetime fields in SQL Server, you often get a DateTime object.
    // Convert it to a string if you just want to display it.
    if ($row['order_date'] instanceof DateTime) {
        $row['order_date'] = $row['order_date']->format('Y-m-d H:i:s');
    }
    $orders[] = $row;
}

sqlsrv_free_stmt($stmt); // Free the statement resources
sqlsrv_close($conn); // Close the connection

?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NxaYGzz Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
            background-color: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 9999px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .profile-btn:hover {
            background-color: #2563eb;
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
    <nav class="bg-blue-600 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">NxaYGzz Shop</a>
            <div>
                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md"><i class="fa-solid fa-cart-shopping"></i></a>

                    <div class="dropdown">
                        <button class="profile-btn">
                            <img src="<?php echo $user_profile_picture; ?>" alt="Profile Picture" class="profile-img">
                            <span class="font-semibold hidden md:inline"><?php echo $username; ?></span>
                            <i class="fa-solid fa-caret-down ml-1"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="profile.php">Profile</a>
                            <a href="./form/logout.php">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">ប្រវត្តិការបញ្ជាទិញរបស់អ្នក</h1>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-md <?php echo strpos($message, 'ជោគជ័យ') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p class="text-center text-gray-600">អ្នកមិនទាន់មានការបញ្ជាទិញណាមួយនៅឡើយទេ។</p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ឈ្មោះផលិតផល
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                តម្លៃ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                កាលបរិច្ឆេទបញ្ជាទិញ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                លុបចោល
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($order['name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    $<?php echo number_format($order['price'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($order['order_date']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="cancel_order.php?order_id=<?php echo $order['order_id']; ?>"
                                       class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('តើអ្នកប្រាកដជាចង់លុបចោលការបញ្ជាទិញនេះមែនទេ?');">
                                        លុបចោល
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-semibold transition duration-300 ease-in-out">
                ត្រឡប់ទៅហាង
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