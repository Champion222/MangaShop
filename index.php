<?php

session_start();
include './db_connect.php'; // This file should now contain SQL Server connection logic

$products = [];
$search_query = '';

// Check if a search query is submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = htmlspecialchars($_GET['search']);

    // Use prepared statement for SQL Server
    // SQL Server uses '?' as a parameter placeholder or named parameters.
    // For simple cases, '?' works with sqlsrv_query or sqlsrv_prepare + sqlsrv_execute.
    $sql = "SELECT id, name, description, price, image_url FROM products WHERE name LIKE ? OR description LIKE ?";
    $params = array('%' . $search_query . '%', '%' . $search_query . '%');

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $products[] = $row;
    }
    sqlsrv_free_stmt($stmt); // Free the statement resources
} else {
    // If no search query, fetch all products
    $sql = "SELECT id, name, description, price, image_url FROM products";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $products[] = $row;
    }
    sqlsrv_free_stmt($stmt); // Free the statement resources
}

sqlsrv_close($conn); // Close the connection

$user_profile_picture = isset($_SESSION['user_profile_picture']) ? htmlspecialchars($_SESSION['user_profile_picture']) : 'https://via.placeholder.com/40';
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest';

// Additional check for user profile picture to handle local paths
// This part remains mostly the same, assuming local paths are correctly handled.
// For web paths, file_exists might not be suitable; consider checking if it's a valid URL.
if (!str_contains($user_profile_picture, 'via.placeholder.com') && !filter_var($user_profile_picture, FILTER_VALIDATE_URL) && !file_exists($user_profile_picture)) {
    $user_profile_picture = 'https://via.placeholder.com/40'; // Fallback if local file not found and not a valid URL
}

?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>NxaYGzz Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-4">
                        <a href="dashboard.php" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md"><i class="fa-solid fa-cart-shopping"></i></a>

                        <div class="dropdown">
                            <button class="profile-btn">
                                <img src="<?php echo $user_profile_picture; ?>" alt="Profile Picture" class="profile-img">
                                <span class="font-semibold hidden md:inline"><?php echo $username; ?></span>
                                <i class="fa-solid fa-caret-down ml-1"></i>
                            </button>
                            <div class="dropdown-content">
                                <a href="./profile.php">Profile</a>
                                <a href="./form/logout.php">Sign Out</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="./form/login.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md mr-2">ចូល</a>
                    <a href="./form/register.php" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md mr-2">ចុះឈ្មោះ</a>
                    <a href="./admin_page/admin_login.php" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md">ចូលអ្នកគ្រប់គ្រង</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">NxaYGzz Shop</h1>

        <div class="mb-8 flex justify-center">
            <form action="index.php" method="GET" class="flex w-full max-w-lg">
                <input type="text" name="search" placeholder="Reseach Book Manga..." value="<?php echo htmlspecialchars($search_query); ?>"
                       class="flex-grow p-3 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button type="submit" class="bg-blue-500 text-white px-5 py-3 rounded-r-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <?php if (empty($products)): ?>
            <p class="text-center text-gray-600">មិនទាន់មានផលិតផលណាមួយនៅឡើយទេ។ <?php echo !empty($search_query) ? 'សម្រាប់ "' . htmlspecialchars($search_query) . '"' : ''; ?></p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h2>
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-blue-600">$<?php echo number_format($product['price'], 2); ?></span>
                                <button data-product-id="<?php echo $product['id']; ?>"
                                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                        data-product-desc="<?php echo htmlspecialchars($product['description']); ?>"
                                        data-product-price="<?php echo number_format($product['price'], 2); ?>"
                                        data-product-image="<?php echo htmlspecialchars($product['image_url']); ?>"
                                        class="buy-button bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-semibold transition duration-300 ease-in-out">
                                    ទិញឥឡូវ
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="purchaseModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-start">បញ្ជាក់ការបញ្ជាទិញ</h2>
            <div class="flex flex-col md:flex-row items-center md:items-start gap-4 mb-6">
                <img id="modal-product-image" src="" alt="Product Image" class="w-32 h-32 object-cover rounded-md shadow-sm">
                <div class="flex-grow text-center md:text-left">
                    <h3 id="modal-product-name" class="text-xl font-semibold text-gray-800 mb-1"></h3>
                    <p id="modal-product-description" class="text-gray-600 text-sm mb-2"></p>
                    <p class="text-lg font-bold text-blue-600">តម្លៃ: $<span id="modal-product-price"></span></p>
                </div>
            </div>
            <div class="flex justify-end gap-4">
                <button id="closeModalButton"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-md font-semibold transition duration-300 ease-in-out shadow-md">
                    Close
                </button>
                <button id="confirmOrderButton"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md font-semibold transition duration-300 ease-in-out shadow-md">
                    Order
                </button>
            </div>
        </div>
    </div>

    <script>
        var modal = document.getElementById("purchaseModal");

        var span = document.getElementsByClassName("close-button")[0];
        var closeModalBtn = document.getElementById("closeModalButton");
        var confirmOrderBtn = document.getElementById("confirmOrderButton");

        var modalProductName = document.getElementById("modal-product-name");
        var modalProductDescription = document.getElementById("modal-product-description");
        var modalProductPrice = document.getElementById("modal-product-price");
        var modalProductImage = document.getElementById("modal-product-image");
        var currentProductId = null;

        document.querySelectorAll('.buy-button').forEach(button => {
            button.addEventListener('click', function() {
                <?php if (!isset($_SESSION['user_id'])): ?>
                    window.location.href = './form/login.php?redirect=buy&product_id=' + this.dataset.productId;
                <?php else: ?>
                    currentProductId = this.dataset.productId;
                    modalProductName.textContent = this.dataset.productName;
                    modalProductDescription.textContent = this.dataset.productDesc;
                    modalProductPrice.textContent = this.dataset.productPrice;
                    modalProductImage.src = this.dataset.productImage;
                    modal.style.display = "flex";
                <?php endif; ?>
            });
        });

        span.onclick = function() {
            modal.style.display = "none";
        }
        closeModalBtn.onclick = function() {
            modal.style.display = "none";
        }

        confirmOrderBtn.onclick = function() {
            if (currentProductId) {
                window.location.href = 'buy.php?product_id=' + currentProductId;
            }
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

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