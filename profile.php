<?php
session_start();
include './db_connect.php'; // Ensure this path is correct and db_connect.php is configured for SQL Server

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./form/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'] ?? '';
    $current_profile_picture = $_POST['current_profile_picture'] ?? ''; // Hidden field to keep current path

    // Update username
    if (!empty($new_username)) {
        // SQL Server prepared statement using sqlsrv_query for simplicity with parameters
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $params = array($new_username, $user_id);
        $stmt_username = sqlsrv_query($conn, $sql, $params);

        if ($stmt_username === false) {
            $error .= 'កំហុសក្នុងការធ្វើបច្ចុប្បន្នភាពឈ្មោះអ្នកប្រើប្រាស់: ' . print_r(sqlsrv_errors(), true) . '<br>'; // Error updating username.
        } else {
            $_SESSION['username'] = $new_username; // Update session with new username
            $message .= 'ឈ្មោះអ្នកប្រើប្រាស់ត្រូវបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ។<br>'; // Username updated successfully.
        }
        // No need for sqlsrv_free_stmt($stmt_username); here as sqlsrv_query automatically frees by default.
        // If using sqlsrv_prepare/sqlsrv_execute for more complex scenarios, you would use sqlsrv_free_stmt.
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['profile_picture']['name'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_size < 5000000) { // Max 5MB file size
                $upload_dir = 'uploads/profile_pictures/';
                if (!is_dir($upload_dir)) {
                    // Make sure the directory creation is successful
                    if (!mkdir($upload_dir, 0777, true)) {
                        $error .= 'កំហុសក្នុងការបង្កើតថតរូបភាពផ្ទុកឡើង។<br>';
                    }
                }
                
                if (empty($error)) { // Only proceed if directory creation was successful or already exists
                    $new_file_name = uniqid('profile_') . '.' . $file_ext;
                    $upload_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // Delete old profile picture if it's not the default placeholder and is a local file
                        if (!empty($current_profile_picture) &&
                            $current_profile_picture !== 'https://via.placeholder.com/40' &&
                            !filter_var($current_profile_picture, FILTER_VALIDATE_URL) && // Ensure it's not a URL
                            file_exists($current_profile_picture)) {
                            
                            unlink($current_profile_picture);
                        }

                        // Update profile_picture_url in the database
                        $sql_pic = "UPDATE users SET profile_picture_url = ? WHERE id = ?";
                        $params_pic = array($upload_path, $user_id);
                        $stmt_pic = sqlsrv_query($conn, $sql_pic, $params_pic);

                        if ($stmt_pic === false) {
                            $error .= 'កំហុសក្នុងការធ្វើបច្ចុប្បន្នភាព URL រូបភាពប្រវត្តិរូប: ' . print_r(sqlsrv_errors(), true) . '<br>'; // Error updating profile picture URL.
                        } else {
                            $_SESSION['user_profile_picture'] = $upload_path; // Update session with new picture path
                            $message .= 'រូបភាពប្រវត្តិរូបត្រូវបានផ្ទុកឡើងដោយជោគជ័យ។<br>'; // Profile picture uploaded successfully.
                        }
                    } else {
                        $error .= 'កំហុសក្នុងការផ្ទុកឡើងរូបភាព។<br>'; // Error uploading image.
                    }
                }
            } else {
                $error .= 'ទំហំរូបភាពធំពេក។ (អតិបរមា 5MB)<br>'; // Image size too large. (Max 5MB)
            }
        } else {
            $error .= 'ប្រភេទឯកសារមិនត្រូវបានអនុញ្ញាតទេ។ អនុញ្ញាតតែ JPG, JPEG, PNG, GIF ប៉ុណ្ណោះ។<br>'; // Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.
        }
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error .= 'កំហុសក្នុងការផ្ទុកឡើងរូបភាព: កូដកំហុស ' . $_FILES['profile_picture']['error'] . '<br>'; // Error uploading image.
    }
}

// Fetch current user data
// Use sqlsrv_query for SELECT statement
$sql_fetch_user = "SELECT username, profile_picture_url FROM users WHERE id = ?";
$params_fetch_user = array($user_id);
$stmt_fetch_user = sqlsrv_query($conn, $sql_fetch_user, $params_fetch_user);

if ($stmt_fetch_user === false) {
    die('កំហុសក្នុងការទាញយកទិន្នន័យអ្នកប្រើប្រាស់: ' . print_r(sqlsrv_errors(), true));
}

$user = sqlsrv_fetch_array($stmt_fetch_user, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt_fetch_user); // Free statement resources

sqlsrv_close($conn); // Close the connection

// Set default profile picture if not found or invalid
$user_profile_picture = $user['profile_picture_url'] ?? 'https://via.placeholder.com/40';
// Check if it's a default, or if it's a local path that doesn't exist, or if it's a directory
if (empty($user_profile_picture) || 
    (!filter_var($user_profile_picture, FILTER_VALIDATE_URL) && !file_exists($user_profile_picture)) || 
    is_dir($user_profile_picture)) { 
    $user_profile_picture = 'https://via.placeholder.com/40';
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>កែប្រែប្រវត្តិរូប</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">កែប្រែប្រវត្តិរូបរបស់អ្នក</h2>
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-6 text-center">
                <img src="<?php echo htmlspecialchars($user_profile_picture); ?>" alt="Profile Picture" class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-blue-500 mb-3">
                <label for="profile_picture" class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-300 ease-in-out">
                    ផ្លាស់ប្តូររូបភាពប្រវត្តិរូប
                </label>
                <input type="file" name="profile_picture" id="profile_picture" class="hidden" accept="image/*">
                <input type="hidden" name="current_profile_picture" value="<?php echo htmlspecialchars($user_profile_picture); ?>">
            </div>

            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">ឈ្មោះអ្នកប្រើប្រាស់:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       required>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                    ធ្វើបច្ចុប្បន្នភាពប្រវត្តិរូប
                </button>
                <a href="index.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    ត្រលប់ទៅទំព័រដើម
                </a>
            </div>
        </form>
    </div>
</body>
</html>