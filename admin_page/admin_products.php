<?php

session_start();
include '../db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';

$admin_username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');

$admin_profile_picture = $_SESSION['admin_profile_picture'] ?? 'https://via.placeholder.com/40/DC2626/FFFFFF?text=AD';

if (!str_contains($admin_profile_picture, 'via.placeholder.com') && !file_exists($admin_profile_picture)) {
    $admin_profile_picture = 'https://via.placeholder.com/40/DC2626/FFFFFF?text=AD';
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    $file_path = $_POST['file_path'];
    $image_url = $_POST['image_url'];

    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, file_path = ?, image_url = ? WHERE id = ?";
        $params = array(&$name, &$description, &$price, &$file_path, &$image_url, &$product_id);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $message = "មានបញ្ហាក្នុងការកែសម្រួលផលិតផល: " . print_r(sqlsrv_errors(), true);
        } else {
            $message = "ផលិតផលត្រូវបានកែសម្រួលដោយជោគជ័យ។";
        }
        sqlsrv_free_stmt($stmt);
    } else {
        $sql = "INSERT INTO products (name, description, price, file_path, image_url) VALUES (?, ?, ?, ?, ?)";
        $params = array(&$name, &$description, &$price, &$file_path, &$image_url);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $message = "មានបញ្ហាក្នុងការបន្ថែមផលិតផល: " . print_r(sqlsrv_errors(), true);
        } else {
            $message = "ផលិតផលត្រូវបានបន្ថែមដោយជោគជ័យ។";
        }
        sqlsrv_free_stmt($stmt);
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $sql = "DELETE FROM products WHERE id = ?";
    $params = array(&$product_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $message = "មានបញ្ហាក្នុងការលុបផលិតផល: " . print_r(sqlsrv_errors(), true);
    } else {
        $message = "ផលិតផលត្រូវបានលុបដោយជោគជ័យ។";
    }
    sqlsrv_free_stmt($stmt);
}

$products = [];
$sql = "SELECT id, name, description, price, file_path, image_url FROM products ORDER BY id DESC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    error_log("Error fetching products: " . print_r(sqlsrv_errors(), true));
} else {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $products[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}

$edit_product = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $sql = "SELECT id, name, description, price, file_path, image_url FROM products WHERE id = ?";
    $params = array(&$edit_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        error_log("Error fetching product for edit: " . print_r(sqlsrv_errors(), true));
    } else {
        if (sqlsrv_has_rows($stmt)) {
            $edit_product = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
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
    <title>គ្រប់គ្រងផលិតផល - NxaYGzz Shop</title>
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
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">គ្រប់គ្រងផលិតផល</h1>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-md <?php echo strpos($message, 'ជោគជ័យ') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php echo $edit_product ? 'កែសម្រួលផលិតផល' : 'បន្ថែមផលិតផលថ្មី'; ?></h2>
            <form action="admin_products.php" method="POST" class="space-y-4">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($edit_product['id']); ?>">
                <?php endif; ?>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">ឈ្មោះផលិតផល:</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">ការពិពណ៌នា:</label>
                    <textarea id="description" name="description" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">តម្លៃ:</label>
                    <input type="number" id="price" name="price" step="0.01" required value="<?php echo htmlspecialchars($edit_product['price'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="file_path" class="block text-sm font-medium text-gray-700">ផ្លូវឯកសារ (ឧ. /downloads/my_ebook.pdf):</label>
                    <input type="text" id="file_path" name="file_path" required value="<?php echo htmlspecialchars($edit_product['file_path'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">
                        ចំណាំ: ក្នុងកម្មវិធីជាក់ស្តែង នេះនឹងជាការផ្ទុកឯកសារឡើងដោយសុវត្ថិភាព។
                    </p>
                </div>
                <div>
                    <label for="image_url" class="block text-sm font-medium text-gray-700">URL រូបភាព (ឧ. https://example.com/image.jpg):</label>
                    <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($edit_product['image_url'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">
                        ប្រើ URL រូបភាពពេញលេញ។
                    </p>
                </div>
                <button type="submit"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <?php echo $edit_product ? 'រក្សាទុកការផ្លាស់ប្តូរ' : 'បន្ថែមផលិតផល'; ?>
                </button>
            </form>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 mb-4">បញ្ជីផលិតផល</h2>
        <?php if (empty($products)): ?>
            <p class="text-center text-gray-600">មិនទាន់មានផលិតផលណាមួយនៅឡើយទេ។</p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                រូបភាព
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                លេខសម្គាល់
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ឈ្មោះ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                តម្លៃ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ផ្លូវឯកសារ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                សកម្មភាព
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-16 h-16 object-cover rounded-md">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($product['id']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    $<?php echo number_format($product['price'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($product['file_path']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="admin_products.php?action=edit&id=<?php echo $product['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">កែសម្រួល</a>
                                    <a href="admin_products.php?action=delete&id=<?php echo $product['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('តើអ្នកប្រាកដជាចង់លុបផលិតផលនេះមែនទេ?');">លុប</a>
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