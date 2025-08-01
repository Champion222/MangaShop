<?php

session_start();
include '../db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$users = [];
$message = '';

$admin_username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');

$admin_profile_picture = $_SESSION['admin_profile_picture'] ?? 'https://via.placeholder.com/40/DC2626/FFFFFF?text=AD';

if (!str_contains($admin_profile_picture, 'via.placeholder.com') && !file_exists($admin_profile_picture)) {
    $admin_profile_picture = 'https://via.placeholder.com/40/DC2626/FFFFFF?text=AD';
}


if (isset($_GET['status'])) {
    if ($_GET['status'] == 'user_updated') {
        $message = "ព័ត៌មានអ្នកប្រើប្រាស់ត្រូវបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ។";
    } elseif ($_GET['status'] == 'user_deleted') {
        $message = "អ្នកប្រើប្រាស់ត្រូវបានលុបដោយជោគជ័យ។";
    } elseif ($_GET['status'] == 'delete_error') {
        $message = "មានបញ្ហាក្នុងការលុបអ្នកប្រើប្រាស់។";
    } elseif ($_GET['status'] == 'edit_error') {
        $message = "មានបញ្ហាក្នុងការធ្វើបច្ចុប្បន្នភាពអ្នកប្រើប្រាស់។";
    } elseif ($_GET['status'] == 'not_found') {
        $message = "អ្នកប្រើប្រាស់មិនត្រូវបានរកឃើញទេ។";
    }
}

$sql = "SELECT id, username, is_admin, created_at FROM users ORDER BY created_at DESC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    error_log("Error executing query: " . print_r(sqlsrv_errors(), true));
} else {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $users[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NxaYGzz Shop</title>
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
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">បញ្ជីអ្នកប្រើប្រាស់</h1>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-md <?php echo (strpos($message, 'ជោគជ័យ') !== false || strpos($message, 'success') !== false) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <p class="text-center text-gray-600">មិនទាន់មានអ្នកប្រើប្រាស់ណាមួយនៅឡើយទេ។</p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                លេខសម្គាល់
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ឈ្មោះអ្នកប្រើប្រាស់
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                អ្នកគ្រប់គ្រង
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                កាលបរិច្ឆេទបង្កើត
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                សកម្មភាព
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($user['id']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $user['is_admin'] ? 'បាទ/ចាស' : 'ទេ'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php
                                        // sqlsrv_fetch_array returns DateTime objects for datetime columns
                                        if ($user['created_at'] instanceof DateTime) {
                                            echo htmlspecialchars($user['created_at']->format('Y-m-d H:i:s'));
                                        } else {
                                            echo htmlspecialchars($user['created_at']);
                                        }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">
                                        កែប្រែ
                                    </a>
                                    <a href="admin_delete_user.php?id=<?php echo $user['id']; ?>"
                                       class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('តើអ្នកប្រាកដជាចង់លុបអ្នកប្រើប្រាស់នេះមែនទេ?');">
                                        លុប
                                    </a>
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