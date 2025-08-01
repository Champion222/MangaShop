<?php

session_start();
include '../db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = '';
$error = '';

$admin_username_navbar = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
$admin_profile_picture_navbar = 'https://via.placeholder.com/40/DC2626/FFFFFF?text=AD';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'] ?? '';
    $current_profile_picture = $_POST['current_profile_picture'] ?? '';

    if (!empty($new_username)) {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $params = array(&$new_username, &$admin_id);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $error .= 'កំហុសក្នុងការធ្វើបច្ចុប្បន្នភាពឈ្មោះអ្នកគ្រប់គ្រង: ' . print_r(sqlsrv_errors(), true) . '<br>';
        } else {
            $_SESSION['admin_username'] = $new_username;
            $message .= 'ឈ្មោះអ្នកគ្រប់គ្រងត្រូវបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ។<br>';
        }
        sqlsrv_free_stmt($stmt);
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['profile_picture']['name'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_size < 5000000) {
                $upload_dir = '../uploads/admin_profile_pictures/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $new_file_name = uniqid('admin_profile_') . '.' . $file_ext;
                $upload_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    if (!empty($current_profile_picture) &&
                        !str_contains($current_profile_picture, 'via.placeholder.com') &&
                        file_exists($current_profile_picture)) {
                        unlink($current_profile_picture);
                    }

                    $sql = "UPDATE users SET profile_picture_url = ? WHERE id = ?";
                    $params = array(&$upload_path, &$admin_id);
                    $stmt = sqlsrv_query($conn, $sql, $params);
                    if ($stmt === false) {
                        $error .= 'កំហុសក្នុងការធ្វើបច្ចុប្បន្នភាព URL រូបភាពប្រវត្តិរូបអ្នកគ្រប់គ្រង: ' . print_r(sqlsrv_errors(), true) . '<br>';
                    } else {
                        $_SESSION['admin_profile_picture'] = $upload_path;
                        $message .= 'រូបភាពប្រវត្តិរូបអ្នកគ្រប់គ្រងត្រូវបានផ្ទុកឡើងដោយជោគជ័យ។<br>';
                    }
                    sqlsrv_free_stmt($stmt);
                } else {
                    $error .= 'កំហុសក្នុងការផ្ទុកឡើងរូបភាព។<br>';
                }
            } else {
                $error .= 'ទំហំរូបភាពធំពេក។ (អតិបរមា 5MB)<br>';
            }
        } else {
            $error .= 'ប្រភេទឯកសារមិនត្រូវបានអនុញ្ញាតទេ។ អនុញ្ញាតតែ JPG, JPEG, PNG, GIF ប៉ុណ្ណោះ។<br>';
        }
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error .= 'កំហុសក្នុងការផ្ទុកឡើងរូបភាព: ' . $_FILES['profile_picture']['error'] . '<br>';
    }
}

$sql = "SELECT username, profile_picture_url FROM users WHERE id = ?";
$params = array(&$admin_id);
$stmt = sqlsrv_query($conn, $sql, $params);
$admin_user_data = null;

if ($stmt === false) {
    error_log("Error fetching admin user data: " . print_r(sqlsrv_errors(), true));
} else {
    if (sqlsrv_has_rows($stmt)) {
        $admin_user_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }
    sqlsrv_free_stmt($stmt);
}

sqlsrv_close($conn);

$admin_profile_picture_display = $admin_user_data['profile_picture_url'] ?? 'https://via.placeholder.com/120/DC2626/FFFFFF?text=AD';
if (!str_contains($admin_profile_picture_display, 'via.placeholder.com') &&
    (!file_exists($admin_profile_picture_display) || is_dir($admin_profile_picture_display))) {
    $admin_profile_picture_display = 'https://via.placeholder.com/120/DC2626/FFFFFF?text=AD';
}

$admin_profile_picture_navbar = $admin_profile_picture_display;

?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>កែប្រែប្រវត្តិរូបអ្នកគ្រប់គ្រង</title>
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
        .profile-card {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 28rem;
        }
        .profile-picture-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem auto;
        }
        .profile-picture-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ef4444;
        }
        .upload-button-wrapper {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: #ef4444;
            border-radius: 50%;
            padding: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease-in-out;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .upload-button-wrapper:hover {
            background-color: #dc2626;
        }
        .upload-button-wrapper i {
            color: white;
            font-size: 1.25rem;
        }
        .form-label {
            display: block;
            color: #4a5568;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            outline: none;
        }
        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .action-button:hover {
            transform: translateY(-1px);
        }
        .btn-update {
            background-color: #22c55e;
            color: white;
        }
        .btn-update:hover {
            background-color: #16a34a;
        }
        .btn-back {
            color: #ef4444;
            font-weight: 600;
        }
        .btn-back:hover {
            color: #b91c1c;
        }
        .message-box {
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .message-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }
        .message-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <nav class="bg-red-700 p-4 text-white shadow-md absolute top-0 left-0 right-0 z-10">
        <div class="container mx-auto flex justify-between items-center">
            <a href="admin_dashboard.php" class="text-2xl font-bold">ផ្ទាំងគ្រប់គ្រងអ្នកគ្រប់គ្រង</a>
            <div>
                <div class="flex items-center gap-4">
                    <a href="admin_dashboard.php" class="bg-red-600 hover:bg-red-800 text-white px-4 py-2 rounded-md"><i class="fa-solid fa-gauge-high"></i></a>

                    <div class="dropdown">
                        <button class="profile-btn">
                            <img src="<?php echo $admin_profile_picture_navbar; ?>" alt="Admin Profile Picture" class="profile-img">
                            <span class="font-semibold hidden md:inline"><?php echo $admin_username_navbar; ?></span>
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

    <div class="profile-card">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">កែប្រែប្រវត្តិរូបអ្នកគ្រប់គ្រង</h2>

        <?php if ($message): ?>
            <div class="message-box message-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message-box message-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="admin_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-6 text-center">
                <div class="profile-picture-container">
                    <img src="<?php echo htmlspecialchars($admin_profile_picture_display); ?>" alt="Admin Profile Picture">
                    <label for="profile_picture" class="upload-button-wrapper">
                        <i class="fas fa-camera"></i>
                        <input type="file" name="profile_picture" id="profile_picture" class="hidden" accept="image/*">
                    </label>
                </div>
                <input type="hidden" name="current_profile_picture" value="<?php echo htmlspecialchars($admin_user_data['profile_picture_url'] ?? ''); ?>">
            </div>

            <div class="mb-5">
                <label for="username" class="form-label">ឈ្មោះអ្នកគ្រប់គ្រង:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin_user_data['username'] ?? ''); ?>"
                       class="form-input" required>
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="action-button btn-update">
                    Save
                </button>
                <a href="admin_dashboard.php" class="action-button btn-back">
                    Close
                </a>
            </div>
        </form>
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