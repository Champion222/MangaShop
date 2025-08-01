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

sqlsrv_close($conn);

?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ផ្ទាំងគ្រប់គ្រងអ្នកគ្រប់គ្រង - NxaYGzz Shop</title>
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
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">សូមស្វាគមន៍មកកាន់ផ្ទាំងគ្រប់គ្រង</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="admin_products.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out text-center">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">គ្រប់គ្រងផលិតផល</h2>
                <p class="text-gray-600">បន្ថែម កែសម្រួល លុបផលិតផល</p>
            </a>

            <a href="admin_orders.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out text-center">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">គ្រប់គ្រងការបញ្ជាទិញ</h2>
                <p class="text-gray-600">មើលរាល់ការបញ្ជាទិញរបស់អតិថិជន</p>
            </a>

            <a href="admin_users.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out text-center">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">គ្រប់គ្រងអ្នកប្រើប្រាស់</h2>
                <p class="text-gray-600">មើលបញ្ជីឈ្មោះអ្នកប្រើប្រាស់</p>
            </a>
        </div>

        <div class="mt-8 text-center">
            <a href="../index.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-semibold transition duration-300 ease-in-out">
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